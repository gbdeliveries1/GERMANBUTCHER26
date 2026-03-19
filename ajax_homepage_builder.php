<?php
// 1. Enable Error Reporting to prevent blank 500 screens during debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. CONNECT TO DATABASE SAFELY
// Since this file is in /user/admin/, the path to your root "on/on.php" is ../../on/on.php
$db_paths = [
    '../../on/on.php',           // Likely correct path based on your index.php
    '../on/on.php',
    '../../includes/db.php',
    '../db_connect.php'
];

$connected = false;
foreach ($db_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $connected = true;
        break;
    }
}

if (!$connected) {
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(['status' => 'error', 'message' => 'Fatal: Could not locate database connection file (on/on.php). Please check paths.']));
}

// Ensure the $conn variable actually exists
if (!isset($conn)) {
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(['status' => 'error', 'message' => 'Fatal: Database variable $conn is not defined in your connection file.']));
}

// 3. Handle Drag-and-Drop Reorder & Toggle (JSON Requests)
$input_raw = file_get_contents('php://input');
$input = json_decode($input_raw, true);

if ($input && isset($input['action'])) {
    header('Content-Type: application/json');

    if ($input['action'] == 'reorder' && isset($input['data'])) {
        foreach ($input['data'] as $item) {
            $id = (int)$item['id'];
            $sort = (int)$item['sort'];
            $conn->query("UPDATE homepage_sections SET sort_order = $sort WHERE id = $id");
        }
        echo json_encode(['status' => 'success']);
        exit;
    }
    
    if ($input['action'] == 'toggle' && isset($input['id'])) {
        $id = (int)$input['id'];
        $val = (int)$input['is_active'];
        $conn->query("UPDATE homepage_sections SET is_active = $val WHERE id = $id");
        echo json_encode(['status' => 'success']);
        exit;
    }
}

// 4. Handle Form POST requests (Saving Content & Image Uploads)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_content') {
    $id = (int)($_POST['section_id'] ?? 0);
    
    if ($id === 0) {
        die("Invalid Section ID.");
    }

    // Rebuild the JSON object
    $new_content = [];
    
    // Process Text Fields safely
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'text_') === 0) {
            $actual_key = str_replace('text_', '', $key);
            $new_content[$actual_key] = htmlspecialchars($value);
        }
    }
    
    // Process File Uploads (Images)
    // Path goes up two folders from /user/admin/ to reach public /images/
    $upload_dir = '../../images/banners/'; 
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, 0755, true); // Suppress errors if permissions block creation
    }

    foreach ($_FILES as $key => $file) {
        if (strpos($key, 'file_') === 0) {
            $actual_key = str_replace('file_', '', $key);
            
            if ($file['error'] === UPLOAD_ERR_OK) {
                // Ensure unique file name and remove spaces
                $clean_name = preg_replace("/[^a-zA-Z0-9.]/", "_", basename($file['name']));
                $filename = time() . '_' . $clean_name;
                $target = $upload_dir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $target)) {
                    // Save relative path for the database to use on frontend
                    $new_content[$actual_key] = 'images/banners/' . $filename; 
                } else {
                    $new_content[$actual_key] = $_POST['old_' . $actual_key] ?? '';
                }
            } else {
                // Keep old image if no new file was uploaded safely using fallback
                $new_content[$actual_key] = $_POST['old_' . $actual_key] ?? '';
            }
        }
    }

    // Save as JSON
    $json_data = json_encode($new_content);
    
    // Prepare SQL
    $stmt = $conn->prepare("UPDATE homepage_sections SET content_data = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $json_data, $id);
        $stmt->execute();
        
        // Redirect back to admin dashboard tab smoothly
        header("Location: index.php?page=admin_manager&manage=homepage_builder&success=Section+Updated+Successfully");
        exit;
    } else {
        die("Database Prepare Error: " . $conn->error);
    }
}
?>