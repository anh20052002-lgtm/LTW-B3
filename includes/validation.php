<?php
declare(strict_types=1);

function text_value(array $input, string $key): string
{
    return isset($input[$key]) && is_string($input[$key]) ? trim($input[$key]) : '';
}

function normalize_phone(string $phone): string
{
    return preg_replace('/[\s.()\-]/', '', $phone) ?? '';
}

function valid_phone(string $phone): bool
{
    return preg_match('/^(?:0[35789][0-9]{8}|\+84[35789][0-9]{8})$/D', $phone) === 1;
}

function canonical_phone(string $phone): string
{
    $phone = normalize_phone($phone);
    return str_starts_with($phone, '+84') ? '0' . substr($phone, 3) : $phone;
}

function string_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : (preg_match_all('/./us', $value) ?: 0);
}

function contact_fields(array $input): array
{
    return [
        'full_name' => text_value($input, 'full_name'),
        'phone' => canonical_phone(text_value($input, 'phone')),
        'email' => text_value($input, 'email'),
    ];
}

function validate_contact(array $data): array
{
    $errors = [];
    if (string_length($data['full_name']) < 2 || string_length($data['full_name']) > 100) {
        $errors['full_name'] = 'Họ tên cần từ 2 đến 100 ký tự.';
    }
    if (!valid_phone($data['phone'])) {
        $errors['phone'] = 'Nhập số di động Việt Nam hợp lệ (10 số hoặc +84).';
    }
    if ($data['email'] !== '' && (strlen($data['email']) > 190 || !filter_var($data['email'], FILTER_VALIDATE_EMAIL))) {
        $errors['email'] = 'Địa chỉ email không hợp lệ (tối đa 190 ký tự).';
    }
    return $errors;
}

function request_fields(array $input, bool $admin = true): array
{
    $data = contact_fields($input);
    $data['service'] = text_value($input, 'service');
    $data['message'] = text_value($input, 'message');
    $data['status'] = $admin ? text_value($input, 'status') : 'new';
    if ($data['status'] === '') {
        $data['status'] = 'new';
    }
    $data['customer_id'] = null;
    if ($admin && isset($input['customer_id']) && $input['customer_id'] !== '' && $input['customer_id'] !== null) {
        $data['customer_id'] = filter_var($input['customer_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: -1;
    }
    return $data;
}

function validate_request(array $data): array
{
    $errors = validate_contact($data);
    if (!array_key_exists($data['service'], service_options())) {
        $errors['service'] = 'Vui lòng chọn một dịch vụ hợp lệ.';
    }
    if (string_length($data['message']) < 10 || string_length($data['message']) > 5000) {
        $errors['message'] = 'Nội dung cần từ 10 đến 5.000 ký tự.';
    }
    if (!array_key_exists($data['status'], status_options())) {
        $errors['status'] = 'Trạng thái không hợp lệ.';
    }
    if ($data['customer_id'] !== null && ($data['customer_id'] < 1 || !find_record('customers', (int)$data['customer_id']))) {
        $errors['customer_id'] = 'Khách hàng được chọn không tồn tại.';
    }
    return $errors;
}

function customer_fields(array $input): array
{
    return contact_fields($input) + ['company' => text_value($input, 'company'), 'notes' => text_value($input, 'notes')];
}

function validate_customer(array $data): array
{
    $errors = validate_contact($data);
    if (string_length($data['company']) > 190) {
        $errors['company'] = 'Tên công ty tối đa 190 ký tự.';
    }
    if (string_length($data['notes']) > 3000) {
        $errors['notes'] = 'Ghi chú tối đa 3.000 ký tự.';
    }
    return $errors;
}
