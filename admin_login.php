<?php
session_start();
header('Content-Type: application/json');

// Admin credentials - KEEP THESE SECURE!
define('ADMIN_EMAIL', 'gbdeliveries1@gmail.com');
define('ADMIN_PASSWORD', '@gbdeliveries123@');

// Get POST data
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = $_POST['remember'] ?? '0';

// Validate input
if (empty($username) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter email and password'
    ]);
    exit;
}

// Check admin credentials
if (strtolower($username) === strtolower(ADMIN_EMAIL) && $password === ADMIN_PASSWORD) {
    
    // Set admin session variables
    $_SESSION['GBDELIVERING_ADMIN_USER'] = true;
    $_SESSION['GBDELIVERING_ADMIN_EMAIL'] = ADMIN_EMAIL;
    $_SESSION['GBDELIVERING_ADMIN_NAME'] = 'Administrator';
    $_SESSION['GBDELIVERING_ADMIN_ROLE'] = 'super_admin';
    $_SESSION['GBDELIVERING_ADMIN_LOGIN_TIME'] = time();
    $_SESSION['GBDELIVERING_ADMIN_IP'] = $_SERVER['REMOTE_ADDR'];
    
    // Set customer session as well (for compatibility)
    $_SESSION['GBDELIVERING_CUSTOMER_USER_2021'] = 'admin_' . md5(ADMIN_EMAIL);
    $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] = 'admin_' . md5(ADMIN_EMAIL);
    
    // Set cookies if remember me is checked
    if ($remember === '1') {
        $expire = time() + (30 * 24 * 60 * 60); // 30 days
        setcookie('GBDELIVERING_ADMIN_USER', 'true', $expire, '/', '', true, true);
        setcookie('GBDELIVERING_ADMIN_TOKEN', md5(ADMIN_EMAIL . ADMIN_PASSWORD . $_SERVER['REMOTE_ADDR']), $expire, '/', '', true, true);
    } else {
        $expire = time() + (24 * 60 * 60); // 1 day
        setcookie('GBDELIVERING_ADMIN_USER', 'true', $expire, '/', '', true, true);
    }
    
    // Log admin login
    $log_file = '../logs/admin_login.log';
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    $log_entry = date('Y-m-d H:i:s') . " | Admin Login | IP: " . $_SERVER['REMOTE_ADDR'] . " | User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => true,
        'message' => 'Admin login successful',
        'type' => 'admin',
        'redirect' => 'admin/index.php',
        'user' => [
            'email' => ADMIN_EMAIL,
            'name' => 'Administrator',
            'role' => 'super_admin'
        ]
    ]);
    
} else {
    // Log failed attempt
    $log_file = '../logs/failed_login.log';
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    $log_entry = date('Y-m-d H:i:s') . " | Failed Admin Login | Email: " . $username . " | IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password'
    ]);
}
?>