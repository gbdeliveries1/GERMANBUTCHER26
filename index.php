<?php
// =======================================================
// user/admin/index.php (FIXED - $conn available to includes)
// =======================================================

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// DB
include "../../on/on.php";

// Admin credentials
$ADMIN_EMAIL = 'gbdeliveries1@gmail.com';
$ADMIN_PASSWORD = '@gbdeliveries123@';

// Logout
if (isset($_GET["sign"]) && $_GET["sign"] === "out") {
    unset($_SESSION["GBDELIVERING_ADMIN_USER_2021"]);
    unset($_SESSION["GBDELIVERING_CUSTOMER_USER_2021"]);
    unset($_SESSION["is_admin"]);
    unset($_SESSION["user_type"]);
    session_destroy();
    header("location:../../index.php");
    exit;
}

// Login POST
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === $ADMIN_EMAIL && $password === $ADMIN_PASSWORD) {
        $_SESSION['GBDELIVERING_ADMIN_USER_2021'] = $ADMIN_EMAIL;
        $_SESSION['GBDELIVERING_CUSTOMER_USER_2021'] = $ADMIN_EMAIL;
        $_SESSION['GBDELIVERING_USER_ID_2021'] = 'admin';
        $_SESSION['is_admin'] = true;
        $_SESSION['user_type'] = 'ADMIN';
        $_SESSION['customer_name'] = 'Administrator';
        header("location:index.php");
        exit;
    } else {
        $login_error = 'Invalid credentials';
    }
}

// Check login
$is_logged_in = false;
if (!empty($_SESSION['GBDELIVERING_ADMIN_USER_2021'])) {
    $is_logged_in = true;
} elseif (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    $is_logged_in = true;
    $_SESSION['GBDELIVERING_ADMIN_USER_2021'] = $_SESSION['GBDELIVERING_CUSTOMER_USER_2021'] ?? $ADMIN_EMAIL;
}

// Show login page
if (!$is_logged_in) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login - GBDeliveries</title>
  <link rel="shortcut icon" href="../../images/favicon.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:linear-gradient(135deg,#1a1a2e,#16213e,#0f3460);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
    .box{background:#fff;padding:45px 40px;border-radius:20px;width:100%;max-width:420px;box-shadow:0 25px 50px rgba(0,0,0,0.3)}
    .h{text-align:center;margin-bottom:28px}
    .icon{width:70px;height:70px;background:linear-gradient(135deg,#ff5000,#ff8533);border-radius:18px;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-size:30px;color:#fff;box-shadow:0 8px 25px rgba(255,80,0,0.4)}
    .h h1{font-size:26px;color:#333;margin-bottom:8px}
    .h p{color:#888;font-size:14px}
    .g{margin-bottom:18px}
    label{display:block;margin-bottom:8px;font-size:13px;font-weight:800;color:#444}
    .w{position:relative}
    input{width:100%;padding:15px 15px 15px 48px;border:2px solid #e5e5e5;border-radius:12px;font-size:15px;background:#fafafa;transition:.2s}
    input:focus{outline:none;border-color:#ff5000;background:#fff;box-shadow:0 0 0 4px rgba(255,80,0,0.1)}
    .i{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:#aaa;font-size:16px}
    button{width:100%;padding:16px;background:linear-gradient(135deg,#ff5000,#ff7033);color:#fff;border:none;border-radius:12px;font-size:16px;font-weight:900;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;box-shadow:0 4px 15px rgba(255,80,0,0.4);transition:.2s}
    button:hover{transform:translateY(-2px);box-shadow:0 6px 25px rgba(255,80,0,0.5)}
    .err{background:#f8d7da;color:#721c24;padding:14px 16px;border-radius:10px;margin-bottom:18px;font-size:14px;display:flex;align-items:center;gap:10px}
    .back{text-align:center;margin-top:18px}
    .back a{color:#888;text-decoration:none;font-size:14px}
    .back a:hover{color:#ff5000}
  </style>
</head>
<body>
  <div class="box">
    <div class="h">
      <div class="icon"><i class="fas fa-user-shield"></i></div>
      <h1>Admin Panel</h1>
      <p>Sign in to access dashboard</p>
    </div>

    <?php if($login_error): ?>
      <div class="err"><i class="fas fa-exclamation-circle"></i><?php echo htmlspecialchars($login_error); ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="admin_login" value="1">
      <div class="g">
        <label>Email Address</label>
        <div class="w">
          <input type="email" name="email" placeholder="Enter admin email" required>
          <i class="fas fa-envelope i"></i>
        </div>
      </div>
      <div class="g">
        <label>Password</label>
        <div class="w">
          <input type="password" name="password" placeholder="Enter password" required>
          <i class="fas fa-lock i"></i>
        </div>
      </div>
      <button type="submit"><i class="fas fa-sign-in-alt"></i> Sign In</button>
    </form>

    <div class="back"><a href="../../index.php"><i class="fas fa-arrow-left"></i> Back to Website</a></div>
  </div>
</body>
</html>
<?php
    exit;
}

// Logged in
$customer_name = $_SESSION['customer_name'] ?? 'Admin';
$page = $_GET['page'] ?? 'home';

// ✅ SAFE INCLUDE (FIXED): makes $conn visible inside included pages
function safe_include_page($file){
    global $conn; // <<< THIS IS THE FIX
    if (file_exists($file)) { include $file; return; }
    echo "<div style='padding:16px;background:#fff;border:1px solid #eee;border-radius:12px;max-width:1100px;margin:20px auto;font-family:system-ui'>
            <b style='color:#e11d48'>Missing file:</b> ".htmlspecialchars($file)."
          </div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>GBDeliveries - Admin Dashboard</title>
  <link rel="shortcut icon" href="../../images/favicon.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:#f6f7fb}
    .topbar{position:sticky;top:0;z-index:999;background:#111;color:#fff;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px}
    .brand{display:flex;align-items:center;gap:10px}
    .logo{width:40px;height:40px;border-radius:14px;background:linear-gradient(135deg,#ff6a00,#ff8f00);display:flex;align-items:center;justify-content:center;font-weight:900}
    .brand .t{line-height:1.1}
    .brand .t b{display:block}
    .actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
    .btn{display:inline-flex;align-items:center;gap:8px;padding:10px 12px;border-radius:999px;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.08);color:#fff;text-decoration:none;font-weight:900;font-size:12px}
    .btn:hover{background:rgba(255,255,255,.14)}
    .btn.red{border:none;background:#7f1d1d}
    .btn.red:hover{background:#991b1b}
    .wrap{max-width:1400px;margin:0 auto;padding:14px}
  </style>
</head>
<body>

  <div class="topbar">
    <div class="brand">
      <div class="logo">🛒</div>
      <div class="t">
        <b>GBDeliveries</b>
        <small style="opacity:.85;font-weight:700">Admin Dashboard</small>
      </div>
    </div>

    <div class="actions">
      <a class="btn" href="index.php?page=admin_manager&manage=dashboard"><i class="fa-solid fa-bolt"></i> Control Panel</a>
      <a class="btn" href="../../index.php" target="_blank"><i class="fa-solid fa-up-right-from-square"></i> Website</a>
      <span class="btn" style="cursor:default"><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($customer_name); ?></span>
      <a class="btn red" href="?sign=out"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
  </div>

  <div class="wrap">
    <?php
      // your old routes (kept)
      if (isset($_GET['home'])) { safe_include_page(__DIR__ . "/home.php"); }
      elseif (isset($_GET['users'])) { safe_include_page(__DIR__ . "/users.php"); }
      elseif (isset($_GET['product-categories'])) { safe_include_page(__DIR__ . "/product-categories.php"); }
      elseif (isset($_GET['new-product-category'])) { safe_include_page(__DIR__ . "/new-product-category.php"); }
      elseif (isset($_GET['product-sub-categories'])) { safe_include_page(__DIR__ . "/product-sub-categories.php"); }
      elseif (isset($_GET['new-product-sub-category'])) { safe_include_page(__DIR__ . "/new-product-sub-category.php"); }
      elseif (isset($_GET['shipping-fees'])) { safe_include_page(__DIR__ . "/shipping-fees.php"); }
      elseif (isset($_GET['new-shipping-fee'])) { safe_include_page(__DIR__ . "/new-shipping-fee.php"); }
      elseif (isset($_GET['sector-shipping-fees'])) { safe_include_page(__DIR__ . "/sector-shipping-fees.php"); }
      elseif (isset($_GET['new-sector-shipping-fee'])) { safe_include_page(__DIR__ . "/new-sector-shipping-fee.php"); }
      elseif (isset($_GET['orders'])) { safe_include_page(__DIR__ . "/orders.php"); }
      elseif (isset($_GET['last-orders'])) { safe_include_page(__DIR__ . "/last-orders.php"); }
      elseif (isset($_GET['search-product'])) { safe_include_page(__DIR__ . "/search-product.php"); }
      elseif (isset($_GET['profile'])) { safe_include_page(__DIR__ . "/profile.php"); }
      elseif (isset($_GET['edit-profile'])) { safe_include_page(__DIR__ . "/edit-profile.php"); }
      elseif (isset($_GET['products'])) { safe_include_page(__DIR__ . "/products.php"); }
      elseif (($page ?? '') === 'bulk_editor') { safe_include_page(__DIR__ . "/bulk_editor.php"); }
      elseif (($page ?? '') === 'admin_manager') { safe_include_page(__DIR__ . "/admin_manager.php"); }
      elseif (isset($_GET['products-stock'])) { safe_include_page(__DIR__ . "/products-stock.php"); }
      elseif (isset($_GET['payment-transactions'])) { safe_include_page(__DIR__ . "/payment-transactions.php"); }
      else { safe_include_page(__DIR__ . "/home.php"); }

      if (file_exists("../../includes/user-footer.php")) { include "../../includes/user-footer.php"; }
    ?>
  </div>

</body>
</html>