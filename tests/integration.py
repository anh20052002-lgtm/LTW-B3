"""Real HTTP + MySQL integration checks. Run only against a disposable database.

Usage: python tests/integration.py http://127.0.0.1:8090 --allow-write
Environment: NEXA_TEST_DATABASE (a disposable database name containing _test),
NEXA_TEST_EMAIL and NEXA_TEST_PASSWORD (or enter credentials at the prompt).
The suite creates/removes its own customer/request records. Lookup rate-limit
checks deliberately consume the local IP's lookup quota; use a test instance.
"""
from __future__ import annotations

import getpass
import argparse
import datetime as dt
import http.cookiejar
import json
import os
import re
import secrets
import sys
import time
import urllib.error
import urllib.parse
import urllib.request


def test_target():
    """Require an explicit operator confirmation before any HTTP mutation.

    The caller must configure the server for the named disposable database;
    a client cannot infer the server's private database configuration.
    """
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("base", nargs="?", default="http://127.0.0.1:8090")
    parser.add_argument("--allow-write", action="store_true", help="confirm this is a disposable test instance")
    args = parser.parse_args()
    database = os.environ.get("NEXA_TEST_DATABASE", "")
    if not args.allow_write or not re.fullmatch(r"[A-Za-z0-9_]+", database) or "test" not in database.split("_"):
        parser.error("Use --allow-write and NEXA_TEST_DATABASE naming a separate disposable database (with a _test token).")
    if os.environ.get("NEXA_DB_NAME", database) != database:
        parser.error("NEXA_DB_NAME and NEXA_TEST_DATABASE must match when both are supplied.")
    print("Disposable database confirmed by operator: " + database)
    return args.base


class Client:
    def __init__(self, base: str):
        self.base = base.rstrip("/") + "/"
        self.jar = http.cookiejar.CookieJar()
        self.http = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar))
        self.token = ""

    def call(self, path, body=None, *, csrf=True, method=None):
        method = method or ("POST" if body is not None else "GET")
        headers = {"Accept": "application/json"}
        if body is not None:
            headers["Content-Type"] = "application/json"
        if csrf and self.token:
            headers["X-CSRF-Token"] = self.token
        request = urllib.request.Request(
            self.base + path,
            data=json.dumps(body).encode() if body is not None else None,
            headers=headers,
            method=method,
        )
        try:
            response = self.http.open(request, timeout=15)
        except urllib.error.HTTPError as error:
            response = error
        with response:
            status = response.code
            raw = response.read().decode("utf-8")
            try:
                payload = json.loads(raw)
            except json.JSONDecodeError as error:
                raise AssertionError(f"{path}: expected JSON; HTTP {status}: {raw[:300]}") from error
            assert {"success", "message", "data", "errors"} <= payload.keys(), (path, payload)
            assert isinstance(payload["success"], bool), payload
            assert isinstance(payload["message"], str) and isinstance(payload["errors"], dict), payload
            return status, payload

    def session(self):
        status, payload = self.call("api/session.php")
        assert status == 200, payload
        self.token = payload["data"]["csrf_token"]
        assert len(self.token) >= 32
        return payload["data"]


checks = 0


def check(condition, label, details=None):
    global checks
    assert condition, f"{label}: {details}"
    checks += 1
    print(f"PASS {checks:02d} {label}")


def expect(client, path, body, status, label, **kwargs):
    actual, result = client.call(path, body, **kwargs)
    check(actual == status, label, result)
    return result["data"]


def all_records(client, resource):
    rows = []
    page = 1
    while True:
        data = expect(client, f"api/admin/{resource}/list.php?per_page=100&page={page}", None, 200, "Read stored " + resource + " for independent report comparison")
        rows.extend(data["items"])
        if page >= data["pagination"]["total_pages"]:
            check(len(rows) == data["pagination"]["total"], "All stored " + resource + " were read for report comparison")
            return rows
        page += 1


def report_checks(admin):
    customers = all_records(admin, "customers")
    requests = all_records(admin, "requests")
    totals = {"customers": len(customers), "total": len(requests), **{status: sum(row["status"] == status for row in requests) for status in ["new", "processing", "completed"]}}
    default = expect(admin, "api/admin/reports.php", None, 200, "Reports default to a30-day calendar window")
    check(default["range"]["days"] == 30, "Default report range is30 days")
    for value in ["0", "1", "8", "365", "wrong", "", "7&days%5B%5D=30"]:
        expect(admin, "api/admin/reports.php?days=" + value, None, 422, "Reports reject unsupported or array day ranges")
    today = dt.datetime.now(dt.timezone(dt.timedelta(hours=7))).date()
    for days in [7, 30, 90]:
        result = expect(admin, f"api/admin/reports.php?days={days}", None, 200, f"Read the{days}-day report from MySQL")
        start = today - dt.timedelta(days=days - 1)
        check(result["range"] == {"days": days, "start_date": start.isoformat(), "end_date": today.isoformat(), "timezone": "Asia/Ho_Chi_Minh"}, f"The{days}-day window includes exactly today and preceding calendar days", result["range"])
        check(result["totals"] == totals, f"The{days}-day report preserves all-time totals", result["totals"])
        selected = [row for row in requests if start.isoformat() <= row["created_at"][:10] <= today.isoformat()]
        period = {"customers": sum(start.isoformat() <= row["created_at"][:10] <= today.isoformat() for row in customers), "total": len(selected), **{status: sum(row["status"] == status for row in selected) for status in ["new", "processing", "completed"]}}
        period["completion_rate"] = round(period["completed"] / period["total"] * 100, 1) if period["total"] else 0
        check(result["period"] == period, f"The{days}-day totals and completion rate match persisted records", result["period"])
        expected = []
        for offset in range(days):
            date = (start + dt.timedelta(days=offset)).isoformat()
            expected.append({"date": date, "customers": sum(row["created_at"][:10] == date for row in customers), "requests": sum(row["created_at"][:10] == date for row in requests)})
        check(result["daily"] == expected, f"The{days}-day daily series has exact dates, real counts and zero-filled gaps")
        check(all(type(row[key]) is int for row in result["daily"] for key in ["customers", "requests"]) and all(type(result["totals"][key]) is int for key in totals), f"The{days}-day report counters are JSON integers")
        check(sum(row["requests"] for row in result["daily"]) == period["total"] and sum(row["customers"] for row in result["daily"]) == period["customers"], f"The{days}-day daily sums agree with its period totals")


def password_checks(admin, base, email, password):
    account = expect(admin, "api/admin/account.php", None, 200, "An authenticated Admin can read account information")
    check(set(account) == {"id", "name", "email", "created_at"} and account["email"] == email and type(account["id"]) is int, "Account JSON exposes only its four public profile fields")
    new_password = "Nexa" + secrets.token_hex(4)  # Exactly12 bytes: the accepted minimum.
    body = {"current_password": password, "new_password": new_password, "confirm_password": new_password}
    expect(admin, "api/admin/password.php", body, 403, "Changing password requires a CSRF token", csrf=False)
    invalid = [
        {},
        {**body, "current_password": []},
        {**body, "new_password": [], "confirm_password": []},
        {**body, "confirm_password": []},
        {**body, "new_password": "a" * 11, "confirm_password": "a" * 11},
        {**body, "new_password": "a" * 73, "confirm_password": "a" * 73},
        {**body, "new_password": "đ" * 37, "confirm_password": "đ" * 37},
        {**body, "new_password": "Nexa-QA-1234\0", "confirm_password": "Nexa-QA-1234\0"},
        {**body, "current_password": "bad\0password"},
        {**body, "confirm_password": "different password"},
    ]
    for data in invalid:
        expect(admin, "api/admin/password.php", data, 422, "Password validation rejects missing, malformed, mismatched or out-of-range input")
    expect(admin, "api/admin/password.php", {**body, "current_password": "incorrect current password"}, 422, "Password change verifies the existing password")
    expect(admin, "api/admin/password.php", {"current_password": password, "new_password": password, "confirm_password": password}, 422, "The replacement password must differ from the current password")

    observer = Client(base)
    observer.session()
    expect(observer, "api/auth/login.php", {"email": email, "password": password}, 200, "A second browser authenticates before the password change")
    observer.session()
    observer_token = observer.token
    old_token = admin.token
    old_cookies = [(cookie.name, cookie.value) for cookie in admin.jar]
    changed = expect(admin, "api/admin/password.php", body, 200, "Admin changes a real hashed password using the12-byte minimum")
    admin.token = changed["csrf_token"]
    fresh = Client(base)
    try:
        check(set(changed) == {"csrf_token"} and admin.token != old_token and len(admin.token) >= 32, "Password change rotates CSRF without disclosing passwords or hashes")
        check(old_cookies != [(cookie.name, cookie.value) for cookie in admin.jar], "Password change regenerates the active session ID")
        current_token = admin.token
        admin.token = old_token
        expect(admin, "api/admin/customers/create.php", {}, 403, "The previous CSRF token is rejected after changing password")
        admin.token = current_token
        retained = expect(admin, "api/admin/account.php", None, 200, "The browser that changed its password remains authenticated")
        check(retained == account, "Password changes leave the Admin profile unchanged")
        expect(observer, "api/admin/account.php", None, 401, "A password change revokes the other authenticated browser")
        check(observer.session()["admin"] is None and observer.token != observer_token, "A revoked browser receives a public session with a fresh CSRF token")
        fresh.session()
        expect(fresh, "api/auth/login.php", {"email": email, "password": password}, 401, "The old password no longer authenticates")
        expect(fresh, "api/auth/login.php", {"email": email, "password": new_password}, 200, "The new password authenticates on a fresh browser")
        fresh.session()
        persisted = expect(fresh, "api/admin/account.php", None, 200, "The changed password and account persist across HTTP requests")
        check(persisted == account, "Fresh authentication still returns the sanitized profile")
    finally:
        restored = expect(admin, "api/admin/password.php", {"current_password": new_password, "new_password": password, "confirm_password": password}, 200, "Restore the disposable Admin password for subsequent browser QA")
        admin.token = restored["csrf_token"]
    expect(fresh, "api/admin/account.php", None, 401, "Restoring a password also revokes other authenticated sessions")
    restored_login = Client(base)
    restored_login.session()
    expect(restored_login, "api/auth/login.php", {"email": email, "password": password}, 200, "The restored password authenticates successfully")
    restored_login.session()
    expect(restored_login, "api/admin/account.php", None, 200, "Restored authentication persists")
    expect(admin, "api/admin/password.php", {**body, "current_password": "incorrect current password"}, 422, "The fifth valid-shaped password attempt is checked")
    expect(admin, "api/admin/password.php", {**body, "current_password": "incorrect current password"}, 429, "Password changes are rate-limited after five valid-shaped attempts")
    expect(restored_login, "api/admin/password.php", {**body, "current_password": "incorrect current password"}, 429, "The password limit is shared by authenticated sessions for the same Admin")
    expect(restored_login, "api/auth/logout.php", {}, 200, "Log out the secondary test browser")


def run():
    base = test_target()
    email = os.environ.get("NEXA_TEST_EMAIL") or input("Admin email: ")
    password = os.environ.get("NEXA_TEST_PASSWORD") or getpass.getpass("Admin password: ")
    guest = Client(base)
    admin = Client(base)
    suffix = str(int(time.time()))
    contact = {"full_name": "Kiểm thử " + suffix + " %_!", "phone": "0901234567", "email": "qa@example.test"}
    request = {**contact, "service": "website", "message": "Yêu cầu kiểm thử tích hợp qua HTTP và MySQL thật."}
    created_requests = []
    created_customers = []
    authenticated = False

    try:
        check(guest.session()["admin"] is None, "Public session does not authenticate a visitor")
        for path in ["dashboard.php", "requests/list.php", "requests/detail.php?id=1", "customers/list.php", "customers/detail.php?id=1", "reports.php", "account.php"]:
            status, result = guest.call("api/admin/" + path)
            check(status == 401, "Unauthenticated Admin API is denied: " + path, result)
        for path in ["requests/create.php", "requests/update.php", "requests/delete.php", "requests/status.php", "customers/create.php", "customers/update.php", "customers/delete.php", "password.php"]:
            status, result = guest.call("api/admin/" + path, {})
            check(status == 401, "Unauthenticated mutation is denied: " + path, result)

        expect(guest, "api/public/create.php", request, 403, "Public create rejects a missing CSRF token", csrf=False)
        invalid = expect(guest, "api/public/create.php", {}, 422, "Public form validates all required fields")
        expect(guest, "api/public/create.php", {**request, "phone": "wrong", "email": "bad"}, 422, "Server rejects malformed contact information")
        created = expect(guest, "api/public/create.php", request, 201, "Visitor creates a real consultation request")
        tracking = created["tracking_code"]
        check(bool(tracking) and len(tracking) >= 12, "A tracking code is returned")
        expect(guest, "api/public/lookup.php", {"tracking_code": tracking, "phone": "0999999999"}, 404, "Tracking requires matching telephone")
        found = expect(guest, "api/public/lookup.php", {"tracking_code": tracking, "phone": contact["phone"]}, 200, "Visitor can look up the new request")
        check(found["status"] == "new", "Public requests start with status new", found)
        check(not ({"full_name", "phone", "email", "message", "customer_id"} & set(found)), "Lookup does not disclose personal data", found)
        check(set(found) == {"tracking_code", "service", "status", "created_at", "updated_at"}, "Public lookup exposes only its documented fields", found)
        found_canonical = expect(guest, "api/public/lookup.php", {"tracking_code": tracking.lower(), "phone": "+84 901 234567"}, 200, "Lookup normalizes tracking code and Vietnamese telephone")
        check(found_canonical["tracking_code"] == tracking, "Canonical lookup still resolves the same request")

        admin.session()
        expect(admin, "api/auth/login.php", {"email": email, "password": password}, 403, "Login also requires CSRF", csrf=False)
        before_cookies = [(c.name, c.value) for c in admin.jar]
        old_token = admin.token
        expect(admin, "api/auth/login.php", {"email": email, "password": "invalid password"}, 401, "Wrong credentials are rejected")
        expect(admin, "api/auth/login.php", {"email": email, "password": password}, 200, "Admin authenticates with password_verify")
        authenticated = True
        after_cookies = [(c.name, c.value) for c in admin.jar]
        check(before_cookies != after_cookies, "Session ID changes after authentication")
        check(admin.session()["admin"] is not None, "Authentication persists on a subsequent HTTP request")
        current_token = admin.token
        check(current_token != old_token, "Login rotates the CSRF token")
        admin.token = old_token
        expect(admin, "api/admin/customers/create.php", {**contact, "company": "", "notes": ""}, 403, "A pre-login CSRF token cannot authorize an Admin mutation")
        admin.token = current_token
        dashboard = expect(admin, "api/admin/dashboard.php", None, 200, "Dashboard reads database statistics")
        check({"customers", "total", "new", "processing", "completed"} <= dashboard["stats"].keys(), "Dashboard exposes all five counters", dashboard)
        stats = dashboard["stats"]
        check(all(isinstance(value, int) for value in stats.values()) and stats["total"] == stats["new"] + stats["processing"] + stats["completed"], "Dashboard counters have integer types and consistent totals", stats)
        listed = expect(admin, "api/admin/requests/list.php?search=" + urllib.parse.quote(tracking), None, 200, "Search returns the request created by the visitor")
        check(len(listed["items"]) == 1 and listed["items"][0]["tracking_code"] == tracking, "Created public data survives a new request")
        public_id = int(listed["items"][0]["id"])
        created_requests.append(public_id)
        check(listed["items"][0]["customer_id"] is None, "A visitor is not automatically merged into a customer")
        literal = expect(admin, "api/admin/requests/list.php?search=" + urllib.parse.quote("%_!"), None, 200, "Request search accepts literal SQL wildcard characters")
        check(any(int(row["id"]) == public_id for row in literal["items"]) and all("%_!" in row["full_name"] for row in literal["items"]), "Percent, underscore and escape characters do not expand request search", literal)

        customer_body = {**contact, "company": "Công ty kiểm thử", "notes": "Tạo từ bộ kiểm thử tự động."}
        customer = expect(admin, "api/admin/customers/create.php", customer_body, 201, "Admin creates a customer")
        customer_id = int(customer["id"])
        created_customers.append(customer_id)
        stored = expect(admin, "api/admin/customers/detail.php?id=" + str(customer_id), None, 200, "Read customer detail")
        check(stored["full_name"] == contact["full_name"], "Customer detail matches submitted data", stored)
        check(stored["request_count"] == 0, "A new customer has zero linked requests")
        expect(admin, "api/admin/customers/update.php", {**customer_body, "id": customer_id, "company": "Công ty đã sửa"}, 200, "Admin updates customer information")
        customer_results = expect(admin, "api/admin/customers/list.php?search=" + urllib.parse.quote(suffix), None, 200, "AJAX customer search reads stored data")
        check(any(int(row["id"]) == customer_id and row["company"] == "Công ty đã sửa" for row in customer_results["items"]), "Customer update is persisted")
        customer_literal = expect(admin, "api/admin/customers/list.php?search=" + urllib.parse.quote("%_!"), None, 200, "Customer search accepts literal SQL wildcard characters")
        check(any(int(row["id"]) == customer_id for row in customer_literal["items"]) and all("%_!" in row["full_name"] for row in customer_literal["items"]), "Customer wildcard escaping returns only literal matches", customer_literal)
        unlinked = expect(admin, "api/admin/customers/list.php?has_requests=without&search=" + suffix, None, 200, "Customer filter finds contacts without requests")
        check(unlinked["pagination"]["total"] == 1 and int(unlinked["items"][0]["id"]) == customer_id, "Without-requests filter count matches its rows", unlinked)
        linked_empty = expect(admin, "api/admin/customers/list.php?has_requests=with&search=" + suffix, None, 200, "With-requests filter excludes an unlinked contact")
        check(linked_empty["items"] == [] and linked_empty["pagination"]["total"] == 0, "Empty customer filter keeps the pagination envelope")
        for query in ["has_requests=invalid", "has_requests%5B%5D=with"]:
            expect(admin, "api/admin/customers/list.php?" + query, None, 422, "Customer relation filter rejects invalid input: " + query)
        expect(admin, "api/admin/customers/create.php", {**customer_body, "full_name": "A"}, 422, "Customer validation rejects a name shorter than two characters")
        expect(admin, "api/admin/customers/create.php", {**customer_body, "company": "x" * 191}, 422, "Customer validation enforces the company length limit")
        expect(admin, "api/admin/customers/create.php", {**customer_body, "notes": "đ" * 3001}, 422, "Customer validation enforces the Unicode note length limit")

        request_body = {**request, "status": "processing", "customer_id": customer_id}
        manual = expect(admin, "api/admin/requests/create.php", request_body, 201, "Admin creates a linked consultation request")
        request_id = int(manual["id"])
        created_requests.append(request_id)
        detail = expect(admin, "api/admin/requests/detail.php?id=" + str(request_id), None, 200, "Admin reads consultation detail")
        check(int(detail["customer_id"]) == customer_id and detail["status"] == "processing", "Request retains its explicit customer relationship")
        linked_customer = expect(admin, "api/admin/customers/detail.php?id=" + str(customer_id), None, 200, "Customer detail recalculates its linked request count")
        check(linked_customer["request_count"] == 1, "Linked customer request_count is one")
        linked = expect(admin, "api/admin/customers/list.php?has_requests=with&search=" + suffix + "&page=999&per_page=5", None, 200, "Customer relation filter combines with search and pagination")
        check(linked["pagination"]["total"] == 1 and linked["pagination"]["page"] == 1 and linked["items"][0]["request_count"] == 1, "Filtered customer count, clamped page and rows agree", linked)
        linked_requests = expect(admin, "api/admin/requests/list.php?customer_id=" + str(customer_id) + "&status=processing&service=website&search=" + suffix, None, 200, "Customer request filter combines with existing search and filters")
        check(linked_requests["pagination"]["total"] == 1 and int(linked_requests["items"][0]["id"]) == request_id, "Customer request filter excludes the unlinked public request")
        for query in ["customer_id=0", "customer_id=-1", "customer_id=wrong", "customer_id%5B%5D=1"]:
            expect(admin, "api/admin/requests/list.php?" + query, None, 422, "Request customer filter rejects invalid input: " + query)
        unlinked_after = expect(admin, "api/admin/customers/list.php?has_requests=without&search=" + suffix, None, 200, "Without-requests filter refreshes after linking")
        check(unlinked_after["items"] == [] and unlinked_after["pagination"]["total"] == 0, "Linking changes customer filter membership")
        expect(admin, "api/admin/requests/update.php", {**request_body, "id": request_id, "service": "crm", "message": "Nội dung đã sửa bằng AJAX."}, 200, "Admin updates service and request content")
        omit_link = {**request, "id": request_id, "status": "processing", "service": "crm", "message": "Nội dung đã sửa bằng AJAX."}
        preserved = expect(admin, "api/admin/requests/update.php", omit_link, 200, "Updating a request without customer_id preserves its existing link")
        check(int(preserved["customer_id"]) == customer_id, "Omitted customer_id does not silently unlink the request")
        expect(admin, "api/admin/requests/status.php", {"id": request_id, "status": "completed"}, 200, "Admin changes request status")
        detail = expect(admin, "api/admin/requests/detail.php?id=" + str(request_id), None, 200, "Request update survives a fresh HTTP read")
        check(detail["service"] == "crm" and detail["status"] == "completed" and detail["message"] == "Nội dung đã sửa bằng AJAX.", "All edited request fields persist", detail)
        filtered = expect(admin, "api/admin/requests/list.php?status=completed&service=crm&search=" + suffix, None, 200, "Combined search and filters work")
        check(any(int(item["id"]) == request_id for item in filtered["items"]), "Filtered result includes updated request")
        page = expect(admin, "api/admin/requests/list.php?per_page=5&page=1&sort=created_asc", None, 200, "Server supports sorting and pagination")
        check(len(page["items"]) <= 5 and {"page", "per_page", "total", "total_pages"} <= page["pagination"].keys(), "Pagination envelope is complete", page)
        check([(row["created_at"], int(row["id"])) for row in page["items"]] == sorted((row["created_at"], int(row["id"])) for row in page["items"]), "created_asc sorting is deterministic")
        last_page = expect(admin, "api/admin/requests/list.php?per_page=5&page=99999", None, 200, "An oversized page number clamps to the last page")
        check(last_page["pagination"]["page"] == last_page["pagination"]["total_pages"] and 0 < len(last_page["items"]) <= 5, "Last-page pagination returns actual rows", last_page)
        fallback = expect(admin, "api/admin/requests/list.php?per_page=999&sort=" + urllib.parse.quote("id;DROP TABLE customers"), None, 200, "Unsupported page size and SQL-like sort use safe defaults")
        check(fallback["pagination"]["per_page"] == 10, "Pagination size is restricted to the server whitelist")
        injection = expect(admin, "api/admin/requests/list.php?search=" + urllib.parse.quote("' OR 1=1 --"), None, 200, "SQL-like search text is treated as data")
        check(injection["items"] == [] and injection["pagination"]["total"] == 0, "SQL injection text cannot bypass the request search predicate")
        customer_injection = expect(admin, "api/admin/customers/list.php?search=" + urllib.parse.quote("' OR 1=1 --"), None, 200, "Customer search treats SQL injection text as data")
        check(customer_injection["items"] == [] and customer_injection["pagination"]["total"] == 0, "SQL injection text cannot bypass the customer search predicate")
        for query in ["status=invalid", "service=invalid", "search=" + "a" * 101]:
            expect(admin, "api/admin/requests/list.php?" + query, None, 422, "Request list rejects an invalid filter or overlong search")
        expect(admin, "api/admin/requests/detail.php?id=0", None, 422, "Request detail rejects a nonpositive ID")
        expect(admin, "api/admin/requests/detail.php?id=2147483647", None, 404, "Request detail returns404 for an absent record")
        expect(admin, "api/admin/requests/status.php", {"id": request_id, "status": "invalid"}, 422, "Server rejects an unsupported status")
        expect(admin, "api/admin/requests/update.php", {**request_body, "id": request_id, "customer_id": 2147483647}, 422, "Server rejects a nonexistent customer relationship")
        expect(admin, "api/admin/requests/delete.php", {"id": request_id}, 403, "Authenticated mutation still needs CSRF", csrf=False)
        unchanged = expect(admin, "api/admin/requests/detail.php?id=" + str(request_id), None, 200, "Invalid updates and missing CSRF leave the request readable")
        check(unchanged["status"] == "completed" and int(unchanged["customer_id"]) == customer_id, "Rejected mutations do not modify persisted fields")
        expect(admin, "api/admin/customers/delete.php", {"id": customer_id}, 200, "Admin deletes a customer")
        created_customers.remove(customer_id)
        detail = expect(admin, "api/admin/requests/detail.php?id=" + str(request_id), None, 200, "Deleting a customer keeps historical requests")
        check(detail["customer_id"] is None, "Foreign key SET NULL preserves request history")
        empty_link = expect(admin, "api/admin/requests/list.php?customer_id=" + str(customer_id), None, 200, "Filtering a deleted customer ID returns an empty list")
        check(empty_link["items"] == [] and empty_link["pagination"]["total"] == 0, "SET NULL also removes deleted-customer filter membership")
        expect(admin, "api/admin/requests/delete.php", {"id": request_id}, 200, "Admin deletes a consultation request")
        created_requests.remove(request_id)
        expect(admin, "api/admin/requests/detail.php?id=" + str(request_id), None, 404, "Deleted request no longer exists in the database")
        expect(admin, "api/admin/requests/delete.php", {"id": public_id}, 200, "Clean up public test request")
        created_requests.remove(public_id)

        report_checks(admin)
        password_checks(admin, base, email, password)

        expect(admin, "api/auth/logout.php", {}, 200, "Admin logs out")
        authenticated = False
        status, result = admin.call("api/admin/requests/list.php")
        check(status == 401, "Logout revokes Admin API access", result)

        limited = False
        for _ in range(70):
            status, result = guest.call("api/public/lookup.php", {"tracking_code": tracking, "phone": contact["phone"]})
            if status == 429:
                limited = True
                break
            assert status == 404, ("Unexpected response before quota exhaustion", result)
        check(limited, "Lookup enforces an HTTP 429 rate limit")
        fresh = Client(base)
        fresh.session()
        status, result = fresh.call("api/public/lookup.php", {"tracking_code": tracking, "phone": contact["phone"]})
        check(status == 429, "Rate limit survives a new browser session", result)
        print(f"\nRESULT: {checks} checks passed against real HTTP/PHP/MySQL.")
    finally:
        if authenticated:
            for request_id in created_requests:
                admin.call("api/admin/requests/delete.php", {"id": request_id})
            for customer_id in created_customers:
                admin.call("api/admin/customers/delete.php", {"id": customer_id})


if __name__ == "__main__":
    try:
        run()
    except Exception as error:
        print(f"FAIL: {error}", file=sys.stderr)
        raise
