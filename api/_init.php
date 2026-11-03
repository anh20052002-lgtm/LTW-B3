<?php
declare(strict_types=1);

function api_response(bool $success, string $message, mixed $data = null, array $errors = [], int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, private');
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data, 'errors' => (object)$errors], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

set_exception_handler(function (Throwable $exception): void {
    error_log('[Nexa API] ' . $exception->getMessage());
    if ($exception instanceof PDOException || $exception instanceof RuntimeException) {
        api_response(false, 'Chưa thể kết nối hệ thống dữ liệu. Vui lòng thử lại sau hoặc liên hệ quản trị viên.', null, [], 503);
    }
    api_response(false, 'Hệ thống gặp sự cố. Vui lòng thử lại sau.', null, [], 500);
});

require_once dirname(__DIR__) . '/includes/bootstrap.php';

function require_method(string $method): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $method) {
        header('Allow: ' . $method);
        api_response(false, 'Phương thức không được hỗ trợ.', null, [], 405);
    }
}

function require_admin_api(): array
{
    $admin = current_admin();
    if (!$admin) {
        api_response(false, 'Vui lòng đăng nhập tài khoản quản trị.', null, [], 401);
    }
    return $admin;
}

function verify_csrf(): void
{
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        api_response(false, 'Phiên biểu mẫu đã thay đổi. Vui lòng tải lại trang rồi thử lại.', null, [], 403);
    }
}

function json_input(): array
{
    $type = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
    if ($type !== 'application/json') {
        api_response(false, 'Nội dung yêu cầu phải là JSON.', null, [], 400);
    }
    if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 65536) {
        api_response(false, 'Dữ liệu gửi lên vượt quá giới hạn.', null, [], 400);
    }
    $raw = file_get_contents('php://input', false, null, 0, 65537);
    if ($raw === false || strlen($raw) > 65536) {
        api_response(false, 'Dữ liệu gửi lên vượt quá giới hạn.', null, [], 400);
    }
    try {
        $input = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        api_response(false, 'Dữ liệu JSON không hợp lệ.', null, [], 400);
    }
    if (!is_array($input) || !str_starts_with(ltrim($raw), '{')) {
        api_response(false, 'Dữ liệu JSON phải là một đối tượng.', null, [], 400);
    }
    return $input;
}

function require_valid(array $errors): void
{
    if ($errors) {
        api_response(false, 'Vui lòng kiểm tra lại thông tin đã nhập.', null, $errors, 422);
    }
}

function positive_id(mixed $id): int
{
    $id = is_scalar($id) ? filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
    if ($id === false) {
        api_response(false, 'Mã dữ liệu không hợp lệ.', null, ['id' => 'ID phải là số nguyên dương.'], 422);
    }
    return (int)$id;
}

function record_or_404(string $table, int $id): array
{
    $record = find_record($table, $id);
    if (!$record) {
        api_response(false, 'Dữ liệu không tồn tại hoặc đã được xóa.', null, [], 404);
    }
    return $record;
}

function pagination(int $total, int $page, int $perPage): array
{
    $pages = max(1, (int)ceil($total / $perPage));
    return ['page' => min($page, $pages), 'per_page' => $perPage, 'total' => $total, 'total_pages' => $pages];
}

function list_options(): array
{
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = (int)($_GET['per_page'] ?? 10);
    $perPage = in_array($perPage, [5, 10, 20, 25, 50, 100], true) ? $perPage : 10;
    $search = is_string($_GET['search'] ?? '') ? trim($_GET['search'] ?? '') : '';
    if (string_length($search) > 100) {
        api_response(false, 'Từ khóa tìm kiếm tối đa 100 ký tự.', null, ['search' => 'Từ khóa quá dài.'], 422);
    }
    $sort = is_string($_GET['sort'] ?? '') ? ($_GET['sort'] ?? '') : '';
    return [$page, $perPage, $search, $sort];
}

function like_term(string $term): string
{
    // Escape SQL LIKE wildcards so search terms are literal text.
    return '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term) . '%';
}
