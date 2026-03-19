<?php
/**
 * Admin Authentication Check
 * Include this file at the top of all admin pages
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
function isAdminLoggedIn() {
    // Check session
    if (isset($_SESSION['GBDELIVERING_ADMIN_USER']) && $_SESSION['GBDELIVERING_ADMIN_USER'] === true) {
        return true;
    }
    
    // Check cookie (for remember me)
    if (isset($_COOKIE['GBDELIVERING_ADMIN_USER']) && $_COOKIE['GBDELIVERING_ADMIN_USER'] === 'true') {
        // Verify token
        if (isset($_COOKIE['GBDELIVERING_ADMIN_TOKEN'])) {
            $expected_token = md5('gbdeliveries1@gmail.com' . '@gbdeliveries123@' . $_SERVER['REMOTE_ADDR']);
            if ($_COOKIE['GBDELIVERING_ADMIN_TOKEN'] === $expected_token) {
                // Restore session
                $_SESSION['GBDELIVERING_ADMIN_USER'] = true;
                $_SESSION['GBDELIVERING_ADMIN_EMAIL'] = 'gbdeliveries1@gmail.com';
                $_SESSION['GBDELIVERING_ADMIN_NAME'] = 'Administrator';
                $_SESSION['GBDELIVERING_ADMIN_ROLE'] = 'super_admin';
                return true;
            }
        }
    }
    
    return false;
}

// Get admin info
function getAdminInfo() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    return [
        'email' => $_SESSION['GBDELIVERING_ADMIN_EMAIL'] ?? 'gbdeliveries1@gmail.com',
        'name' => $_SESSION['GBDELIVERING_ADMIN_NAME'] ?? 'Administrator',
        'role' => $_SESSION['GBDELIVERING_ADMIN_ROLE'] ?? 'super_admin',
        'login_time' => $_SESSION['GBDELIVERING_ADMIN_LOGIN_TIME'] ?? time()
    ];
}

// Logout admin
function adminLogout() {
    // Clear session
    unset($_SESSION['GBDELIVERING_ADMIN_USER']);
    unset($_SESSION['GBDELIVERING_ADMIN_EMAIL']);
    unset($_SESSION['GBDELIVERING_ADMIN_NAME']);
    unset($_SESSION['GBDELIVERING_ADMIN_ROLE']);
    unset($_SESSION['GBDELIVERING_ADMIN_LOGIN_TIME']);
    unset($_SESSION['GBDELIVERING_ADMIN_IP']);
    
    // Clear cookies
    setcookie('GBDELIVERING_ADMIN_USER', '', time() - 3600, '/');
    setcookie('GBDELIVERING_ADMIN_TOKEN', '', time() - 3600, '/');
    
    return true;
}

// Require admin login (redirect if not logged in)
function requireAdminLogin($redirect_url = 'index.php?sign-in') {
    if (!isAdminLoggedIn()) {
        header('Location: ' . $redirect_url);
        exit;
    }
}

// Check if request is for admin area and protect it
if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
    if (!isAdminLoggedIn()) {
        // Allow login page
        if (strpos($_SERVER['REQUEST_URI'], 'login') === false) {
            header('Location: ../index.php?sign-in');
            exit;
        }
    }
}
?>