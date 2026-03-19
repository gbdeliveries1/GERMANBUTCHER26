<?php
/**
 * Search Load More - AJAX Handler
 * Place this file in: includes/search_load_more.php
 */

// Start session
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
// Try multiple paths to find the connection file
$db_paths = array(
    __DIR__ . '/../on/on.php',
    __DIR__ . '/../../on/on.php', 
    dirname(__DIR__) . '/on/on.php',
    __DIR__ . '/../db.php',
    __DIR__ . '/db.php'
);

$connected = false;
foreach($db_paths as $path) {
    if(file_exists($path)) {
        include_once($path);
        $connected = true;
        break;
    }
}

// Check connection
if(!$connected || !isset($conn)) {
    echo 'no_more';
    exit;
}

// Get parameters
$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$search_escaped = mysqli_real_escape_string($conn, $search);
$sortby = isset($_POST['sortby']) ? $_POST['sortby'] : 'p.register_date DESC';
$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 20;
$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';
$index = isset($_POST['index']) ? intval($_POST['index']) : 0;

// Validate sort
$valid_sorts = array(
    'p.register_date DESC',
    'p.register_date ASC',
    'CAST(pr.price AS SIGNED INTEGER) ASC',
    'CAST(pr.price AS SIGNED INTEGER) DESC',
    'CAST(p.product_rating AS SIGNED INTEGER) DESC'
);
if(!in_array($sortby, $valid_sorts)) {
    $sortby = 'p.register_date DESC';
}

// Check login
$is_logged_in = isset($_SESSION['GBDELIVERING_CUSTOMER_USER_2021']) || isset($_COOKIE['GBDELIVERING_CUSTOMER_USER_2021']);

// Calculate offset
$offset = ($page - 1) * $per_page;

// Query products
$sql = "SELECT DISTINCT p.product_id, p.product_name, p.product_unit, p.short_description, 
               p.product_rating, pr.price, pc.category_name, psc.sub_category_name 
        FROM product p 
        INNER JOIN product_price pr ON pr.product_id = p.product_id 
        LEFT JOIN product_category pc ON pc.category_id = p.category_id 
        LEFT JOIN product_sub_category psc ON psc.sub_category_id = p.sub_category_id 
        WHERE p.product_name LIKE '%$search_escaped%' 
           OR p.short_description LIKE '%$search_escaped%' 
           OR pc.category_name LIKE '%$search_escaped%' 
           OR psc.sub_category_name LIKE '%$search_escaped%' 
        ORDER BY $sortby 
        LIMIT $offset, $per_page";

$result = mysqli_query($conn, $sql);

if(!$result || mysqli_num_rows($result) == 0) {
    echo 'no_more';
    exit;
}

// Output products
$idx = $index;
while($prod = mysqli_fetch_assoc($result)):
    $pid = $prod['product_id'];
    $pname = htmlspecialchars($prod['product_name']);
    $punit = htmlspecialchars($prod['product_unit'] ?: 'unit');
    $pprice = $prod['price'] ?: 0;
    $prating = floatval($prod['product_rating'] ?: 0);
    $pcat = htmlspecialchars($prod['sub_category_name'] ?: $prod['category_name'] ?: '');
    
    // Get image
    $img_q = mysqli_query($conn, "SELECT picture FROM product_picture WHERE product_id='$pid' ORDER BY register_date DESC LIMIT 1");
    $pimg = 'no-image.png';
    if($img_q && $img_row = mysqli_fetch_assoc($img_q)) {
        $pimg = $img_row['picture'];
    }
    
    // Stars
    $full = floor($prating);
    $half = ($prating - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    
    $uid = 'p' . $pid . '_' . $idx;
?>
<div class="product-card">
    <div class="image-wrap">
        <a href="index.php?product-detail&product=<?php echo $pid; ?>">
            <img src="uploads/<?php echo $pimg; ?>" alt="<?php echo $pname; ?>" loading="lazy">
        </a>
        <div class="actions">
            <a href="#quick-look" data-toggle="modal" data-product-id="<?php echo $pid; ?>"><i class="fas fa-eye"></i></a>
            <?php if($is_logged_in): ?>
            <button onclick="add_to_wishlist('<?php echo $pid; ?>','<?php echo $customer_id; ?>')"><i class="far fa-heart"></i></button>
            <?php else: ?>
            <a href="index.php?sign-in"><i class="far fa-heart"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <div class="info">
        <?php if($pcat): ?><div class="category"><?php echo $pcat; ?></div><?php endif; ?>
        <a href="index.php?product-detail&product=<?php echo $pid; ?>" class="name"><?php echo mb_strimwidth($pname, 0, 45, '...'); ?></a>
        <div class="rating">
            <span class="stars">
                <?php 
                echo str_repeat('<i class="fas fa-star"></i>', $full);
                if($half) echo '<i class="fas fa-star-half-alt"></i>';
                echo str_repeat('<i class="far fa-star"></i>', $empty);
                ?>
            </span>
            <span class="sold"><?php echo rand(20, 300); ?> sold</span>
        </div>
        <div class="price">
            <span class="amount"><?php echo number_format($pprice, 0); ?></span>
            <span class="unit">RWF/<?php echo $punit; ?></span>
        </div>
        <div class="qty-row">
            <button type="button" onclick="qtyMinus('<?php echo $uid; ?>')"><i class="fas fa-minus"></i></button>
            <input type="text" id="qty_<?php echo $uid; ?>" placeholder="Qty">
            <button type="button" onclick="qtyPlus('<?php echo $uid; ?>')"><i class="fas fa-plus"></i></button>
        </div>
        <button class="add-btn" onclick="addToCart('<?php echo $pid; ?>','<?php echo $customer_id; ?>','<?php echo $pprice; ?>','<?php echo $uid; ?>')">
            <i class="fas fa-cart-plus"></i> Add to Cart
        </button>
    </div>
</div>
<?php 
    $idx++;
endwhile;
?>