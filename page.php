<?php
$slug = isset($_GET['slug']) ? $conn->real_escape_string($_GET['slug']) : '';
$res = $conn->query("SELECT * FROM custom_pages WHERE slug = '$slug' AND status = 1");

if (!$res || $res->num_rows == 0) {
    echo "<div class='container py-5 text-center'><h3>Page not found.</h3></div>";
    return;
}

$page_data = $res->fetch_assoc();
$blocks = json_decode($page_data['content_blocks'], true) ?: [];

// Fetch global customer id for add_to_cart function
$customer_id_1 = isset($_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021']) ? $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] : '';
?>

<div class="custom-page-wrapper" style="min-height: 60vh;">
    <?php foreach ($blocks as $block): ?>
        
        <?php if ($block['type'] == 'text'): ?>
            <div class="container" style="padding: 40px 15px; font-size: 16px; line-height: 1.8; color: #444;">
                <?php echo $block['content']; ?>
            </div>
        <?php endif; ?>

        <?php if ($block['type'] == 'image'): ?>
            <div class="container" style="padding: 20px 15px; text-align: center;">
                <?php if(!empty($block['link'])): ?><a href="<?php echo htmlspecialchars($block['link']); ?>"><?php endif; ?>
                <img src="<?php echo htmlspecialchars($block['url']); ?>" style="max-width: 100%; border-radius: 8px;">
                <?php if(!empty($block['link'])): ?></a><?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($block['type'] == 'video'): ?>
            <div class="container" style="padding: 20px 15px;">
                <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px;">
                    <iframe src="<?php echo htmlspecialchars($block['url']); ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allowfullscreen></iframe>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($block['type'] == 'banner'): ?>
            <div style="background-image: url('<?php echo htmlspecialchars($block['bg_image']); ?>'); background-size: cover; background-position: center; padding: 100px 20px; text-align: center; color: #fff; background-color: #333; position: relative;">
                <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.5);"></div>
                <div style="position: relative; z-index: 1;">
                    <h1 style="font-size: 42px; font-weight: 800; margin-bottom: 10px; color:#fff;"><?php echo htmlspecialchars($block['title']); ?></h1>
                    <p style="font-size: 20px; margin-bottom: 20px;"><?php echo htmlspecialchars($block['subtitle']); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($block['type'] == 'button'): ?>
            <div class="container" style="padding: 20px 15px; text-align: center;">
                <a href="<?php echo htmlspecialchars($block['link']); ?>" style="display: inline-block; background: #ff5000; color: #fff; padding: 14px 35px; border-radius: 30px; font-weight: bold; font-size: 16px; text-decoration: none;">
                    <?php echo htmlspecialchars($block['text']); ?>
                </a>
            </div>
        <?php endif; ?>

        <?php if ($block['type'] == 'product_grid'): ?>
            <div class="container" style="padding: 40px 15px;">
                <h2 style="margin-bottom: 25px; font-size: 24px; border-bottom: 2px solid #eee; padding-bottom: 10px;"><?php echo htmlspecialchars($block['title']); ?></h2>
                <div class="pgd" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 15px;">
                    <?php 
                    $limit = (int)$block['limit'];
                    $q = "SELECT * FROM product ORDER BY RAND() LIMIT $limit";
                    $res_p = $conn->query($q);
                    if($res_p) {
                        while($p = $res_p->fetch_assoc()) {
                            $img = !empty($p['image']) ? $p['image'] : 'images/placeholder.jpg';
                            echo '
                            <div class="pcd" style="border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                                <a href="index.php?product-detail&product='.$p['product_id'].'" style="text-decoration:none; color:inherit;">
                                    <img src="'.htmlspecialchars($img).'" style="width:100%; aspect-ratio:1; object-fit:cover; border-radius:6px; margin-bottom:10px;">
                                    <div style="font-size: 13px; font-weight:500; height:36px; overflow:hidden;">'.htmlspecialchars($p['product_name']).'</div>
                                    <div style="color: #ff5000; font-weight:bold; margin-top:5px;">'.number_format($p['price']).' RWF</div>
                                </a>
                            </div>';
                        }
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endforeach; ?>
</div>