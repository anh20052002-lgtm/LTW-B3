<?php
declare(strict_types=1);

// Copy to config/config.local.php and fill credentials locally.
// Alternatively set NEXA_DB_HOST, NEXA_DB_PORT, NEXA_DB_NAME,
// NEXA_DB_USER, NEXA_DB_PASS and optionally NEXA_APP_URL in the environment.
return [
    'db_host' => '127.0.0.1',
    'db_port' => 3306,
    'db_name' => 'nexa_crm',
    'db_user' => '',
    'db_pass' => '',
    // Leave blank for automatic XAMPP subdirectory detection.
    'app_url' => '',
];
