<?php
header('Content-Type: application/json');
// require 'db_connect.php'; // Include DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Whitelist of allowed keys to prevent database injection mapping
    $allowed_keys = [
        'primary_color', 'secondary_color', 'button_color', 
        'font_family', 'font_size_base', 'font_size_heading', 
        'container_width', 'border_radius', 'box_shadow', 'card_style'
    ];

    $stmt = $conn->prepare("INSERT INTO site_design_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Database error.']);
        exit;
    }

    $success = true;

    foreach ($_POST as $key => $value) {
        if (in_array($key, $allowed_keys)) {
            // Sanitize string
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
        echo json_encode(['status' => 'error', 'message' => 'Failed to save some settings.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}