<?php
// This file should ONLY run when product-detail page is requested
// The check is already handled by index.php's elseif structure
// But we still need to validate the product parameter

$product_id = isset($_GET['product']) ? trim($_GET['product']) : '';

// If no product ID, show error and stop
if (empty($product_id)) {
    ?>
    <div class="container py-5 text-center" style="min-height: 50vh; display: flex; flex-direction: column; align-items: center; justify-content: center;">
        <i class="fas fa-box-open" style="font-size: 48px; color: #d1d5db; margin-bottom: 20px;"></i>
        <h3 style="color: #111827; font-size: 24px; font-weight: 700; margin-bottom: 10px;">Product not found</h3>
        <p style="color: #6b7280; margin-bottom: 24px;">The item you are looking for might have been removed or is temporarily unavailable.</p>
        <a href="index.php?shop" style="background: var(--c1, #ff5000); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 6px rgba(255,80,0,0.2);">Go to Shop</a>
    </div>
    <?php
    return;
}

// Get product from database
$product_id_escaped = $conn->real_escape_string($product_id);
$sql = "SELECT * FROM product WHERE product_id='$product_id_escaped'";
$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    ?>
    <div class="container py-5 text-center" style="min-height: 50vh; display: flex; flex-direction: column; align-items: center; justify-content: center;">
        <i class="fas fa-box-open" style="font-size: 48px; color: #d1d5db; margin-bottom: 20px;"></i>
        <h3 style="color: #111827; font-size: 24px; font-weight: 700; margin-bottom: 10px;">Product not found</h3>
        <p style="color: #6b7280; margin-bottom: 24px;">The item you are looking for might have been removed or is temporarily unavailable.</p>
        <a href="index.php?shop" style="background: var(--c1, #ff5000); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 6px rgba(255,80,0,0.2);">Go to Shop</a>
    </div>
    <?php
    return;
}

$product = $result->fetch_assoc();
$product_name = $product['product_name'] ?? '';
// FIX: Pulling correct column for Units, fallback to 'unit' if empty
$product_unit = !empty($product['units']) ? $product['units'] : 'unit'; 
$category_id = $product['category_id'] ?? '';
$short_description = $product['short_description'] ?? '';
$long_description = $product['long_description'] ?? '';
// FIX: Pulling correct column for Minimum Order, cast to float to remove trailing .00
$product_minimum_order = isset($product['minimum_order']) ? (float)$product['minimum_order'] : 1; 

// Get price
$product_price = 0;
$sql_price = "SELECT price FROM product_price WHERE product_id='$product_id_escaped'";
$result_price = $conn->query($sql_price);
if ($result_price && $row_price = $result_price->fetch_assoc()) {
    $product_price = $row_price['price'];
}

// Get stock
$stock_quantity = 0;
$sql_stock = "SELECT stock_quantity FROM product_stock WHERE product_id='$product_id_escaped'";
$result_stock = $conn->query($sql_stock);
if ($result_stock && $row_stock = $result_stock->fetch_assoc()) {
    // FIX: Cast stock to float so decimal stocks (e.g. 5.5) show correctly
    $stock_quantity = (float)$row_stock['stock_quantity'];
}

// Get category name
$category_name = '';
if (!empty($category_id)) {
    $sql_cat = "SELECT category_name FROM product_category WHERE category_id='$category_id'";
    $result_cat = $conn->query($sql_cat);
    if ($result_cat && $row_cat = $result_cat->fetch_assoc()) {
        $category_name = $row_cat['category_name'];
    }
}

// Get images
$images = [];
$sql_images = "SELECT picture FROM product_picture WHERE product_id='$product_id_escaped' ORDER BY register_date DESC";
$result_images = $conn->query($sql_images);
if ($result_images) {
    while ($row_img = $result_images->fetch_assoc()) {
        $images[] = $row_img['picture'];
    }
}
if (empty($images)) {
    $images[] = 'no-image.png';
}

// Get customer ID
$customer_id_1 = isset($_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021']) ? $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] : '';

// Delivery dates
$delivery_kigali = date('M d', strtotime('+1 day'));
$delivery_outside = date('M d', strtotime('+3 days'));

// Shipping prices in RWF
$shipping_kigali = 1000;
$shipping_outside = 2000;
?>

<!-- Required element for add_to_cart function from main.js -->
<div id="result_response_cart" style="display:none;"></div>

<style>
/* ====== MODERN MOBILE-FIRST CSS ARCHITECTURE ====== */
:root {
    --pd-primary: #ff5000;
    --pd-secondary: #ff6a33;
    --pd-dark: #111827;
    --pd-gray-dark: #4b5563;
    --pd-gray: #6b7280;
    --pd-gray-light: #d1d5db;
    --pd-bg: #f9fafb;
    --pd-surface: #ffffff;
    --pd-green: #10b981;
    --pd-whatsapp: #25d366;
    --pd-red: #ef4444;
    --pd-radius-lg: 16px;
    --pd-radius-md: 12px;
    --pd-radius-sm: 8px;
    --pd-shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
    --pd-shadow-md: 0 4px 12px rgba(0,0,0,0.08);
}

.pd-wrapper { max-width: 1200px; margin: 0 auto; padding: 20px 16px 80px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: transparent; }
.pd-wrapper * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

/* Breadcrumbs */
.pd-bread { padding: 0 0 20px; font-size: 13px; color: var(--pd-gray); display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.pd-bread a { color: var(--pd-gray-dark); text-decoration: none; font-weight: 500; transition: 0.2s; }
.pd-bread a:hover { color: var(--pd-primary); }
.pd-bread span { color: var(--pd-dark); font-weight: 600; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }

/* Main Box */
.pd-box { display: flex; gap: 40px; background: var(--pd-surface); border-radius: var(--pd-radius-lg); padding: 32px; margin-bottom: 24px; box-shadow: var(--pd-shadow-md); border: 1px solid rgba(0,0,0,0.02); }

/* Gallery */
.pd-gal { display: flex; gap: 16px; width: 500px; flex-shrink: 0; }
.pd-ths { width: 72px; display: flex; flex-direction: column; gap: 12px; }
.pd-th { width: 72px; height: 72px; border: 2px solid var(--pd-bg); border-radius: var(--pd-radius-sm); overflow: hidden; cursor: pointer; transition: 0.2s; background: var(--pd-bg); }
.pd-th:hover, .pd-th.on { border-color: var(--pd-primary); }
.pd-th img { width: 100%; height: 100%; object-fit: contain; mix-blend-mode: multiply; }
.pd-big { flex: 1; background: var(--pd-bg); border-radius: var(--pd-radius-md); display: flex; align-items: center; justify-content: center; min-height: 400px; padding: 20px; position: relative; }
.pd-big img { width: 100%; height: 100%; object-fit: contain; mix-blend-mode: multiply; }

/* Product Info */
.pd-inf { flex: 1; display: flex; flex-direction: column; gap: 20px; }
.pd-ttl { font-size: 26px; font-weight: 800; color: var(--pd-dark); line-height: 1.3; margin: 0; }

.pd-rt { display: flex; align-items: center; gap: 12px; font-size: 14px; color: var(--pd-gray); }
.pd-st { color: #fbbf24; font-size: 16px; letter-spacing: 2px; }
.pd-st-text { font-weight: 600; color: var(--pd-dark); }

/* Pricing Block */
.pd-pr { background: linear-gradient(135deg, var(--pd-dark), #374151); border-radius: var(--pd-radius-md); padding: 20px 24px; position: relative; overflow: hidden; }
.pd-pr::after { content: ''; position: absolute; top: 0; right: 0; width: 150px; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05)); transform: skewX(-20deg) translateX(50px); }
.pd-prr { display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; position: relative; z-index: 2; }
.pd-amt { color: #ffffff; font-size: 32px; font-weight: 800; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
.pd-per { color: rgba(255,255,255,0.7); font-size: 15px; font-weight: 500; }
.pd-stk { background: rgba(255,255,255,0.15); color: #ffffff; font-size: 11px; padding: 4px 10px; border-radius: 20px; margin-left: auto; font-weight: 600; backdrop-filter: blur(4px); }

/* Quick Details Rows */
.pd-rws { border: 1px solid var(--pd-gray-light); border-radius: var(--pd-radius-md); background: var(--pd-surface); }
.pd-rw { display: flex; justify-content: space-between; padding: 14px 20px; border-bottom: 1px solid var(--pd-bg); font-size: 14px; }
.pd-rw:last-child { border-bottom: none; }
.pd-rwl { color: var(--pd-gray); font-weight: 500; }
.pd-rwv { color: var(--pd-dark); font-weight: 600; text-align: right; }
.pd-rwv a { color: var(--pd-primary); text-decoration: none; }
.pd-rwv a:hover { text-decoration: underline; }

/* Shipping Info */
.pd-shp { background: var(--pd-bg); border-radius: var(--pd-radius-md); padding: 16px 20px; border: 1px dashed var(--pd-gray-light); }
.pd-shph { font-size: 14px; font-weight: 700; color: var(--pd-dark); margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
.pd-shph i { color: var(--pd-primary); }
.pd-shpr { display: flex; justify-content: space-between; font-size: 13px; padding: 6px 0; color: var(--pd-gray-dark); font-weight: 500; }
.pd-shpr span:last-child { font-weight: 700; color: var(--pd-dark); }

/* Quantity Selector */
.pd-qty { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; margin-top: 10px; }
.pd-qtyl { font-size: 15px; font-weight: 700; color: var(--pd-dark); }
.pd-qtyb { display: flex; border: 2px solid var(--pd-gray-light); border-radius: var(--pd-radius-sm); overflow: hidden; height: 48px; background: var(--pd-surface); transition: 0.2s; }
.pd-qtyb:focus-within { border-color: var(--pd-primary); box-shadow: 0 0 0 3px rgba(255,80,0,0.1); }
.pd-qtyb button { width: 48px; height: 100%; border: none; background: var(--pd-bg); cursor: pointer; font-size: 20px; color: var(--pd-gray-dark); transition: 0.2s; }
.pd-qtyb button:hover, .pd-qtyb button:active { background: var(--pd-gray-light); color: var(--pd-dark); }
.pd-qtyb input { width: 64px; height: 100%; border: none; border-left: 1px solid var(--pd-gray-light); border-right: 1px solid var(--pd-gray-light); text-align: center; font-size: 16px; font-weight: 700; color: var(--pd-dark); background: transparent; padding: 0; }
.pd-qtyb input:focus { outline: none; }
.pd-qtyb input::-webkit-outer-spin-button, .pd-qtyb input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.pd-qtyi { font-size: 13px; color: var(--pd-gray); font-weight: 500; background: var(--pd-bg); padding: 6px 12px; border-radius: 20px; }

/* Desktop Buttons */
.pd-btns { display: flex; gap: 16px; margin-top: 10px; }
.pd-btn { flex: 1; height: 52px; border-radius: 26px; font-size: 16px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; border: none; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.pd-buy { background: linear-gradient(135deg, var(--pd-primary), var(--pd-secondary)); color: #fff; box-shadow: 0 4px 12px rgba(255,80,0,0.2); }
.pd-buy:hover { transform: translateY(-3px); box-shadow: 0 8px 16px rgba(255,80,0,0.3); }
.pd-cart { background: var(--pd-surface); color: var(--pd-dark); border: 2px solid var(--pd-dark); }
.pd-cart:hover { background: var(--pd-dark); color: #fff; transform: translateY(-3px); }
.pd-btn-disabled { opacity: 0.5; cursor: not-allowed; background: #9ca3af; color: #fff; border-color: #9ca3af; }
.pd-btn-disabled:hover { transform: none; box-shadow: none; background: #9ca3af; }
.pd-qtyb-disabled { opacity: 0.5; }
.pd-qtyi-oos { color: var(--pd-red); background: #fee2e2; }

.pd-wa { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 16px; background: var(--pd-whatsapp); border-radius: 26px; color: #fff; text-decoration: none; font-size: 16px; font-weight: 700; transition: 0.3s; box-shadow: 0 4px 12px rgba(37,211,102,0.2); }
.pd-wa:hover { background: #1da855; transform: translateY(-3px); box-shadow: 0 8px 16px rgba(37,211,102,0.3); }

/* Trust Badges */
.pd-tru { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 10px; }
.pd-tri { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: var(--pd-gray-dark); background: var(--pd-bg); padding: 8px 14px; border-radius: var(--pd-radius-sm); }
.pd-tri i { color: var(--pd-green); font-size: 14px; }

/* Tabs System */
.pd-tabs { background: var(--pd-surface); border-radius: var(--pd-radius-lg); padding: 32px; margin-bottom: 24px; box-shadow: var(--pd-shadow-md); border: 1px solid rgba(0,0,0,0.02); }
.pd-tnav { display: flex; gap: 32px; border-bottom: 2px solid var(--pd-bg); margin-bottom: 24px; overflow-x: auto; -webkit-overflow-scrolling: touch; }
.pd-tnav::-webkit-scrollbar { display: none; }
.pd-tbtn { padding: 0 0 16px 0; border: none; background: none; font-size: 16px; font-weight: 600; color: var(--pd-gray); cursor: pointer; border-bottom: 3px solid transparent; margin-bottom: -2px; white-space: nowrap; transition: 0.2s; }
.pd-tbtn:hover { color: var(--pd-dark); }
.pd-tbtn.on { color: var(--pd-primary); border-color: var(--pd-primary); }

.pd-tpane { display: none; font-size: 15px; line-height: 1.7; color: var(--pd-gray-dark); animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.pd-tpane.on { display: block; }
.pd-tpane p { margin-top: 0; margin-bottom: 16px; }
.pd-tpane table { width: 100%; border-collapse: collapse; border-radius: var(--pd-radius-sm); overflow: hidden; border: 1px solid var(--pd-gray-light); }
.pd-tpane td { padding: 14px 16px; border-bottom: 1px solid var(--pd-gray-light); font-size: 14px; }
.pd-tpane tr:last-child td { border-bottom: none; }
.pd-tpane td:first-child { background: var(--pd-bg); width: 140px; color: var(--pd-gray-dark); font-weight: 600; }

/* Related Products Grid */
.pd-rel { background: var(--pd-surface); border-radius: var(--pd-radius-lg); padding: 32px; box-shadow: var(--pd-shadow-md); border: 1px solid rgba(0,0,0,0.02); }
.pd-rel h3 { font-size: 20px; font-weight: 800; color: var(--pd-dark); margin: 0 0 24px; display: flex; align-items: center; gap: 10px; }
.pd-rel h3 i { color: var(--pd-primary); }

.pd-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; }
.pd-card { border: 1px solid var(--pd-gray-light); border-radius: var(--pd-radius-md); overflow: hidden; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; background: var(--pd-surface); }
.pd-card:hover { box-shadow: var(--pd-shadow-md); transform: translateY(-4px); border-color: transparent; }
.pd-card a { text-decoration: none; display: block; flex: 1; }
.pd-cimg { aspect-ratio: 1; background: var(--pd-bg); padding: 12px; display: flex; align-items: center; justify-content: center; overflow: hidden; border-bottom: 1px solid var(--pd-bg); }
.pd-cimg img { width: 100%; height: 100%; object-fit: contain; transition: 0.4s ease; mix-blend-mode: multiply; }
.pd-card:hover .pd-cimg img { transform: scale(1.08); }
.pd-cbod { padding: 16px; display: flex; flex-direction: column; gap: 8px; }
.pd-cnam { font-size: 13px; font-weight: 600; color: var(--pd-dark); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 36px; }
.pd-cprc { font-size: 15px; font-weight: 800; color: var(--pd-primary); }

.pd-cqty { display: flex; gap: 6px; padding: 0 16px 16px; margin-top: auto; }
.pd-cqty input { flex: 1; padding: 0 8px; border: 1px solid var(--pd-gray-light); border-radius: var(--pd-radius-sm); font-size: 13px; font-weight: 600; text-align: center; color: var(--pd-dark); height: 38px; min-width: 0; appearance: textfield; }
.pd-cqty input:focus { outline: none; border-color: var(--pd-primary); }
.pd-cqty button { width: 44px; height: 38px; background: var(--pd-primary); border: none; border-radius: var(--pd-radius-sm); color: #fff; cursor: pointer; font-size: 14px; transition: 0.2s; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.pd-cqty button:hover, .pd-cqty button:active { background: var(--pd-secondary); }

/* Mobile Sticky Action Bar */
.pd-mob { display: none; position: fixed; bottom: 0; left: 0; right: 0; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); padding: 12px 16px; box-shadow: 0 -4px 20px rgba(0,0,0,0.1); z-index: 10000; gap: 12px; padding-bottom: calc(12px + env(safe-area-inset-bottom)); border-top: 1px solid var(--pd-gray-light); }
.pd-mwa { width: 48px; height: 48px; background: var(--pd-whatsapp); border-radius: var(--pd-radius-sm); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; text-decoration: none; flex-shrink: 0; transition: 0.2s; box-shadow: var(--pd-shadow-sm); }
.pd-mwa:active { transform: scale(0.95); }
.pd-mcart, .pd-mbuy { flex: 1; height: 48px; border-radius: var(--pd-radius-sm); font-size: 14px; font-weight: 700; cursor: pointer; border: none; transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px; }
.pd-mcart { background: var(--pd-surface); color: var(--pd-dark); border: 2px solid var(--pd-dark); }
.pd-mcart:active { background: var(--pd-bg); }
.pd-mbuy { background: linear-gradient(135deg, var(--pd-primary), var(--pd-secondary)); color: #fff; box-shadow: 0 4px 10px rgba(255,80,0,0.25); }
.pd-mbuy:active { transform: scale(0.98); }

/* ========================================================
   RESPONSIVE BREAKPOINTS
   ======================================================== */
@media (max-width: 1024px) {
    .pd-gal { width: 400px; }
    .pd-grid { grid-template-columns: repeat(4, 1fr); }
}

@media (max-width: 900px) {
    .pd-box { flex-direction: column; padding: 24px; gap: 24px; }
    .pd-gal { width: 100%; flex-direction: column-reverse; gap: 12px; }
    .pd-ths { flex-direction: row; width: 100%; overflow-x: auto; padding-bottom: 8px; -webkit-overflow-scrolling: touch; }
    .pd-th { width: 64px; height: 64px; flex-shrink: 0; border-radius: var(--pd-radius-sm); }
    .pd-big { min-height: 320px; padding: 12px; }
    .pd-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 600px) {
    .pd-wrapper { padding: 12px 12px 100px; }
    .pd-box { padding: 16px; border-radius: var(--pd-radius-md); margin-bottom: 16px; }
    .pd-gal { gap: 10px; }
    .pd-ths { gap: 10px; }
    .pd-th { width: 60px; height: 60px; }
    .pd-big { min-height: 280px; border-radius: var(--pd-radius-sm); background: #fff; }
    
    .pd-inf { gap: 16px; }
    .pd-ttl { font-size: 22px; }
    .pd-pr { padding: 16px; border-radius: var(--pd-radius-sm); }
    .pd-amt { font-size: 28px; }
    
    .pd-rw { padding: 12px 0; font-size: 13px; }
    .pd-shp { padding: 12px; }
    
    /* Hide desktop action buttons, show Sticky Mobile Bar */
    .pd-btns, .pd-wa { display: none; }
    .pd-mob { display: flex; }
    
    .pd-qty { gap: 12px; justify-content: space-between; background: var(--pd-bg); padding: 12px; border-radius: var(--pd-radius-sm); }
    .pd-qtyl { font-size: 14px; }
    .pd-qtyi { margin-left: auto; background: var(--pd-surface); border: 1px solid var(--pd-gray-light); }
    
    .pd-tabs { padding: 20px 16px; border-radius: var(--pd-radius-md); margin-bottom: 16px; }
    .pd-tnav { gap: 20px; margin-bottom: 20px; }
    .pd-tbtn { font-size: 14px; padding-bottom: 12px; }
    
    .pd-rel { padding: 20px 16px; border-radius: var(--pd-radius-md); }
    .pd-rel h3 { font-size: 18px; margin-bottom: 16px; }
    
    /* Grid perfectly snaps to 2 columns on phones */
    .pd-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .pd-cbod { padding: 12px; gap: 6px; }
    .pd-cqty { padding: 0 12px 12px; }
    .pd-cqty input { height: 36px; font-size: 12px; }
    .pd-cqty button { height: 36px; width: 36px; }
}
</style>

<div class="pd-wrapper">
    <div class="pd-bread">
        <a href="index.php"><i class="fas fa-home"></i> Home</a> <i class="fas fa-chevron-right" style="font-size:10px;opacity:0.5;"></i> 
        <a href="index.php?shop">Shop</a> <i class="fas fa-chevron-right" style="font-size:10px;opacity:0.5;"></i> 
        <span><?php echo htmlspecialchars($product_name); ?></span>
    </div>

    <div class="pd-box">
        <div class="pd-gal">
            <div class="pd-ths">
                <?php foreach ($images as $i => $img): ?>
                <div class="pd-th <?php echo $i===0?'on':''; ?>" onclick="setImg('<?php echo htmlspecialchars($img); ?>',this)">
                    <img src="uploads/<?php echo htmlspecialchars($img); ?>" alt="Thumbnail">
                </div>
                <?php endforeach; ?>
            </div>
            <div class="pd-big">
                <img src="uploads/<?php echo htmlspecialchars($images[0]); ?>" id="mainImg" alt="<?php echo htmlspecialchars($product_name); ?>">
            </div>
        </div>

        <div class="pd-inf">
            <h1 class="pd-ttl"><?php echo htmlspecialchars($product_name); ?></h1>
            <div class="pd-rt">
                <span class="pd-st">★★★★★</span>
                <span class="pd-st-text">4.5 Rating</span>
                <span>•</span>
                <span><?php echo rand(50,500); ?>+ Sold</span>
            </div>
            
            <div class="pd-pr">
                <div class="pd-prr">
                    <span class="pd-amt" data-price="<?php echo $product_price; ?>"><?php echo number_format($product_price, 0); ?> RWF</span>
                    <span class="pd-per">/ <?php echo htmlspecialchars($product_unit); ?></span>
                    <?php if ($stock_quantity > 0 && $stock_quantity <= 10): ?>
                    <span class="pd-stk">Only <?php echo $stock_quantity; ?> left</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pd-rws">
                <div class="pd-rw">
                    <span class="pd-rwl">Category</span>
                    <span class="pd-rwv"><a href="index.php?shop-search&search=<?php echo urlencode($category_name); ?>"><?php echo htmlspecialchars($category_name); ?></a></span>
                </div>
                <div class="pd-rw">
                    <span class="pd-rwl">Stock</span>
                    <span class="pd-rwv" style="color:<?php echo $stock_quantity>0?'var(--pd-green)':'var(--pd-red)'; ?>">
                        <i class="fas <?php echo $stock_quantity>0?'fa-check-circle':'fa-times-circle'; ?>"></i> <?php echo $stock_quantity>0?$stock_quantity.' available':'Out of stock'; ?>
                    </span>
                </div>
                <div class="pd-rw">
                    <span class="pd-rwl">Min. Order</span>
                    <span class="pd-rwv"><?php echo $product_minimum_order; ?> <?php echo htmlspecialchars($product_unit); ?></span>
                </div>
            </div>

            <div class="pd-shp">
                <div class="pd-shph"><i class="fas fa-truck"></i> Estimated Shipping</div>
                <div class="pd-shpr">
                    <span>Kigali Area (By <?php echo $delivery_kigali; ?>)</span>
                    <span data-price="<?php echo $shipping_kigali; ?>"><?php echo number_format($shipping_kigali, 0); ?> RWF</span>
                </div>
                <div class="pd-shpr">
                    <span>Outside Kigali (By <?php echo $delivery_outside; ?>)</span>
                    <span data-price="<?php echo $shipping_outside; ?>"><?php echo number_format($shipping_outside, 0); ?> RWF</span>
                </div>
            </div>

            <div class="pd-qty">
                <span class="pd-qtyl">Quantity:</span>
                <?php if ($stock_quantity > 0): ?>
                <div class="pd-qtyb">
                    <button type="button" onclick="chgQty(-1)" aria-label="Decrease"><i class="fas fa-minus" style="font-size: 14px;"></i></button>
                    <!-- step="any" allows decimal input natively -->
                    <input type="number" id="pdQty" value="<?php echo $product_minimum_order; ?>" step="any" min="<?php echo $product_minimum_order; ?>" max="<?php echo $stock_quantity; ?>" aria-label="Quantity">
                    <button type="button" onclick="chgQty(1)" aria-label="Increase"><i class="fas fa-plus" style="font-size: 14px;"></i></button>
                </div>
                <span class="pd-qtyi"><?php echo $stock_quantity; ?> Total Available</span>
                <?php else: ?>
                <div class="pd-qtyb pd-qtyb-disabled">
                    <button type="button" disabled><i class="fas fa-minus" style="font-size: 14px;"></i></button>
                    <input type="number" id="pdQty" value="0" disabled aria-label="Quantity">
                    <button type="button" disabled><i class="fas fa-plus" style="font-size: 14px;"></i></button>
                </div>
                <span class="pd-qtyi pd-qtyi-oos">0 Available</span>
                <?php endif; ?>
            </div>

            <div class="pd-btns">
                <?php if ($stock_quantity > 0): ?>
                <button type="button" class="pd-btn pd-buy" onclick="buyNow()"><i class="fas fa-bolt"></i> Buy Now</button>
                <button type="button" class="pd-btn pd-cart" onclick="addToCartNow()"><i class="fas fa-cart-plus"></i> Add to Cart</button>
                <?php else: ?>
                <button type="button" class="pd-btn pd-buy pd-btn-disabled" disabled><i class="fas fa-bolt"></i> Out of Stock</button>
                <button type="button" class="pd-btn pd-cart pd-btn-disabled" disabled><i class="fas fa-times-circle"></i> Unavailable</button>
                <?php endif; ?>
            </div>

            <a href="https://wa.me/250783654454?text=<?php echo urlencode('Hi! I want: '.$product_name.' - RWF '.number_format($product_price,0)); ?>" target="_blank" class="pd-wa">
                <i class="fab fa-whatsapp" style="font-size: 20px;"></i> Fast Order via WhatsApp
            </a>

            <div class="pd-tru">
                <div class="pd-tri"><i class="fas fa-shield-alt"></i> Secure Payment</div>
                <div class="pd-tri"><i class="fas fa-shipping-fast"></i> Express Delivery</div>
                <div class="pd-tri"><i class="fas fa-undo"></i> Easy Returns</div>
            </div>
        </div>
    </div>

    <div class="pd-tabs">
        <div class="pd-tnav">
            <button class="pd-tbtn on" onclick="showTab('desc',this)">Description</button>
            <button class="pd-tbtn" onclick="showTab('specs',this)">Specifications</button>
            <button class="pd-tbtn" onclick="showTab('ship',this)">Shipping & Delivery</button>
        </div>
        <div class="pd-tpane on" id="pDesc">
            <?php if(!empty($short_description)): ?>
                <p style="font-size: 16px; color: var(--pd-dark); font-weight: 600;"><?php echo nl2br(htmlspecialchars($short_description)); ?></p>
            <?php endif; ?>
            <?php echo !empty($long_description) ? '<p>'.nl2br(htmlspecialchars($long_description)).'</p>' : '<p>Premium quality '.htmlspecialchars($product_name).' locally sourced and delivered fresh to your door. Our strict quality control ensures you get only the best items.</p>'; ?>
        </div>
        <div class="pd-tpane" id="pSpecs">
            <table>
                <tr><td>Product Name</td><td><?php echo htmlspecialchars($product_name); ?></td></tr>
                <tr><td>Category</td><td><?php echo htmlspecialchars($category_name); ?></td></tr>
                <tr><td>Selling Unit</td><td><?php echo htmlspecialchars($product_unit); ?></td></tr>
                <tr><td>Minimum Order</td><td><?php echo $product_minimum_order; ?> <?php echo htmlspecialchars($product_unit); ?>(s)</td></tr>
                <tr><td>Base Price</td><td><span data-price="<?php echo $product_price; ?>"><?php echo number_format($product_price, 0); ?> RWF</span></td></tr>
            </table>
        </div>
        <div class="pd-tpane" id="pShip">
            <table>
                <tr>
                    <td>Kigali Delivery</td>
                    <td>Same Day / Next Day</td>
                    <td><span data-price="<?php echo $shipping_kigali; ?>"><?php echo number_format($shipping_kigali, 0); ?> RWF</span></td>
                </tr>
                <tr>
                    <td>Outside Kigali</td>
                    <td>2-3 Business Days</td>
                    <td><span data-price="<?php echo $shipping_outside; ?>"><?php echo number_format($shipping_outside, 0); ?> RWF</span></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="pd-rel">
        <h3><i class="fas fa-heart"></i> You May Also Like</h3>
        <div class="pd-grid">
            <?php
            // Fetching related products utilizing existing minimum_order logic
            $sql_rel = "SELECT p.product_id, p.product_name, p.minimum_order, pr.price, 
                        (SELECT picture FROM product_picture WHERE product_id = p.product_id LIMIT 1) as picture 
                        FROM product p 
                        LEFT JOIN product_price pr ON pr.product_id = p.product_id 
                        WHERE p.product_id != '$product_id_escaped' 
                        ORDER BY RAND() LIMIT 10";
            $res_rel = $conn->query($sql_rel);
            if ($res_rel && $res_rel->num_rows > 0): 
                while ($rel = $res_rel->fetch_assoc()): 
                    $r_img = !empty($rel['picture']) ? $rel['picture'] : 'no-image.png';
                    $r_price = (float)($rel['price'] ?? 0);
                    $r_min = (float)($rel['minimum_order'] ?? 1);
            ?>
            <div class="pd-card">
                <a href="index.php?product-detail&product=<?php echo $rel['product_id']; ?>">
                    <div class="pd-cimg"><img src="uploads/<?php echo htmlspecialchars($r_img); ?>" alt="<?php echo htmlspecialchars($rel['product_name']); ?>" loading="lazy"></div>
                    <div class="pd-cbod">
                        <div class="pd-cnam"><?php echo htmlspecialchars($rel['product_name']); ?></div>
                        <div class="pd-cprc" data-price="<?php echo $r_price; ?>"><?php echo number_format($r_price, 0); ?> RWF</div>
                    </div>
                </a>
                <div class="pd-cqty">
                    <input type="number" id="relQty<?php echo $rel['product_id']; ?>" value="<?php echo $r_min; ?>" min="<?php echo $r_min; ?>" step="any" aria-label="Quantity">
                    <button type="button" onclick="quickAddCart('<?php echo $rel['product_id']; ?>',<?php echo $r_price; ?>,'relQty<?php echo $rel['product_id']; ?>')" aria-label="Add to cart"><i class="fas fa-cart-plus"></i></button>
                </div>
            </div>
            <?php endwhile; endif; ?>
        </div>
    </div>
</div>

<!-- Mobile Sticky Bottom Action Bar -->
<div class="pd-mob">
    <a href="https://wa.me/250783654454?text=<?php echo urlencode('Order: '.$product_name); ?>" class="pd-mwa" aria-label="Order on WhatsApp"><i class="fab fa-whatsapp"></i></a>
    <?php if ($stock_quantity > 0): ?>
    <button type="button" class="pd-mcart" onclick="addToCartNow()"><i class="fas fa-cart-plus"></i> Add to Cart</button>
    <button type="button" class="pd-mbuy" onclick="buyNow()"><i class="fas fa-bolt"></i> Buy Now</button>
    <?php else: ?>
    <button type="button" class="pd-mcart pd-btn-disabled" disabled><i class="fas fa-times-circle"></i> Out of Stock</button>
    <button type="button" class="pd-mbuy pd-btn-disabled" disabled><i class="fas fa-ban"></i> Unavailable</button>
    <?php endif; ?>
</div>

<!-- Scripts (Preserved perfectly) -->
<script>
// Product data object mapping to PHP payload
var pdProduct = {
    id: '<?php echo addslashes($product_id); ?>',
    price: <?php echo (float)$product_price; ?>,
    minQty: <?php echo (float)$product_minimum_order; ?>,
    maxQty: <?php echo (float)$stock_quantity; ?>,
    customerId: '<?php echo addslashes($customer_id_1); ?>'
};

// Initialize currency display on page load
document.addEventListener('DOMContentLoaded', function() {
    if (typeof gbUpdatePrices === 'function') {
        gbUpdatePrices();
    }
});

// Get quantity payload securely
function getPdQty() {
    var val = parseFloat(document.getElementById('pdQty').value) || pdProduct.minQty;
    if (val < pdProduct.minQty) val = pdProduct.minQty;
    if (pdProduct.maxQty > 0 && val > pdProduct.maxQty) val = pdProduct.maxQty;
    return val;
}

// Advanced step-based quantity changer (Handles decimals flawlessly)
function chgQty(delta) {
    var inp = document.getElementById('pdQty');
    var val = parseFloat(inp.value) || pdProduct.minQty;
    
    // Auto-detect step based on the minimum quantity logic
    var step = 1;
    if (pdProduct.minQty < 1 || pdProduct.minQty % 1 !== 0) {
        step = 0.5; // Fractional minimum order steps by 0.5 
    }
    if (pdProduct.minQty <= 0.1) {
        step = 0.1; // Micro orders step by 0.1
    }

    val = val + (delta * step);
    
    // Rounding to prevent JS floating point visual bugs
    val = Math.round(val * 100) / 100;
    
    if (val < pdProduct.minQty) val = pdProduct.minQty;
    if (pdProduct.maxQty > 0 && val > pdProduct.maxQty) val = pdProduct.maxQty;
    
    inp.value = val;
}

// Add to cart bridge calling main.js
function isInStock() {
    if (pdProduct.maxQty <= 0) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Out of Stock',
                text: 'Sorry, this product is currently out of stock.',
                confirmButtonColor: '#ff5000'
            });
        } else {
            alert('Sorry, this product is currently out of stock.');
        }
        return false;
    }
    return true;
}

function addToCartNow() {
    if (!isInStock()) return;
    var qty = getPdQty();
    if (typeof add_to_cart === 'function') {
        add_to_cart(pdProduct.id, pdProduct.customerId, pdProduct.price, qty);
        
        // Visual feedback on mobile sticky bar
        var btn = document.querySelector('.pd-mcart');
        if(btn && window.innerWidth <= 600) {
            var origHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Added';
            btn.style.background = '#10b981';
            btn.style.color = '#fff';
            btn.style.borderColor = '#10b981';
            setTimeout(function(){
                btn.innerHTML = origHtml;
                btn.style.background = '';
                btn.style.color = '';
                btn.style.borderColor = '';
            }, 2000);
        }
    } else {
        console.error('add_to_cart function not found');
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error', 'Cart function not loaded. Please refresh the page.', 'error');
        } else {
            alert('Error: Cart function not loaded. Please refresh the page.');
        }
    }
}

// Buy Now redirect wrapper
function buyNow() {
    if (!isInStock()) return;
    var qty = getPdQty();
    if (typeof add_to_cart === 'function') {
        add_to_cart(pdProduct.id, pdProduct.customerId, pdProduct.price, qty);
        
        // Show loading state
        var btns = document.querySelectorAll('.pd-buy, .pd-mbuy');
        btns.forEach(function(b) { b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redirecting...'; });
        
        setTimeout(function() {
            window.location.href = 'index.php?cart';
        }, 1500);
    } else {
        console.error('add_to_cart function not found');
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error', 'Cart function not loaded. Please refresh the page.', 'error');
        } else {
            alert('Error: Cart function not loaded. Please refresh the page.');
        }
    }
}

// Quick add for related products loop
function quickAddCart(productId, price, inputId) {
    var inp = document.getElementById(inputId);
    var qty = inp ? parseFloat(inp.value) || 1 : 1;
    if (typeof add_to_cart === 'function') {
        add_to_cart(productId, pdProduct.customerId, price, qty);
    }
}

// Image gallery toggle
function setImg(imgName, el) {
    var mainImg = document.getElementById('mainImg');
    mainImg.style.opacity = '0.5';
    
    setTimeout(function() {
        mainImg.src = 'uploads/' + imgName;
        mainImg.style.opacity = '1';
    }, 150);
    
    document.querySelectorAll('.pd-th').forEach(function(t) { t.classList.remove('on'); });
    el.classList.add('on');
}

// Tab navigation engine
function showTab(tabId, btn) {
    document.querySelectorAll('.pd-tpane').forEach(function(p) { p.classList.remove('on'); });
    document.querySelectorAll('.pd-tbtn').forEach(function(b) { b.classList.remove('on'); });
    
    var paneId = 'p' + tabId.charAt(0).toUpperCase() + tabId.slice(1);
    var pane = document.getElementById(paneId);
    
    if (pane) pane.classList.add('on');
    btn.classList.add('on');
}
</script>