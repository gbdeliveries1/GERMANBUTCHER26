<?php
// Find DB connection
$db_paths = ['../../on/on.php', '../on/on.php', '../../includes/db.php', '../db_connect.php'];
foreach ($db_paths as $path) { if (file_exists($path)) { require_once $path; break; } }

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Whitelist allowed keys
    $allowed_keys = [
        'pc_card_layout', 'pc_card_radius', 'pc_badge_display', 
        'pc_price_color', 'pc_btn_color', 'pc_btn_style', 
        'pc_gallery_layout', 'pc_tab_style', 'pc_related_count'
    ];

    $stmt = $conn->prepare("INSERT INTO site_design_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    
    $success = true;
    foreach ($_POST as $key => $value) {
        if (in_array($key, $allowed_keys)) {
            $val = htmlspecialchars(strip_tags(trim($value)));
            $stmt->bind_param("sss", $key, $val, $val);
            if (!$stmt->execute()) $success = false;
        }
    }

    echo json_encode(['status' => $success ? 'success' : 'error']);
}
?>