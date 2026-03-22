<?php
// AliExpress Style Admin Control Panel - Complete Version
$section = isset($_GET['manage']) ? $_GET['manage'] : 'dashboard';

// Get counts for header stats
$r = $conn->query("SELECT COUNT(*) as c FROM user"); $totalUsers = $r ? $r->fetch_assoc()['c'] : 0;
$r = $conn->query("SELECT COUNT(*) as c FROM product"); $totalProducts = $r ? $r->fetch_assoc()['c'] : 0;
$r = $conn->query("SELECT COUNT(*) as c FROM orders"); $totalOrders = $r ? $r->fetch_assoc()['c'] : 0;
$r = $conn->query("SELECT COUNT(*) as c FROM product_category"); $totalCats = $r ? $r->fetch_assoc()['c'] : 0;
$r = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'"); $pendingOrders = $r ? $r->fetch_assoc()['c'] : 0;
$r = $conn->query("SELECT COUNT(*) as c FROM product p LEFT JOIN product_stock ps ON p.product_id=ps.product_id WHERE ps.stock_quantity=0 OR ps.stock_quantity IS NULL"); $outOfStock = $r ? $r->fetch_assoc()['c'] : 0;

// Include Admin CSS styles
include 'admin_styles.php';
?>

<div class="ali-admin">

<!-- Header -->
<div class="ali-header">
    <div class="ali-header-left">
        <h1>🛒 GB Deliveries</h1>
        <span class="ali-header-subtitle">Admin Control Panel</span>
    </div>
    <div class="ali-header-right">
        <div class="ali-header-stat">
            <span class="ali-header-num"><?php echo number_format($totalOrders); ?></span>
            <span class="ali-header-label">Orders</span>
        </div>
        <div class="ali-header-stat">
            <span class="ali-header-num"><?php echo number_format($totalProducts); ?></span>
            <span class="ali-header-label">Products</span>
        </div>
        <div class="ali-header-stat">
            <span class="ali-header-num"><?php echo number_format($totalUsers); ?></span>
            <span class="ali-header-label">Users</span>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<div class="ali-nav">
    <div style="padding: 10px 15px; font-size: 11px; text-transform: uppercase; color: #888; font-weight: bold; letter-spacing: 1px;">Core Management</div>
    
    <a href="?page=admin_manager&manage=dashboard" class="ali-nav-item <?php echo $section=='dashboard'?'active':''; ?>">
        <span class="ali-nav-icon">📊</span> Dashboard
    </a>
    <a href="?page=admin_manager&manage=categories" class="ali-nav-item <?php echo $section=='categories'?'active':''; ?>">
        <span class="ali-nav-icon">📁</span> Categories
        <span class="ali-nav-badge"><?php echo $totalCats; ?></span>
    </a>
    <a href="?page=admin_manager&manage=subcategories" class="ali-nav-item <?php echo $section=='subcategories'?'active':''; ?>">
        <span class="ali-nav-icon">📂</span> Sub Categories
    </a>
    <a href="?page=admin_manager&manage=products" class="ali-nav-item <?php echo $section=='products'?'active':''; ?>">
        <span class="ali-nav-icon">📦</span> Products
        <span class="ali-nav-badge"><?php echo $totalProducts; ?></span>
    </a>
    <!-- NEW BULK EDITOR LINK -->
    <a href="?page=admin_manager&manage=bulk_editor" class="ali-nav-item <?php echo $section=='bulk_editor'?'active':''; ?>">
        <span class="ali-nav-icon">📝</span> Bulk Editor (BEAR)
    </a>
    <a href="?page=admin_manager&manage=stock" class="ali-nav-item <?php echo $section=='stock'?'active':''; ?>">
        <span class="ali-nav-icon">📈</span> Stock
        <?php if($outOfStock > 0): ?><span class="ali-nav-badge ali-nav-badge-red"><?php echo $outOfStock; ?> out</span><?php endif; ?>
    </a>
    <a href="?page=admin_manager&manage=orders" class="ali-nav-item <?php echo $section=='orders'?'active':''; ?>">
        <span class="ali-nav-icon">🛒</span> Orders
        <?php if($pendingOrders > 0): ?><span class="ali-nav-badge ali-nav-badge-orange"><?php echo $pendingOrders; ?> new</span><?php endif; ?>
    </a>
    <a href="?page=admin_manager&manage=shipping" class="ali-nav-item <?php echo $section=='shipping'?'active':''; ?>">
        <span class="ali-nav-icon">🚚</span> Shipping
    </a>
    <a href="?page=admin_manager&manage=users" class="ali-nav-item <?php echo $section=='users'?'active':''; ?>">
        <span class="ali-nav-icon">👥</span> Users
        <span class="ali-nav-badge"><?php echo $totalUsers; ?></span>
    </a>
    <a href="?page=admin_manager&manage=payments" class="ali-nav-item <?php echo $section=='payments'?'active':''; ?>">
        <span class="ali-nav-icon">💳</span> Payments
    </a>
    
    <!-- Storefront Builder Group -->
    <div style="padding: 15px 15px 10px 15px; font-size: 11px; text-transform: uppercase; color: #888; font-weight: bold; letter-spacing: 1px; border-top: 1px solid #eee; margin-top: 10px;">Storefront Builder</div>
    
    <a href="?page=admin_manager&manage=website_control" class="ali-nav-item <?php echo $section=='website_control'?'active':''; ?>">
        <span class="ali-nav-icon">⚙️</span> Site Settings
    </a>
    <a href="?page=admin_manager&manage=theme_manager" class="ali-nav-item <?php echo $section=='theme_manager'?'active':''; ?>">
        <span class="ali-nav-icon">🎨</span> Theme Manager
    </a>
    <a href="?page=admin_manager&manage=page_builder" class="ali-nav-item <?php echo $section=='page_builder'?'active':''; ?>">
        <span class="ali-nav-icon">📄</span> Custom Pages
    </a>
    <a href="?page=admin_manager&manage=design_manager" class="ali-nav-item <?php echo $section=='design_manager'?'active':''; ?>">
        <span class="ali-nav-icon">🖌️</span> Design & Colors
    </a>
    <a href="?page=admin_manager&manage=frontend_builder" class="ali-nav-item <?php echo $section=='frontend_builder'?'active':''; ?>">
        <span class="ali-nav-icon">🖼️</span> Header & Footer
    </a>
    <a href="?page=admin_manager&manage=homepage_builder" class="ali-nav-item <?php echo $section=='homepage_builder'?'active':''; ?>">
        <span class="ali-nav-icon">🏗️</span> Homepage Builder
    </a>
    <a href="?page=admin_manager&manage=product_card_customizer" class="ali-nav-item <?php echo $section=='product_card_customizer'?'active':''; ?>">
        <span class="ali-nav-icon">🎴</span> Card Customizer
    </a>
    <a href="?page=admin_manager&manage=product_customizer" class="ali-nav-item <?php echo $section=='product_customizer'?'active':''; ?>">
        <span class="ali-nav-icon">🛍️</span> Product Theme
    </a>
    <a href="?page=admin_manager&manage=product_ux_builder" class="ali-nav-item <?php echo $section=='product_ux_builder'?'active':''; ?>">
        <span class="ali-nav-icon">🧩</span> Product UX Builder
    </a>
</div>

<!-- Main Content -->
<div class="ali-main">

<?php if(isset($_GET['success'])): ?>
<div class="ali-alert ali-alert-success">
    <span class="ali-alert-icon">✅</span>
    <?php echo htmlspecialchars($_GET['success']); ?>
</div>
<?php endif; ?>

<?php if(isset($_GET['error'])): ?>
<div class="ali-alert ali-alert-error">
    <span class="ali-alert-icon">❌</span>
    <?php echo htmlspecialchars($_GET['error']); ?>
</div>
<?php endif; ?>

<?php
// Load section content dynamically based on URL parameter
switch($section) {
    // Core Modules
    case 'dashboard':
        include 'sections/dashboard.php';
        break;
    case 'categories':
        include 'sections/categories.php';
        break;
    case 'subcategories':
        include 'sections/subcategories.php';
        break;
    case 'products':
        include 'sections/products.php';
        break;
    case 'bulk_editor':
        include 'admin_bulk_editor.php';
        break;
    case 'stock':
        include 'sections/stock.php';
        break;
    case 'orders':
        include 'sections/orders.php';
        break;
    case 'shipping':
        include 'sections/shipping.php';
        break;
    case 'users':
        include 'sections/users.php';
        break;
    case 'payments':
        include 'sections/payments.php';
        break;
        
    // Storefront Builder Modules
    case 'website_control':
        include 'sections/website_control.php'; 
        break;
    case 'theme_manager':
        include 'sections/admin_theme_manager.php';
        break;
    case 'page_builder':
        include 'sections/admin_page_builder.php'; 
        break;
    case 'frontend_builder':
        include 'sections/frontend_builder.php'; 
        break;
    case 'design_manager':
        include 'sections/admin_design_manager.php';
        break;
    case 'homepage_builder':
        include 'sections/admin_homepage_builder.php'; 
        break;
    case 'product_card_customizer':
        include 'sections/admin_product_card_customizer.php'; 
        break;
    case 'product_customizer':
        include 'sections/admin_product_customizer.php'; 
        break;
    case 'product_ux_builder':
        include 'sections/admin_product_ux.php'; 
        break;
        
    // Default fallback
    default:
        include 'sections/dashboard.php';
}
?>

</div>
</div>

<script>
// Helper JS functions for Admin functionality
function toggleForm(id) {
    var form = document.getElementById(id);
    if(form) form.classList.toggle('show');
}

function filterTable(input, tableId) {
    var filter = input.value.toLowerCase();
    var table = document.getElementById(tableId);
    if(!table) return;
    var rows = table.getElementsByTagName('tr');
    for (var i = 1; i < rows.length; i++) {
        var text = rows[i].textContent.toLowerCase();
        rows[i].style.display = text.indexOf(filter) > -1 ? '' : 'none';
    }
}

function filterBySelect(select, tableId, dataAttr) {
    var val = select.value;
    var table = document.getElementById(tableId);
    if(!table) return;
    var rows = table.getElementsByTagName('tr');
    for (var i = 1; i < rows.length; i++) {
        if (val === '' || rows[i].getAttribute('data-' + dataAttr) === val) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}
</script>