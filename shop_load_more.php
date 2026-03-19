<?php
/**
 * Shop Load More - AJAX Handler for Shop Page Infinite Scroll
 * AliExpress Style
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 0);
error_reporting(E_ALL);

// Include database
$paths = ['../on/on.php', '../../on/on.php', '../db.php'];
$included = false;
foreach ($paths as $p) {
    if (file_exists(__DIR__ . '/' . $p)) { include(__DIR__ . '/' . $p); $included = true; break; }
}
if (!$included) {
    foreach ([dirname(__DIR__).'/on/on.php', dirname(dirname(__DIR__)).'/on/on.php'] as $p) {
        if (file_exists($p)) { include($p); $included = true; break; }
    }
}
if (!$included || !isset($conn)) { echo ""; exit; }

// Parameters
$page = isset($_POST['page']) ? max(1, (int)$_POST['page']) : 1;
$per_page = isset($_POST['per_page']) ? max(1, min(100, (int)$_POST['per_page'])) : 24;
$sortby = isset($_POST['sortby']) ? $_POST['sortby'] : 'RAND()';
$condition = isset($_POST['condition']) ? $_POST['condition'] : '';
$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';
$idx = isset($_POST['index']) ? (int)$_POST['index'] : 0;

$allowed = ['RAND()','p.register_date DESC','p.register_date ASC','CAST(pr.price AS SIGNED INTEGER) ASC','CAST(pr.price AS SIGNED INTEGER) DESC','CAST(p.product_rating AS SIGNED INTEGER) DESC'];
if (!in_array($sortby, $allowed)) $sortby = 'RAND()';

$login_status = isset($_SESSION['GBDELIVERING_CUSTOMER_USER_2021']) || isset($_COOKIE['GBDELIVERING_CUSTOMER_USER_2021']);
$offset = ($page - 1) * $per_page;

$sql = "SELECT p.product_id, p.product_name, p.product_unit, p.product_rating, pr.price 
        FROM product p 
        JOIN product_price pr ON pr.product_id = p.product_id 
        $condition 
        ORDER BY $sortby 
        LIMIT $offset, $per_page";

$res = $conn->query($sql);
if (!$res || $res->num_rows === 0) { echo "no_more"; exit; }

while ($row = $res->fetch_assoc()):
    $pid = $row['product_id'];
    $pname = htmlspecialchars($row['product_name'] ?? '');
    $punit = htmlspecialchars($row['product_unit'] ?? 'unit');
    $price = $row['price'] ?? 0;
    $rating = (float)($row['product_rating'] ?? 0);

    $img_q = $conn->query("SELECT picture FROM product_picture WHERE product_id='$pid' ORDER BY register_date DESC LIMIT 1");
    $img = ($img_q && $img_q->num_rows > 0) ? $img_q->fetch_assoc()['picture'] : 'no-image.png';

    $full = floor($rating);
    $half = ($rating - $full) >= 0.5;
    $empty = 5 - $full - ($half ? 1 : 0);
    $uid = 'p' . $pid . '_' . $idx;
?>

<div class="ae-card">
    <div class="ae-card-img">
        <a href="index.php?product-detail&product=<?php echo $pid; ?>">
            <img src="uploads/<?php echo $img; ?>" alt="<?php echo $pname; ?>" loading="lazy">
        </a>
        <div class="ae-card-actions">
            <a href="#quick-look" data-toggle="modal" data-product-id="<?php echo $pid; ?>" class="action-btn"><i class="fas fa-eye"></i></a>
            <?php if ($login_status): ?>
            <button onclick="add_to_wishlist('<?php echo $pid; ?>','<?php echo $customer_id; ?>')" class="action-btn"><i class="far fa-heart"></i></button>
            <?php else: ?>
            <a href="index.php?sign-in" class="action-btn"><i class="far fa-heart"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <div class="product-body">
        <a href="index.php?product-detail&product=<?php echo $pid; ?>" class="product-name"><?php echo mb_strimwidth($pname, 0, 45, '...'); ?></a>
        <div class="product-rating">
            <div class="stars"><?php echo str_repeat('<i class="fas fa-star"></i>', $full); if ($half) echo '<i class="fas fa-star-half-alt"></i>'; echo str_repeat('<i class="far fa-star"></i>', $empty); ?></div>
            <span class="sold-count"><?php echo rand(50, 500); ?> sold</span>
        </div>
        <div class="product-price">
            <span class="price-main"><?php echo number_format($price, 0); ?></span>
            <span class="price-unit">RWF/<?php echo $punit; ?></span>
        </div>
        <div class="qty-wrapper">
            <button class="qty-btn" onclick="decreaseQty('<?php echo $uid; ?>')"><i class="fas fa-minus"></i></button>
            <input type="text" id="qty_<?php echo $uid; ?>" class="qty-input" placeholder="Qty" inputmode="decimal">
            <button class="qty-btn" onclick="increaseQty('<?php echo $uid; ?>')"><i class="fas fa-plus"></i></button>
        </div>
        <button class="add-cart-btn" onclick="shopAddToCart('<?php echo $pid; ?>','<?php echo $customer_id; ?>','<?php echo $price; ?>','<?php echo $uid; ?>')">
            <i class="fas fa-cart-plus"></i> Add to Cart
        </button>
    </div>
</div>

<?php $idx++; endwhile; ?>