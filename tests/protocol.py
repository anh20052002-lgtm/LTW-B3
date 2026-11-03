"""HTTP method, JSON, cookie and response-header checks for a local test instance.

Run after starting PHP + MySQL: python tests/protocol.py http://127.0.0.1:8088
Consumes five lookup attempts; run against a disposable instance, not production.
"""
import http.cookiejar
import json
import sys
import urllib.error
import urllib.parse
import urllib.request


base = (sys.argv[1] if len(sys.argv) > 1 else "http://127.0.0.1:8088").rstrip("/") + "/"
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
cookie = headers.get("Set-Cookie", "").lower()
check("httponly" in cookie and "samesite=lax" in cookie, "Session cookie has HttpOnly and SameSite")
expected_cookie_path = urllib.parse.urlparse(base).path.lower()
check("path=" + expected_cookie_path + ";" in cookie, "Session cookie is scoped to the application base path")
token = session["data"]["csrf_token"]
for path in ["api/public/create.php", "api/public/lookup.php", "api/auth/login.php"]:
    status, headers, payload = call(path)
    check(status == 405 and headers.get("Allow") == "POST", "Wrong method is rejected: " + path)

common = {"X-CSRF-Token": token, "Content-Type": "application/json"}
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

for path in ["database.sql", "README.md", ".tools/browser-admin.json", "config/config.example.php", "includes/bootstrap.php", "scripts/create-admin.php", "tests/integration.py"]:
    try:
        response = http.open(base + path, timeout=15)
    except urllib.error.HTTPError as error:
        response = error
    with response:
        check(response.code == 403, "Private project path is denied: " + path)

print(f"\nRESULT: {count} HTTP protocol checks passed.")
