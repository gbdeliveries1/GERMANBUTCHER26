<?php
session_start();
include "../on/on.php";

// Admin credentials
$ADMIN_EMAIL = 'gbdeliveries1@gmail.com';
$ADMIN_PASSWORD = '@gbdeliveries123@';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo "Please fill all fields";
    exit;
}

// Check if admin login
if ($username === $ADMIN_EMAIL && $password === $ADMIN_PASSWORD) {
    $_SESSION['GBDELIVERING_CUSTOMER_USER_2021'] = $ADMIN_EMAIL;
    $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] = 'admin_user';
    $_SESSION['GBDELIVERING_ADMIN_USER_2021'] = $ADMIN_EMAIL;
    $_SESSION['is_admin'] = true;
    echo "success";
    exit;
}

// Regular customer login
$username = $conn->real_escape_string($username);

$sql = "SELECT * FROM customer WHERE username='$username' OR email='$username'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stored_password = $row['password'];
    $customer_id = $row['customer_id'];
    
    // Check password - try plain text, md5, and password_verify
    $password_match = false;
    
    if ($password === $stored_password) {
        $password_match = true;
    } elseif (md5($password) === $stored_password) {
        $password_match = true;
    } elseif (function_exists('password_verify') && password_verify($password, $stored_password)) {
        $password_match = true;
    }
    
    if ($password_match) {
        $_SESSION['GBDELIVERING_CUSTOMER_USER_2021'] = $customer_id;
        $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] = $customer_id;
        
        // Set cookie if remember me
        if (isset($_POST['remember']) && $_POST['remember'] == '1') {
            setcookie('GBDELIVERING_CUSTOMER_USER_2021', $customer_id, time() + (30 * 24 * 60 * 60), '/');
        }
        
        echo "success";
        exit;
    } else {
        echo "Invalid password";
        exit;
    }
} else {
    echo "Account not found";
    exit;
}
?>