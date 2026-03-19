<?php
// Safely connect to database
$db_paths = ['../../on/on.php', '../on/on.php', '../../includes/db.php', '../db_connect.php'];
foreach ($db_paths as $path) {
    if (file_exists($path)) { require_once $path; break; }
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // List of allowed settings to prevent DB injection
    $allowed_keys = [
        'prod_badges', 'prod_card_layout', 'prod_image_size', 
        'prod_gallery_layout', 'prod_tabs_style', 'prod_price_color', 
        'prod_price_size', 'prod_btn_style', 'prod_related_show', 'prod_related_count'
    ];

    $stmt = $conn->prepare("INSERT INTO site_design_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    
    $success = true;

    foreach ($_POST as $key => $value) {
        if (in_array($key, $allowed_keys)) {
            $safe_val = htmlspecialchars(strip_tags(trim($value)));
            $stmt->bind_param("sss", $key, $safe_val, $safe_val);
            if (!$stmt->execute()) {
                $success = false;
            }
        }
    }

    if ($success) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save settings']);
    }
}
?>