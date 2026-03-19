<?php
session_start();

// Check if already logged in
if (isset($_SESSION['GBDELIVERING_CUSTOMER_USER_2021']) && !empty($_SESSION['GBDELIVERING_CUSTOMER_USER_2021'])) {
    // Redirect based on user type
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        header('Location: user/admin/');
        exit;
    }
    header('Location: index.php');
    exit;
}

// Include database
include 'on/on.php';

// Admin credentials
$ADMIN_EMAIL = 'gbdeliveries1@gmail.com';
$ADMIN_PASSWORD = '@gbdeliveries123@';

// Handle login POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo 'empty';
        exit;
    }
    
    // Check admin credentials first
    if ($username === $ADMIN_EMAIL && $password === $ADMIN_PASSWORD) {
        $_SESSION['GBDELIVERING_CUSTOMER_USER_2021'] = $ADMIN_EMAIL;
        $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] = 'admin';
        $_SESSION['GBDELIVERING_ADMIN_USER_2021'] = $ADMIN_EMAIL;
        $_SESSION['GBDELIVERING_USER_ID_2021'] = 'admin';
        $_SESSION['is_admin'] = true;
        $_SESSION['user_type'] = 'ADMIN';
        $_SESSION['customer_name'] = 'Administrator';
        echo 'admin';
        exit;
    }
    
    // Check in USER table
    $username_esc = $conn->real_escape_string($username);
    $sql = "SELECT * FROM user WHERE (email='$username_esc' OR username='$username_esc' OR phone_no='$username_esc') LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Check if account is active
        $status = strtoupper($row['status'] ?? 'ACTIVE');
        if ($status !== 'ACTIVE') {
            echo 'inactive';
            exit;
        }
        
        // Check password (supports plain text, MD5, and bcrypt)
        $stored_password = $row['password'] ?? '';
        $password_valid = false;
        
        if ($password === $stored_password) {
            $password_valid = true;
        } elseif (md5($password) === $stored_password) {
            $password_valid = true;
        } elseif (password_verify($password, $stored_password)) {
            $password_valid = true;
        }
        
        if ($password_valid) {
            $user_id = $row['user_id'] ?? '';
            $type_id = strtoupper($row['type_id'] ?? 'CLIENT');
            $first_name = $row['first_name'] ?? '';
            $last_name = $row['last_name'] ?? '';
            
            $_SESSION['GBDELIVERING_CUSTOMER_USER_2021'] = $user_id;
            $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] = $user_id;
            $_SESSION['GBDELIVERING_USER_ID_2021'] = $user_id;
            $_SESSION['customer_name'] = trim($first_name . ' ' . $last_name);
            $_SESSION['user_type'] = $type_id;
            
            // Merge temp cart
            if (isset($_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'])) {
                $temp = $conn->real_escape_string($_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021']);
                if ($temp !== $user_id && strpos($temp, 'TEMP') === 0) {
                    $conn->query("UPDATE cart SET customer_id='$user_id' WHERE customer_id='$temp'");
                }
            }
            
            // Return based on user type
            if ($type_id === 'ADMIN' || $type_id === 'ADMINISTRATOR') {
                $_SESSION['is_admin'] = true;
                $_SESSION['GBDELIVERING_ADMIN_USER_2021'] = $row['email'];
                echo 'admin';
            } elseif ($type_id === 'SELLER' || $type_id === 'VENDOR') {
                echo 'seller';
            } else {
                echo 'success';
            }
            exit;
        } else {
            echo 'invalid';
            exit;
        }
    } else {
        echo 'not_found';
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - GBDeliveries</title>
    <link rel="icon" href="images/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #ff5000 0%, #ff8533 50%, #ffad33 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-box {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #222, #333);
            padding: 35px 30px;
            text-align: center;
            color: #fff;
        }
        .login-logo {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #ff5000, #ff8533);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 32px;
            box-shadow: 0 8px 25px rgba(255,80,0,0.4);
        }
        .login-header h1 { font-size: 26px; margin-bottom: 6px; }
        .login-header p { opacity: 0.8; font-size: 14px; }
        .login-form { padding: 35px 30px; }
        .form-group { margin-bottom: 22px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #444;
        }
        .input-wrap { position: relative; }
        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 48px;
            border: 2px solid #e5e5e5;
            border-radius: 12px;
            font-size: 15px;
            background: #fafafa;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #ff5000;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(255,80,0,0.1);
        }
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }
        .pwd-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            cursor: pointer;
        }
        .pwd-toggle:hover { color: #ff5000; }
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 13px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: #555;
        }
        .remember input { width: 18px; height: 18px; accent-color: #ff5000; }
        .forgot-link { color: #ff5000; text-decoration: none; font-weight: 500; }
        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ff5000, #ff7033);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(255,80,0,0.4);
            transition: all 0.3s;
        }
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(255,80,0,0.5);
        }
        .login-btn:disabled {
            background: #ccc;
            box-shadow: none;
            transform: none;
            cursor: not-allowed;
        }
        .msg {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
            align-items: center;
            gap: 10px;
        }
        .msg.show { display: flex; }
        .msg.success { background: #d4edda; color: #155724; }
        .msg.error { background: #f8d7da; color: #721c24; }
        .divider {
            display: flex;
            align-items: center;
            margin: 28px 0;
            color: #aaa;
            font-size: 12px;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e5e5;
        }
        .divider span { padding: 0 18px; }
        .social-btns { display: flex; flex-direction: column; gap: 12px; }
        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        .wa-btn { background: #25d366; color: #fff; }
        .wa-btn:hover { background: #1da855; color: #fff; }
        .phone-btn { background: #007bff; color: #fff; }
        .phone-btn:hover { background: #0056b3; color: #fff; }
        .login-footer {
            text-align: center;
            padding: 25px 30px 30px;
            background: #fafafa;
            border-top: 1px solid #eee;
        }
        .login-footer p { margin: 0 0 15px; color: #666; font-size: 14px; }
        .login-footer a { color: #ff5000; text-decoration: none; font-weight: 600; }
        .security {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 11px;
            color: #999;
        }
        .security i { color: #28a745; }
        .spinner {
            width: 18px;
            height: 18px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (max-width: 480px) {
            .login-form, .login-header, .login-footer { padding-left: 25px; padding-right: 25px; }
            .form-options { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="login-header">
        <div class="login-logo"><i class="fas fa-shopping-cart"></i></div>
        <h1>GBDeliveries</h1>
        <p>Fresh groceries delivered to your door</p>
    </div>
    
    <div class="login-form">
        <div class="msg" id="msg">
            <i class="fas fa-info-circle" id="msgIcon"></i>
            <span id="msgText"></span>
        </div>
        
        <form id="loginForm" onsubmit="doLogin(event)">
            <div class="form-group">
                <label>Email, Username or Phone</label>
                <div class="input-wrap">
                    <input type="text" id="username" placeholder="Enter email, username or phone" required>
                    <i class="fas fa-user input-icon"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrap">
                    <input type="password" id="password" placeholder="Enter your password" required>
                    <i class="fas fa-lock input-icon"></i>
                    <i class="fas fa-eye pwd-toggle" onclick="togglePwd()"></i>
                </div>
            </div>
            
            <div class="form-options">
                <label class="remember">
                    <input type="checkbox" id="remember">
                    <span>Remember me</span>
                </label>
                <a href="index.php?lost-password" class="forgot-link">Forgot Password?</a>
            </div>
            
            <button type="submit" class="login-btn" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i>
                <span>Sign In</span>
            </button>
        </form>
        
        <div class="divider"><span>or contact us</span></div>
        
        <div class="social-btns">
            <a href="https://wa.me/250783654454" target="_blank" class="social-btn wa-btn">
                <i class="fab fa-whatsapp"></i> Order via WhatsApp
            </a>
            <a href="tel:+250783654454" class="social-btn phone-btn">
                <i class="fas fa-phone"></i> Call: +250 783 654 454
            </a>
        </div>
    </div>
    
    <div class="login-footer">
        <p>Don't have an account? <a href="index.php?sign-up">Sign Up</a></p>
        <p><a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
        <div class="security">
            <i class="fas fa-shield-alt"></i>
            <span>Secured with SSL encryption</span>
        </div>
    </div>
</div>

<script>
function togglePwd() {
    var input = document.getElementById('password');
    var icon = document.querySelector('.pwd-toggle');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function showMsg(text, type) {
    var msg = document.getElementById('msg');
    msg.className = 'msg show ' + type;
    document.getElementById('msgText').textContent = text;
    document.getElementById('msgIcon').className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
}

function doLogin(e) {
    e.preventDefault();
    
    var user = document.getElementById('username').value.trim();
    var pass = document.getElementById('password').value;
    var btn = document.getElementById('loginBtn');
    
    if (!user || !pass) {
        showMsg('Please fill in all fields', 'error');
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Signing in...';
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'login.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        var res = xhr.responseText.trim().toLowerCase();
        console.log('Login response:', res);
        
        if (res === 'admin') {
            showMsg('Admin login successful! Redirecting to dashboard...', 'success');
            setTimeout(function() {
                window.location.href = 'user/admin/';
            }, 1000);
        } else if (res === 'seller') {
            showMsg('Seller login successful! Redirecting...', 'success');
            setTimeout(function() {
                window.location.href = 'user/seller/';
            }, 1000);
        } else if (res === 'success') {
            showMsg('Login successful! Redirecting...', 'success');
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 1000);
        } else if (res === 'invalid') {
            showMsg('Incorrect password. Please try again.', 'error');
            resetBtn();
        } else if (res === 'not_found') {
            showMsg('Account not found. Check your email/username.', 'error');
            resetBtn();
        } else if (res === 'inactive') {
            showMsg('Your account is inactive. Contact support.', 'error');
            resetBtn();
        } else {
            showMsg('Login failed. Please try again.', 'error');
            resetBtn();
        }
    };
    
    xhr.onerror = function() {
        showMsg('Connection error. Please try again.', 'error');
        resetBtn();
    };
    
    xhr.send('username=' + encodeURIComponent(user) + '&password=' + encodeURIComponent(pass));
}

function resetBtn() {
    var btn = document.getElementById('loginBtn');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> <span>Sign In</span>';
}
</script>

</body>
</html>