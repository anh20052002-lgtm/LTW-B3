<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/_init.php';
require_method('POST');
require_admin_api();
verify_csrf();
$_SESSION = [];
session_regenerate_id(true);
api_response(true, 'Đã đăng xuất an toàn.', ['csrf_token' => csrf_token()]);
