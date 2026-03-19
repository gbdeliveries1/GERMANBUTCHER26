<?php
// Locate DB connection
$db_paths = ['../../on/on.php', '../on/on.php', '../../includes/db.php', '../db_connect.php'];
foreach ($db_paths as $path) { if (file_exists($path)) { require_once $path; break; } }

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate keys
    $allowed = [
        'card_grid_columns', 'card_image_ratio', 'card_hover_effect', 'card_image_zoom',
        'card_lazy_load', 'card_show_title', 'card_show_price', 'card_show_category',
        'card_show_rating', 'card_show_stock', 'card_btn_style', 'card_show_quickview',
        'card_show_wishlist', 'card_badge_sale', 'card_badge_new', 'card_badge_hot',
        'card_border', 'card_shadow', 'card_radius', 'card_bg_color'
    ];

    $stmt = $conn->prepare("INSERT INTO site_design_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $success = true;

    foreach ($_POST as $key => $value) {
        if (in_array($key, $allowed)) {
            $val = htmlspecialchars(strip_tags(trim($value)));
            $stmt->bind_param("sss", $key, $val, $val);
            if (!$stmt->execute()) $success = false;
        }
    }

    echo json_encode(['status' => $success ? 'success' : 'error']);
}
?>