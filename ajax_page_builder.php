<?php
$db_paths = ['../../on/on.php', '../on/on.php', '../../includes/db.php', '../db_connect.php'];
foreach ($db_paths as $path) { if (file_exists($path)) { require_once $path; break; } }

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Handle Delete
    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $conn->query("DELETE FROM custom_pages WHERE id = $id");
        echo json_encode(['status' => 'success']);
        exit;
    }

    // Handle Save / Update
    if (isset($_POST['title']) && isset($_POST['slug'])) {
        $id = (int)$_POST['id'];
        $title = $conn->real_escape_string(strip_tags($_POST['title']));
        $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_POST['slug'])));
        
        $status = (int)$_POST['status'];
        $show_header = (int)$_POST['show_header'];
        $show_footer = (int)$_POST['show_footer'];
        
        $meta_title = $conn->real_escape_string(strip_tags($_POST['meta_title']));
        $meta_desc = $conn->real_escape_string(strip_tags($_POST['meta_desc']));
        $blocks = $conn->real_escape_string($_POST['blocks']);

        if ($id > 0) {
            $sql = "UPDATE custom_pages SET 
                    title='$title', slug='$slug', status=$status, 
                    show_in_header=$show_header, show_in_footer=$show_footer, 
                    meta_title='$meta_title', meta_description='$meta_desc', 
                    content_blocks='$blocks' 
                    WHERE id=$id";
        } else {
            $sql = "INSERT INTO custom_pages 
                    (title, slug, status, show_in_header, show_in_footer, meta_title, meta_description, content_blocks) 
                    VALUES 
                    ('$title', '$slug', $status, $show_header, $show_footer, '$meta_title', '$meta_desc', '$blocks')";
        }

        if ($conn->query($sql)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $conn->error]);
        }
    }
}
?>