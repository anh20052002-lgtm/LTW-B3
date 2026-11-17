"""HTTP method, JSON, cookie and response-header checks for a local test instance.

Run after starting PHP + MySQL: python tests/protocol.py http://127.0.0.1:8090 --allow-write
Set NEXA_TEST_DATABASE to the separate disposable database configured on the server.
Consumes five lookup attempts; run against a disposable instance, not production.
"""
import http.cookiejar
import json
import sys
import urllib.error
import urllib.parse
import urllib.request
from integration import test_target


base = test_target().rstrip("/") + "/"
jar = http.cookiejar.CookieJar()
http = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
count = 0


def check(condition, label):
    global count
    assert condition, label
    count += 1
    print(f"PASS {count:02d} {label}")


def call(path, data=None, headers=None, method=None):
    request = urllib.request.Request(base + path, data=data, headers=headers or {}, method=method)
    try:
        response = http.open(request, timeout=15)
    except urllib.error.HTTPError as error:
        response = error
    with response:
        raw = response.read().decode()
        return response.code, dict(response.headers.items()), json.loads(raw)


status, headers, session = call("api/session.php")
check(status == 200 and session["success"], "Session endpoint returns successful JSON")
check(headers.get("X-Content-Type-Options") == "nosniff", "Browser MIME sniffing is disabled")
check(headers.get("X-Frame-Options") == "DENY", "Page framing is denied")
check("no-store" in headers.get("Cache-Control", ""), "Session response is not cached")
check("application/json" in headers.get("Content-Type", ""), "API declares its JSON content type")
check(headers.get("Referrer-Policy") == "same-origin", "Referrer information is restricted to the same origin")
policy = headers.get("Content-Security-Policy", "")
check("script-src 'self'" in policy and "frame-ancestors 'none'" in policy, "CSP restricts scripts and embedding")
cookie = headers.get("Set-Cookie", "").lower()
check("httponly" in cookie and "samesite=lax" in cookie, "Session cookie has HttpOnly and SameSite")
expected_cookie_path = urllib.parse.urlparse(base).path.lower()
check("path=" + expected_cookie_path + ";" in cookie, "Session cookie is scoped to the application base path")
token = session["data"]["csrf_token"]
for path in ["api/public/create.php", "api/public/lookup.php", "api/auth/login.php", "api/admin/requests/update.php", "api/admin/customers/delete.php", "api/admin/password.php"]:
    status, headers, payload = call(path)
    check(status == 405 and headers.get("Allow") == "POST", "Wrong method is rejected: " + path)

common = {"X-CSRF-Token": token, "Content-Type": "application/json"}
for path in ["api/admin/reports.php", "api/admin/account.php"]:
    status, headers, payload = call(path, b"{}", common, method="POST")
    check(status == 405 and headers.get("Allow") == "GET", "Read-only account/report endpoint rejects POST: " + path)
status, headers, payload = call("api/auth/login.php", json.dumps({"email": "qa@example.test", "password": "bad\0password"}).encode(), common)
check(status == 422 and "password" in payload["errors"], "Login rejects a null byte before password verification")
status, headers, payload = call("api/public/lookup.php", b"{}", {**common, "X-CSRF-Token": "invalid-token"})
check(status == 403 and payload["success"] is False, "An incorrect CSRF token is rejected before processing JSON")
cases = [
    (b"not-json", common, 400, "Malformed JSON returns400"),
    (b"[]", common, 400, "JSON array is rejected; API expects an object"),
    (b"{}", {**common, "Content-Type": "text/plain"}, 400, "Wrong content type returns400"),
    (b'{"padding":"' + b"a" * 65537 + b'"}', common, 400, "Oversized JSON is rejected"),
    (b'{"tracking_code":"BAD","phone":"abc"}', common, 422, "Invalid lookup fields return422"),
]
for data, request_headers, expected, label in cases:
    status, headers, payload = call("api/public/lookup.php", data, request_headers)
    check(status == expected and payload["success"] is False, label)
    check({"success", "message", "data", "errors"} <= payload.keys(), "Error response keeps the JSON envelope")
    check(isinstance(payload["message"], str) and isinstance(payload["errors"], dict), "Error message and field errors retain stable JSON types")
    check(not any(secret in json.dumps(payload).lower() for secret in ["sqlstate", "password_hash", "pdoexception", "stack trace"]), "Malformed input does not expose SQL or internal exception details")

for path in ["database.sql", "README.md", "AGENTS.md", "MULTIPAGE_PLAN.md", "MULTIPAGE_REPORT.md", "IMPROVEMENT_PLAN.md", "IMPROVEMENT_REPORT.md", ".tools/browser-admin.json", "config/config.example.php", "includes/bootstrap.php", "scripts/create-admin.php", "tests/integration.py"]:
    try:
        response = http.open(base + path, timeout=15)
    except urllib.error.HTTPError as error:
        response = error
    with response:
        check(response.code == 403, "Private project path is denied: " + path)

print(f"\nRESULT: {count} HTTP protocol checks passed.")
