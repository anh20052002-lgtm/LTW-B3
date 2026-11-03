"""Real HTTP + MySQL integration checks. Run only against a disposable database.

Usage: python tests/integration.py http://127.0.0.1:8088
Environment: NEXA_TEST_EMAIL and NEXA_TEST_PASSWORD (or enter at the prompt).
The suite creates/removes its own customer/request records. Lookup rate-limit
checks deliberately consume the local IP's lookup quota; use a test instance.
"""
from __future__ import annotations

import getpass
import http.cookiejar
import json
import os
import sys
import time
import urllib.error
import urllib.parse
import urllib.request


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


def run():
    base = sys.argv[1] if len(sys.argv) > 1 else "http://127.0.0.1:8088"
    email = os.environ.get("NEXA_TEST_EMAIL") or input("Admin email: ")
    password = os.environ.get("NEXA_TEST_PASSWORD") or getpass.getpass("Admin password: ")
    guest = Client(base)
    admin = Client(base)
    suffix = str(int(time.time()))
    contact = {"full_name": "Kiểm thử " + suffix, "phone": "0901234567", "email": "qa@example.test"}
    request = {**contact, "service": "website", "message": "Yêu cầu kiểm thử tích hợp qua HTTP và MySQL thật."}
    created_requests = []
    created_customers = []
    authenticated = False

    try:
        check(guest.session()["admin"] is None, "Public session does not authenticate a visitor")
        for path in ["dashboard.php", "requests/list.php", "requests/detail.php?id=1", "customers/list.php", "customers/detail.php?id=1"]:
            status, result = guest.call("api/admin/" + path)
            check(status == 401, "Unauthenticated Admin API is denied: " + path, result)
        for path in ["requests/create.php", "requests/update.php", "requests/delete.php", "requests/status.php", "customers/create.php", "customers/update.php", "customers/delete.php"]:
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

        admin.session()
        expect(admin, "api/auth/login.php", {"email": email, "password": password}, 403, "Login also requires CSRF", csrf=False)
        before_cookies = [(c.name, c.value) for c in admin.jar]
        expect(admin, "api/auth/login.php", {"email": email, "password": "invalid password"}, 401, "Wrong credentials are rejected")
        expect(admin, "api/auth/login.php", {"email": email, "password": password}, 200, "Admin authenticates with password_verify")
        authenticated = True
        after_cookies = [(c.name, c.value) for c in admin.jar]
        check(before_cookies != after_cookies, "Session ID changes after authentication")
        check(admin.session()["admin"] is not None, "Authentication persists on a subsequent HTTP request")
        dashboard = expect(admin, "api/admin/dashboard.php", None, 200, "Dashboard reads database statistics")
        check({"customers", "total", "new", "processing", "completed"} <= dashboard["stats"].keys(), "Dashboard exposes all five counters", dashboard)
        listed = expect(admin, "api/admin/requests/list.php?search=" + urllib.parse.quote(tracking), None, 200, "Search returns the request created by the visitor")
        check(len(listed["items"]) == 1 and listed["items"][0]["tracking_code"] == tracking, "Created public data survives a new request")
        public_id = int(listed["items"][0]["id"])
        created_requests.append(public_id)
        check(listed["items"][0]["customer_id"] is None, "A visitor is not automatically merged into a customer")

        customer_body = {**contact, "company": "Công ty kiểm thử", "notes": "Tạo từ bộ kiểm thử tự động."}
        customer = expect(admin, "api/admin/customers/create.php", customer_body, 201, "Admin creates a customer")
        customer_id = int(customer["id"])
        created_customers.append(customer_id)
        stored = expect(admin, "api/admin/customers/detail.php?id=" + str(customer_id), None, 200, "Read customer detail")
        check(stored["full_name"] == contact["full_name"], "Customer detail matches submitted data", stored)
        expect(admin, "api/admin/customers/update.php", {**customer_body, "id": customer_id, "company": "Công ty đã sửa"}, 200, "Admin updates customer information")
        customer_results = expect(admin, "api/admin/customers/list.php?search=" + urllib.parse.quote(suffix), None, 200, "AJAX customer search reads stored data")
        check(any(int(row["id"]) == customer_id and row["company"] == "Công ty đã sửa" for row in customer_results["items"]), "Customer update is persisted")

        request_body = {**request, "status": "processing", "customer_id": customer_id}
        manual = expect(admin, "api/admin/requests/create.php", request_body, 201, "Admin creates a linked consultation request")
        request_id = int(manual["id"])
        created_requests.append(request_id)
        detail = expect(admin, "api/admin/requests/detail.php?id=" + str(request_id), None, 200, "Admin reads consultation detail")
        check(int(detail["customer_id"]) == customer_id and detail["status"] == "processing", "Request retains its explicit customer relationship")
        expect(admin, "api/admin/requests/update.php", {**request_body, "id": request_id, "service": "crm", "message": "Nội dung đã sửa bằng AJAX."}, 200, "Admin updates service and request content")
        expect(admin, "api/admin/requests/status.php", {"id": request_id, "status": "completed"}, 200, "Admin changes request status")
        detail = expect(admin, "api/admin/requests/detail.php?id=" + str(request_id), None, 200, "Request update survives a fresh HTTP read")
        check(detail["service"] == "crm" and detail["status"] == "completed" and detail["message"] == "Nội dung đã sửa bằng AJAX.", "All edited request fields persist", detail)
        filtered = expect(admin, "api/admin/requests/list.php?status=completed&service=crm&search=" + suffix, None, 200, "Combined search and filters work")
        check(any(int(item["id"]) == request_id for item in filtered["items"]), "Filtered result includes updated request")
        page = expect(admin, "api/admin/requests/list.php?per_page=5&page=1&sort=created_asc", None, 200, "Server supports sorting and pagination")
        check(len(page["items"]) <= 5 and {"page", "per_page", "total", "total_pages"} <= page["pagination"].keys(), "Pagination envelope is complete", page)
        expect(admin, "api/admin/requests/list.php?search=" + urllib.parse.quote("' OR 1=1 --"), None, 200, "SQL-like search text is treated as data")
        expect(admin, "api/admin/requests/status.php", {"id": request_id, "status": "invalid"}, 422, "Server rejects an unsupported status")
        expect(admin, "api/admin/requests/update.php", {**request_body, "id": request_id, "customer_id": 2147483647}, 422, "Server rejects a nonexistent customer relationship")
        expect(admin, "api/admin/requests/delete.php", {"id": request_id}, 403, "Authenticated mutation still needs CSRF", csrf=False)
        expect(admin, "api/admin/customers/delete.php", {"id": customer_id}, 200, "Admin deletes a customer")
        created_customers.remove(customer_id)
        detail = expect(admin, "api/admin/requests/detail.php?id=" + str(request_id), None, 200, "Deleting a customer keeps historical requests")
        check(detail["customer_id"] is None, "Foreign key SET NULL preserves request history")
        expect(admin, "api/admin/requests/delete.php", {"id": request_id}, 200, "Admin deletes a consultation request")
        created_requests.remove(request_id)
        expect(admin, "api/admin/requests/detail.php?id=" + str(request_id), None, 404, "Deleted request no longer exists in the database")
        expect(admin, "api/admin/requests/delete.php", {"id": public_id}, 200, "Clean up public test request")
        created_requests.remove(public_id)

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
