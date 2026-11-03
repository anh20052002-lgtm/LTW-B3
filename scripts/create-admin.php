<?php
declare(strict_types=1);

// CLI only: php scripts/create-admin.php
// For automation, provide NEXA_ADMIN_NAME, NEXA_ADMIN_EMAIL, NEXA_ADMIN_PASSWORD
// through the process environment. Passwords are never command-line arguments.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/includes/bootstrap.php';

function prompt(string $label): string
{
    fwrite(STDOUT, $label);
    $line = fgets(STDIN);
    return $line === false ? '' : trim($line);
}

function secret_prompt(): string
{
    if (PHP_OS_FAMILY === 'Windows') {
        // Read SecureString in a child PowerShell terminal and return only to this process.
        // Never log or print the resulting password.
        $command = 'powershell.exe -NoProfile -Command "$s = Read-Host -AsSecureString \'Mật khẩu (ít nhất 12 ký tự)\'; $p = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($s); try { [Runtime.InteropServices.Marshal]::PtrToStringBSTR($p) } finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($p) }"';
        $value = shell_exec($command);
        return is_string($value) ? rtrim($value, "\r\n") : '';
    }
    fwrite(STDOUT, 'Mật khẩu (ít nhất 12 ký tự): ');
    $state = shell_exec('stty -g');
    if (is_string($state) && trim($state) !== '') {
        shell_exec('stty -echo');
        try {
            $value = fgets(STDIN);
        } finally {
            shell_exec('stty ' . escapeshellarg(trim($state)));
            fwrite(STDOUT, PHP_EOL);
        }
        return $value === false ? '' : rtrim($value, "\r\n");
    }
    fwrite(STDERR, "Không thể ẩn mật khẩu trong terminal này. Hãy cung cấp biến môi trường NEXA_ADMIN_PASSWORD.\n");
    return '';
}

try {
    $name = getenv('NEXA_ADMIN_NAME') ?: prompt('Họ tên Admin: ');
    $email = strtolower(getenv('NEXA_ADMIN_EMAIL') ?: prompt('Email Admin: '));
    $password = getenv('NEXA_ADMIN_PASSWORD');
    if ($password === false) {
        $password = secret_prompt();
    }
    if (string_length($name) < 2 || string_length($name) > 100) {
        throw new InvalidArgumentException('Họ tên cần từ 2 đến 100 ký tự.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        throw new InvalidArgumentException('Email không hợp lệ.');
    }
    // PASSWORD_DEFAULT on PHP 8 uses bcrypt, which accepts at most 72 bytes.
    if (strlen($password) < 12 || strlen($password) > 72) {
        throw new InvalidArgumentException('Mật khẩu cần từ 12 đến 72 byte.');
    }
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id, is_active FROM admins WHERE email = ?');
    $stmt->execute([$email]);
    $existing = $stmt->fetch();
    if ($existing && (bool)$existing['is_active']) {
        throw new InvalidArgumentException('Email đã có tài khoản hoạt động. Script không ghi đè mật khẩu hiện có.');
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    unset($password);
    if ($existing) {
        $stmt = $pdo->prepare('UPDATE admins SET name = ?, password_hash = ?, is_active = 1 WHERE id = ? AND is_active = 0');
        $stmt->execute([$name, $hash, $existing['id']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO admins (name, email, password_hash, is_active) VALUES (?, ?, ?, 1)');
        $stmt->execute([$name, $email, $hash]);
    }
    fwrite(STDOUT, "Đã tạo/kích hoạt Admin thành công. Bạn có thể đăng nhập bằng email vừa nhập.\n");
} catch (InvalidArgumentException $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
} catch (Throwable $exception) {
    error_log('[Nexa admin setup] ' . $exception->getMessage());
    fwrite(STDERR, "Chưa thể tạo Admin. Kiểm tra cấu hình kết nối và import database.sql trước.\n");
    exit(1);
}
