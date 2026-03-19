<?php
/**
 * Modern E-commerce Header (Amazon/AliExpress Style)
 * Redesigned for perfect responsiveness and touch accessibility
 * GB Deliveries
 */

// Make sure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get cart count
$cart_count = 0;
$customer_temp_id = $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] ?? '';
if ($customer_temp_id) {
    $cart_query = $conn->query("SELECT c.cart_id FROM cart c WHERE c.customer_id='$customer_temp_id' AND c.status='ACTIVE'");
    if ($cart_query && $cart_row = $cart_query->fetch_assoc()) {
        $cart_id_for_count = $cart_row['cart_id'];
        $count_query = $conn->query("SELECT COUNT(*) as cnt FROM cart_item WHERE cart_id='$cart_id_for_count' AND status='ACTIVE'");
        if ($count_query && $count_row = $count_query->fetch_assoc()) {
            $cart_count = intval($count_row['cnt']);
        }
    }
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['GBDELIVERING_CUSTOMER_USER_2021']);
$user_name = $_SESSION['customer_name'] ?? '';

// Get categories for mega menu
$categories = [];
$cat_query = $conn->query("SELECT * FROM product_category WHERE status='ACTIVE' ORDER BY category_name ASC LIMIT 12");
if ($cat_query) {
    while ($cat_row = $cat_query->fetch_assoc()) {
        $categories[] = $cat_row;
    }
}

// Currency data for the dropdown
$gb_currencies = [
    ['code' => 'RWF', 'name' => 'Rwandan Franc', 'symbol' => 'RWF', 'flag' => '🇷🇼'],
    ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'flag' => '🇺🇸'],
    ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'flag' => '🇪🇺'],
    ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'flag' => '🇬🇧'],
    ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'flag' => '🇰🇪'],
    ['code' => 'UGX', 'name' => 'Ugandan Shilling', 'symbol' => 'USh', 'flag' => '🇺🇬'],
    ['code' => 'TZS', 'name' => 'Tanzanian Shilling', 'symbol' => 'TSh', 'flag' => '🇹🇿'],
    ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => '₦', 'flag' => '🇳🇬'],
    ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R', 'flag' => '🇿🇦'],
    ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'flag' => '🇨🇳'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>GB Deliveries - Fresh Groceries Delivered</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
    /* =========================================================
       GLOBAL UI VARIABLES (Isolated)
    ========================================================= */
    :root {
        --hdr-primary: #ff5000;
        --hdr-primary-hover: #e64a19;
        --hdr-dark: #111827;
        --hdr-gray-dark: #4b5563;
        --hdr-gray: #6b7280;
        --hdr-border: #e5e7eb;
        --hdr-bg: #f9fafb;
        --hdr-white: #ffffff;
        --hdr-accent: #ef4444;
        --hdr-max-width: 1400px;
    }
    
    body {
        margin: 0;
        padding: 0;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--hdr-bg);
        color: var(--hdr-dark);
        -webkit-font-smoothing: antialiased;
    }
    
    a { text-decoration: none; color: inherit; }
    
    .hdr-container {
        max-width: var(--hdr-max-width);
        margin: 0 auto;
        padding: 0 20px;
    }

    /* =========================================================
       TIER 1: TOP BAR
    ========================================================= */
    .hdr-topbar {
        background: var(--hdr-dark);
        color: #d1d5db;
        font-size: 13px;
        font-weight: 500;
    }
    
    .hdr-topbar .hdr-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 40px;
    }
    
    .hdr-top-links {
        display: flex;
        align-items: center;
        gap: 24px;
    }
    
    .hdr-top-links a {
        display: flex;
        align-items: center;
        gap: 8px;
        transition: color 0.2s;
    }
    
    .hdr-top-links a:hover { color: var(--hdr-white); }
    .hdr-top-links i { color: var(--hdr-primary); font-size: 14px; }

    /* Currency Dropdown */
    .hdr-currency-wrap { position: relative; z-index: 10001; }
    .hdr-currency-btn {
        display: flex; align-items: center; gap: 8px;
        background: rgba(255,255,255,0.1);
        border: none; border-radius: 6px;
        color: #fff; font-size: 13px; font-weight: 600;
        padding: 4px 12px; cursor: pointer; transition: 0.2s;
    }
    .hdr-currency-btn:hover { background: rgba(255,255,255,0.2); }
    .hdr-currency-dropdown {
        position: absolute; top: calc(100% + 5px); right: 0;
        background: var(--hdr-white); border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        min-width: 260px; display: none; overflow: hidden;
    }
    .hdr-currency-wrap.open .hdr-currency-dropdown { display: block; animation: dropIn 0.2s ease; }
    
    .hdr-currency-header {
        padding: 16px; background: var(--hdr-bg);
        border-bottom: 1px solid var(--hdr-border);
        color: var(--hdr-dark); font-weight: 700;
    }
    .hdr-currency-header p { margin: 4px 0 0; font-size: 12px; color: var(--hdr-gray); font-weight: 500; }
    
    .hdr-currency-list { max-height: 300px; overflow-y: auto; }
    .hdr-currency-item {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 16px; width: 100%; border: none; background: none;
        cursor: pointer; border-bottom: 1px solid var(--hdr-border);
        text-align: left; transition: 0.2s; color: var(--hdr-dark);
    }
    .hdr-currency-item:hover { background: #fff5f0; }
    .hdr-currency-item.active { background: #ffe0d1; }
    .hdr-currency-item .flag { font-size: 20px; }
    .hdr-currency-item .symbol { margin-left: auto; font-weight: 800; color: var(--hdr-primary); }

    @keyframes dropIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* =========================================================
       TIER 2: MAIN HEADER
    ========================================================= */
    .hdr-main {
        background: var(--hdr-white);
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        position: sticky; top: 0; z-index: 10000;
        padding: 16px 0;
    }
    
    .hdr-main .hdr-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 40px;
    }

    /* Mobile Hamburger */
    .hdr-mobile-menu {
        display: none;
        background: none; border: none;
        font-size: 24px; color: var(--hdr-dark);
        cursor: pointer; padding: 4px;
    }

    /* Logo */
    .hdr-logo {
        display: flex; align-items: center; gap: 10px; flex-shrink: 0;
    }
    .hdr-logo-icon {
        width: 48px; height: 48px;
        background: linear-gradient(135deg, var(--hdr-primary), #ff6a33);
        border-radius: 12px; display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 24px; box-shadow: 0 4px 10px rgba(255,80,0,0.2);
    }
    .hdr-logo-text {
        font-size: 26px; font-weight: 900; letter-spacing: -0.5px; color: #4b2671;
    }
    .hdr-logo-text span { color: var(--hdr-primary); }

    /* Search Bar (Amazon Style) */
    .hdr-search {
        flex: 1; min-width: 0; max-width: 800px;
    }
    .hdr-search-inner {
        display: flex; height: 48px;
        border: 2px solid var(--hdr-primary); border-radius: 8px;
        overflow: hidden; background: var(--hdr-white);
        transition: box-shadow 0.2s;
    }
    .hdr-search-inner:focus-within { box-shadow: 0 0 0 4px rgba(255,80,0,0.1); }
    
    .hdr-search-category {
        padding: 0 16px; border: none; border-right: 1px solid var(--hdr-border);
        background: var(--hdr-bg); font-size: 14px; font-weight: 600;
        color: var(--hdr-gray-dark); cursor: pointer; outline: none;
        appearance: none; width: 160px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%234b5563' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 12px center;
    }
    
    .hdr-search-input {
        flex: 1; padding: 0 20px; border: none; outline: none;
        font-size: 15px; font-family: 'Inter', sans-serif; font-weight: 500;
        color: var(--hdr-dark); min-width: 0;
    }
    
    .hdr-search-btn {
        padding: 0 24px; background: var(--hdr-primary); border: none;
        color: #fff; font-size: 18px; cursor: pointer; transition: 0.2s;
    }
    .hdr-search-btn:hover { background: var(--hdr-primary-hover); }

    /* Action Icons */
    .hdr-actions {
        display: flex; align-items: center; gap: 8px; flex-shrink: 0;
    }
    .hdr-action {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: 6px 12px; border-radius: 8px; color: var(--hdr-dark);
        position: relative; transition: 0.2s; min-width: 64px; cursor: pointer;
    }
    .hdr-action:hover { background: var(--hdr-bg); color: var(--hdr-primary); }
    .hdr-action i { font-size: 22px; margin-bottom: 4px; }
    .hdr-action span { font-size: 12px; font-weight: 700; }
    
    .hdr-badge {
        position: absolute; top: 0; right: 8px;
        background: var(--hdr-accent); color: #fff;
        font-size: 11px; font-weight: 800;
        min-width: 20px; height: 20px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        padding: 0 6px; border: 2px solid var(--hdr-white);
    }

    /* User Dropdown */
    .hdr-user-wrap { position: relative; }
    .hdr-user-dropdown {
        position: absolute; top: calc(100% + 5px); right: 0;
        background: var(--hdr-white); border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid var(--hdr-border);
        min-width: 240px; display: none; overflow: hidden; z-index: 10001;
    }
    .hdr-user-wrap:hover .hdr-user-dropdown { display: block; animation: dropIn 0.2s ease; }
    
    .hdr-user-header { padding: 20px; background: var(--hdr-bg); text-align: center; border-bottom: 1px solid var(--hdr-border); }
    .hdr-user-avatar {
        width: 56px; height: 56px; background: var(--hdr-primary);
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 24px; margin: 0 auto 12px;
    }
    .hdr-user-name { font-size: 16px; font-weight: 800; color: var(--hdr-dark); }
    
    .hdr-user-menu a {
        display: flex; align-items: center; gap: 12px; padding: 14px 20px;
        font-size: 14px; font-weight: 600; color: var(--hdr-gray-dark);
        border-bottom: 1px solid var(--hdr-border); transition: 0.2s;
    }
    .hdr-user-menu a:hover { background: #fff5f0; color: var(--hdr-primary); padding-left: 24px; }
    .hdr-user-menu a i { width: 20px; text-align: center; font-size: 16px; color: var(--hdr-gray); }
    .hdr-user-menu a:hover i { color: var(--hdr-primary); }
    .hdr-user-menu a.logout { color: var(--hdr-accent); }
    .hdr-user-menu a.logout i { color: var(--hdr-accent); }
    .hdr-user-menu a:last-child { border-bottom: none; }

    /* =========================================================
       TIER 3: NAVIGATION BAR
    ========================================================= */
    .hdr-navbar {
        background: var(--hdr-white);
        border-bottom: 1px solid var(--hdr-border);
    }
    .hdr-navbar .hdr-container {
        display: flex; align-items: stretch; height: 50px;
    }
    
    .hdr-cat-btn {
        display: flex; align-items: center; gap: 12px; padding: 0 24px;
        background: var(--hdr-primary); color: #fff;
        font-size: 14px; font-weight: 800; border: none; cursor: pointer;
        min-width: 240px; justify-content: space-between; transition: 0.2s;
    }
    .hdr-cat-btn:hover { background: var(--hdr-primary-hover); }

    .hdr-nav-links {
        display: flex; align-items: center; padding-left: 16px; flex: 1; overflow-x: auto;
    }
    .hdr-nav-links::-webkit-scrollbar { display: none; }
    
    .hdr-nav-link {
        padding: 0 16px; font-size: 14px; font-weight: 700;
        color: var(--hdr-dark); text-transform: uppercase;
        display: flex; align-items: center; height: 100%;
        position: relative; transition: color 0.2s; white-space: nowrap;
    }
    .hdr-nav-link:hover { color: var(--hdr-primary); }
    .hdr-nav-link.active { color: var(--hdr-primary); }
    .hdr-nav-link.active::after {
        content: ''; position: absolute; bottom: 0; left: 16px; right: 16px;
        height: 3px; background: var(--hdr-primary); border-radius: 3px 3px 0 0;
    }

    /* =========================================================
       MOBILE RESPONSIVENESS ENGINE
    ========================================================= */
    @media (max-width: 1024px) {
        .hdr-topbar-left { display: none; }
        .hdr-navbar { display: none; } /* Hide bottom nav, use hamburger instead */
        .hdr-mobile-menu { display: block; }
    }

    @media (max-width: 768px) {
        /* Restructure Main Header for Mobile */
        .hdr-main { padding: 12px 0; }
        .hdr-main .hdr-container {
            flex-wrap: wrap; /* Allows search bar to drop down */
            gap: 12px;
        }
        
        /* Row 1 Layout */
        .hdr-mobile-menu { order: 1; margin-right: 8px; }
        .hdr-logo { order: 2; flex: 1; }
        .hdr-logo-text { font-size: 22px; }
        .hdr-logo-icon { width: 36px; height: 36px; font-size: 18px; border-radius: 8px; }
        .hdr-actions { order: 3; gap: 4px; }
        
        /* Row 2: Search Box drops to full width */
        .hdr-search {
            order: 4;
            width: 100%;
            max-width: 100%;
            flex: none;
        }
        .hdr-search-category { display: none; } /* Hide category select to maximize mobile input space */
        .hdr-search-inner { height: 44px; border-radius: 6px; }
        .hdr-search-input { font-size: 16px; } /* Prevents iOS auto-zoom */
        
        /* Optimize Icons for Thumb Tap */
        .hdr-action span { display: none; } /* Hide text on mobile */
        .hdr-action { padding: 8px; min-width: 44px; }
        .hdr-action i { font-size: 24px; margin: 0; }
        .hdr-badge { top: 0px; right: 0px; }
        
        /* Currency dropdown mobile fix */
        .hdr-currency-dropdown {
            position: fixed; top: auto; bottom: 0; left: 0; right: 0;
            border-radius: 20px 20px 0 0; max-height: 70vh; z-index: 100000;
        }
    }
    
    /* Mobile Menu SweetAlert Customization */
    .mobile-menu-popup { border-radius: 16px !important; padding: 0 0 10px 0 !important; }
    </style>
</head>
<body>

<!-- Hidden inputs required by backend logic -->
<input type="hidden" id="customer_temp_id" value="<?php echo htmlspecialchars($customer_temp_id); ?>">
<input type="hidden" id="result_response" value="">
<input type="hidden" id="result_response_up" value="">
<input type="hidden" id="redirect_link" value="">
<div id="result_response_cart" style="display:none"></div>
<div id="result_response_payment" style="display:none"></div>
<div id="result_response_payment_2" style="display:none"></div>
<input type="hidden" id="result_response_payment_request" value="">

<!-- TIER 1: TOP BAR -->
<div class="hdr-topbar">
    <div class="hdr-container">
        <div class="hdr-top-links hdr-topbar-left">
            <a href="tel:+250783654454"><i class="fas fa-phone-alt"></i> Call: +250 783 654 454</a>
            <a href="mailto:info@gbdeliveries.com"><i class="fas fa-envelope"></i> Mail: info@gbdeliveries.com</a>
        </div>
        
        <div class="hdr-top-links">
            <!-- Currency Switcher -->
            <div class="hdr-currency-wrap" id="currencyWrap">
                <button type="button" class="hdr-currency-btn" id="currencyToggleBtn">
                    <span class="flag" id="currentCurrencyFlag">🇷🇼</span>
                    <span class="code" id="currentCurrencyCode">RWF</span>
                    <i class="fas fa-chevron-down" style="font-size:10px; margin-left:4px;"></i>
                </button>
                
                <div class="hdr-currency-dropdown" id="currencyDropdown">
                    <div class="hdr-currency-header">
                        <h4><i class="fas fa-globe"></i> Select Currency</h4>
                        <p>Prices update automatically</p>
                    </div>
                    <div class="hdr-currency-list">
                        <?php foreach ($gb_currencies as $cur): ?>
                        <button type="button" class="hdr-currency-item" data-currency="<?php echo $cur['code']; ?>">
                            <span class="flag"><?php echo $cur['flag']; ?></span>
                            <span style="font-weight:600;"><?php echo htmlspecialchars($cur['name']); ?></span>
                            <span class="symbol"><?php echo $cur['code']; ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <a href="index.php?track-order"><i class="fas fa-truck"></i> Track Order</a>
        </div>
    </div>
</div>

<!-- TIER 2: MAIN HEADER -->
<header class="hdr-main">
    <div class="hdr-container">
        
        <!-- Mobile Menu Toggle -->
        <button type="button" class="hdr-mobile-menu" onclick="toggleMobileMenu()">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Logo -->
        <a href="index.php" class="hdr-logo">
            <div class="hdr-logo-icon"><i class="fas fa-shopping-basket"></i></div>
            <div class="hdr-logo-text">GB<span>delivering</span></div>
        </a>
        
        <!-- Search Bar -->
        <div class="hdr-search">
            <form action="index.php" method="GET" class="hdr-search-inner" onsubmit="return handleSearch(event)">
                <input type="hidden" name="shop-search" value="">
                <select name="category" class="hdr-search-category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="search" id="main-search" class="hdr-search-input" placeholder="Search for fresh groceries, meat, vegetables...">
                <button type="submit" class="hdr-search-btn" aria-label="Search"><i class="fas fa-search"></i></button>
            </form>
        </div>
        
        <!-- Action Icons -->
        <div class="hdr-actions">
            
            <!-- User Account -->
            <div class="hdr-user-wrap">
                <a href="<?php echo $is_logged_in ? '#' : 'index.php?sign-in'; ?>" class="hdr-action">
                    <i class="far fa-user"></i>
                    <span><?php echo $is_logged_in ? 'Account' : 'Sign In'; ?></span>
                </a>
                
                <?php if ($is_logged_in): ?>
                <div class="hdr-user-dropdown">
                    <div class="hdr-user-header">
                        <div class="hdr-user-avatar"><i class="fas fa-user"></i></div>
                        <div class="hdr-user-name"><?php echo htmlspecialchars($user_name); ?></div>
                    </div>
                    <div class="hdr-user-menu">
                        <a href="index.php?my-account"><i class="fas fa-user-circle"></i> My Profile</a>
                        <a href="index.php?my-orders"><i class="fas fa-box-open"></i> My Orders</a>
                        <a href="index.php?wishlist"><i class="fas fa-heart"></i> Saved Items</a>
                        <a href="index.php?addresses"><i class="fas fa-map-marker-alt"></i> Addresses</a>
                        <a href="action/logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Wishlist -->
            <a href="index.php?wishlist" class="hdr-action">
                <i class="far fa-heart"></i>
                <span>Wishlist</span>
            </a>
            
            <!-- Cart -->
            <a href="index.php?cart" class="hdr-action">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart</span>
                <div class="hdr-badge" id="cart_items_count_1"><?php echo $cart_count > 0 ? $cart_count : '0'; ?></div>
            </a>
            
        </div>
    </div>
</header>

<!-- TIER 3: NAVIGATION BAR -->
<nav class="hdr-navbar">
    <div class="hdr-container">
        
        <button type="button" class="hdr-cat-btn" onclick="window.location.href='index.php?shop'">
            <div style="display:flex;align-items:center;gap:10px;">
                <i class="fas fa-bars"></i> CATEGORIES
            </div>
            <i class="fas fa-chevron-down"></i>
        </button>
        
        <div class="hdr-nav-links">
            <a href="index.php" class="hdr-nav-link <?php echo !isset($_GET['shop']) && !isset($_GET['cart']) && !isset($_GET['shop-search']) ? 'active' : ''; ?>">Home</a>
            <a href="index.php?shop-search&search=&sortby=p.register_date DESC" class="hdr-nav-link">New Arrivals</a>
            <a href="index.php?shop" class="hdr-nav-link <?php echo isset($_GET['shop']) || isset($_GET['shop-search']) ? 'active' : ''; ?>">Shop</a>
            <a href="index.php?track-order" class="hdr-nav-link">Track Order</a>
            <a href="index.php?about" class="hdr-nav-link">About Us</a>
            <a href="index.php?contact" class="hdr-nav-link">Contact Us</a>
        </div>
        
    </div>
</nav>

<script>
// ============================================
// CURRENCY DROPDOWN FUNCTIONALITY
// ============================================
(function() {
    var currencyWrap = document.getElementById('currencyWrap');
    var currencyToggleBtn = document.getElementById('currencyToggleBtn');
    
    if (currencyToggleBtn) {
        currencyToggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            currencyWrap.classList.toggle('open');
        });
    }
    
    var desktopItems = document.querySelectorAll('#currencyDropdown .hdr-currency-item');
    desktopItems.forEach(function(item) {
        item.addEventListener('click', function() {
            var code = this.getAttribute('data-currency');
            if (code && typeof gbSetCurrency === 'function') gbSetCurrency(code);
            currencyWrap.classList.remove('open');
        });
    });
    
    document.addEventListener('click', function(e) {
        if (currencyWrap && !currencyWrap.contains(e.target)) currencyWrap.classList.remove('open');
    });
    
    function updateHeaderCurrency() {
        if (typeof GB_CUR === 'undefined' || typeof GB_CURRENCIES === 'undefined') return;
        var info = GB_CURRENCIES[GB_CUR];
        if (!info) return;
        
        var flagEl = document.getElementById('currentCurrencyFlag');
        var codeEl = document.getElementById('currentCurrencyCode');
        if (flagEl) flagEl.textContent = info.flag;
        if (codeEl) codeEl.textContent = GB_CUR;
        
        desktopItems.forEach(function(item) {
            item.classList.toggle('active', item.getAttribute('data-currency') === GB_CUR);
        });
    }
    
    var originalUpdateUI = window.gbUpdateUI;
    window.gbUpdateUI = function() {
        if (typeof originalUpdateUI === 'function') originalUpdateUI();
        updateHeaderCurrency();
    };
    
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(updateHeaderCurrency, 100);
    });
})();

// ============================================
// SEARCH ROUTING
// ============================================
function handleSearch(e) {
    var searchInput = document.getElementById('main-search');
    if (searchInput && searchInput.value.trim()) {
        window.location = "index.php?shop-search&search=" + encodeURIComponent(searchInput.value.trim());
        return false;
    }
    return false;
}

// ============================================
// MOBILE MENU (SWEETALERT)
// ============================================
function toggleMobileMenu() {
    Swal.fire({
        title: 'Main Menu',
        html: `
            <div style="text-align:left; font-family: 'Inter', sans-serif;">
                <a href="index.php" style="display:flex;align-items:center;gap:16px;padding:16px;border-bottom:1px solid #f3f4f6;color:#111827;font-weight:700;font-size:15px;"><i class="fas fa-home" style="width:24px;text-align:center;color:#ff5000;"></i> Home</a>
                <a href="index.php?shop" style="display:flex;align-items:center;gap:16px;padding:16px;border-bottom:1px solid #f3f4f6;color:#111827;font-weight:700;font-size:15px;"><i class="fas fa-store" style="width:24px;text-align:center;color:#ff5000;"></i> Shop</a>
                <a href="index.php?shop-search&search=&sortby=p.register_date DESC" style="display:flex;align-items:center;gap:16px;padding:16px;border-bottom:1px solid #f3f4f6;color:#111827;font-weight:700;font-size:15px;"><i class="fas fa-star" style="width:24px;text-align:center;color:#ff5000;"></i> New Arrivals</a>
                <a href="index.php?track-order" style="display:flex;align-items:center;gap:16px;padding:16px;border-bottom:1px solid #f3f4f6;color:#111827;font-weight:700;font-size:15px;"><i class="fas fa-truck" style="width:24px;text-align:center;color:#ff5000;"></i> Track Order</a>
                <a href="index.php?about" style="display:flex;align-items:center;gap:16px;padding:16px;border-bottom:1px solid #f3f4f6;color:#111827;font-weight:700;font-size:15px;"><i class="fas fa-info-circle" style="width:24px;text-align:center;color:#ff5000;"></i> About Us</a>
                <a href="index.php?contact" style="display:flex;align-items:center;gap:16px;padding:16px;border-bottom:1px solid #f3f4f6;color:#111827;font-weight:700;font-size:15px;"><i class="fas fa-envelope" style="width:24px;text-align:center;color:#ff5000;"></i> Contact Us</a>
                <?php if ($is_logged_in): ?>
                <a href="index.php?my-orders" style="display:flex;align-items:center;gap:16px;padding:16px;border-bottom:1px solid #f3f4f6;color:#111827;font-weight:700;font-size:15px;"><i class="fas fa-box-open" style="width:24px;text-align:center;color:#ff5000;"></i> My Orders</a>
                <a href="action/logout.php" style="display:flex;align-items:center;gap:16px;padding:16px;color:#ef4444;font-weight:800;font-size:15px;"><i class="fas fa-sign-out-alt" style="width:24px;text-align:center;"></i> Logout</a>
                <?php else: ?>
                <a href="index.php?sign-in" style="display:flex;align-items:center;gap:16px;padding:16px;color:#ff5000;font-weight:800;font-size:15px;"><i class="fas fa-user-circle" style="width:24px;text-align:center;"></i> Sign In / Register</a>
                <?php endif; ?>
            </div>
        `,
        showConfirmButton: false,
        showCloseButton: true,
        customClass: { popup: 'mobile-menu-popup' }
    });
}

// Ensure cart logic is loaded
if (typeof get_cart_items === 'function') {
    get_cart_items();
}
</script>
</body>
</html>