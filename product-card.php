<?php
/**
 * Product Card Component
 * Usage: Set $product variable then include this file
 * 
 * Example:
 * $product = ['product_id' => '123', 'product_name' => 'Apple', 'product_unit' => 'Kg'];
 * include 'includes/product_card.php';
 */

if (!isset($product) || !isset($conn)) return;

// Get price
$pc_price = 0;
$pc_price_q = $conn->query("SELECT price FROM product_price WHERE product_id='" . $conn->real_escape_string($product['product_id']) . "' LIMIT 1");
if ($pc_price_q && $pc_price_r = $pc_price_q->fetch_assoc()) {
    $pc_price = floatval($pc_price_r['price']);
}

// Get image
$pc_image = 'no-image.png';
$pc_img_q = $conn->query("SELECT picture FROM product_picture WHERE product_id='" . $conn->real_escape_string($product['product_id']) . "' ORDER BY register_date DESC LIMIT 1");
if ($pc_img_q && $pc_img_r = $pc_img_q->fetch_assoc()) {
    $pc_image = $pc_img_r['picture'];
}

// Get category
$pc_category = '';
if (isset($product['category_id'])) {
    $pc_cat_q = $conn->query("SELECT category_name FROM product_category WHERE category_id='" . $conn->real_escape_string($product['category_id']) . "' LIMIT 1");
    if ($pc_cat_q && $pc_cat_r = $pc_cat_q->fetch_assoc()) {
        $pc_category = $pc_cat_r['category_name'];
    }
}
?>

<div class="gb-product-card">
    <a href="index.php?product-detail&product=<?php echo urlencode($product['product_id']); ?>" class="gb-pc-image">
        <img src="uploads/<?php echo htmlspecialchars($pc_image); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" loading="lazy">
        <?php if (isset($product['is_new']) && $product['is_new']): ?>
        <span class="gb-pc-badge new">NEW</span>
        <?php endif; ?>
        <?php if (isset($product['discount']) && $product['discount'] > 0): ?>
        <span class="gb-pc-badge sale">-<?php echo $product['discount']; ?>%</span>
        <?php endif; ?>
    </a>
    
    <div class="gb-pc-info">
        <?php if ($pc_category): ?>
        <span class="gb-pc-category"><?php echo htmlspecialchars($pc_category); ?></span>
        <?php endif; ?>
        
        <a href="index.php?product-detail&product=<?php echo urlencode($product['product_id']); ?>" class="gb-pc-name">
            <?php echo htmlspecialchars($product['product_name']); ?>
        </a>
        
        <div class="gb-pc-price">
            <?php echo formatPrice($pc_price); ?>
        </div>
        
        <div class="gb-pc-unit">
            per <?php echo htmlspecialchars($product['product_unit'] ?? 'unit'); ?>
        </div>
        
        <button type="button" class="gb-pc-cart-btn" onclick="addToCart('<?php echo $product['product_id']; ?>')">
            <i class="fas fa-cart-plus"></i> Add to Cart
        </button>
    </div>
</div>

<style>
.gb-product-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.3s;
}

.gb-product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
}

.gb-pc-image {
    display: block;
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
    background: #f5f5f5;
}

.gb-pc-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s;
}

.gb-product-card:hover .gb-pc-image img {
    transform: scale(1.08);
}

.gb-pc-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
}

.gb-pc-badge.new { background: #00a650; }
.gb-pc-badge.sale { background: #ff4747; }

.gb-pc-info {
    padding: 16px;
}

.gb-pc-category {
    display: inline-block;
    font-size: 11px;
    color: #999;
    background: #f5f5f5;
    padding: 3px 8px;
    border-radius: 4px;
    margin-bottom: 8px;
}

.gb-pc-name {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #333;
    line-height: 1.4;
    height: 40px;
    overflow: hidden;
    margin-bottom: 10px;
    text-decoration: none;
}

.gb-pc-name:hover {
    color: #ff6000;
}

.gb-pc-price {
    font-size: 20px;
    font-weight: 700;
    color: #ff6000;
}

.gb-pc-unit {
    font-size: 12px;
    color: #999;
    margin-top: 4px;
}

.gb-pc-cart-btn {
    width: 100%;
    margin-top: 14px;
    padding: 12px;
    background: linear-gradient(135deg, #ff6000, #ff8533);
    border: none;
    border-radius: 8px;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.gb-pc-cart-btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.gb-pc-cart-btn i {
    font-size: 14px;
}
</style>