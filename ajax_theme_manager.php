<?php
$db_paths = ['../../on/on.php', '../on/on.php', '../../includes/db.php', '../db_connect.php'];
foreach ($db_paths as $path) { if (file_exists($path)) { require_once $path; break; } }

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Helper to update DB
    function updateSetting($conn, $key, $val) {
        $stmt = $conn->prepare("INSERT INTO site_design_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("sss", $key, $val, $val);
        $stmt->execute();
    }

    // 1. ACTIVATE THEME
    if ($action === 'activate_theme') {
        $slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['slug']);
        updateSetting($conn, 'active_theme', $slug);

        // Read theme.json and apply defaults
        $json_path = '../../themes/' . $slug . '/theme.json';
        if (file_exists($json_path)) {
            $data = json_decode(file_get_contents($json_path), true);
            if (isset($data['defaults']) && is_array($data['defaults'])) {
                foreach ($data['defaults'] as $key => $val) {
                    updateSetting($conn, $key, $val);
                }
            }
        }
        echo json_encode(['status' => 'success']);
        exit;
    }

    // 2. SAVE CUSTOMIZER
    if ($action === 'save_customizer') {
        // Handle File Upload for Logo
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../images/logo/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            
            $file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['site_logo']['name']));
            $target = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $target)) {
                updateSetting($conn, 'site_logo', 'images/logo/' . $file_name);
            }
        }

        // Handle text/color settings
        $fields = ['theme_primary_color', 'theme_secondary_color', 'theme_bg_color', 'theme_font', 'theme_header_style', 'theme_footer_style'];
        foreach ($fields as $f) {
            if (isset($_POST[$f])) {
                updateSetting($conn, $f, strip_tags($_POST[$f]));
            }
        }

        echo json_encode(['status' => 'success']);
        exit;
    }
}
?>