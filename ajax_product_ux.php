<?php
// Find DB connection
$db_paths = ['../../on/on.php', '../on/on.php', '../../includes/db.php', '../db_connect.php'];
foreach ($db_paths as $path) { if (file_exists($path)) { require_once $path; break; } }

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'save_product_ux') {
    
    $info_blocks = $_POST['info_blocks'];
    $bottom_blocks = $_POST['bottom_blocks'];

    $stmt = $conn->prepare("INSERT INTO site_design_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    
    // Save Info Blocks
    $key1 = 'ux_product_info_blocks';
    $stmt->bind_param("sss", $key1, $info_blocks, $info_blocks);
    $stmt->execute();

    // Save Bottom Blocks
    $key2 = 'ux_product_bottom_blocks';
    $stmt->bind_param("sss", $key2, $bottom_blocks, $bottom_blocks);
    $stmt->execute();

    echo json_encode(['status' => 'success']);
}
?>