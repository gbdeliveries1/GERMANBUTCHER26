<?php
/**
 * Shop Search Page - AliExpress Style with Infinite Scroll
 * Fixed Version with Units Support
 * 
 * @version 2.1
 * @updated 2026-03-17
 */

// Get search term safely
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_escaped = mysqli_real_escape_string($conn, $search);

// Get sorting parameter
$sortby = isset($_GET['sortby']) ? $_GET['sortby'] : 'p.register_date DESC';

// Validate sort parameter
$valid_sorts = array(
    'p.register_date DESC',
    'p.register_date ASC', 
    'CAST(pr.price AS SIGNED INTEGER) ASC',
    'CAST(pr.price AS SIGNED INTEGER) DESC',
    'CAST(p.product_rating AS SIGNED INTEGER) DESC'
);

if (!in_array($sortby, $valid_sorts)) {
    $sortby = 'p.register_date DESC';
}

// Products per page
$per_page = 20;

// Get customer ID from session
$customer_id = '';
if (isset($_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'])) {
    $customer_id = $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'];
}

// Check login status
$is_logged_in = false;
if (isset($login_status)) {
    $is_logged_in = $login_status;
} elseif (isset($_COOKIE['GBDELIVERING_CUSTOMER_USER_2021'])) {
    $is_logged_in = true;
}

// Count total matching products
$count_query = "SELECT COUNT(DISTINCT p.product_id) as total 
                FROM product p 
                INNER JOIN product_price pr ON pr.product_id = p.product_id 
                LEFT JOIN product_category pc ON pc.category_id = p.category_id 
                LEFT JOIN product_sub_category psc ON psc.sub_category_id = p.sub_category_id 
                WHERE p.product_name LIKE '%$search_escaped%' 
                   OR p.short_description LIKE '%$search_escaped%' 
                   OR pc.category_name LIKE '%$search_escaped%' 
                   OR psc.sub_category_name LIKE '%$search_escaped%'";

$count_result = mysqli_query($conn, $count_query);
$total_products = 0;
if ($count_result && $row = mysqli_fetch_assoc($count_result)) {
    $total_products = (int)$row['total'];
}

// Get categories for filter sidebar
$cat_query = "SELECT category_id, category_name FROM product_category ORDER BY category_name ASC";
$cat_result = mysqli_query($conn, $cat_query);
$categories = array();
if ($cat_result) {
    while ($cat = mysqli_fetch_assoc($cat_result)) {
        $cat_id = $cat['category_id'];
        $cat_count_query = "SELECT COUNT(*) as cnt FROM product WHERE category_id = '$cat_id'";
        $cat_count_result = mysqli_query($conn, $cat_count_query);
        $cat_count = 0;
        if ($cat_count_result && $cc = mysqli_fetch_assoc($cat_count_result)) {
            $cat_count = $cc['cnt'];
        }
        $cat['product_count'] = $cat_count;
        $categories[] = $cat;
    }
}

// FIXED: Using COALESCE to check both 'units' and 'product_unit'
$products_query = "SELECT DISTINCT p.product_id, p.product_name, 
                          COALESCE(NULLIF(p.units, ''), p.product_unit, 'unit') as product_unit,
                          p.short_description, p.minimum_order,
                          p.product_rating, pr.price, pc.category_name, psc.sub_category_name,
                          COALESCE(ps.stock_quantity, 0) as stock_quantity
                   FROM product p 
                   INNER JOIN product_price pr ON pr.product_id = p.product_id 
                   LEFT JOIN product_stock ps ON ps.product_id = p.product_id
                   LEFT JOIN product_category pc ON pc.category_id = p.category_id 
                   LEFT JOIN product_sub_category psc ON psc.sub_category_id = p.sub_category_id 
                   WHERE p.product_name LIKE '%$search_escaped%' 
                      OR p.short_description LIKE '%$search_escaped%' 
                      OR pc.category_name LIKE '%$search_escaped%' 
                      OR psc.sub_category_name LIKE '%$search_escaped%' 
                   ORDER BY $sortby 
                   LIMIT 0, $per_page";

$products_result = mysqli_query($conn, $products_query);
$products = array();
if ($products_result) {
    while ($prod = mysqli_fetch_assoc($products_result)) {
        $products[] = $prod;
    }
}
?>

<!-- JAVASCRIPT MUST BE FIRST (before HTML that uses it) -->
<script>
// =====================================================
// SHOP SEARCH JAVASCRIPT - GLOBAL FUNCTIONS
// =====================================================

// Configuration
var searchConfig = {
    searchTerm: '<?php echo addslashes($search); ?>',
    sortBy: '<?php echo addslashes($sortby); ?>',
    perPage: <?php echo $per_page; ?>,
    totalProducts: <?php echo $total_products; ?>,
    customerId: '<?php echo addslashes($customer_id); ?>',
    currentPage: 1,
    isLoading: false,
    hasMore: <?php echo ($total_products > $per_page) ? 'true' : 'false'; ?>,
    productIndex: <?php echo count($products); ?>
};

// --- Drawer Functions ---
function openDrawer() {
    var drawer = document.getElementById('ssDrawer');
    var bg = document.getElementById('ssDrawerBg');
    if (drawer) drawer.classList.add('show');
    if (bg) bg.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeDrawer() {
    var drawer = document.getElementById('ssDrawer');
    var bg = document.getElementById('ssDrawerBg');
    if (drawer) drawer.classList.remove('show');
    if (bg) bg.classList.remove('show');
    document.body.style.overflow = '';
}

// --- Sort Navigation ---
function changeSort(val) {
    window.location.href = 'index.php?shop-search&search=' + encodeURIComponent(searchConfig.searchTerm) + '&sortby=' + encodeURIComponent(val);
}

// --- Format Decimal ---
function formatQtyNum(n) {
    if (n === '' || isNaN(n)) return '';
    return parseFloat(parseFloat(n).toFixed(3)).toString();
}

// --- Quantity Functions ---
function ssIncQty(uid) {
    var el = document.getElementById('qty_' + uid);
    if (!el) return;
    var cur = parseFloat(el.value) || 0;
    var inc = (cur % 1 !== 0) ? 0.5 : 1;
    el.value = formatQtyNum(cur + inc);
}

function ssDecQty(uid) {
    var el = document.getElementById('qty_' + uid);
    if (!el) return;
    var cur = parseFloat(el.value) || 0;
    var dec = (cur % 1 !== 0) ? 0.5 : 1;
    var newVal = cur - dec;
    if (newVal > 0) {
        el.value = formatQtyNum(newVal);
    }
}

function ssValidateQty(input) {
    var v = input.value.replace(/[^0-9.]/g, '');
    var parts = v.split('.');
    if (parts.length > 2) {
        v = parts[0] + '.' + parts.slice(1).join('');
    }
    input.value = v;
}

function ssBlurQty(input, minOrder) {
    var v = input.value.trim();
    var min = minOrder || 1;
    if (!v || v === '.') {
        input.value = min;
        return;
    }
    var n = parseFloat(v);
    if (isNaN(n) || n <= 0) {
        input.value = min;
        return;
    }
    input.value = formatQtyNum(n);
}

// --- Toast Notification ---
function ssShowToast(msg, type) {
    var t = document.getElementById('ssToast');
    if (!t) return;
    
    var icon = 'check-circle';
    if (type === 'warning') icon = 'exclamation-circle';
    if (type === 'error') icon = 'times-circle';
    
    t.className = 'ss-toast ' + (type || 'success');
    t.innerHTML = '<i class="fas fa-' + icon + '"></i> ' + msg;
    t.classList.add('show');
    
    setTimeout(function() {
        t.classList.remove('show');
    }, 3000);
}

// --- Add to Cart ---
function ssAddToCart(productId, custId, price, uid, evt, stock) {
    if (stock !== undefined && stock <= 0) {
        ssShowToast('Sorry, this product is out of stock', 'error');
        return;
    }

    var input = document.getElementById('qty_' + uid);
    if (!input) {
        ssShowToast('Error: Input not found', 'error');
        return;
    }
    
    var qtyStr = input.value.trim();
    
    if (!qtyStr || qtyStr === '0') {
        ssShowToast('Please enter a quantity', 'warning');
        input.focus();
        return;
    }
    
    var qty = parseFloat(qtyStr);
    if (isNaN(qty) || qty <= 0) {
        ssShowToast('Please enter a valid quantity', 'warning');
        input.focus();
        return;
    }
    
    // Resolve guest customer id
    if (!custId || custId === '') {
        var tmp = document.getElementById('customer_temp_id');
        if (tmp && tmp.value) custId = tmp.value;
    }
    
    // Button feedback
    var btn = evt ? evt.currentTarget : null;
    var origHTML = btn ? btn.innerHTML : '';
    if (btn) {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
    }
    
    function restoreBtn() {
        if (btn) {
            btn.innerHTML = origHTML;
            btn.disabled = false;
        }
    }
    
    // Use global add_to_cart if exists
    if (typeof add_to_cart === 'function') {
        add_to_cart(productId, custId, price, qty);
        setTimeout(function() {
            restoreBtn();
            ssShowToast('Added to cart!', 'success');
        }, 800);
    } else {
        // Fallback AJAX
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'action/insert.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            restoreBtn();
            if (xhr.status === 200) {
                var resp = document.getElementById('result_response_cart');
                if (resp) resp.innerHTML = xhr.responseText;
                if (typeof get_cart_items === 'function') get_cart_items();
                ssShowToast('Added to cart!', 'success');
            } else {
                ssShowToast('Error adding to cart', 'error');
            }
        };
        xhr.onerror = function() {
            restoreBtn();
            ssShowToast('Network error', 'error');
        };
        xhr.send('action=ADD_TO_CART&product_id=' + productId + '&customer_id=' + custId + '&price=' + price + '&product_quantity=' + qty);
    }
}

// --- Infinite Scroll ---
function ssLoadMore() {
    if (searchConfig.isLoading || !searchConfig.hasMore) return;
    
    searchConfig.isLoading = true;
    searchConfig.currentPage++;
    
    var loadingBox = document.getElementById('ssLoadingBox');
    if (loadingBox) loadingBox.classList.add('show');
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'includes/search_load_more.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        searchConfig.isLoading = false;
        if (loadingBox) loadingBox.classList.remove('show');
        
        if (xhr.status === 200) {
            var response = xhr.responseText.trim();
            if (response && response !== 'no_more' && response !== '') {
                var grid = document.getElementById('ssProductsGrid');
                if (grid) {
                    grid.insertAdjacentHTML('beforeend', response);
                    searchConfig.productIndex = grid.querySelectorAll('.ss-product-card').length;
                    
                    // Re-apply currency converter
                    if (typeof gbUpdatePrices === 'function') gbUpdatePrices();
                }
                
                if (searchConfig.currentPage * searchConfig.perPage >= searchConfig.totalProducts) {
                    searchConfig.hasMore = false;
                    var endBox = document.getElementById('ssEndBox');
                    if (endBox) endBox.classList.add('show');
                }
            } else {
                searchConfig.hasMore = false;
                var endBox = document.getElementById('ssEndBox');
                if (endBox) endBox.classList.add('show');
            }
        }
    };
    
    xhr.onerror = function() {
        searchConfig.isLoading = false;
        if (loadingBox) loadingBox.classList.remove('show');
    };
    
    var params = 'search=' + encodeURIComponent(searchConfig.searchTerm) +
                 '&sortby=' + encodeURIComponent(searchConfig.sortBy) +
                 '&page=' + searchConfig.currentPage +
                 '&per_page=' + searchConfig.perPage +
                 '&customer_id=' + encodeURIComponent(searchConfig.customerId) +
                 '&index=' + searchConfig.productIndex;
    
    xhr.send(params);
}

// --- Initialize on DOM Ready ---
document.addEventListener('DOMContentLoaded', function() {
    // Currency converter
    if (typeof gbUpdatePrices === 'function') gbUpdatePrices();
    
    // Show end message if no more on initial load
    if (searchConfig.totalProducts > 0 && searchConfig.totalProducts <= searchConfig.perPage) {
        var endBox = document.getElementById('ssEndBox');
        if (endBox) endBox.classList.add('show');
    }
    
    // Close drawer on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDrawer();
    });
    
    // Infinite scroll with Intersection Observer
    var loadingBox = document.getElementById('ssLoadingBox');
    if (loadingBox && 'IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function(entries) {
            if (entries[0].isIntersecting) {
                ssLoadMore();
            }
        }, { rootMargin: '500px' });
        observer.observe(loadingBox);
    } else if (loadingBox) {
        // Fallback scroll listener
        window.addEventListener('scroll', function() {
            var rect = loadingBox.getBoundingClientRect();
            if (rect.top <= window.innerHeight + 500) {
                ssLoadMore();
            }
        }, { passive: true });
    }
});
</script>

<style>
/* ===== CSS VARIABLES ===== */
:root {
    --ss-primary: #ff5000;
    --ss-primary-hover: #e64800;
    --ss-dark: #111827;
    --ss-gray: #6b7280;
    --ss-gray-light: #e5e7eb;
    --ss-bg: #f3f4f6;
    --ss-white: #ffffff;
    --ss-radius: 12px;
    --ss-shadow: 0 2px 8px rgba(0,0,0,0.04);
    --ss-shadow-hover: 0 12px 24px rgba(0,0,0,0.1);
}

.ss-search-page { padding: 20px 0 60px; background: var(--ss-bg); }
.ss-search-page * { box-sizing: border-box; }
.ss-container { max-width: 1400px; margin: 0 auto; padding: 0 16px; }

/* ===== HEADER ===== */
.ss-header {
    background: var(--ss-white);
    padding: 20px 24px;
    margin-bottom: 20px;
    border-radius: var(--ss-radius);
    box-shadow: var(--ss-shadow);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.ss-header h1 {
    font-size: 20px;
    font-weight: 800;
    color: var(--ss-dark);
    margin: 0;
}
.ss-header h1 span { color: var(--ss-primary); }
.ss-header .ss-count {
    font-size: 14px;
    color: var(--ss-gray);
    background: var(--ss-bg);
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 600;
}
.ss-header .ss-count strong { color: var(--ss-primary); }

/* ===== MOBILE BAR ===== */
.ss-mobile-bar {
    display: none;
    position: sticky;
    top: 60px;
    z-index: 100;
    background: var(--ss-white);
    padding: 12px 16px;
    gap: 12px;
    margin: -20px -16px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.ss-mobile-bar button {
    padding: 10px 16px;
    background: var(--ss-white);
    border: 1.5px solid var(--ss-gray-light);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: var(--ss-dark);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ss-mobile-bar button i { color: var(--ss-primary); }
.ss-mobile-bar select {
    flex: 1;
    padding: 10px 14px;
    border: 1.5px solid var(--ss-gray-light);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    background: var(--ss-white);
    color: var(--ss-dark);
}

/* ===== LAYOUT ===== */
.ss-layout { display: flex; gap: 24px; align-items: flex-start; }

/* ===== SIDEBAR ===== */
.ss-sidebar { width: 260px; flex-shrink: 0; position: sticky; top: 90px; }
.ss-sidebar-box {
    background: var(--ss-white);
    border-radius: var(--ss-radius);
    padding: 20px;
    box-shadow: var(--ss-shadow);
}
.ss-sidebar-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--ss-dark);
    margin: 0 0 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--ss-bg);
    display: flex;
    align-items: center;
    gap: 8px;
}
.ss-sidebar-title i { color: var(--ss-primary); }
.ss-cat-list { list-style: none; padding: 0; margin: 0; }
.ss-cat-list li { margin-bottom: 6px; }
.ss-cat-list a {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    border-radius: 8px;
    color: var(--ss-dark);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: 0.2s;
    border: 1px solid transparent;
}
.ss-cat-list a:hover {
    background: #fff5f0;
    color: var(--ss-primary);
    border-color: #ffe0d1;
}
.ss-cat-list a span {
    font-size: 12px;
    background: var(--ss-bg);
    padding: 2px 8px;
    border-radius: 20px;
    color: var(--ss-gray);
}
.ss-cat-list a:hover span { background: var(--ss-primary); color: #fff; }

/* ===== MAIN ===== */
.ss-main { flex: 1; min-width: 0; }

/* Sort Bar */
.ss-sort-bar {
    background: var(--ss-white);
    padding: 12px 16px;
    border-radius: var(--ss-radius);
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--ss-shadow);
    flex-wrap: wrap;
    gap: 10px;
}
.ss-sort-bar .ss-result { font-size: 14px; color: var(--ss-gray); }
.ss-sort-bar .ss-result strong { color: var(--ss-dark); }
.ss-sort-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
.ss-sort-tabs button {
    padding: 8px 14px;
    background: var(--ss-bg);
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--ss-gray);
    cursor: pointer;
    transition: 0.2s;
}
.ss-sort-tabs button:hover { background: var(--ss-gray-light); color: var(--ss-dark); }
.ss-sort-tabs button.active { background: var(--ss-primary); color: #fff; }

/* ===== PRODUCTS GRID ===== */
.ss-products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
}

/* Product Card */
.ss-product-card {
    background: var(--ss-white);
    border-radius: var(--ss-radius);
    overflow: hidden;
    box-shadow: var(--ss-shadow);
    display: flex;
    flex-direction: column;
    border: 1.5px solid transparent;
    transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
}
.ss-product-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--ss-shadow-hover);
    border-color: #ffd0c0;
}

.ss-product-card .ss-img-wrap {
    aspect-ratio: 1/1;
    position: relative;
    background: #fafafa;
    padding: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.ss-product-card .ss-img-wrap img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform 0.4s;
}
.ss-product-card:hover .ss-img-wrap img { transform: scale(1.05); }

.ss-product-card .ss-actions {
    position: absolute;
    top: 8px;
    right: 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    opacity: 0;
    transform: translateX(8px);
    transition: 0.25s;
}
.ss-product-card:hover .ss-actions { opacity: 1; transform: translateX(0); }
.ss-product-card .ss-actions button,
.ss-product-card .ss-actions a {
    width: 34px;
    height: 34px;
    background: rgba(255,255,255,0.95);
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ss-gray);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    cursor: pointer;
    text-decoration: none;
    transition: 0.2s;
}
.ss-product-card .ss-actions button:hover,
.ss-product-card .ss-actions a:hover { background: var(--ss-primary); color: #fff; }

.ss-product-card .ss-info {
    padding: 14px;
    display: flex;
    flex-direction: column;
    flex: 1;
    gap: 6px;
    background: #fafafa;
}
.ss-product-card .ss-cat {
    font-size: 11px;
    font-weight: 600;
    color: var(--ss-gray);
    text-transform: uppercase;
}
.ss-product-card .ss-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--ss-dark);
    text-decoration: none;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 40px;
}
.ss-product-card .ss-name:hover { color: var(--ss-primary); }

.ss-product-card .ss-rating {
    display: flex;
    align-items: center;
    gap: 6px;
}
.ss-product-card .ss-stars { color: #ffc107; font-size: 11px; }
.ss-product-card .ss-sold { font-size: 12px; color: var(--ss-gray); }

.ss-product-card .ss-price { display: flex; align-items: baseline; gap: 4px; }
.ss-product-card .ss-amount { font-size: 17px; font-weight: 800; color: var(--ss-primary); }
.ss-product-card .ss-unit { font-size: 12px; color: var(--ss-gray); }

/* Qty Row */
.ss-product-card .ss-qty-row {
    display: flex;
    border: 1.5px solid var(--ss-gray-light);
    border-radius: 8px;
    overflow: hidden;
    height: 36px;
    margin-top: 8px;
    background: #fff;
}
.ss-product-card .ss-qty-row:focus-within { border-color: var(--ss-primary); }
.ss-product-card .ss-qty-row button {
    width: 36px;
    background: var(--ss-bg);
    border: none;
    color: var(--ss-dark);
    font-size: 13px;
    cursor: pointer;
    transition: 0.2s;
    flex-shrink: 0;
}
.ss-product-card .ss-qty-row button:hover { background: var(--ss-primary); color: #fff; }
.ss-product-card .ss-qty-row input {
    flex: 1;
    min-width: 0;
    border: none;
    text-align: center;
    font-size: 14px;
    font-weight: 700;
    color: var(--ss-dark);
    outline: none;
    background: transparent;
}

.ss-product-card .ss-add-btn {
    width: 100%;
    padding: 10px;
    background: var(--ss-primary);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 10px;
    transition: 0.2s;
}
.ss-product-card .ss-add-btn:hover { background: var(--ss-primary-hover); }
.ss-product-card .ss-add-btn:disabled { opacity: .5; cursor: not-allowed; background: #9ca3af !important; }

/* Out of Stock */
.ss-oos-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #ef4444;
    color: #fff;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    z-index: 5;
    pointer-events: none;
}
.ss-product-card.out-of-stock .ss-img-wrap::after {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,.5);
    z-index: 2;
    pointer-events: none;
}
.ss-qty-row input:disabled,
.ss-qty-row button:disabled {
    opacity: .5;
    cursor: not-allowed;
    background: #f3f4f6;
}

/* ===== NO RESULTS ===== */
.ss-no-results {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    background: var(--ss-white);
    border-radius: var(--ss-radius);
}
.ss-no-results i { font-size: 50px; color: var(--ss-gray-light); margin-bottom: 16px; display: block; }
.ss-no-results h3 { font-size: 18px; font-weight: 800; margin: 0 0 8px; }
.ss-no-results p { color: var(--ss-gray); margin-bottom: 20px; }
.ss-no-results .ss-suggestions { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; }
.ss-no-results .ss-suggestions a {
    padding: 8px 16px;
    background: var(--ss-bg);
    border-radius: 8px;
    color: var(--ss-dark);
    font-weight: 600;
    text-decoration: none;
    font-size: 13px;
    transition: 0.2s;
}
.ss-no-results .ss-suggestions a:hover { background: var(--ss-primary); color: #fff; }

/* ===== LOADING & END ===== */
.ss-loading-box, .ss-end-box {
    text-align: center;
    padding: 24px;
    display: none;
    color: var(--ss-gray);
    font-weight: 600;
    font-size: 14px;
}
.ss-loading-box.show, .ss-end-box.show { display: flex; justify-content: center; align-items: center; gap: 10px; }
.ss-loading-box i { color: var(--ss-primary); animation: ssSpin 0.8s linear infinite; }
.ss-end-box {
    background: var(--ss-white);
    border-radius: var(--ss-radius);
    margin-top: 20px;
    border: 1px dashed var(--ss-gray-light);
}
.ss-end-box i { color: #10b981; }
@keyframes ssSpin { to { transform: rotate(360deg); } }

/* ===== TOAST ===== */
.ss-toast {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(-100px);
    background: #10b981;
    color: #fff;
    padding: 12px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    z-index: 9999;
    transition: transform 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}
.ss-toast.show { transform: translateX(-50%) translateY(0); }
.ss-toast.warning { background: #f59e0b; }
.ss-toast.error { background: #ef4444; }

/* ===== DRAWER ===== */
.ss-drawer-bg {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1040;
    opacity: 0;
    visibility: hidden;
    transition: 0.3s;
}
.ss-drawer-bg.show { opacity: 1; visibility: visible; }
.ss-drawer {
    position: fixed;
    bottom: -100%;
    left: 0;
    width: 100%;
    max-height: 80vh;
    background: var(--ss-white);
    border-radius: 16px 16px 0 0;
    z-index: 1050;
    transition: 0.3s;
    display: flex;
    flex-direction: column;
}
.ss-drawer.show { bottom: 0; }
.ss-drawer-head {
    padding: 16px 20px;
    border-bottom: 1px solid var(--ss-gray-light);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.ss-drawer-head h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ss-drawer-head h4 i { color: var(--ss-primary); }
.ss-drawer-head button {
    width: 32px;
    height: 32px;
    background: var(--ss-bg);
    border: none;
    border-radius: 50%;
    font-size: 18px;
    color: var(--ss-gray);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ss-drawer-body { flex: 1; padding: 16px 20px; overflow-y: auto; }

/* ===== RESPONSIVE ===== */
@media (max-width: 991px) {
    .ss-sidebar { display: none; }
    .ss-mobile-bar { display: flex; }
    .ss-sort-bar { display: none; }
}
@media (max-width: 767px) {
    .ss-products-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .ss-header { flex-direction: column; align-items: flex-start; padding: 16px; }
    .ss-header h1 { font-size: 18px; }
    .ss-product-card .ss-info { padding: 12px; }
    .ss-product-card .ss-name { font-size: 13px; }
    .ss-product-card .ss-amount { font-size: 15px; }
    .ss-product-card .ss-actions { opacity: 1; transform: none; flex-direction: row; }
}
@media (max-width: 480px) {
    .ss-product-card .ss-rating { display: none; }
    .ss-product-card .ss-cat { display: none; }
}
</style>

<!-- HTML CONTENT -->
<div class="ss-search-page">

    <!-- Mobile Bar -->
    <div class="ss-mobile-bar">
        <button onclick="openDrawer()">
            <i class="fas fa-filter"></i> Categories
        </button>
        <select onchange="changeSort(this.value)">
            <option value="p.register_date DESC" <?php if($sortby=='p.register_date DESC') echo 'selected'; ?>>Newest</option>
            <option value="CAST(pr.price AS SIGNED INTEGER) ASC" <?php if($sortby=='CAST(pr.price AS SIGNED INTEGER) ASC') echo 'selected'; ?>>Price: Low</option>
            <option value="CAST(pr.price AS SIGNED INTEGER) DESC" <?php if($sortby=='CAST(pr.price AS SIGNED INTEGER) DESC') echo 'selected'; ?>>Price: High</option>
            <option value="CAST(p.product_rating AS SIGNED INTEGER) DESC" <?php if($sortby=='CAST(p.product_rating AS SIGNED INTEGER) DESC') echo 'selected'; ?>>Top Rated</option>
        </select>
    </div>

    <!-- Drawer -->
    <div class="ss-drawer-bg" id="ssDrawerBg" onclick="closeDrawer()"></div>
    <div class="ss-drawer" id="ssDrawer">
        <div class="ss-drawer-head">
            <h4><i class="fas fa-list-ul"></i> Categories</h4>
            <button onclick="closeDrawer()"><i class="fas fa-times"></i></button>
        </div>
        <div class="ss-drawer-body">
            <ul class="ss-cat-list">
                <?php foreach($categories as $cat): ?>
                <li>
                    <a href="index.php?shop-search&search=<?php echo urlencode($cat['category_name']); ?>" onclick="closeDrawer()">
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                        <span><?php echo $cat['product_count']; ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="ss-container">
        
        <!-- Header -->
        <div class="ss-header">
            <h1>Results for "<span><?php echo htmlspecialchars($search); ?></span>"</h1>
            <div class="ss-count"><strong><?php echo number_format($total_products); ?></strong> products</div>
        </div>

        <!-- Layout -->
        <div class="ss-layout">
            
            <!-- Sidebar -->
            <div class="ss-sidebar">
                <div class="ss-sidebar-box">
                    <div class="ss-sidebar-title"><i class="fas fa-list-ul"></i> Categories</div>
                    <ul class="ss-cat-list">
                        <?php foreach($categories as $cat): ?>
                        <li>
                            <a href="index.php?shop-search&search=<?php echo urlencode($cat['category_name']); ?>">
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                                <span><?php echo $cat['product_count']; ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Main -->
            <div class="ss-main">
                
                <!-- Sort Bar -->
                <div class="ss-sort-bar">
                    <div class="ss-result">Showing <strong><?php echo min($per_page, $total_products); ?></strong> of <strong><?php echo number_format($total_products); ?></strong></div>
                    <div class="ss-sort-tabs">
                        <button class="<?php if($sortby=='p.register_date DESC') echo 'active'; ?>" onclick="changeSort('p.register_date DESC')">Newest</button>
                        <button class="<?php if($sortby=='CAST(pr.price AS SIGNED INTEGER) ASC') echo 'active'; ?>" onclick="changeSort('CAST(pr.price AS SIGNED INTEGER) ASC')">Price ↑</button>
                        <button class="<?php if($sortby=='CAST(pr.price AS SIGNED INTEGER) DESC') echo 'active'; ?>" onclick="changeSort('CAST(pr.price AS SIGNED INTEGER) DESC')">Price ↓</button>
                        <button class="<?php if($sortby=='CAST(p.product_rating AS SIGNED INTEGER) DESC') echo 'active'; ?>" onclick="changeSort('CAST(p.product_rating AS SIGNED INTEGER) DESC')">Top Rated</button>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="ss-products-grid" id="ssProductsGrid">
                    
                    <?php if(count($products) > 0): ?>
                        <?php 
                        $idx = 0;
                        foreach($products as $prod): 
                            $pid = $prod['product_id'];
                            $pname = htmlspecialchars($prod['product_name']);
                            $punit = htmlspecialchars($prod['product_unit'] ?: 'unit');
                            $pprice = $prod['price'] ?: 0;
                            $prating = floatval($prod['product_rating'] ?: 0);
                            $pcat = htmlspecialchars($prod['sub_category_name'] ?: $prod['category_name'] ?: '');
                            $pmin = isset($prod['minimum_order']) ? max(1, (float)$prod['minimum_order']) : 1;
                            $pstock = (float)($prod['stock_quantity'] ?? 0);
                            $pInStock = ($pstock > 0);

                            $img_q = mysqli_query($conn, "SELECT picture FROM product_picture WHERE product_id='$pid' ORDER BY register_date DESC LIMIT 1");
                            $pimg = ($img_q && $img_row = mysqli_fetch_assoc($img_q)) ? $img_row['picture'] : 'no-image.png';
                            
                            $full = floor($prating);
                            $half = ($prating - $full) >= 0.5 ? 1 : 0;
                            $empty = 5 - $full - $half;
                            
                            $uid = 'ss_' . $pid . '_' . $idx;
                        ?>
                        <div class="ss-product-card<?php echo !$pInStock ? ' out-of-stock' : ''; ?>">
                            <div class="ss-img-wrap">
                                <?php if (!$pInStock): ?>
                                <span class="ss-oos-badge">Out of Stock</span>
                                <?php endif; ?>
                                <a href="index.php?product-detail&product=<?php echo $pid; ?>">
                                    <img src="uploads/<?php echo $pimg; ?>" alt="<?php echo $pname; ?>" loading="lazy" onerror="this.src='assets/images/no-image.png'">
                                </a>
                                <div class="ss-actions">
                                    <a href="#quick-look" data-toggle="modal" data-product-id="<?php echo $pid; ?>"><i class="fas fa-eye"></i></a>
                                    <?php if($is_logged_in): ?>
                                    <button onclick="add_to_wishlist('<?php echo $pid; ?>','<?php echo $customer_id; ?>')"><i class="far fa-heart"></i></button>
                                    <?php else: ?>
                                    <a href="index.php?sign-in"><i class="far fa-heart"></i></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="ss-info">
                                <?php if($pcat): ?><div class="ss-cat"><?php echo $pcat; ?></div><?php endif; ?>
                                <a href="index.php?product-detail&product=<?php echo $pid; ?>" class="ss-name"><?php echo $pname; ?></a>
                                
                                <div class="ss-rating">
                                    <span class="ss-stars">
                                        <?php 
                                        echo str_repeat('<i class="fas fa-star"></i>', $full);
                                        if($half) echo '<i class="fas fa-star-half-alt"></i>';
                                        echo str_repeat('<i class="far fa-star"></i>', $empty);
                                        ?>
                                    </span>
                                    <span class="ss-sold"><?php echo rand(20, 300); ?> sold</span>
                                </div>
                                
                                <div class="ss-price">
                                    <span class="ss-amount" data-price="<?php echo $pprice; ?>"><?php echo number_format($pprice, 0); ?> RWF</span>
                                    <span class="ss-unit">/ <?php echo $punit; ?></span>
                                </div>
                                
                                <div class="ss-qty-row">
                                    <button type="button" onclick="ssDecQty('<?php echo $uid; ?>')" <?php echo !$pInStock ? 'disabled' : ''; ?>><i class="fas fa-minus"></i></button>
                                    <input type="text" id="qty_<?php echo $uid; ?>" value="<?php echo $pmin; ?>" inputmode="decimal" oninput="ssValidateQty(this)" onblur="ssBlurQty(this, <?php echo $pmin; ?>)" <?php echo !$pInStock ? 'disabled' : ''; ?>>
                                    <button type="button" onclick="ssIncQty('<?php echo $uid; ?>')" <?php echo !$pInStock ? 'disabled' : ''; ?>><i class="fas fa-plus"></i></button>
                                </div>
                                
                                <button class="ss-add-btn" onclick="ssAddToCart('<?php echo $pid; ?>','<?php echo $customer_id; ?>','<?php echo $pprice; ?>','<?php echo $uid; ?>', event, <?php echo $pstock; ?>)" <?php echo !$pInStock ? 'disabled' : ''; ?>>
                                    <i class="fas <?php echo $pInStock ? 'fa-cart-plus' : 'fa-ban'; ?>"></i> <?php echo $pInStock ? 'Add' : 'Out of Stock'; ?>
                                </button>
                            </div>
                        </div>
                        <?php $idx++; endforeach; ?>
                    <?php else: ?>
                        <div class="ss-no-results">
                            <i class="fas fa-box-open"></i>
                            <h3>No products found</h3>
                            <p>Try a different search term.</p>
                            <div class="ss-suggestions">
                                <?php 
                                $sug_q = mysqli_query($conn, "SELECT category_name FROM product_category ORDER BY RAND() LIMIT 5");
                                if($sug_q): while($sug = mysqli_fetch_assoc($sug_q)):
                                ?>
                                <a href="index.php?shop-search&search=<?php echo urlencode($sug['category_name']); ?>"><?php echo htmlspecialchars($sug['category_name']); ?></a>
                                <?php endwhile; endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                </div>

                <!-- Loading & End -->
                <div class="ss-loading-box" id="ssLoadingBox">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
                <div class="ss-end-box" id="ssEndBox">
                    <i class="fas fa-check-circle"></i> You've seen all results!
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="ss-toast" id="ssToast"></div>

<!-- Hidden Cart Response -->
<div id="result_response_cart" style="display:none;"></div>