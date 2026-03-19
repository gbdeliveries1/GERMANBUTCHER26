<?php
session_start();
require_once 'admin_auth_check.php';

// Logout admin
adminLogout();

// Also clear customer session if it's admin
if (isset($_SESSION['GBDELIVERING_CUSTOMER_USER_2021']) && strpos($_SESSION['GBDELIVERING_CUSTOMER_USER_2021'], 'admin_') === 0) {
    unset($_SESSION['GBDELIVERING_CUSTOMER_USER_2021']);
    unset($_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021']);
    setcookie('GBDELIVERING_CUSTOMER_USER_2021', '', time() - 3600, '/');
}

// Redirect to sign in page
header('Location: ../index.php?sign-in&logged_out=1');
exit;
?>