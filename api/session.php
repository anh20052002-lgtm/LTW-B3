<?php
declare(strict_types=1);
require_once __DIR__ . '/_init.php';
require_method('GET');
api_response(true, 'Thông tin phiên làm việc.', ['csrf_token' => csrf_token(), 'admin' => current_admin()]);
