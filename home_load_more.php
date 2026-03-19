<?php
/**
 * Home Load More - AJAX Handler for Infinite Scroll on Homepage
 * AliExpress Style - Fully Responsive
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 0);
error_reporting(E_ALL);

// Include database connection
$possible_paths = [
    '../on/on.php',
    '../../on/on.php',
    '../db.php',
    'db.php',
];

$db_included = false;
foreach ($possible_paths as $path) {
    $full_path = __DIR__ . '/' . $path;
    if (file_exists($full_path)) {
        include($full_path);
        $db_included = true;
        break;
    }
}

if (!$db_included) {
    $root_paths = [
        dirname(__DIR__) . '/on/on.php',
        dirname(dirname(__DIR__)) . '/on/on.php',
    ];
    foreach ($root_paths as $path) {
        if (file_exists($path)) {
            include($path);
            $db_included = true;
            break;
        }
    }
}

if (!$db_included || !isset($conn) || !$conn) {
    echo "";
    exit;
}

// Get parameters
$offset = isset($_POST['offset']) ? max(0, (int)$_POST['offset']) : 0;
$limit = isset($_POST['limit']) ? max(1, min(100, (int)$_POST['limit'])) : 24;
$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';

$login_status = isset($_SESSION['GBDELIVERING_CUSTOMER_USER_2021']) || isset($_COOKIE['GBDELIVERING_CUSTOMER_USER_2021']);

// Query products
$sql = "SELECT p.product_id, p.product_name, p.product_unit, p.product_rating, pr.price
        FROM product p 
        LEFT JOIN product_price pr ON pr.product_id = p.product_id 
        WHERE pr.product_id IS NOT NULL
        ORDER BY p.register_date DESC 
        LIMIT $offset, $limit";

$res = $conn->query($sql);

if (!$res || $res->num_rows === 0) {
    echo "";
    exit;
}

$idx = $offset;
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
    $uid = 'h' . $pid . '_' . $idx;
?>

<div class="ae-card">
    <div class="ae-card-img">
        <a href="index.php?product-detail&product=<?php echo $pid; ?>">
            <img src="uploads/<?php echo $img; ?>" alt="<?php echo $pname; ?>" loading="lazy">
        </a>
        <div class="ae-card-actions">
            <a href="#quick-look" data-toggle="modal" data-product-id="<?php echo $pid; ?>" class="ae-action-btn">
                <i class="fas fa-eye"></i>
            </a>
            <?php if ($login_status): ?>
            <button onclick="add_to_wishlist('<?php echo $pid; ?>','<?php echo $customer_id; ?>')" class="ae-action-btn">
                <i class="far fa-heart"></i>
            </button>
            <?php else: ?>
            <a href="index.php?sign-in" class="ae-action-btn"><i class="far fa-heart"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <div class="ae-card-body">
        <a href="index.php?product-detail&product=<?php echo $pid; ?>" class="ae-card-name">
            <?php echo mb_strimwidth($pname, 0, 32, '...'); ?>
        </a>
        <div class="ae-card-rating">
            <div class="ae-stars">
                <?php
                echo str_repeat('<i class="fas fa-star"></i>', $full);
                if ($half) echo '<i class="fas fa-star-half-alt"></i>';
                echo str_repeat('<i class="far fa-star"></i>', $empty);
                ?>
            </div>
            <span class="ae-sold"><?php echo rand(50, 500); ?> sold</span>
        </div>
        <div class="ae-card-price">
            <span class="ae-price"><?php echo number_format($price, 0); ?></span>
            <span class="ae-unit">RWF/<?php echo $punit; ?></span>
        </div>
        <div class="ae-card-actions">
            <div class="ae-qty-row">
                <button class="ae-qty-btn" onclick="qtyMinus('<?php echo $uid; ?>')"><i class="fas fa-minus"></i></button>
                <input type="text" id="qty_<?php echo $uid; ?>" class="ae-qty-input" placeholder="Qty" inputmode="decimal">
                <button class="ae-qty-btn" onclick="qtyPlus('<?php echo $uid; ?>')"><i class="fas fa-plus"></i></button>
            </div>
            <button class="ae-add-btn" onclick="addToCart('<?php echo $pid; ?>','<?php echo $customer_id; ?>','<?php echo $price; ?>','<?php echo $uid; ?>')">
                <i class="fas fa-cart-plus"></i> Add
            </button>
        </div>
    </div>
</div>

<?php 
    $idx++;
endwhile;
?>