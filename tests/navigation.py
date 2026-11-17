"""Real HTTP navigation checks for the multi-page PHP site (standard library only).

Use the same disposable-instance guard and credentials as integration.py:
python tests/navigation.py http://127.0.0.1:8090 --allow-write
This checks rendered HTML and URL contracts; browser interaction/responsiveness
still needs a real browser and is reported separately.
"""
from __future__ import annotations

import getpass
import os
from html.parser import HTMLParser
import urllib.error
import urllib.parse
import urllib.request

from integration import Client, test_target


class Page(HTMLParser):
    def __init__(self, html):
        super().__init__(convert_charrefs=True)
        self.stack = []
        self.ids = set()
        self.forms = set()
        self.fields = set()
        self.anchors = []
        self.nav = []
        self.sidebar = []
        self.assets = []
        self.selected = []
        self.password_fields = []
        self.body = {}
        self.title = ""
        self.h1 = []
        self.headers = self.footers = 0
        self.feed(html)

    def handle_starttag(self, tag, values):
        attrs = dict(values)
        if attrs.get("id"):
            self.ids.add(attrs["id"])
        if tag == "body":
            self.body = attrs
        if tag == "header":
            self.headers += 1
        if tag == "footer":
            self.footers += 1
        if tag == "h1":
            self.h1.append("")
        if tag == "form":
            self.forms.add(attrs.get("id", ""))
        if tag in ["input", "select", "textarea"] and attrs.get("name"):
            self.fields.add(attrs["name"])
        if tag == "input" and attrs.get("type") == "password":
            self.password_fields.append(attrs)
        if tag == "a":
            self.anchors.append(attrs)
            if any(parent.get("id") == "main-nav" for _, parent in self.stack) and "nav-link" in attrs.get("class", "").split():
                self.nav.append(attrs)
            if any(parent.get("id") == "adminSidebar" for _, parent in self.stack) and "nav-item" in attrs.get("class", "").split():
                self.sidebar.append(attrs)
        if tag == "script" and attrs.get("src"):
            self.assets.append(attrs["src"])
        if tag == "link" and attrs.get("rel") == "stylesheet":
            self.assets.append(attrs.get("href", ""))
        if tag == "option" and "selected" in attrs and any(parent.get("name") == "service" for _, parent in self.stack):
            self.selected.append(attrs.get("value", ""))
        if tag not in ["area", "base", "br", "col", "embed", "hr", "img", "input", "link", "meta", "param", "source", "track", "wbr"]:
            self.stack.append((tag, attrs))

    def handle_endtag(self, tag):
        for offset in range(len(self.stack) - 1, -1, -1):
            if self.stack[offset][0] == tag:
                del self.stack[offset:]
                return

    def handle_data(self, data):
        if any(tag == "title" for tag, _ in self.stack):
            self.title += data
        if self.h1 and any(tag == "h1" for tag, _ in self.stack):
            self.h1[-1] += data


count = 0


def check(condition, label, details=None):
    global count
    assert condition, f"{label}: {details}"
    count += 1
    print(f"PASS {count:02d} {label}")


def run():
    base = test_target().rstrip("/") + "/"
    parsed_base = urllib.parse.urlparse(base)
    public = {"index.php": "home", "gioi-thieu.php": "about", "dich-vu.php": "services", "tu-van.php": "consultation", "tra-cuu.php": "lookup", "lien-he.php": "contact"}
    admin_pages = {"admin/index.php": "dashboard", "admin/customers.php": "customers", "admin/requests.php": "requests", "admin/thong-ke.php": "reports", "admin/tai-khoan.php": "account"}
    services = ["website", "crm", "marketing", "consulting"]
    guest = Client(base)
    admin = Client(base)
    cache = {}

    def fetch(path, authenticated=False):
        url = urllib.parse.urljoin(base, path)
        key = (authenticated, url)
        if key in cache:
            return cache[key]
        request = urllib.request.Request(url, headers={"Accept": "text/html"})
        try:
            response = (admin if authenticated else guest).http.open(request, timeout=15)
        except urllib.error.HTTPError as error:
            response = error
        with response:
            html = response.read().decode("utf-8")
            result = (response.code, html, response.geturl(), Page(html))
        cache[key] = result
        return result

    def links_and_assets(page, current_url, authenticated=False):
        errors = []
        for anchor in page.anchors:
            href = anchor.get("href", "")
            if not href or href == "#":
                errors.append("Empty or placeholder href")
                continue
            url = urllib.parse.urljoin(current_url, href)
            target = urllib.parse.urlparse(url)
            if target.scheme not in ["http", "https"] or target.netloc != parsed_base.netloc:
                continue
            if not target.path.startswith(parsed_base.path):
                errors.append("Link leaves the deployment directory: " + href)
                continue
            clean_url = urllib.parse.urlunparse(target._replace(fragment=""))
            status, _, _, target_page = fetch(clean_url, authenticated)
            if status != 200 or (target.fragment and target.fragment not in target_page.ids):
                errors.append(f"Broken link {href} (HTTP{status})")
        for asset in page.assets:
            url = urllib.parse.urljoin(current_url, asset)
            target = urllib.parse.urlparse(url)
            if target.netloc != parsed_base.netloc or not target.path.startswith(parsed_base.path):
                errors.append("Asset leaves the deployment directory: " + asset)
            elif fetch(url, authenticated)[0] != 200:
                errors.append("Missing asset: " + asset)
        return errors

    titles, headings = [], []
    expected_public = {urllib.parse.urljoin(base, path) for path in public}
    for path, key in public.items():
        status, html, final_url, page = fetch(path)
        check(status == 200 and final_url == urllib.parse.urljoin(base, path), "Public page has its own working URL: " + path)
        check(page.body.get("data-public-page") == key and len(page.h1) == 1 and page.h1[0].strip() and page.title.strip(), "Public page has its own identity, title and main heading: " + path)
        titles.append(page.title.strip())
        headings.append(page.h1[0].strip())
        check(page.headers == 1 and page.footers == 1, "Public page renders one shared header and footer: " + path)
        targets = {urllib.parse.urljoin(final_url, anchor.get("href", "")) for anchor in page.nav}
        check(len(page.nav) == 6 and targets == expected_public, "Public main navigation targets all six real pages: " + path, targets)
        active = [anchor for anchor in page.nav if anchor.get("aria-current") == "page"]
        check(len(active) == 1 and "active" in active[0].get("class", "").split() and urllib.parse.urljoin(final_url, active[0]["href"]) == final_url, "Public active state matches its page: " + path)
        check(("consultation-form" in page.forms) == (key == "consultation") and ("lookup-form" in page.forms) == (key == "lookup"), "Public forms belong to their dedicated pages: " + path)
        check(not links_and_assets(page, final_url), "Public links, fragments and local assets resolve: " + path, links_and_assets(page, final_url))
    check(len(set(titles)) == 6 and len(set(headings)) == 6, "Six public pages have distinct titles and primary content headings")
    consultation = fetch("tu-van.php")[3]
    lookup = fetch("tra-cuu.php")[3]
    check({"full_name", "phone", "email", "service", "message"} <= consultation.fields, "The dedicated consultation form retains its existing API field names")
    check({"tracking_code", "phone"} <= lookup.fields, "The dedicated lookup form retains its existing API field names")
    catalog = fetch("dich-vu.php")[3]
    choices = {urllib.parse.parse_qs(urllib.parse.urlparse(anchor.get("href", "")).query).get("service", [""])[0] for anchor in catalog.anchors if "tu-van.php" in anchor.get("href", "")}
    check(set(services) <= choices, "Each service card links to a consultation with its service parameter")
    for service in services:
        status, _, _, page = fetch("tu-van.php?service=" + service)
        check(status == 200 and page.selected == [service], "Consultation preselects the requested service: " + service)
        status, _, url, page = fetch("dich-vu.php?service=" + service)
        check(status == 200 and any("tu-van.php?service=" + service in anchor.get("href", "") for anchor in page.anchors), "Service detail has a real consultation CTA: " + service)
        check(not links_and_assets(page, url), "Service detail links and assets resolve: " + service)
    for query in ["service=unknown", "service=", "service%5B%5D=crm"]:
        check(fetch("dich-vu.php?" + query)[0] == 404, "Unknown, empty or array service details return404")
        status, _, _, page = fetch("tu-van.php?" + query)
        check(status == 200 and page.selected in [[], [""]], "An invalid consultation prefill is safely ignored")

    for path in admin_pages:
        status, _, final_url, _ = fetch(path)
        check(status == 200 and urllib.parse.urlparse(final_url).path == urllib.parse.urlparse(urllib.parse.urljoin(base, "admin/login.php")).path, "Guest Admin page redirects to login: " + path)
    email = os.environ.get("NEXA_TEST_EMAIL") or input("Admin email: ")
    password = os.environ.get("NEXA_TEST_PASSWORD") or getpass.getpass("Admin password: ")
    admin.session()
    status, payload = admin.call("api/auth/login.php", {"email": email, "password": password})
    check(status == 200, "Authenticate the disposable Admin for navigation checks")
    admin.session()
    try:
        expected_admin = {urllib.parse.urljoin(base, path) for path in admin_pages}
        for path, key in admin_pages.items():
            status, html, final_url, page = fetch(path, True)
            check(status == 200 and final_url == urllib.parse.urljoin(base, path) and page.body.get("data-page") == key, "Admin module has its own authenticated page: " + path)
            targets = {urllib.parse.urljoin(final_url, anchor.get("href", "")) for anchor in page.sidebar}
            check(len(page.sidebar) == 5 and targets == expected_admin, "Admin sidebar targets all five modules: " + path)
            active = [anchor for anchor in page.sidebar if anchor.get("aria-current") == "page"]
            check(len(active) == 1 and "active" in active[0].get("class", "").split() and urllib.parse.urljoin(final_url, active[0]["href"]) == final_url, "Admin active state matches its module: " + path)
            check(not links_and_assets(page, final_url, True), "Admin links, fragments and assets resolve: " + path, links_and_assets(page, final_url, True))
            if key == "reports":
                check({"reportDays", "reportChart", "reportDailyBody"} <= page.ids, "Reports page exposes range, chart and daily data controls")
            if key == "account":
                check("passwordForm" in page.forms and len(page.password_fields) == 3 and all(not field.get("value") for field in page.password_fields), "Account password fields are present and never prefilled with credentials")
    finally:
        status, _ = admin.call("api/auth/logout.php", {})
        check(status == 200, "Log out the navigation test session")
    print(f"\nRESULT: {count} multi-page navigation checks passed.")


if __name__ == "__main__":
    run()
