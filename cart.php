<?php
$customer_temp_id = $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] ?? '';

$sql3 = "SELECT * from cart where customer_id='$customer_temp_id'";
$result3 = $conn->query($sql3);

$cart_id = '0';
if ($result3 && $result3->num_rows > 0) {
    $row3 = $result3->fetch_assoc();
    $cart_id = $row3['cart_id'];
}

$query = $conn->query("SELECT * from cart_item where cart_id='$cart_id' and status='ACTIVE'");
$cart_count = $query ? $query->num_rows : 0;

$sum_cart_items = 0;
if ($cart_id !== '0') {
    $sql_sum = "SELECT sum(price) as s1 from cart_item where cart_id='$cart_id' and status='ACTIVE'";
    $result_sum = $conn->query($sql_sum);
    if ($result_sum && $row_sum = $result_sum->fetch_assoc()) {
        $sum_cart_items = $row_sum['s1'] ?? 0;
    }
}

$provinces = [];
$sql_prov = "SELECT DISTINCT province FROM rw_location ORDER BY province ASC";
$result_prov = $conn->query($sql_prov);
if ($result_prov) {
    while ($row_prov = $result_prov->fetch_assoc()) {
        $provinces[] = $row_prov['province'];
    }
}
?>

<div id="result_response_cart" style="display:none;"></div>
<input type="hidden" id="result_response" value="">
<input type="hidden" id="current_cart_id" value="<?php echo htmlspecialchars($cart_id); ?>">

<style>
:root {
    --ali-red: #ff4747;
    --ali-red-dark: #e63c3c;
    --ali-orange: #ff6600;
    --ali-green: #00a650;
    --ali-dark: #222;
    --ali-gray: #666;
    --ali-light: #f5f5f5;
    --ali-border: #e8e8e8;
    --wa-green: #25d366;
}

* { box-sizing: border-box; }

.ali-cart {
    max-width: 1200px;
    margin: 0 auto;
    padding: 15px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
    background: #f5f5f5;
    min-height: 70vh;
}

.ali-bread { display: flex; align-items: center; gap: 8px; padding: 12px 0; font-size: 13px; color: var(--ali-gray); }
.ali-bread a { color: var(--ali-gray); text-decoration: none; }
.ali-bread a:hover { color: var(--ali-red); }
.ali-bread i { font-size: 10px; color: #ccc; }

.ali-cart-title {
    display: flex; align-items: center; justify-content: space-between;
    background: linear-gradient(135deg, var(--ali-red), var(--ali-orange));
    padding: 20px 25px; border-radius: 12px 12px 0 0; color: #fff;
}
.ali-cart-title h1 { font-size: 22px; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 12px; }
.ali-cart-count { background: rgba(255,255,255,0.2); padding: 6px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; }

.ali-cart-body { display: flex; gap: 20px; }
.ali-cart-left { flex: 1; min-width: 0; }
.ali-cart-right { width: 320px; flex-shrink: 0; }

.ali-items-wrap { background: #fff; border-radius: 0 0 12px 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden; }

.ali-cart-header {
    display: grid; grid-template-columns: 50px 2fr 1fr 140px 1fr 50px;
    gap: 15px; padding: 14px 20px; background: #fafafa;
    border-bottom: 1px solid var(--ali-border);
    font-size: 12px; font-weight: 600; color: var(--ali-gray); text-transform: uppercase;
}

.ali-cart-item {
    display: grid; grid-template-columns: 50px 2fr 1fr 140px 1fr 50px;
    gap: 15px; padding: 20px; border-bottom: 1px solid #f0f0f0; align-items: center;
}
.ali-cart-item:last-child { border-bottom: none; }

.ali-check { display: flex; align-items: center; justify-content: center; }
.ali-check input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: var(--ali-red); }

.ali-product { display: flex; gap: 15px; align-items: center; }
.ali-product-img { width: 90px; height: 90px; border-radius: 8px; overflow: hidden; border: 1px solid var(--ali-border); flex-shrink: 0; }
.ali-product-img img { width: 100%; height: 100%; object-fit: cover; }
.ali-product-info { flex: 1; min-width: 0; }
.ali-product-name { font-size: 14px; font-weight: 500; color: var(--ali-dark); text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; margin-bottom: 6px; }
.ali-product-name:hover { color: var(--ali-red); }
.ali-product-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 6px; }
.ali-product-tag { font-size: 11px; padding: 3px 8px; background: #f5f5f5; border-radius: 4px; color: var(--ali-gray); }
.ali-product-tag i { margin-right: 4px; color: var(--ali-red); }

.ali-price { text-align: center; }
.ali-price-current { font-size: 16px; font-weight: 700; color: var(--ali-red); }
.ali-price-unit { font-size: 11px; color: var(--ali-gray); margin-top: 2px; }

.ali-quantity { display: flex; justify-content: center; }
.ali-qty-box { display: flex; align-items: center; border: 2px solid var(--ali-border); border-radius: 8px; overflow: hidden; }
.ali-qty-box:hover, .ali-qty-box:focus-within { border-color: var(--ali-red); }
.ali-qty-btn { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: #fafafa; border: none; cursor: pointer; font-size: 14px; color: var(--ali-dark); transition: all 0.2s; }
.ali-qty-btn:hover { background: var(--ali-red); color: #fff; }
.ali-qty-input { width: 50px; height: 36px; border: none; text-align: center; font-size: 14px; font-weight: 600; color: var(--ali-dark); background: #fff; }
.ali-qty-input:focus { outline: none; background: #fffbf0; }

.ali-total { text-align: center; }
.ali-total-price { font-size: 18px; font-weight: 700; color: var(--ali-red); }

.ali-actions { display: flex; align-items: center; justify-content: center; }
.ali-del-btn { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--ali-border); border-radius: 50%; color: var(--ali-gray); background: #fff; cursor: pointer; transition: all 0.2s; text-decoration: none; }
.ali-del-btn:hover { background: #dc3545; border-color: #dc3545; color: #fff; }

.ali-cart-footer { display: flex; justify-content: space-between; align-items: center; padding: 20px; background: #fff; border-radius: 12px; margin-top: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); flex-wrap: wrap; gap: 15px; }
.ali-footer-left { display: flex; align-items: center; gap: 20px; }
.ali-select-all { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--ali-dark); cursor: pointer; }
.ali-select-all input { width: 18px; height: 18px; accent-color: var(--ali-red); }
.ali-footer-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border: 1px solid var(--ali-border); border-radius: 8px; background: #fff; color: var(--ali-gray); font-size: 13px; font-weight: 500; text-decoration: none; cursor: pointer; transition: all 0.2s; }
.ali-footer-btn:hover { border-color: var(--ali-red); color: var(--ali-red); }
.ali-footer-btn.primary { background: var(--ali-red); border-color: var(--ali-red); color: #fff; }
.ali-footer-btn.primary:hover { background: var(--ali-red-dark); }

.ali-summary { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); position: sticky; top: 20px; overflow: hidden; }
.ali-summary-header { background: linear-gradient(135deg, #333, #555); padding: 18px 20px; color: #fff; }
.ali-summary-header h2 { font-size: 16px; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 10px; }
.ali-summary-body { padding: 20px; }
.ali-sum-row { display: flex; justify-content: space-between; padding: 12px 0; font-size: 14px; border-bottom: 1px dashed #eee; }
.ali-sum-row span:first-child { color: var(--ali-gray); }
.ali-sum-row span:last-child { font-weight: 600; color: var(--ali-dark); }
.ali-sum-total { display: flex; justify-content: space-between; align-items: baseline; padding: 18px 20px; margin: 15px -20px 0; background: linear-gradient(135deg, #fff5f5, #fff); border-top: 2px solid var(--ali-red); }
.ali-sum-total span:first-child { font-size: 14px; font-weight: 600; }
.ali-sum-total-price { font-size: 28px; font-weight: 700; color: var(--ali-red); }
.ali-sum-total-price small { font-size: 14px; font-weight: 400; }

.ali-promo { margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee; }
.ali-promo-label { font-size: 12px; color: var(--ali-gray); margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
.ali-promo-label i { color: var(--ali-orange); }
.ali-promo-input { display: flex; gap: 8px; }
.ali-promo-input input { flex: 1; padding: 10px 12px; border: 1px solid var(--ali-border); border-radius: 6px; font-size: 13px; }
.ali-promo-input input:focus { outline: none; border-color: var(--ali-red); }
.ali-promo-input button { padding: 10px 16px; background: var(--ali-dark); border: none; border-radius: 6px; color: #fff; font-size: 12px; font-weight: 600; cursor: pointer; }

.ali-checkout-btns { padding: 0 20px 20px; }
.ali-checkout-btn { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 16px; border: none; border-radius: 30px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; margin-bottom: 12px; }
.ali-checkout-btn.primary { background: linear-gradient(135deg, var(--ali-red), var(--ali-orange)); color: #fff; box-shadow: 0 4px 15px rgba(255,71,71,0.3); }
.ali-checkout-btn.primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,71,71,0.4); }
.ali-checkout-btn.whatsapp { background: var(--wa-green); color: #fff; box-shadow: 0 4px 15px rgba(37,211,102,0.3); }
.ali-checkout-btn.whatsapp:hover { background: #1da855; transform: translateY(-2px); }

.ali-trust { display: flex; flex-wrap: wrap; gap: 8px; padding: 15px 20px; background: #fafafa; border-top: 1px solid #eee; }
.ali-trust-item { display: flex; align-items: center; gap: 5px; font-size: 11px; color: var(--ali-gray); }
.ali-trust-item i { color: var(--ali-green); }

.ali-empty { text-align: center; padding: 80px 30px; background: #fff; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); max-width: 450px; margin: 40px auto; }
.ali-empty-icon { width: 120px; height: 120px; background: linear-gradient(135deg, #f5f5f5, #eee); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; }
.ali-empty-icon i { font-size: 50px; color: #ccc; }
.ali-empty h2 { font-size: 22px; color: var(--ali-dark); margin: 0 0 10px; }
.ali-empty p { font-size: 14px; color: var(--ali-gray); margin: 0 0 30px; }
.ali-empty-btn { display: inline-flex; align-items: center; gap: 10px; padding: 14px 35px; background: linear-gradient(135deg, var(--ali-red), var(--ali-orange)); color: #fff; border-radius: 30px; text-decoration: none; font-size: 15px; font-weight: 600; box-shadow: 0 4px 15px rgba(255,71,71,0.3); }

/* WhatsApp Modal */
.wa-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; padding: 20px; }
.wa-modal.show { display: flex; }
.wa-modal-content { background: #fff; border-radius: 16px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3); animation: modalSlide 0.3s ease; }
@keyframes modalSlide { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.wa-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 20px; background: var(--wa-green); color: #fff; border-radius: 16px 16px 0 0; }
.wa-modal-header h3 { margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px; }
.wa-modal-header h3 i { font-size: 22px; }
.wa-close { background: none; border: none; color: #fff; font-size: 28px; cursor: pointer; opacity: 0.8; line-height: 1; }
.wa-close:hover { opacity: 1; }
.wa-modal-body { padding: 20px; }
.wa-intro { font-size: 13px; color: var(--ali-gray); margin-bottom: 20px; padding: 12px; background: #e8f5e9; border-radius: 8px; border-left: 4px solid var(--wa-green); }
.wa-intro strong { color: var(--ali-dark); }
.wa-form-group { margin-bottom: 15px; }
.wa-form-group label { display: block; font-size: 13px; font-weight: 600; color: var(--ali-dark); margin-bottom: 6px; }
.wa-form-group label i { color: var(--wa-green); margin-right: 6px; }
.wa-form-group label .required { color: var(--ali-red); }
.wa-input, .wa-select, .wa-textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--ali-border); border-radius: 8px; font-size: 14px; color: var(--ali-dark); transition: border-color 0.2s; }
.wa-input:focus, .wa-select:focus, .wa-textarea:focus { outline: none; border-color: var(--wa-green); box-shadow: 0 0 0 3px rgba(37,211,102,0.1); }
.wa-textarea { resize: vertical; min-height: 70px; }
.wa-form-row { display: flex; gap: 12px; }
.wa-form-row .wa-form-group { flex: 1; }

/* Delivery Fee Display */
.wa-delivery-box { background: linear-gradient(135deg, #fff3e0, #ffe0b2); border: 2px solid #ff9800; border-radius: 10px; padding: 15px; margin: 15px 0; display: flex; align-items: center; justify-content: space-between; transition: all 0.3s; }
.wa-delivery-box.calculated { background: linear-gradient(135deg, #e8f5e9, #c8e6c9); border-color: var(--ali-green); }
.wa-delivery-info { display: flex; align-items: center; gap: 12px; }
.wa-delivery-icon { width: 45px; height: 45px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.wa-delivery-icon i { font-size: 20px; color: #ff9800; }
.wa-delivery-box.calculated .wa-delivery-icon i { color: var(--ali-green); }
.wa-delivery-text { }
.wa-delivery-label { font-size: 12px; color: var(--ali-gray); margin-bottom: 2px; }
.wa-delivery-value { font-size: 14px; font-weight: 600; color: var(--ali-dark); }
.wa-delivery-fee { font-size: 24px; font-weight: 700; color: #e65100; }
.wa-delivery-box.calculated .wa-delivery-fee { color: var(--ali-green); }

.wa-order-summary { background: #f9f9f9; border-radius: 10px; padding: 15px; margin-top: 15px; }
.wa-order-summary h4 { font-size: 14px; font-weight: 600; margin: 0 0 12px; display: flex; align-items: center; gap: 8px; }
.wa-order-summary h4 i { color: var(--ali-red); }
.wa-items-list { max-height: 120px; overflow-y: auto; margin-bottom: 12px; }
.wa-item-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #ddd; font-size: 13px; }
.wa-item-row:last-child { border-bottom: none; }
.wa-item-name { flex: 2; color: var(--ali-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.wa-item-qty { flex: 1; text-align: center; color: var(--ali-gray); }
.wa-item-price { flex: 1; text-align: right; font-weight: 600; color: var(--ali-red); }

.wa-totals { border-top: 2px solid var(--wa-green); padding-top: 12px; margin-top: 8px; }
.wa-total-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; }
.wa-total-row span:first-child { color: var(--ali-gray); }
.wa-total-row span:last-child { font-weight: 600; color: var(--ali-dark); }
.wa-total-row.grand { font-size: 16px; font-weight: 700; padding-top: 10px; border-top: 1px dashed #ddd; margin-top: 6px; }
.wa-total-row.grand span:last-child { color: var(--ali-red); }

.wa-modal-footer { display: flex; gap: 10px; padding: 15px 20px 20px; background: #f9f9f9; border-radius: 0 0 16px 16px; }
.wa-btn { flex: 1; padding: 14px; border: none; border-radius: 30px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
.wa-btn-cancel { background: #fff; color: var(--ali-gray); border: 1px solid var(--ali-border); }
.wa-btn-cancel:hover { background: #f5f5f5; }
.wa-btn-send { background: var(--wa-green); color: #fff; flex: 2; }
.wa-btn-send:hover { background: #1da855; transform: scale(1.02); }
.wa-btn-send:disabled { background: #ccc; cursor: not-allowed; transform: none; }
.wa-btn-send i { font-size: 18px; }

/* Responsive */
@media (max-width: 1024px) { .ali-cart-body { flex-direction: column; } .ali-cart-right { width: 100%; } .ali-summary { position: static; } }
@media (max-width: 768px) {
    .ali-cart-header { display: none; }
    .ali-cart-item { grid-template-columns: 1fr; gap: 12px; padding: 15px; position: relative; }
    .ali-check { position: absolute; top: 15px; left: 15px; }
    .ali-product { padding-left: 30px; }
    .ali-product-img { width: 70px; height: 70px; }
    .ali-price, .ali-total { text-align: left; }
    .ali-quantity { justify-content: flex-start; }
    .ali-actions { position: absolute; top: 15px; right: 15px; }
    .ali-cart-title h1 { font-size: 18px; }
    .ali-footer-left { width: 100%; justify-content: space-between; }
    .wa-form-row { flex-direction: column; gap: 0; }
}
</style>

<div class="ali-cart">
    <div class="ali-bread">
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <i class="fas fa-chevron-right"></i>
        <span>Shopping Cart</span>
    </div>

    <?php if ($cart_id !== '0' && $cart_count >= 1): ?>
    
    <div class="ali-cart-title">
        <h1><i class="fas fa-shopping-cart"></i> Shopping Cart</h1>
        <span class="ali-cart-count"><?php echo $cart_count; ?> Item<?php echo $cart_count > 1 ? 's' : ''; ?></span>
    </div>

    <div class="ali-cart-body">
        <div class="ali-cart-left">
            <div class="ali-items-wrap">
                <div class="ali-cart-header">
                    <div>Select</div><div>Product</div><div>Unit Price</div><div>Quantity</div><div>Subtotal</div><div>Action</div>
                </div>

                <?php
                $sql12 = "SELECT * from cart_item where cart_id='$cart_id' and status='ACTIVE' order by register_date DESC";
                $result12 = $conn->query($sql12);
                $a = 1;
                
                while ($row12 = $result12->fetch_assoc()):
                    $item_id = $row12['item_id'];
                    $product_id = $row12['product_id'];
                    $cart_item_product_quantity = $row12['product_quantity'];
                    $cart_item_price = $row12['price'];

                    $sql53 = "SELECT * from product where product_id='$product_id'";
                    $result53 = $conn->query($sql53);
                    
                    if ($result53 && $result53->num_rows > 0):
                        $row53 = $result53->fetch_assoc();
                        $product_name = $row53['product_name'];
                        $product_unit = $row53['product_unit'];
                        $category_id = $row53['category_id'];
                        $product_minimum_order = $row53['product_minimum_order'] ?? 1;

                        $product_price = 0;
                        $sql_price = "SELECT * from product_price where product_id='$product_id'";
                        $result_price = $conn->query($sql_price);
                        if ($result_price && $row_price = $result_price->fetch_assoc()) {
                            $product_price = $row_price['price'];
                        }

                        $category_name = '';
                        $sql_cat = "SELECT * from product_category where category_id='$category_id'";
                        $result_cat = $conn->query($sql_cat);
                        if ($result_cat && $row_cat = $result_cat->fetch_assoc()) {
                            $category_name = $row_cat['category_name'];
                        }

                        $picture = 'no-image.png';
                        $sql_img = "SELECT * from product_picture where product_id='$product_id' order by register_date DESC limit 1";
                        $result_img = $conn->query($sql_img);
                        if ($result_img && $row_img = $result_img->fetch_assoc()) {
                            $picture = $row_img['picture'];
                        }
                ?>
                
                <div class="ali-cart-item" id="cartRow_<?php echo $a; ?>">
                    <input type="hidden" id="cartItemId_<?php echo $a; ?>" value="<?php echo $item_id; ?>">
                    <input type="hidden" id="cartPrice_<?php echo $a; ?>" value="<?php echo $product_price; ?>">
                    <input type="hidden" id="cartMinQty_<?php echo $a; ?>" value="<?php echo $product_minimum_order; ?>">
                    <input type="hidden" id="cartName_<?php echo $a; ?>" value="<?php echo htmlspecialchars($product_name); ?>">
                    <input type="hidden" id="cartUnit_<?php echo $a; ?>" value="<?php echo htmlspecialchars($product_unit); ?>">
                    
                    <div class="ali-check">
                        <input type="checkbox" class="item-checkbox" data-index="<?php echo $a; ?>" checked>
                    </div>
                    
                    <div class="ali-product">
                        <a href="index.php?product-detail&product=<?php echo $product_id; ?>" class="ali-product-img">
                            <img src="uploads/<?php echo htmlspecialchars($picture); ?>" alt="">
                        </a>
                        <div class="ali-product-info">
                            <a href="index.php?product-detail&product=<?php echo $product_id; ?>" class="ali-product-name"><?php echo htmlspecialchars($product_name); ?></a>
                            <div class="ali-product-meta">
                                <span class="ali-product-tag"><i class="fas fa-tag"></i><?php echo htmlspecialchars($category_name); ?></span>
                                <span class="ali-product-tag"><i class="fas fa-balance-scale"></i><?php echo htmlspecialchars($product_unit); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ali-price">
                        <div class="ali-price-current"><?php echo number_format($product_price, 0); ?> <small>RWF</small></div>
                        <div class="ali-price-unit">per <?php echo htmlspecialchars($product_unit); ?></div>
                    </div>
                    
                    <div class="ali-quantity">
                        <div class="ali-qty-box">
                            <button type="button" class="ali-qty-btn" onclick="changeQty(<?php echo $a; ?>, -1)"><i class="fas fa-minus"></i></button>
                            <input type="text" class="ali-qty-input" id="cartQty_<?php echo $a; ?>" value="<?php echo $cart_item_product_quantity; ?>" onchange="updateItem(<?php echo $a; ?>)" onblur="updateItem(<?php echo $a; ?>)">
                            <button type="button" class="ali-qty-btn" onclick="changeQty(<?php echo $a; ?>, 1)"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    
                    <div class="ali-total">
                        <div class="ali-total-price" id="cartTotal_<?php echo $a; ?>"><?php echo number_format($cart_item_price, 0); ?> <small>RWF</small></div>
                    </div>
                    
                    <div class="ali-actions">
                        <a href="#/" class="ali-del-btn" onclick="remove_from_cart('<?php echo $item_id; ?>');"><i class="far fa-trash-alt"></i></a>
                    </div>
                </div>
                
                <?php endif; $a++; endwhile; $totalItems = $a - 1; ?>
            </div>

            <div class="ali-cart-footer">
                <div class="ali-footer-left">
                    <label class="ali-select-all">
                        <input type="checkbox" id="selectAll" checked onchange="toggleSelectAll()">
                        <span>Select All</span>
                    </label>
                    <a href="#/" class="ali-footer-btn" onclick="clear_cart('<?php echo $cart_id; ?>');"><i class="far fa-trash-alt"></i> Clear Cart</a>
                </div>
                <a href="index.php?shop" class="ali-footer-btn primary"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
            </div>
        </div>

        <div class="ali-cart-right">
            <div class="ali-summary">
                <div class="ali-summary-header"><h2><i class="fas fa-receipt"></i> Order Summary</h2></div>
                <div class="ali-summary-body">
                    <div class="ali-sum-row"><span>Items (<span id="itemCount"><?php echo $cart_count; ?></span>)</span><span id="cartSubtotal"><?php echo number_format($sum_cart_items, 0); ?> RWF</span></div>
                    <div class="ali-sum-row"><span>Shipping</span><span style="color: var(--ali-green);">Calculated at checkout</span></div>
                    <div class="ali-sum-row"><span>Discount</span><span style="color: var(--ali-red);">-0 RWF</span></div>
                    <div class="ali-sum-total"><span>Total</span><span class="ali-sum-total-price" id="cartGrandTotal"><?php echo number_format($sum_cart_items, 0); ?> <small>RWF</small></span></div>
                    <div class="ali-promo">
                        <div class="ali-promo-label"><i class="fas fa-ticket-alt"></i> Have a promo code?</div>
                        <div class="ali-promo-input"><input type="text" placeholder="Enter code"><button type="button">Apply</button></div>
                    </div>
                </div>
                <div class="ali-checkout-btns">
                    <?php if(isset($_SESSION['GBDELIVERING_CUSTOMER_USER_2021'])): ?>
                        <a href="index.php?checkout" class="ali-checkout-btn primary"><i class="fas fa-lock"></i> Proceed to Checkout</a>
                    <?php else: ?>
                        <a href="index.php?sign-in" class="ali-checkout-btn primary"><i class="fas fa-lock"></i> Sign in to Checkout</a>
                    <?php endif; ?>
                    <button type="button" class="ali-checkout-btn whatsapp" onclick="openWhatsAppModal()"><i class="fab fa-whatsapp"></i> Order via WhatsApp</button>
                </div>
                <div class="ali-trust">
                    <div class="ali-trust-item"><i class="fas fa-shield-alt"></i> Secure Payment</div>
                    <div class="ali-trust-item"><i class="fas fa-truck"></i> Fast Delivery</div>
                    <div class="ali-trust-item"><i class="fas fa-undo"></i> Easy Returns</div>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <div class="ali-empty">
        <div class="ali-empty-icon"><i class="fas fa-shopping-cart"></i></div>
        <h2>Your Cart is Empty</h2>
        <p>Looks like you haven't added anything to your cart yet.</p>
        <a href="index.php?shop" class="ali-empty-btn"><i class="fas fa-store"></i> Start Shopping</a>
    </div>
    <?php endif; ?>
</div>

<!-- WhatsApp Order Modal -->
<div class="wa-modal" id="waModal">
    <div class="wa-modal-content">
        <div class="wa-modal-header">
            <h3><i class="fab fa-whatsapp"></i> Order via WhatsApp</h3>
            <button type="button" class="wa-close" onclick="closeWhatsAppModal()">&times;</button>
        </div>
        
        <div class="wa-modal-body">
            <div class="wa-intro">
                <strong>🚀 Quick order without account!</strong> Fill in your delivery details. Delivery fee is calculated automatically based on your location.
            </div>

            <div class="wa-form-group">
                <label><i class="fas fa-user"></i> Full Name <span class="required">*</span></label>
                <input type="text" class="wa-input" id="waName" placeholder="Enter your full name">
            </div>

            <div class="wa-form-group">
                <label><i class="fas fa-phone"></i> Phone Number <span class="required">*</span></label>
                <input type="tel" class="wa-input" id="waPhone" placeholder="e.g. 0788123456">
            </div>

            <div class="wa-form-group">
                <label><i class="fas fa-map-marker-alt"></i> Province <span class="required">*</span></label>
                <select class="wa-select" id="waProvince" onchange="loadDistricts()">
                    <option value="">Select Province</option>
                    <?php foreach ($provinces as $prov): ?>
                        <option value="<?php echo htmlspecialchars($prov); ?>"><?php echo htmlspecialchars($prov); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="wa-form-row">
                <div class="wa-form-group">
                    <label><i class="fas fa-city"></i> District <span class="required">*</span></label>
                    <select class="wa-select" id="waDistrict" onchange="loadSectors()">
                        <option value="">Select District</option>
                    </select>
                </div>
                <div class="wa-form-group">
                    <label><i class="fas fa-map-pin"></i> Sector <span class="required">*</span></label>
                    <select class="wa-select" id="waSector" onchange="updateDeliveryFee()">
                        <option value="">Select Sector</option>
                    </select>
                </div>
            </div>

            <!-- Delivery Fee Display -->
            <div class="wa-delivery-box" id="waDeliveryBox">
                <div class="wa-delivery-info">
                    <div class="wa-delivery-icon"><i class="fas fa-truck"></i></div>
                    <div class="wa-delivery-text">
                        <div class="wa-delivery-label">Delivery Fee</div>
                        <div class="wa-delivery-value" id="waDeliveryText">Select your sector to see fee</div>
                    </div>
                </div>
                <div class="wa-delivery-fee" id="waDeliveryFee">-- RWF</div>
            </div>

            <div class="wa-form-group">
                <label><i class="fas fa-home"></i> Street / House Details <span class="required">*</span></label>
                <textarea class="wa-textarea" id="waAddress" placeholder="Street name, house number, landmarks, etc."></textarea>
            </div>

            <div class="wa-form-group">
                <label><i class="fas fa-comment"></i> Additional Notes</label>
                <textarea class="wa-textarea" id="waNotes" placeholder="Any special instructions (optional)"></textarea>
            </div>

            <div class="wa-order-summary">
                <h4><i class="fas fa-shopping-bag"></i> Your Order</h4>
                <div class="wa-items-list" id="waItemsList"></div>
                <div class="wa-totals">
                    <div class="wa-total-row"><span>Subtotal:</span><span id="waSubtotal">0 RWF</span></div>
                    <div class="wa-total-row"><span>Delivery:</span><span id="waDeliveryTotal">0 RWF</span></div>
                    <div class="wa-total-row grand"><span>Grand Total:</span><span id="waGrandTotal">0 RWF</span></div>
                </div>
            </div>
        </div>

        <div class="wa-modal-footer">
            <button type="button" class="wa-btn wa-btn-cancel" onclick="closeWhatsAppModal()">Cancel</button>
            <button type="button" class="wa-btn wa-btn-send" id="waSendBtn" onclick="sendWhatsAppOrder()"><i class="fab fa-whatsapp"></i> Send Order</button>
        </div>
    </div>
</div>

<script>
// Cart variables
var totalItems = <?php echo $totalItems ?? 0; ?>;
var cartSubtotal = <?php echo $sum_cart_items; ?>;
var currentDeliveryFee = 0;
var currentCartId = '<?php echo $cart_id; ?>';

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    recalculateTotals();
    document.querySelectorAll('.item-checkbox').forEach(function(cb) {
        cb.addEventListener('change', recalculateTotals);
    });
});

// Quantity functions
function changeQty(index, delta) {
    var input = document.getElementById('cartQty_' + index);
    var minQty = parseFloat(document.getElementById('cartMinQty_' + index).value) || 1;
    var currentQty = parseFloat(input.value) || minQty;
    var step = minQty < 1 ? 0.1 : 1;
    var newQty = Math.round((currentQty + (delta * step)) * 10) / 10;
    if (newQty < minQty) newQty = minQty;
    input.value = newQty;
    updateItem(index);
}

function updateItem(index) {
    var itemId = document.getElementById('cartItemId_' + index).value;
    var input = document.getElementById('cartQty_' + index);
    var price = parseFloat(document.getElementById('cartPrice_' + index).value) || 0;
    var minQty = parseFloat(document.getElementById('cartMinQty_' + index).value) || 1;
    var qty = parseFloat(input.value) || minQty;
    if (qty < minQty) { qty = minQty; input.value = qty; }
    var itemTotal = price * qty;
    document.getElementById('cartTotal_' + index).innerHTML = formatNumber(itemTotal) + ' <small>RWF</small>';
    recalculateTotals();
    saveCartItem(itemId, qty, itemTotal);
}

function saveCartItem(itemId, qty, totalPrice) {
    $.ajax({ type: 'POST', url: 'action/insert.php', data: { action: 'UPDATE_CART_ITEM', item_id: itemId, product_quantity: qty, price: totalPrice } });
}

function recalculateTotals() {
    var total = 0, checkedCount = 0;
    for (var i = 1; i <= totalItems; i++) {
        var row = document.getElementById('cartRow_' + i);
        if (!row) continue;
        var checkbox = row.querySelector('.item-checkbox');
        if (checkbox && checkbox.checked) {
            var qty = parseFloat(document.getElementById('cartQty_' + i).value) || 0;
            var price = parseFloat(document.getElementById('cartPrice_' + i).value) || 0;
            total += qty * price;
            checkedCount++;
        }
    }
    cartSubtotal = total;
    document.getElementById('cartSubtotal').textContent = formatNumber(total) + ' RWF';
    document.getElementById('cartGrandTotal').innerHTML = formatNumber(total) + ' <small>RWF</small>';
    document.getElementById('itemCount').textContent = checkedCount;
}

function toggleSelectAll() {
    var selectAll = document.getElementById('selectAll').checked;
    document.querySelectorAll('.item-checkbox').forEach(function(cb) { cb.checked = selectAll; });
    recalculateTotals();
}

function formatNumber(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// ========== WhatsApp Modal Functions ==========

function openWhatsAppModal() {
    document.getElementById('waModal').classList.add('show');
    document.body.style.overflow = 'hidden';
    populateOrderSummary();
    updateWaTotals();
}

function closeWhatsAppModal() {
    document.getElementById('waModal').classList.remove('show');
    document.body.style.overflow = 'auto';
}

// Close on backdrop click
document.getElementById('waModal').addEventListener('click', function(e) {
    if (e.target === this) closeWhatsAppModal();
});

function populateOrderSummary() {
    var itemsList = document.getElementById('waItemsList');
    var html = '';
    var total = 0;
    
    for (var i = 1; i <= totalItems; i++) {
        var row = document.getElementById('cartRow_' + i);
        if (!row) continue;
        var checkbox = row.querySelector('.item-checkbox');
        if (checkbox && checkbox.checked) {
            var name = document.getElementById('cartName_' + i).value;
            var qty = parseFloat(document.getElementById('cartQty_' + i).value) || 0;
            var unit = document.getElementById('cartUnit_' + i).value;
            var price = parseFloat(document.getElementById('cartPrice_' + i).value) || 0;
            var itemTotal = qty * price;
            total += itemTotal;
            
            html += '<div class="wa-item-row">';
            html += '<span class="wa-item-name">' + name + '</span>';
            html += '<span class="wa-item-qty">' + qty + ' ' + unit + '</span>';
            html += '<span class="wa-item-price">' + formatNumber(itemTotal) + ' RWF</span>';
            html += '</div>';
        }
    }
    
    itemsList.innerHTML = html || '<p style="color:#888;text-align:center;">No items selected</p>';
    cartSubtotal = total;
}

function loadDistricts() {
    var province = document.getElementById('waProvince').value;
    var districtSelect = document.getElementById('waDistrict');
    var sectorSelect = document.getElementById('waSector');
    
    districtSelect.innerHTML = '<option value="">Loading...</option>';
    sectorSelect.innerHTML = '<option value="">Select Sector</option>';
    resetDeliveryFee();
    
    if (!province) {
        districtSelect.innerHTML = '<option value="">Select District</option>';
        return;
    }
    
    $.ajax({
        type: 'POST',
        url: 'includes/get_districts.php',
        data: { province: province },
        success: function(response) {
            districtSelect.innerHTML = '<option value="">Select District</option>' + response;
        },
        error: function() {
            districtSelect.innerHTML = '<option value="">Error loading</option>';
        }
    });
}

function loadSectors() {
    var province = document.getElementById('waProvince').value;
    var district = document.getElementById('waDistrict').value;
    var sectorSelect = document.getElementById('waSector');
    
    sectorSelect.innerHTML = '<option value="">Loading...</option>';
    resetDeliveryFee();
    
    if (!district) {
        sectorSelect.innerHTML = '<option value="">Select Sector</option>';
        return;
    }
    
    $.ajax({
        type: 'POST',
        url: 'includes/get_sectors.php',
        data: { province: province, district: district },
        success: function(response) {
            sectorSelect.innerHTML = '<option value="">Select Sector</option>' + response;
        },
        error: function() {
            sectorSelect.innerHTML = '<option value="">Error loading</option>';
        }
    });
}

function updateDeliveryFee() {
    var sectorSelect = document.getElementById('waSector');
    var selectedOption = sectorSelect.options[sectorSelect.selectedIndex];
    
    if (selectedOption && selectedOption.value) {
        var fee = parseInt(selectedOption.getAttribute('data-fee')) || 0;
        currentDeliveryFee = fee;
        
        document.getElementById('waDeliveryBox').classList.add('calculated');
        document.getElementById('waDeliveryText').textContent = 'Delivery to ' + selectedOption.value;
        document.getElementById('waDeliveryFee').textContent = formatNumber(fee) + ' RWF';
    } else {
        resetDeliveryFee();
    }
    
    updateWaTotals();
}

function resetDeliveryFee() {
    currentDeliveryFee = 0;
    document.getElementById('waDeliveryBox').classList.remove('calculated');
    document.getElementById('waDeliveryText').textContent = 'Select your sector to see fee';
    document.getElementById('waDeliveryFee').textContent = '-- RWF';
    updateWaTotals();
}

function updateWaTotals() {
    var grandTotal = cartSubtotal + currentDeliveryFee;
    document.getElementById('waSubtotal').textContent = formatNumber(cartSubtotal) + ' RWF';
    document.getElementById('waDeliveryTotal').textContent = formatNumber(currentDeliveryFee) + ' RWF';
    document.getElementById('waGrandTotal').textContent = formatNumber(grandTotal) + ' RWF';
}

// ========== SEND WHATSAPP ORDER & CLEAR CART ==========

function sendWhatsAppOrder() {
    // Get form values
    var name = document.getElementById('waName').value.trim();
    var phone = document.getElementById('waPhone').value.trim();
    var province = document.getElementById('waProvince').value;
    var district = document.getElementById('waDistrict').value;
    var sector = document.getElementById('waSector').value;
    var address = document.getElementById('waAddress').value.trim();
    var notes = document.getElementById('waNotes').value.trim();
    
    // Validation
    if (!name) { alert('Please enter your name'); document.getElementById('waName').focus(); return; }
    if (!phone) { alert('Please enter your phone number'); document.getElementById('waPhone').focus(); return; }
    if (!province) { alert('Please select your province'); return; }
    if (!district) { alert('Please select your district'); return; }
    if (!sector) { alert('Please select your sector'); return; }
    if (!address) { alert('Please enter your street/house details'); document.getElementById('waAddress').focus(); return; }
    
    // Collect order items
    var orderItems = [];
    var total = 0;
    
    for (var i = 1; i <= totalItems; i++) {
        var row = document.getElementById('cartRow_' + i);
        if (!row) continue;
        var checkbox = row.querySelector('.item-checkbox');
        if (checkbox && checkbox.checked) {
            var itemName = document.getElementById('cartName_' + i).value;
            var qty = parseFloat(document.getElementById('cartQty_' + i).value) || 0;
            var unit = document.getElementById('cartUnit_' + i).value;
            var price = parseFloat(document.getElementById('cartPrice_' + i).value) || 0;
            var itemTotal = qty * price;
            total += itemTotal;
            orderItems.push({ name: itemName, qty: qty, unit: unit, price: itemTotal });
        }
    }
    
    if (orderItems.length === 0) { alert('Please select at least one item'); return; }
    
    var grandTotal = total + currentDeliveryFee;
    
    // Build WhatsApp message
    var msg = "🛒 *NEW ORDER - GBDELIVERING*\n";
    msg += "━━━━━━━━━━━━━━━━━━━━━\n\n";
    msg += "👤 *CUSTOMER DETAILS*\n";
    msg += "• Name: " + name + "\n";
    msg += "• Phone: " + phone + "\n\n";
    msg += "📍 *DELIVERY ADDRESS*\n";
    msg += "• Province: " + province + "\n";
    msg += "• District: " + district + "\n";
    msg += "• Sector: " + sector + "\n";
    msg += "• Address: " + address + "\n";
    if (notes) msg += "• Notes: " + notes + "\n";
    msg += "\n📦 *ORDER ITEMS*\n";
    msg += "━━━━━━━━━━━━━━━━━━━━━\n";
    
    orderItems.forEach(function(item, index) {
        msg += (index + 1) + ". " + item.name + "\n";
        msg += "   📊 Qty: " + item.qty + " " + item.unit + "\n";
        msg += "   💰 Price: " + formatNumber(item.price) + " RWF\n\n";
    });
    
    msg += "━━━━━━━━━━━━━━━━━━━━━\n";
    msg += "💵 *ORDER SUMMARY*\n";
    msg += "• Subtotal: " + formatNumber(total) + " RWF\n";
    msg += "• Delivery Fee (" + sector + "): " + formatNumber(currentDeliveryFee) + " RWF\n";
    msg += "• *GRAND TOTAL: " + formatNumber(grandTotal) + " RWF*\n\n";
    msg += "✅ Please confirm this order.\n";
    msg += "Thank you for choosing GBDelivering! 🙏";
    
    // WhatsApp number
    var waNumber = "250783654454";
    var waUrl = "https://wa.me/" + waNumber + "?text=" + encodeURIComponent(msg);
    
    // Disable button
    var sendBtn = document.getElementById('waSendBtn');
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    
    // Clear cart via AJAX first, then open WhatsApp
    $.ajax({
        type: 'POST',
        url: 'includes/clear_cart.php',
        data: { cart_id: currentCartId },
        dataType: 'json',
        timeout: 10000,
        success: function(response) {
            console.log('Cart cleared:', response);
            openWhatsAppAndRedirect(waUrl);
        },
        error: function(xhr, status, error) {
            console.log('Clear cart error:', status, error);
            console.log('Response:', xhr.responseText);
            // Still proceed with WhatsApp even if clear fails
            openWhatsAppAndRedirect(waUrl);
        }
    });
}

function openWhatsAppAndRedirect(waUrl) {
    // Open WhatsApp in new tab
    window.open(waUrl, '_blank');
    
    // Close modal
    closeWhatsAppModal();
    
    // Show success message and redirect
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Order Sent!',
            text: 'Your order has been sent to WhatsApp. We will contact you shortly.',
            timer: 2500,
            showConfirmButton: false,
            allowOutsideClick: false
        }).then(function() {
            window.location.href = 'index.php';
        });
    } else {
        // Fallback if SweetAlert not available
        alert('Order sent successfully! Your cart has been cleared.');
        window.location.href = 'index.php';
    }
}
</script>