<?php
/**
 * GBdelivering - Main Index File
 * Version 2.2 - Fixed Preloader, Smaller Toast, Organized WhatsApp Message
 * @updated 2026-03-18
 */

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

include "on/on.php";
include "includes/links.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize all variables
$login_status = false;
$user_id = '';
$user_first_name = '';
$user_last_name = '';
$user_email = '';
$user_phone_no = '';
$user_dob = '';
$user_picture_id = '';
$user_picture = '';
$user_picuter_register_date = '';
$customer_temp_id = '';

// Handle Logout
if (isset($_GET["sign"]) && $_GET["sign"] === "out") {
    unset($_SESSION["GBDELIVERING_CUSTOMER_USER_2021"]);
    unset($_SESSION["GBDELIVERING_TEMP_CUSTOMER_USER_2021"]);
    if (isset($_COOKIE['GBDELIVERING_CUSTOMER_USER_2021'])) {
        unset($_COOKIE['GBDELIVERING_CUSTOMER_USER_2021']);
        setcookie('GBDELIVERING_CUSTOMER_USER_2021', null, -1, '/');
    }
    header("location:index.php");
    exit;
}

// Authentication / Session Bootstrap
if (!isset($_COOKIE["GBDELIVERING_CUSTOMER_USER_2021"])) {
    if (!isset($_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'])) {
        $id = rand(2, 2000000);
        $id2 = md5((string)$id);
        $id3 = md5($id2);
        $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] = $id3 . $id2 . $id;
    }
    $customer_temp_id = $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'];
} else {
    $login_status = true;
    if (isset($conn) && is_object($conn)) {
        try {
            $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
            if ($stmt) {
                $stmt->bind_param("s", $_COOKIE['GBDELIVERING_CUSTOMER_USER_2021']);
                $stmt->execute();
                $result5 = $stmt->get_result();
                if ($result5 && $result5->num_rows > 0) {
                    $row5 = $result5->fetch_assoc();
                    $user_id         = $row5['user_id'] ?? '';
                    $user_first_name = $row5['first_name'] ?? '';
                    $user_last_name  = $row5['last_name'] ?? '';
                    $user_email      = $row5['email'] ?? '';
                    $user_phone_no   = $row5['phone_no'] ?? '';
                    $user_dob        = $row5['dob'] ?? '';
                }
                $stmt->close();
            }
            if (!empty($user_id)) {
                $stmt_pic = $conn->prepare("SELECT * FROM user_picture WHERE user_id = ? ORDER BY register_date ASC LIMIT 1");
                if ($stmt_pic) {
                    $stmt_pic->bind_param("s", $user_id);
                    $stmt_pic->execute();
                    $result5_pic = $stmt_pic->get_result();
                    if ($result5_pic && $result5_pic->num_rows > 0) {
                        $row5_pic = $result5_pic->fetch_assoc();
                        $user_picture_id            = $row5_pic['picture_id'] ?? '';
                        $user_picture               = $row5_pic['picture'] ?? '';
                        $user_picuter_register_date = $row5_pic['register_date'] ?? '';
                    }
                    $stmt_pic->close();
                }
            }
        } catch (Exception $e) { }
    }
    $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] = $user_id;
    $customer_temp_id = $user_id;
}

// AJAX Cart Clear Handler
if (isset($_POST['ajax_clear_cart']) && $_POST['ajax_clear_cart'] === '1') {
    if (!empty($customer_temp_id) && isset($conn) && is_object($conn)) {
        try {
            $stmt_cart = $conn->prepare("SELECT cart_id FROM cart WHERE customer_id = ? LIMIT 1");
            if ($stmt_cart) {
                $stmt_cart->bind_param("s", $customer_temp_id);
                $stmt_cart->execute();
                $cart_query = $stmt_cart->get_result();
                if ($cart_query && $cart_query->num_rows > 0) {
                    $cart_row = $cart_query->fetch_assoc();
                    $cid = $cart_row['cart_id'];
                    $stmt_del = $conn->prepare("DELETE FROM cart_item WHERE cart_id = ? AND status='ACTIVE'");
                    if ($stmt_del) {
                        $stmt_del->bind_param("s", $cid);
                        $stmt_del->execute();
                    }
                }
            }
        } catch (Exception $e) {}
    }
    exit('CART_CLEARED');
}

// AJAX Get Cart For WhatsApp Handler
if (isset($_POST['action']) && $_POST['action'] === 'get_cart_for_whatsapp') {
    header('Content-Type: application/json');
    $result_data = ['items' => [], 'subtotal' => 0, 'delivery_fee' => 0];
    if (!empty($customer_temp_id) && isset($conn) && is_object($conn)) {
        try {
            $stmt_cart = $conn->prepare("SELECT cart_id FROM cart WHERE customer_id = ? LIMIT 1");
            if ($stmt_cart) {
                $stmt_cart->bind_param("s", $customer_temp_id);
                $stmt_cart->execute();
                $cart_query = $stmt_cart->get_result();
                if ($cart_query && $cart_query->num_rows > 0) {
                    $cart_row = $cart_query->fetch_assoc();
                    $cid = $cart_row['cart_id'];
                    $stmt_items = $conn->prepare(
                        "SELECT ci.product_quantity, ci.price AS item_total, p.product_name, p.product_unit,
                                COALESCE((SELECT price FROM product_price WHERE product_id = ci.product_id LIMIT 1), 0) AS unit_price
                         FROM cart_item ci
                         JOIN product p ON ci.product_id = p.product_id
                         WHERE ci.cart_id = ? AND ci.status = 'ACTIVE'
                         ORDER BY ci.register_date DESC"
                    );
                    if ($stmt_items) {
                        $stmt_items->bind_param("s", $cid);
                        $stmt_items->execute();
                        $items_result = $stmt_items->get_result();
                        $subtotal = 0;
                        while ($item_row = $items_result->fetch_assoc()) {
                            $qty        = floatval($item_row['product_quantity']);
                            $item_total = floatval($item_row['item_total']);
                            $unit_price = floatval($item_row['unit_price']);
                            if ($unit_price <= 0 && $qty >= 0.001) {
                                $unit_price = $item_total / $qty;
                            }
                            $subtotal += $item_total;
                            $result_data['items'][] = [
                                'name'       => $item_row['product_name'],
                                'unit'       => $item_row['product_unit'],
                                'qty'        => $qty,
                                'unit_price' => $unit_price,
                                'total'      => $item_total,
                            ];
                        }
                        $result_data['subtotal'] = $subtotal;
                        $stmt_items->close();
                    }
                }
                $stmt_cart->close();
            }
            $sector = isset($_POST['sector']) ? trim($_POST['sector']) : '';
            if ($sector !== '') {
                $stmt_fee = $conn->prepare("SELECT fee FROM sector_shipping_fee WHERE sector = ? LIMIT 1");
                if ($stmt_fee) {
                    $stmt_fee->bind_param("s", $sector);
                    $stmt_fee->execute();
                    $fee_result = $stmt_fee->get_result();
                    if ($fee_result && $fee_result->num_rows > 0) {
                        $fee_row = $fee_result->fetch_assoc();
                        $result_data['delivery_fee'] = floatval($fee_row['fee']);
                    }
                    $stmt_fee->close();
                }
            }
        } catch (Exception $e) {}
    }
    echo json_encode($result_data);
    exit;
}

// Redirect to standalone dashboard
if (isset($_GET['dashboard'])) {
    header('Location: dashboard.php');
    exit;
}

// Site Settings Function
if (!function_exists('getSiteSetting')) {
    function getSiteSetting($conn, $key, $default = '') {
        if (!isset($conn) || !is_object($conn)) return $default;
        try {
            $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $key);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && $row = $res->fetch_assoc()) return $row['setting_value'];
            }
            $stmt2 = $conn->prepare("SELECT setting_value FROM site_design_settings WHERE setting_key = ? LIMIT 1");
            if ($stmt2) {
                $stmt2->bind_param("s", $key);
                $stmt2->execute();
                $res2 = $stmt2->get_result();
                if ($res2 && $row2 = $res2->fetch_assoc()) return $row2['setting_value'];
            }
        } catch (Exception $e) { }
        return $default;
    }
}

// Fetch Site Settings
$site_name       = getSiteSetting($conn, 'site_name', 'GBdelivering');
$site_logo       = getSiteSetting($conn, 'site_logo', 'images/logo/logo-1.png');
$primary_color   = getSiteSetting($conn, 'primary_color', '#ff5000');
$secondary_color = getSiteSetting($conn, 'secondary_color', '#ff6a33');
$font_family     = getSiteSetting($conn, 'font_family', 'Open Sans');
$active_theme    = getSiteSetting($conn, 'active_theme', 'default');
$theme_primary   = getSiteSetting($conn, 'theme_primary_color', $primary_color);
$theme_secondary = getSiteSetting($conn, 'theme_secondary_color', $secondary_color);
$theme_bg        = getSiteSetting($conn, 'theme_bg_color', '#ffffff');
$theme_font      = getSiteSetting($conn, 'theme_font', $font_family);
$font_size_base  = getSiteSetting($conn, 'font_size_base', '14');
$container_width = getSiteSetting($conn, 'container_width', '1200');
$border_radius   = getSiteSetting($conn, 'border_radius', '8');
$box_shadow_type = getSiteSetting($conn, 'box_shadow', 'soft');
$card_style      = getSiteSetting($conn, 'card_style', 'elevated');
$custom_css      = getSiteSetting($conn, 'custom_css', '');

$google_font_url = str_replace(' ', '+', (string)$theme_font);

$shadow_map = [
    'none'   => 'none',
    'soft'   => '0 4px 12px rgba(0,0,0,0.08)',
    'medium' => '0 8px 24px rgba(0,0,0,0.12)',
    'hard'   => '8px 8px 0px rgba(0,0,0,0.2)'
];
$selected_shadow = $shadow_map[$box_shadow_type] ?? '0 4px 12px rgba(0,0,0,0.08)';

$theme_footer_file = "themes/" . $active_theme . "/footer.php";

// SEO Settings
$page_title_seo = htmlspecialchars((string)$site_name) . " | Shop Everything";
$page_desc_seo  = htmlspecialchars((string)$site_name) . " — shop groceries, food, electronics, fashion, and more.";

if (isset($_GET['page_slug']) && isset($conn) && is_object($conn)) {
    try {
        $stmt_seo = $conn->prepare("SELECT title, meta_title, meta_description FROM custom_pages WHERE slug = ? AND status = 1");
        if ($stmt_seo) {
            $stmt_seo->bind_param("s", $_GET['page_slug']);
            $stmt_seo->execute();
            $seo_q = $stmt_seo->get_result();
            if ($seo_q && $seo_q->num_rows > 0 && $seo_row = $seo_q->fetch_assoc()) {
                $page_title_seo = !empty($seo_row['meta_title']) 
                    ? htmlspecialchars($seo_row['meta_title']) . " | " . htmlspecialchars($site_name) 
                    : htmlspecialchars($seo_row['title']) . " | " . htmlspecialchars($site_name);
                if (!empty($seo_row['meta_description'])) {
                    $page_desc_seo = htmlspecialchars($seo_row['meta_description']);
                }
            }
            $stmt_seo->close();
        }
    } catch(Exception $e) {}
}

// Fetch Categories and Cart Count
$nav_categories = [];
$cart_count = 0;

if (isset($conn) && is_object($conn)) {
    try {
        $cat_q = $conn->query("SELECT * FROM product_category WHERE status='ACTIVE' ORDER BY category_name ASC");
        if (!$cat_q) {
            $cat_q = $conn->query("SELECT * FROM product_category ORDER BY category_name ASC");
        }
        if ($cat_q && $cat_q->num_rows > 0) {
            while ($row = $cat_q->fetch_assoc()) {
                $nav_categories[] = $row;
            }
        }

        if (!empty($customer_temp_id)) {
            $stmt_cart = $conn->prepare("SELECT cart_id FROM cart WHERE customer_id = ? LIMIT 1");
            if ($stmt_cart) {
                $stmt_cart->bind_param("s", $customer_temp_id);
                $stmt_cart->execute();
                $cart_query = $stmt_cart->get_result();
                if ($cart_query && $cart_query->num_rows > 0 && $cart_row = $cart_query->fetch_assoc()) {
                    $cart_id_for_count = $cart_row['cart_id'];
                    $stmt_count = $conn->prepare("SELECT COUNT(*) as cnt FROM cart_item WHERE cart_id = ? AND status='ACTIVE'");
                    if ($stmt_count) {
                        $stmt_count->bind_param("s", $cart_id_for_count);
                        $stmt_count->execute();
                        $count_query = $stmt_count->get_result();
                        if ($count_query && $count_query->num_rows > 0 && $count_row = $count_query->fetch_assoc()) {
                            $cart_count = intval($count_row['cnt']);
                        }
                    }
                }
            }
        }
    } catch (Exception $e) { }
}
?>
<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="<?php echo $page_desc_seo; ?>">
    <meta name="author" content="<?php echo htmlspecialchars((string)$site_name); ?>">
    
    <link href="images/favicon.png" rel="shortcut icon">
    <title><?php echo $page_title_seo; ?></title>

    <!-- Error Suppressor -->
    <script>
        window.addEventListener('error', function(e) {
            if (e.message && (
                e.message.includes("module is not defined") || 
                e.message.includes("exports is not defined") || 
                e.message.includes("innerText") ||
                e.message.includes("null")
            )) {
                e.preventDefault(); 
                e.stopImmediatePropagation(); 
                return true;
            }
        }, true);
    </script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=<?php echo $google_font_url; ?>:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- CSS Files -->
    <link rel="stylesheet" href="css/vendor.css">
    <link rel="stylesheet" href="css/utility.css">
    <link rel="stylesheet" href="css/app.css">
    <link rel="stylesheet" href="css/app_1.css">

    <!-- Google Ads -->
    <script data-ad-client="ca-pub-5745320266901948" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    
    <!-- Google Sign-In -->
    <script src="https://apis.google.com/js/client:platform.js?onload=renderButton" async defer></script>
    <meta name="google-signin-client_id" content="730586474834-2mv65dti5gn2la73apgecv8bd6qdbsri.apps.googleusercontent.com">

    <style>
    /* ==========================================================================
       CSS VARIABLES
    ========================================================================== */
    :root {
        --primary: <?php echo htmlspecialchars((string)$theme_primary); ?>;
        --primary-hover: #e64a19;
        --secondary: <?php echo htmlspecialchars((string)$theme_secondary); ?>;
        --theme-bg: <?php echo htmlspecialchars((string)$theme_bg); ?>;
        --font-family: '<?php echo htmlspecialchars((string)$theme_font); ?>', sans-serif;
        --font-size-base: <?php echo htmlspecialchars((string)$font_size_base); ?>px;
        --container-max: <?php echo htmlspecialchars((string)$container_width); ?>px;
        --global-radius: <?php echo htmlspecialchars((string)$border_radius); ?>px;
        --global-shadow: <?php echo $selected_shadow; ?>;
        --hdr-dark: #111827;
        --hdr-light: #f9fafb;
        --hdr-border: #e5e7eb;
        --success: #10b981;
        --error: #ef4444;
        --warning: #f59e0b;
        --wa-green: #25D366;
    }

    /* ==========================================================================
       BASE STYLES
    ========================================================================== */
    * {
        box-sizing: border-box;
    }
    
    body {
        font-family: var(--font-family);
        font-size: var(--font-size-base);
        background-color: var(--theme-bg);
        overflow-x: hidden;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 100%;
        max-width: var(--container-max) !important;
        padding: 0 15px;
        margin: 0 auto;
    }
    
    a {
        text-decoration: none;
        color: inherit;
    }

    /* Hide legacy cart response */
    #result_response_cart,
    #result_response_cart * {
        display: none !important;
        visibility: hidden !important;
        position: absolute !important;
        z-index: -999999 !important;
        height: 0 !important;
        width: 0 !important;
    }

    /* Product Cards */
    .product-m {
        border-radius: var(--global-radius);
        <?php if($card_style == 'flat'): ?>
            border: none;
            box-shadow: none;
        <?php elseif($card_style == 'bordered'): ?>
            border: 1px solid #eee;
            box-shadow: none;
        <?php else: ?>
            border: none;
            box-shadow: var(--global-shadow);
        <?php endif; ?>
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .product-m:hover {
        transform: translateY(-3px);
    }

    /* ==========================================================================
       PRELOADER
    ========================================================================== */
    #sitePreloader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 999999;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    
    #sitePreloader.hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }
    
    #sitePreloader img {
        max-width: 60px;
        animation: preloaderSpin 1s linear infinite;
    }
    
    @keyframes preloaderSpin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* ==========================================================================
       HEADER STYLES
    ========================================================================== */
    .gb-header-wrapper {
        background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        position: sticky;
        top: 0;
        z-index: 10000;
        width: 100%;
    }
    
    /* Top Bar */
    .gb-topbar {
        background: var(--hdr-dark);
        color: #d1d5db;
        font-size: 13px;
        font-weight: 500;
    }
    
    .gb-topbar .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 36px;
    }
    
    .gb-top-links {
        display: flex;
        gap: 20px;
        align-items: center;
    }
    
    .gb-top-links a {
        display: flex;
        align-items: center;
        gap: 6px;
        transition: 0.2s;
        color: #d1d5db;
    }
    
    .gb-top-links a:hover {
        color: #fff;
    }
    
    .gb-top-links i {
        color: var(--primary);
        font-size: 14px;
    }
    
    /* Main Header */
    .gb-main-header {
        padding: 16px 0;
        background: #fff;
    }
    
    .gb-main-header .container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 30px;
    }
    
    .gb-menu-btn {
        display: none;
        background: none;
        border: none;
        font-size: 24px;
        color: var(--hdr-dark);
        cursor: pointer;
        padding: 5px;
    }
    
    /* Logo */
    .gb-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }
    
    .gb-logo img {
        max-height: 45px;
        border-radius: 8px;
    }
    
    .gb-logo h2 {
        font-size: 24px;
        font-weight: 800;
        color: var(--hdr-dark);
        margin: 0;
    }
    
    .gb-logo h2 span {
        color: var(--primary);
    }
    
    /* Search */
    .live-search-wrapper {
        flex: 1;
        max-width: 800px;
        position: relative;
    }
    
    .gb-search-form {
        display: flex;
        width: 100%;
        height: 46px;
        border: 2px solid var(--primary);
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        transition: 0.2s;
    }
    
    .gb-search-form:focus-within {
        box-shadow: 0 0 0 4px rgba(255,80,0,0.1);
    }
    
    .gb-search-cat {
        background: var(--hdr-light);
        border: none;
        border-right: 1px solid var(--hdr-border);
        padding: 0 15px;
        color: #4b5563;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        outline: none;
        max-width: 150px;
    }
    
    .gb-search-input {
        flex: 1;
        border: none;
        padding: 0 16px;
        font-size: 15px;
        outline: none;
        color: var(--hdr-dark);
        width: 100%;
    }
    
    .gb-search-btn {
        background: var(--primary);
        color: #fff;
        border: none;
        padding: 0 24px;
        font-size: 16px;
        cursor: pointer;
        transition: 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .gb-search-btn:hover {
        background: var(--primary-hover);
    }
    
    /* Live Search Results */
    .live-search-results {
        position: absolute;
        top: calc(100% + 5px);
        left: 0;
        width: 100%;
        background: #fff;
        border-radius: 8px;
        box-shadow: var(--global-shadow);
        z-index: 1000;
        max-height: 400px;
        overflow-y: auto;
        display: none;
        border: 1px solid var(--hdr-border);
    }
    
    .ls-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px;
        border-bottom: 1px solid #eee;
        color: #333;
        transition: 0.2s;
    }
    
    .ls-item:hover {
        background: var(--hdr-light);
        color: var(--primary);
    }

    /* Action Buttons */
    .gb-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }
    
    .gb-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 6px 12px;
        border-radius: 8px;
        color: var(--hdr-dark);
        position: relative;
        transition: 0.2s;
        cursor: pointer;
        min-width: 60px;
        border: none;
        background: transparent;
    }
    
    .gb-action-btn:hover {
        background: var(--hdr-light);
        color: var(--primary);
    }
    
    .gb-action-btn i {
        font-size: 22px;
    }
    
    .gb-action-btn span {
        font-size: 12px;
        font-weight: 700;
    }
    
    .gb-cart-badge {
        position: absolute;
        top: 0;
        right: 6px;
        background: var(--error);
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        min-width: 18px;
        height: 18px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        border: 2px solid #fff;
    }

    /* Navigation Bar */
    .gb-nav-wrapper {
        border-top: 1px solid var(--hdr-border);
        background: #fff;
    }
    
    .gb-nav-wrapper .container {
        display: flex;
        align-items: stretch;
        height: 48px;
    }
    
    .hdr-cat-wrap {
        position: relative;
    }
    
    .gb-cat-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 24px;
        background: var(--primary);
        color: #fff;
        font-size: 14px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        min-width: 230px;
        justify-content: space-between;
        transition: 0.2s;
        text-transform: uppercase;
        height: 100%;
    }
    
    .gb-cat-btn:hover {
        background: var(--primary-hover);
    }
    
    /* Mega Menu */
    .hdr-mega-menu {
        position: absolute;
        top: 100%;
        left: 0;
        width: 230px;
        background: #fff;
        border: 1px solid var(--hdr-border);
        border-top: none;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        display: none;
        z-index: 10000;
        border-radius: 0 0 8px 8px;
        overflow: hidden;
    }
    
    .hdr-cat-wrap:hover .hdr-mega-menu {
        display: block;
    }
    
    .hdr-mega-menu a {
        display: block;
        padding: 12px 20px;
        font-size: 14px;
        font-weight: 500;
        color: var(--hdr-dark);
        border-bottom: 1px solid var(--hdr-light);
        transition: 0.2s;
    }
    
    .hdr-mega-menu a:hover {
        background: var(--hdr-light);
        color: var(--primary);
        padding-left: 24px;
    }
    
    /* Nav Links */
    .gb-nav-links {
        display: flex;
        align-items: center;
        padding-left: 16px;
        flex: 1;
        overflow-x: auto;
        gap: 5px;
    }
    
    .gb-nav-links::-webkit-scrollbar {
        display: none;
    }
    
    .gb-nav-link {
        padding: 0 16px;
        font-size: 14px;
        font-weight: 700;
        color: var(--hdr-dark);
        display: flex;
        align-items: center;
        height: 100%;
        text-transform: uppercase;
        transition: 0.2s;
        white-space: nowrap;
    }
    
    .gb-nav-link:hover {
        color: var(--primary);
    }

    /* ==========================================================================
       MOBILE RESPONSIVE
    ========================================================================== */
    @media (max-width: 768px) {
        .gb-main-header {
            padding: 12px 0;
        }
        
        .gb-main-header .container {
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .gb-menu-btn {
            display: block;
            order: 1;
            margin-right: 4px;
        }
        
        .gb-logo {
            order: 2;
            flex: 1;
        }
        
        .gb-logo img {
            max-height: 36px;
        }
        
        .gb-logo h2 {
            font-size: 20px;
        }
        
        .gb-actions {
            order: 3;
            gap: 4px;
        }
        
        .gb-action-btn span {
            display: none;
        }
        
        .gb-action-btn {
            padding: 8px;
            min-width: 44px;
        }
        
        .gb-action-btn i {
            font-size: 24px;
        }
        
        .gb-cart-badge {
            top: 0;
            right: -2px;
        }
        
        .live-search-wrapper {
            order: 4;
            width: 100%;
            max-width: 100%;
            flex: none;
        }
        
        .gb-search-form {
            height: 44px;
            border-radius: 6px;
        }
        
        .gb-search-cat {
            display: none;
        }
        
        .gb-topbar,
        .gb-nav-wrapper {
            display: none;
        }
        
        .mobile-nav {
            display: block;
        }
        
        body {
            padding-bottom: 65px;
        }
    }

    /* ==========================================================================
       SIDE DRAWER (Mobile Menu)
    ========================================================================== */
    .drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.6);
        z-index: 100000;
        opacity: 0;
        visibility: hidden;
        transition: 0.3s;
    }
    
    .drawer-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    .side-drawer {
        position: fixed;
        top: 0;
        left: -320px;
        width: 300px;
        max-width: 85%;
        height: 100%;
        background: #fff;
        z-index: 100001;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        box-shadow: 4px 0 24px rgba(0,0,0,0.15);
    }
    
    .side-drawer.active {
        left: 0;
    }
    
    .drawer-header {
        background: var(--primary);
        color: #fff;
        padding: 20px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .drawer-header h3 {
        font-size: 18px;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #fff;
    }
    
    .drawer-close {
        background: none;
        border: none;
        color: #fff;
        font-size: 28px;
        cursor: pointer;
        line-height: 1;
        padding: 0;
    }
    
    .drawer-body {
        flex: 1;
        overflow-y: auto;
        padding: 10px 0;
    }
    
    .drawer-link {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 14px 20px;
        font-size: 15px;
        font-weight: 600;
        color: var(--hdr-dark);
        border-bottom: 1px solid var(--hdr-light);
    }
    
    .drawer-link i {
        color: #9ca3af;
        font-size: 18px;
        width: 24px;
        text-align: center;
    }
    
    .drawer-title {
        padding: 20px 20px 10px;
        font-size: 13px;
        font-weight: 800;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* ==========================================================================
       CART DRAWER
    ========================================================================== */
    .cart-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.6);
        z-index: 1000000;
        opacity: 0;
        pointer-events: none;
        transition: 0.3s;
        backdrop-filter: blur(2px);
    }
    
    .cart-overlay.active {
        opacity: 1;
        pointer-events: auto;
    }
    
    .cart-drawer {
        position: fixed;
        top: 0;
        right: -450px;
        width: 100%;
        max-width: 400px;
        height: 100%;
        background: #fff;
        z-index: 1000001;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        box-shadow: -5px 0 30px rgba(0,0,0,0.1);
    }
    
    .cart-drawer.active {
        right: 0;
    }
    
    .cart-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 18px;
        font-weight: 800;
        background: #fff;
        color: #111;
    }
    
    .cart-close {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: #6b7280;
        transition: 0.2s;
        line-height: 1;
        padding: 0;
    }
    
    .cart-close:hover {
        color: var(--error);
    }
    
    .cart-body {
        flex: 1;
        overflow-y: auto;
        padding: 0;
        background: #f9fafb;
    }
    
    .cart-footer {
        padding: 20px;
        border-top: 1px solid #eee;
        background: #fff;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .checkout-btn {
        width: 100%;
        background: var(--primary);
        color: #fff;
        border: none;
        padding: 14px;
        border-radius: var(--global-radius);
        font-size: 15px;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: 0.2s;
        font-family: var(--font-family);
        gap: 10px;
    }
    
    .checkout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .whatsapp-btn {
        background: var(--wa-green);
    }
    
    .whatsapp-btn:hover {
        background: #1ebe57;
        color: #fff;
    }

    /* ==========================================================================
       MOBILE BOTTOM NAVIGATION
    ========================================================================== */
    .mobile-nav {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: var(--theme-bg);
        border-top: 1px solid #e5e7eb;
        z-index: 9999;
        padding-bottom: env(safe-area-inset-bottom);
        box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    }
    
    .mn-inner {
        display: flex;
        justify-content: space-around;
        padding: 10px 0;
    }
    
    .mn-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        color: #6b7280;
        font-size: 11px;
        font-weight: 600;
        transition: 0.2s;
    }
    
    .mn-item.active,
    .mn-item:hover {
        color: var(--primary);
    }
    
    .mn-item i {
        font-size: 20px;
        margin-bottom: 2px;
    }

    /* ==========================================================================
       TINY TOAST NOTIFICATION
    ========================================================================== */
    #gb-toast {
        position: fixed;
        top: 70px;
        right: 15px;
        background: var(--success);
        color: #fff;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        z-index: 2147483647;
        transform: translateX(calc(100% + 20px));
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 6px;
        max-width: 200px;
        pointer-events: none;
    }
    
    #gb-toast.show {
        transform: translateX(0);
    }
    
    #gb-toast.error {
        background: var(--error);
    }
    
    #gb-toast.warning {
        background: var(--warning);
    }
    
    #gb-toast i {
        font-size: 12px;
    }
    
    @media (max-width: 480px) {
        #gb-toast {
            top: auto;
            bottom: 75px;
            right: 10px;
            left: 10px;
            max-width: none;
            justify-content: center;
            font-size: 11px;
            padding: 7px 10px;
        }
    }

    /* ==========================================================================
       WHATSAPP DRAWER
    ========================================================================== */
    #wa-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.6);
        z-index: -1;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s;
        backdrop-filter: blur(2px);
    }
    
    #wa-overlay.active {
        z-index: 1000002;
        opacity: 1;
        pointer-events: auto;
    }

    #wa-drawer {
        position: fixed;
        top: 0;
        right: -450px;
        width: 100%;
        max-width: 400px;
        height: 100%;
        background: #fff;
        z-index: -1;
        transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        box-shadow: -5px 0 30px rgba(0,0,0,0.2);
    }
    
    #wa-drawer.active {
        z-index: 1000003;
        right: 0;
    }

    .wa-drawer-header {
        background: var(--wa-green);
        color: #fff;
        padding: 18px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 17px;
        font-weight: 800;
    }
    
    .wa-drawer-header i {
        margin-right: 8px;
    }
    
    .wa-close-btn {
        background: rgba(255,255,255,0.2);
        border: none;
        color: #fff;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        font-size: 22px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }
    
    .wa-close-btn:hover {
        background: rgba(255,255,255,0.3);
    }

    .wa-drawer-body {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #f9fafb;
    }

    .wa-info-box {
        font-size: 13px;
        color: #047857;
        background: #ecfdf5;
        padding: 12px 15px;
        border-radius: 8px;
        border-left: 4px solid var(--wa-green);
        line-height: 1.5;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    
    .wa-info-box i {
        color: var(--wa-green);
        margin-top: 2px;
    }

    .wa-form-group {
        margin-bottom: 16px;
    }
    
    .wa-form-group label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 6px;
    }
    
    .wa-form-group input,
    .wa-form-group select,
    .wa-form-group textarea {
        width: 100%;
        padding: 12px 14px;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        font-size: 15px;
        font-family: inherit;
        background: #fff;
        transition: border-color 0.2s, box-shadow 0.2s;
        color: #111;
    }
    
    .wa-form-group input:focus,
    .wa-form-group select:focus,
    .wa-form-group textarea:focus {
        outline: none;
        border-color: var(--wa-green);
        box-shadow: 0 0 0 3px rgba(37,211,102,0.1);
    }
    
    .wa-form-group input.error,
    .wa-form-group select.error {
        border-color: var(--error);
        box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
    }
    
    .wa-form-group textarea {
        resize: vertical;
        min-height: 60px;
    }

    .wa-drawer-footer {
        padding: 16px 20px;
        border-top: 1px solid #e5e7eb;
        background: #fff;
    }

    .wa-submit-btn {
        width: 100%;
        background: var(--wa-green);
        color: #fff;
        border: none;
        padding: 14px 20px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        font-family: inherit;
        transition: 0.2s;
    }
    
    .wa-submit-btn:hover {
        background: #1ebe57;
    }
    
    .wa-submit-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    .wa-submit-btn i {
        font-size: 18px;
    }
    
    @media (max-width: 480px) {
        #wa-drawer {
            max-width: 100%;
        }
    }

    /* Custom CSS from Admin */
    <?php echo $custom_css; ?>
    </style>

    <?php 
        if (file_exists('includes/dynamic_card_styles.php')) {
            include 'includes/dynamic_card_styles.php';
        }
        if (file_exists('includes/dynamic_product_styles.php')) {
            include 'includes/dynamic_product_styles.php';
        }
    ?>

    <script>
    var clicked_status = false;

    function renderButton() {
        if (typeof gapi !== 'undefined' && gapi.signin2) {
            gapi.signin2.render('gSignIn', {
                'scope': 'profile email',
                'width': 240,
                'height': 50,
                'longtitle': true,
                'theme': 'dark',
                'onsuccess': onSuccess,
                'onfailure': onFailure
            });
        }
    }
    
    function startFunction() {
        clicked_status = true;
    }

    function onSuccess(googleUser) {
        if (clicked_status && typeof Swal !== 'undefined') {
            Swal.fire({
                timer: 30000,
                didOpen: function() { Swal.showLoading(); }
            });
        }
        
        if (typeof gapi !== 'undefined' && gapi.client) {
            gapi.client.load('oauth2', 'v2', function () {
                gapi.client.oauth2.userinfo.get({ 'userId': 'me' }).execute(function (resp) {
                    if (typeof $ !== 'undefined') {
                        $.post('action/login_google.php', { oauth_provider: 'google', userData: JSON.stringify(resp) });
                    }
                    
                    var userEl = document.getElementById("username_2");
                    var passEl = document.getElementById("password_2");
                    if (userEl) userEl.value = resp.id;
                    if (passEl) passEl.value = resp.id;

                    if (clicked_status) {
                        var myInterv = setInterval(function() {
                            if (userEl && userEl.value === resp.id) {
                                clearInterval(myInterv);
                                if (typeof login2 === 'function') login2();
                            }
                        }, 1000);
                    }
                });
            });
        }
    }
    
    function onFailure(error) {
        clicked_status = false;
    }
    
    function signOut() {
        if (typeof gapi !== 'undefined' && gapi.auth2) {
            var auth2 = gapi.auth2.getAuthInstance();
            if (auth2) {
                auth2.signOut();
                auth2.disconnect();
            }
        }
    }
    </script>
    
    <!-- HubSpot -->
    <script type="text/javascript" id="hs-script-loader" async defer src="//js-eu1.hs-scripts.com/148034548.js"></script>
</head>
<body class="config">

    <!-- ==========================================================================
         PRELOADER
    ========================================================================== -->
    <div id="sitePreloader">
        <div>
            <img src="images/preloader.png" alt="Loading..." onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAiIGhlaWdodD0iNTAiIHZpZXdCb3g9IjAgMCA1MCA1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGN4PSIyNSIgY3k9IjI1IiByPSIyMCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjZmY1MDAwIiBzdHJva2Utd2lkdGg9IjQiIHN0cm9rZS1kYXNoYXJyYXk9IjMxLjQgMzEuNCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+PC9zdmc+'">
        </div>
    </div>
    <script>
    (function(){
        function hidePreloader() {
            var p = document.getElementById('sitePreloader');
            if (p) {
                p.classList.add('hidden');
                p.style.opacity = '0';
                p.style.visibility = 'hidden';
                p.style.pointerEvents = 'none';
                setTimeout(function() {
                    if (p) p.style.display = 'none';
                }, 400);
            }
        }
        setTimeout(hidePreloader, 1500);
        window.addEventListener('load', hidePreloader);
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(hidePreloader, 300);
        });
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(hidePreloader, 100);
        }
    })();
    </script>

    <!-- Hidden Legacy Elements -->
    <div style="display:none;" id="hidden-sinks-for-legacy-scripts">
        <span id="cart_items_count_2">0</span>
        <span id="cart_items_count_3">0</span>
        <span id="cart_items_count">0</span>
        <span id="total">0</span>
        <span id="total_price">0</span>
        <span id="sub_total">0</span>
        <span id="tax">0</span>
        <span id="delivery_fee">0</span>
        <span id="shipping_fee">0</span>
    </div>

    <!-- ==========================================================================
         HEADER
    ========================================================================== -->
    <header class="gb-header-wrapper">
        <!-- Top Bar -->
        <div class="gb-topbar">
            <div class="container">
                <div class="gb-top-links">
                    <a href="tel:+250788225709"><i class="fas fa-phone-alt"></i> Call: +250 788 225 709</a>
                    <a href="mailto:info@gbdelivering.com"><i class="fas fa-envelope"></i> Mail: info@gbdelivering.com</a>
                </div>
                <div class="gb-top-links">
                    <a href="index.php?track-my-order"><i class="fas fa-truck"></i> Track Order</a>
                    <?php if($login_status): ?>
                        <a href="index.php?dashboard"><i class="fas fa-user-circle"></i> Account</a>
                        <a href="?sign=out" onclick="signOut()"><i class="fas fa-sign-out-alt" style="color:#ef4444;"></i> Logout</a>
                    <?php else: ?>
                        <a href="index.php?sign-in"><i class="fas fa-sign-in-alt"></i> Sign In / Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main Header -->
        <div class="gb-main-header">
            <div class="container">
                <button class="gb-menu-btn" aria-label="Open Menu" onclick="toggleMobileDrawer()">
                    <i class="fas fa-bars"></i>
                </button>
                
                <a href="index.php" class="gb-logo">
                    <?php if(!empty($site_logo)): ?>
                        <img src="<?php echo htmlspecialchars((string)$site_logo); ?>" alt="<?php echo htmlspecialchars((string)$site_name); ?>" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='block';">
                        <h2 style="display:none;">GB<span>delivering</span></h2>
                    <?php else: ?>
                        <h2>GB<span>delivering</span></h2>
                    <?php endif; ?>
                </a>

                <div class="live-search-wrapper">
                    <form class="gb-search-form" method="get" action="index.php">
                        <input type="hidden" name="shop-search" value="">
                        <select name="category" class="gb-search-cat" id="search_category">
                            <option value="">All Categories</option>
                            <?php foreach($nav_categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars((string)$cat['category_name']); ?>"><?php echo htmlspecialchars((string)$cat['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                        <?php $search_val = $_GET['search'] ?? ''; ?>
                        <input type="text" name="search" class="gb-search-input" value="<?php echo htmlspecialchars((string)$search_val); ?>" id="main-search" placeholder="Search for groceries, electronics, fashion..." autocomplete="off" onkeyup="if(typeof debounceSearch === 'function') debounceSearch(this.value)">
                        
                        <button type="submit" class="gb-search-btn" aria-label="Search">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                    <div class="live-search-results" id="live-search-results"></div>
                </div>

                <div class="gb-actions">
                    <a href="index.php?wishlist" class="gb-action-btn">
                        <i class="far fa-heart"></i>
                        <span>Wishlist</span>
                    </a>
                    <button class="gb-action-btn" onclick="toggleCartDrawer()">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Cart</span>
                        <div class="gb-cart-badge" id="cart_items_count_1"><?php echo (int)$cart_count; ?></div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Navigation Bar -->
        <div class="gb-nav-wrapper">
            <div class="container">
                <div class="hdr-cat-wrap">
                    <button class="gb-cat-btn" onclick="window.location.href='index.php?shop'">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <i class="fas fa-bars"></i> ALL CATEGORIES
                        </div>
                        <i class="fas fa-chevron-down" style="font-size:12px;"></i>
                    </button>
                    <div class="hdr-mega-menu">
                        <?php foreach ($nav_categories as $cat): ?>
                            <a href="index.php?shop-search&search=<?php echo urlencode($cat['category_name']); ?>"><?php echo htmlspecialchars((string)$cat['category_name']); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="gb-nav-links">
                    <a href="index.php" class="gb-nav-link">Home</a>
                    <a href="index.php?new-arrivals" class="gb-nav-link">New Arrivals</a>
                    <a href="index.php?shop" class="gb-nav-link">Shop</a>
                    <a href="index.php?track-my-order" class="gb-nav-link">Track Order</a>
                    <?php
                    if(isset($conn) && is_object($conn)) {
                        try {
                            $menu_res = $conn->query("SELECT title, slug FROM custom_pages WHERE status = 1 AND show_in_header = 1 ORDER BY id ASC");
                            if ($menu_res && $menu_res->num_rows > 0) {
                                while ($link = $menu_res->fetch_assoc()) {
                                    echo '<a href="index.php?page_slug='.htmlspecialchars((string)$link['slug']).'" class="gb-nav-link">'.strtoupper(htmlspecialchars((string)$link['title'])).'</a>';
                                }
                            } else {
                                echo '<a href="index.php?about-us" class="gb-nav-link">About Us</a>';
                                echo '<a href="index.php?contact-us" class="gb-nav-link">Contact Us</a>';
                            }
                        } catch(Exception $e) { }
                    }
                    ?>
                </div>
            </div>
        </div>
    </header>

    <!-- ==========================================================================
         MOBILE SIDE DRAWER
    ========================================================================== -->
    <div class="drawer-overlay" id="mobileDrawerOverlay" onclick="toggleMobileDrawer()"></div>
    <div class="side-drawer" id="mobileDrawer">
        <div class="drawer-header">
            <h3><i class="fas fa-user-circle" style="font-size:24px;"></i> Hello, <?php echo $login_status ? htmlspecialchars((string)$user_first_name) : 'Sign In'; ?></h3>
            <button class="drawer-close" onclick="toggleMobileDrawer()">&times;</button>
        </div>
        <div class="drawer-body">
            <div class="drawer-title">Navigation</div>
            <a href="index.php" class="drawer-link"><i class="fas fa-home"></i> Home</a>
            <a href="index.php?shop" class="drawer-link"><i class="fas fa-store"></i> Shop</a>
            <a href="javascript:void(0);" onclick="toggleMobileDrawer(); setTimeout(toggleCartDrawer, 300);" class="drawer-link"><i class="fas fa-shopping-cart"></i> Cart</a>
            <a href="index.php?wishlist" class="drawer-link"><i class="fas fa-heart"></i> Wishlist</a>
            <a href="index.php?track-my-order" class="drawer-link"><i class="fas fa-truck"></i> Track Order</a>
            
            <div class="drawer-title">Categories</div>
            <?php 
            $c_count = 0; 
            foreach($nav_categories as $cat): 
                if($c_count++ >= 6) break; 
            ?>
                <a href="index.php?shop-search&search=<?php echo urlencode($cat['category_name']); ?>" class="drawer-link" style="padding-left:30px;">
                    <i class="fas fa-angle-right" style="font-size:12px;"></i> <?php echo htmlspecialchars((string)$cat['category_name']); ?>
                </a>
            <?php endforeach; ?>
            <a href="index.php?shop" class="drawer-link" style="color:var(--primary); font-weight:800; padding-left:30px;">View All Categories</a>

            <div class="drawer-title">Account</div>
            <?php if ($login_status): ?>
                <a href="index.php?dashboard" class="drawer-link"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="?sign=out" class="drawer-link" style="color:#ef4444;"><i class="fas fa-sign-out-alt" style="color:#ef4444;"></i> Logout</a>
            <?php else: ?>
                <a href="index.php?sign-in" class="drawer-link" style="color:var(--primary);"><i class="fas fa-sign-in-alt" style="color:var(--primary);"></i> Sign In / Register</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ==========================================================================
         MAIN APP WRAPPER
    ========================================================================== -->
    <div id="app">
        <input type="hidden" id="customer_temp_id" value="<?php echo htmlspecialchars((string)$customer_temp_id); ?>">
        
        <div class="app-content">
            <?php
            $route_found = false;

            $routes = [
                'home'             => 'home.php',
                'home_1'           => 'home_1.php',
                'about-us'         => 'about-us.php',
                'contact-us'       => 'contact-us.php',
                'sign-in-old'      => 'sign-in-old.php',
                'sign-in'          => 'sign-in.php',
                'cont-sign-in'     => 'cont-sign-in.php',
                'sign-up'          => 'sign-up.php',
                'cart'             => 'cart.php',
                'wishlist'         => 'wishlist.php',
                'checkout_old_1'   => 'checkout_old_1.php',
                'checkout_old_2'   => 'checkout_old_2.php',
                'checkout_old_3'   => 'checkout_old_3.php',
                'checkout_new'     => 'checkout_new.php',
                'shop'             => 'shop.php',
                'terms_conditions' => 'terms_conditions.php',
                'shop-search'      => 'shop-search.php',
                'product-detail'   => 'product-detail.php',
                'new-arrivals'     => 'new-arrivals.php',
                'track-my-order'   => 'track-my-order.php',
                'track-order'      => 'track-order.php',
                'dashboard_old_1'  => 'dashboard_old_1.php',
                'my-profile'       => 'my-profile.php',
                'manage_address'   => 'manage_address.php',
                'new_address'      => 'new_address.php',
                'my-orders'        => 'my-orders.php',
                'lost-password'    => 'lost-password.php',
                'reset-password'   => 'reset-password.php'
            ];

            if (isset($_GET['page_slug'])) {
                $_GET['slug'] = $_GET['page_slug'];
                if (file_exists("page.php")) {
                    include("page.php");
                    $route_found = true;
                }
            } elseif (isset($_GET['checkout'])) {
                if (!isset($_SESSION['GBDELIVERING_CUSTOMER_USER_2021'])) {
                    if (file_exists("sign-in.php")) include("sign-in.php");
                } else {
                    if (file_exists("checkout.php")) include("checkout.php");
                }
                $route_found = true;
            } else {
                foreach ($routes as $param => $file) {
                    if (isset($_GET[$param])) {
                        if (file_exists($file)) {
                            include($file);
                        }
                        $route_found = true;
                        break;
                    }
                }
            }

            if (!$route_found) {
                if (file_exists("home.php")) include("home.php");
            }
            ?>
        </div>

        <?php 
        if (file_exists($theme_footer_file)) {
            include $theme_footer_file;
        } else {
            if (file_exists("includes/front-footer.php")) include "includes/front-footer.php";
        }
        ?>

        <!-- Hidden Legacy Modal -->
        <div class="modal fade" id="quick-look" style="display:none !important;"></div>

        <!-- ==========================================================================
             CART DRAWER
        ========================================================================== -->
        <div class="cart-overlay" id="cart-overlay" onclick="toggleCartDrawer()"></div>
        <div class="cart-drawer" id="cart-drawer">
            <div class="cart-header">
                <span><i class="fas fa-shopping-bag" style="color:var(--primary)"></i> Shopping Cart</span>
                <button class="cart-close" aria-label="Close cart" onclick="toggleCartDrawer()">&times;</button>
            </div>
            <div class="cart-body" id="drawer-cart-items">
                <div style="text-align:center; padding: 60px 20px; color:#999;">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p style="margin-top:15px; font-weight:600;">Loading Cart...</p>
                </div>
            </div>
            <div class="cart-footer" id="drawer-cart-footer" style="display:none;">
                <button onclick="openWaDeliveryDrawer(event)" class="checkout-btn whatsapp-btn">
                    <i class="fab fa-whatsapp" style="font-size: 18px;"></i>
                    <span>Order via WhatsApp</span>
                </button>
            </div>
        </div>

        <!-- Mobile Bottom Navigation -->
        <nav class="mobile-nav">
            <div class="mn-inner">
                <a href="index.php" class="mn-item <?php echo (!isset($_GET) || empty($_GET) || isset($_GET['home'])) ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>Home
                </a>
                <a href="index.php?shop" class="mn-item <?php echo isset($_GET['shop']) ? 'active' : ''; ?>">
                    <i class="fas fa-search"></i>Shop
                </a>
                <a href="javascript:void(0);" class="mn-item" onclick="toggleCartDrawer()">
                    <i class="fas fa-shopping-bag"></i>Cart
                </a>
                <a href="index.php?<?php echo $login_status ? 'dashboard' : 'sign-in'; ?>" class="mn-item <?php echo (isset($_GET['dashboard']) || isset($_GET['sign-in'])) ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i>Account
                </a>
            </div>
        </nav>

        <!-- Tiny Toast Container -->
        <div id="gb-toast"></div>
    </div>

    <!-- ==========================================================================
         WHATSAPP DELIVERY DRAWER
    ========================================================================== -->
    <div id="wa-overlay" onclick="closeWaDeliveryDrawer()"></div>
    <div id="wa-drawer">
        <div class="wa-drawer-header">
            <span><i class="fab fa-whatsapp"></i> WhatsApp Order</span>
            <button onclick="closeWaDeliveryDrawer()" class="wa-close-btn">&times;</button>
        </div>
        <div class="wa-drawer-body">
            <div class="wa-info-box">
                <i class="fas fa-info-circle"></i>
                <span>Please provide your delivery details. We'll confirm your order and delivery fee on WhatsApp!</span>
            </div>
            
            <div class="wa-form-group">
                <label>Full Name *</label>
                <input type="text" id="wa_full_name" placeholder="e.g. John Doe">
            </div>
            
            <div class="wa-form-group">
                <label>Phone Number *</label>
                <input type="tel" id="wa_phone" placeholder="e.g. 0788123456">
            </div>
            
            <div class="wa-form-group">
                <label>Province *</label>
                <select id="wa_province" onchange="fetchWaDistricts(this.value)">
                    <option value="">-- Select Province --</option>
                    <option value="Kigali City">Kigali City</option>
                    <option value="Eastern Province">Eastern Province</option>
                    <option value="Northern Province">Northern Province</option>
                    <option value="Southern Province">Southern Province</option>
                    <option value="Western Province">Western Province</option>
                </select>
            </div>
            
            <div class="wa-form-group">
                <label>District *</label>
                <select id="wa_district" onchange="fetchWaSectors(this.value)">
                    <option value="">-- Select District --</option>
                </select>
            </div>
            
            <div class="wa-form-group">
                <label>Sector *</label>
                <select id="wa_sector">
                    <option value="">-- Select Sector --</option>
                </select>
            </div>
            
            <div class="wa-form-group">
                <label>Street / Exact Location *</label>
                <input type="text" id="wa_street" placeholder="e.g. KG 11 Ave">
            </div>
            
            <div class="wa-form-group">
                <label>Order Notes (Optional)</label>
                <textarea id="wa_notes" placeholder="Any special instructions..." rows="2"></textarea>
            </div>
        </div>
        <div class="wa-drawer-footer">
            <button type="button" onclick="submitWaOrder(this)" class="wa-submit-btn">
                <i class="fab fa-whatsapp"></i>
                <span>Send Order to WhatsApp</span>
            </button>
        </div>
    </div>

    <?php if (file_exists("includes/front-script.php")) include "includes/front-script.php"; ?>

    <script>
    (function() {
        'use strict';

        // ==========================================================================
        // TINY TOAST NOTIFICATION
        // ==========================================================================
        window.gbToast = function(message, type) {
            type = type || 'success';
            var toast = document.getElementById('gb-toast');
            
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'gb-toast';
                document.body.appendChild(toast);
            }
            
            toast.classList.remove('show', 'error', 'warning');
            
            if (type === 'error') {
                toast.classList.add('error');
            } else if (type === 'warning') {
                toast.classList.add('warning');
            }
            
            var icon = 'check-circle';
            if (type === 'error') icon = 'times-circle';
            else if (type === 'warning') icon = 'exclamation-circle';
            
            toast.innerHTML = '<i class="fas fa-' + icon + '"></i>' + message;
            
            requestAnimationFrame(function() {
                toast.classList.add('show');
            });
            
            setTimeout(function() {
                toast.classList.remove('show');
            }, 2000);
        };
        
        window.showUXToast = function(message, isError) {
            gbToast(message, isError ? 'error' : 'success');
        };

        // ==========================================================================
        // SWEETALERT INTERCEPTOR
        // ==========================================================================
        var swalCheckInterval = setInterval(function() {
            if (typeof Swal !== 'undefined') {
                clearInterval(swalCheckInterval);
                var originalSwal = Swal.fire;
                
                Swal.fire = function() {
                    var options = arguments[0];
                    
                    if (typeof options === 'string') {
                        var msg = options.toUpperCase();
                        if (msg === 'SUCCESS' || msg === 'ADDED' || msg.includes('CART')) {
                            gbToast('Added to cart!');
                            return Promise.resolve({ isConfirmed: true });
                        }
                    }
                    
                    if (typeof options === 'object' && options !== null) {
                        var title = (options.title || '').toString().toUpperCase();
                        if (title === 'SUCCESS' || title.includes('ADDED') || title.includes('CART')) {
                            gbToast(options.title || 'Added to cart!');
                            return Promise.resolve({ isConfirmed: true });
                        }
                    }
                    
                    return originalSwal.apply(this, arguments);
                };
            }
        }, 100);

        // ==========================================================================
        // MOBILE DRAWER
        // ==========================================================================
        window.toggleMobileDrawer = function() {
            var drawer = document.getElementById('mobileDrawer');
            var overlay = document.getElementById('mobileDrawerOverlay');
            
            if (drawer && overlay) {
                drawer.classList.toggle('active');
                overlay.classList.toggle('active');
                document.body.style.overflow = drawer.classList.contains('active') ? 'hidden' : '';
            }
        };

        // ==========================================================================
        // LIVE SEARCH
        // ==========================================================================
        var searchTimer;
        window.debounceSearch = function(query) {
            if (typeof $ === 'undefined') return;
            clearTimeout(searchTimer);
            
            var resultBox = document.getElementById('live-search-results');
            if (!resultBox) return;
            
            if (query.trim().length < 2) {
                resultBox.style.display = 'none';
                return;
            }
            
            searchTimer = setTimeout(function() {
                $.post('action/search_action.php', { keyword: query, action: 'live_search' }, function(res) {
                    if (res && res.trim()) {
                        resultBox.innerHTML = res;
                    } else {
                        resultBox.innerHTML = '<div style="padding:15px; text-align:center; color:#666;">Press Enter to search...</div>';
                    }
                    resultBox.style.display = 'block';
                });
            }, 300);
        };
        
        document.addEventListener('click', function(e) {
            var wrapper = document.querySelector('.live-search-wrapper');
            var results = document.getElementById('live-search-results');
            if (wrapper && results && !wrapper.contains(e.target)) {
                results.style.display = 'none';
            }
        });

        // ==========================================================================
        // CART DRAWER
        // ==========================================================================
        window.toggleCartDrawer = function() {
            if (typeof $ === 'undefined') {
                window.location.href = 'index.php?cart';
                return;
            }
            
            var drawer = document.getElementById('cart-drawer');
            var overlay = document.getElementById('cart-overlay');
            
            if (!drawer || !overlay) return;
            
            var isOpening = !drawer.classList.contains('active');
            drawer.classList.toggle('active');
            overlay.classList.toggle('active');
            
            if (isOpening) {
                document.getElementById('drawer-cart-items').innerHTML = '<div style="text-align:center; padding:60px 20px;"><i class="fas fa-spinner fa-spin fa-3x" style="color:var(--primary);"></i><p style="margin-top:20px; font-weight:600; color:#4b5563;">Loading...</p></div>';
                document.getElementById('drawer-cart-footer').style.display = 'none';
                loadDrawerCartItems();
            }
        };
        
        window.loadDrawerCartItems = function() {
            if (typeof $ === 'undefined') return;
            
            $.post('action/get_cart_items_desc.php', function(data) {
                var container = document.getElementById('drawer-cart-items');
                var footer = document.getElementById('drawer-cart-footer');
                
                if (data && data.trim() && data.toLowerCase().indexOf('empty') === -1) {
                    container.innerHTML = data;
                    footer.style.display = 'flex';
                } else {
                    container.innerHTML = '<div style="text-align:center; padding:60px 20px;"><i class="fas fa-shopping-cart fa-4x" style="color:#e5e7eb; margin-bottom:20px;"></i><h3 style="color:#6b7280; font-size:16px; margin:0 0 8px;">Your cart is empty</h3><p style="color:#9ca3af; font-size:14px; margin:0 0 20px;">Add some products!</p><button onclick="toggleCartDrawer(); window.location.href=\'index.php?shop\';" style="background:var(--primary); color:#fff; padding:12px 24px; border-radius:8px; border:none; font-weight:600; cursor:pointer;">Start Shopping</button></div>';
                    footer.style.display = 'none';
                }
            }).fail(function() {
                document.getElementById('drawer-cart-items').innerHTML = '<div style="padding:40px 20px; text-align:center;"><i class="fas fa-exclamation-triangle fa-3x" style="color:#ef4444; margin-bottom:15px;"></i><p style="color:#ef4444; font-weight:600;">Failed to load cart.</p><button onclick="loadDrawerCartItems()" style="margin-top:15px; padding:10px 20px; cursor:pointer; border-radius:6px; border:1px solid #ddd; background:#fff;">Retry</button></div>';
                document.getElementById('drawer-cart-footer').style.display = 'none';
            });
        };

        // ==========================================================================
        // WHATSAPP DRAWER
        // ==========================================================================
        window.openWaDeliveryDrawer = function(e) {
            if (e) e.preventDefault();
            document.getElementById('wa-overlay').classList.add('active');
            document.getElementById('wa-drawer').classList.add('active');
            document.body.style.overflow = 'hidden';
        };
        
        window.closeWaDeliveryDrawer = function() {
            document.getElementById('wa-overlay').classList.remove('active');
            document.getElementById('wa-drawer').classList.remove('active');
            document.body.style.overflow = '';
        };

        // ==========================================================================
        // LOCATION FETCHING
        // ==========================================================================
        window.fetchWaDistricts = function(province) {
            if (typeof $ === 'undefined' || !province) {
                document.getElementById('wa_district').innerHTML = '<option value="">-- Select District --</option>';
                document.getElementById('wa_sector').innerHTML = '<option value="">-- Select Sector --</option>';
                return;
            }
            
            document.getElementById('wa_district').innerHTML = '<option value="">Loading...</option>';
            document.getElementById('wa_sector').innerHTML = '<option value="">-- Select Sector --</option>';
            
            var urls = ['action/get_district.php', 'action/get_districts.php', 'includes/get_district.php', 'includes/get_districts.php'];
            
            function tryFetch(index, prov) {
                if (index >= urls.length) {
                    if (prov === 'Kigali City') {
                        tryFetch(0, 'Kigali');
                        return;
                    }
                    document.getElementById('wa_district').innerHTML = '<option value="">Failed</option>';
                    return;
                }
                
                $.post(urls[index], { province: prov, province_id: prov, province_name: prov }, function(res) {
                    if (res && res.indexOf('<option') !== -1) {
                        document.getElementById('wa_district').innerHTML = '<option value="">-- Select District --</option>' + res;
                    } else {
                        tryFetch(index + 1, prov);
                    }
                }).fail(function() {
                    tryFetch(index + 1, prov);
                });
            }
            
            tryFetch(0, province);
        };
        
        window.fetchWaSectors = function(district) {
            if (typeof $ === 'undefined' || !district) {
                document.getElementById('wa_sector').innerHTML = '<option value="">-- Select Sector --</option>';
                return;
            }
            
            document.getElementById('wa_sector').innerHTML = '<option value="">Loading...</option>';
            
            var urls = ['action/get_sector.php', 'action/get_sectors.php', 'includes/get_sector.php', 'includes/get_sectors.php'];
            
            function tryFetch(index) {
                if (index >= urls.length) {
                    document.getElementById('wa_sector').innerHTML = '<option value="">Failed</option>';
                    return;
                }
                
                $.post(urls[index], { district: district, district_id: district }, function(res) {
                    if (res && res.indexOf('<option') !== -1) {
                        document.getElementById('wa_sector').innerHTML = '<option value="">-- Select Sector --</option>' + res;
                    } else {
                        tryFetch(index + 1);
                    }
                }).fail(function() {
                    tryFetch(index + 1);
                });
            }
            
            tryFetch(0);
        };

        // ==========================================================================
        // FORMAT NUMBER HELPER
        // ==========================================================================
        function formatNumber(num) {
            return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        // ==========================================================================
        // SUBMIT WHATSAPP ORDER
        // Message Format:
        // 1Kilogram(kg) Chicken Wings x 8,500 = 8,500
        // ==========================================================================
        window.submitWaOrder = function(btn) {
            var nameEl = document.getElementById('wa_full_name');
            var phoneEl = document.getElementById('wa_phone');
            var provEl = document.getElementById('wa_province');
            var distEl = document.getElementById('wa_district');
            var sectEl = document.getElementById('wa_sector');
            var streetEl = document.getElementById('wa_street');
            var notesEl = document.getElementById('wa_notes');

            var name = nameEl.value.trim();
            var phone = phoneEl.value.trim();
            var province = provEl.options[provEl.selectedIndex] ? provEl.options[provEl.selectedIndex].text : '';
            var district = distEl.options[distEl.selectedIndex] ? distEl.options[distEl.selectedIndex].text : '';
            var sector = sectEl.options[sectEl.selectedIndex] ? sectEl.options[sectEl.selectedIndex].text : '';
            var street = streetEl.value.trim();
            var notes = notesEl ? notesEl.value.trim() : '';

            // Reset errors
            [nameEl, phoneEl, provEl, distEl, sectEl, streetEl].forEach(function(el) {
                if (el) el.classList.remove('error');
            });

            // Validation
            var valid = true;
            if (!name) { nameEl.classList.add('error'); valid = false; }
            if (!phone) { phoneEl.classList.add('error'); valid = false; }
            if (!provEl.value) { provEl.classList.add('error'); valid = false; }
            if (!distEl.value || district.includes('Select') || district.includes('Failed')) { distEl.classList.add('error'); valid = false; }
            if (!sectEl.value || sector.includes('Select') || sector.includes('Failed')) { sectEl.classList.add('error'); valid = false; }
            if (!street) { streetEl.classList.add('error'); valid = false; }

            if (!valid) {
                gbToast('Please fill all required fields', 'error');
                return;
            }

            var originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            btn.disabled = true;

            $.post('index.php', { action: 'get_cart_for_whatsapp', sector: sector }, function(data) {
                var items = data.items || [];
                var subtotal = data.subtotal || 0;
                var deliveryFee = data.delivery_fee || 0;
                var total = subtotal + deliveryFee;

                // BUILD WHATSAPP MESSAGE
                var msg = '🛒 NEW ORDER - GERMAN BUTCHERY\n';
                msg += '━━━━━━━━━━━━━━━━━━━\n';

                items.forEach(function(item) {
                    var qtyStr = item.qty % 1 === 0 ? item.qty.toString() : item.qty.toFixed(2);
                    var unitStr = (item.unit && item.unit.trim()) ? item.unit.trim() : 'x';
                    msg += qtyStr + unitStr + ' ' + item.name + ' x ' + formatNumber(item.unit_price) + ' = ' + formatNumber(item.total) + '\n';
                });

                msg += '───────────────────────────\n';
                msg += 'Subtotal: ' + formatNumber(subtotal) + ' RWF\n';
                msg += 'Delivery Fee: ' + formatNumber(deliveryFee) + ' RWF\n';
                msg += 'TOTAL: ' + formatNumber(total) + ' RWF\n';
                msg += '\n';
                msg += 'Name: ' + name + '\n';
                msg += 'Phone: ' + phone;

                var waUrl = 'https://wa.me/250788225709?text=' + encodeURIComponent(msg);

                $.post('index.php', { ajax_clear_cart: '1' }, function() {
                    window.open(waUrl, '_blank');

                    document.querySelectorAll('[id^="cart_items_count"]').forEach(function(el) {
                        el.innerText = '0';
                    });

                    var cartItems = document.getElementById('drawer-cart-items');
                    if (cartItems) {
                        cartItems.innerHTML = '<div style="text-align:center; padding:60px 20px;"><i class="fas fa-check-circle fa-4x" style="color:#10b981; margin-bottom:20px;"></i><h3 style="color:#111; font-size:18px; margin:0 0 8px;">Order Sent!</h3><p style="color:#6b7280; font-size:14px;">Check WhatsApp</p></div>';
                    }

                    document.getElementById('drawer-cart-footer').style.display = 'none';

                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                    closeWaDeliveryDrawer();
                });
            }, 'json').fail(function() {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            });
        };

        // ==========================================================================
        // WHATSAPP BUTTON INTERCEPTOR
        // ==========================================================================
        document.addEventListener('click', function(e) {
            var target = e.target.closest('a, button');
            if (!target || target.closest('#wa-drawer')) return;
            
            var isWaBtn = target.classList.contains('whatsapp-btn') || target.closest('.whatsapp-btn');
            var text = (target.textContent || '').toLowerCase();
            var isWaText = text.includes('whatsapp') && (text.includes('order') || text.includes('checkout'));
            var isCartCheckout = target.classList.contains('checkout-btn') && target.closest('#cart-drawer');
            
            if (isWaBtn || isWaText || isCartCheckout) {
                e.preventDefault();
                e.stopPropagation();
                openWaDeliveryDrawer(e);
                return false;
            }
        }, true);

        // ==========================================================================
        // CART COUNT UPDATER
        // ==========================================================================
        var originalGetCartItems = window.get_cart_items;
        window.get_cart_items = function() {
            if (typeof originalGetCartItems === 'function') {
                try { originalGetCartItems(); } catch(e) {}
            }
            
            if (typeof $ !== 'undefined') {
                $.post('action/insert.php', { action: 'GET_CART_COUNT' }, function(res) {
                    var n = parseInt(res, 10);
                    if (!isNaN(n) && n >= 0) {
                        document.querySelectorAll('[id^="cart_items_count"]').forEach(function(el) {
                            el.innerText = n;
                        });
                    }
                });
            }
        };

        // Initialize on document ready
        if (typeof $ !== 'undefined') {
            $(function() {
                if (typeof window.get_cart_items === 'function') {
                    window.get_cart_items();
                }
            });
        }
    })();
    </script>
</body>
</html>
