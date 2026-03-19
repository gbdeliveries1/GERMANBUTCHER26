<?php
// Bulk Save Handler - Fixed & Complete
ob_start();
session_start();
include "../../on/on.php";
ob_end_clean();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

function generateId() { 
    return md5(time() . rand(1000,9999)) . md5(rand(1000,9999)); 
}

function uploadFile($file) {
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'jpg';
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp','jfif'])) return false;
    $fn = md5(time() . rand(1000,9999)) . '.' . $ext;
    return move_uploaded_file($file['tmp_name'], $dir . $fn) ? $fn : false;
}

// GET IMAGES
if ($action === 'get_images') {
    $id = $conn->real_escape_string($_POST['id'] ?? '');
    $images = [];
    $r = $conn->query("SELECT picture_id, picture FROM product_picture WHERE product_id='$id' ORDER BY register_date ASC");
    if ($r) while ($row = $r->fetch_assoc()) $images[] = $row;
    echo json_encode(['success' => true, 'images' => $images]);
    exit;
}

// UPLOAD IMAGE
if ($action === 'upload_image') {
    $id = $conn->real_escape_string($_POST['id'] ?? '');
    $keepExisting = ($_POST['keep_existing'] ?? '') === '1';
    
    if (empty($id) || !isset($_FILES['image']) || $_FILES['image']['error'] !== 0) { 
        echo json_encode(['success' => false, 'error' => 'No file']); 
        exit; 
    }
    
    $fn = uploadFile($_FILES['image']);
    if ($fn) {
        if (!$keepExisting) {
            $conn->query("DELETE FROM product_picture WHERE product_id='$id'");
        }
        $picId = generateId();
        $now = date('Y-m-d H:i:s');
        $conn->query("INSERT INTO product_picture (picture_id, product_id, picture, register_date) VALUES ('$picId', '$id', '$fn', '$now')");
        echo json_encode(['success' => true, 'filename' => $fn, 'picture_id' => $picId]);
    } else { 
        echo json_encode(['success' => false, 'error' => 'Upload failed']); 
    }
    exit;
}

// DELETE IMAGE
if ($action === 'delete_image') {
    $picId = $conn->real_escape_string($_POST['picture_id'] ?? '');
    $r = $conn->query("SELECT picture FROM product_picture WHERE picture_id='$picId'");
    if ($r && $row = $r->fetch_assoc()) { 
        $f = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $row['picture']; 
        if (file_exists($f)) @unlink($f); 
    }
    $ok = $conn->query("DELETE FROM product_picture WHERE picture_id='$picId'");
    echo json_encode(['success' => (bool)$ok]);
    exit;
}

// UPDATE PRODUCT
if ($action === 'update_product') {
    $id = $conn->real_escape_string($_POST['id'] ?? '');
    $field = $_POST['field'] ?? '';
    $value = $conn->real_escape_string($_POST['value'] ?? '');
    
    if (empty($id) || empty($field)) { 
        echo json_encode(['success' => false, 'error' => 'Missing data']); 
        exit; 
    }
    
    $ok = false;
    
    switch ($field) {
        case 'name':
            $ok = $conn->query("UPDATE product SET product_name='$value' WHERE product_id='$id'");
            break;
            
        case 'category':
            $ok = $conn->query("UPDATE product SET category_id='$value', sub_category_id=NULL WHERE product_id='$id'");
            break;
            
        case 'sub_category':
            if ($value) {
                $ok = $conn->query("UPDATE product SET sub_category_id='$value' WHERE product_id='$id'");
            } else {
                $ok = $conn->query("UPDATE product SET sub_category_id=NULL WHERE product_id='$id'");
            }
            break;
            
        case 'price':
            $r = $conn->query("SELECT * FROM product_price WHERE product_id='$id'");
            if ($r && $r->num_rows > 0) {
                $ok = $conn->query("UPDATE product_price SET price='$value' WHERE product_id='$id'");
            } else {
                $ok = $conn->query("INSERT INTO product_price (product_id, price) VALUES ('$id', '$value')");
            }
            break;
            
        case 'stock':
            $r = $conn->query("SELECT * FROM product_stock WHERE product_id='$id'");
            if ($r && $r->num_rows > 0) {
                $ok = $conn->query("UPDATE product_stock SET stock_quantity='$value' WHERE product_id='$id'");
            } else {
                $ok = $conn->query("INSERT INTO product_stock (product_id, stock_quantity) VALUES ('$id', '$value')");
            }
            break;
            
        case 'unit':
            $ok = $conn->query("UPDATE product SET unit='$value' WHERE product_id='$id'");
            break;
            
        case 'minimum_order':
            $ok = $conn->query("UPDATE product SET minimum_order='$value' WHERE product_id='$id'");
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Unknown field']);
            exit;
    }
    
    echo json_encode(['success' => (bool)$ok, 'error' => $ok ? '' : $conn->error]);
    exit;
}

// ADD PRODUCT
if ($action === 'add_product') {
    $name = $conn->real_escape_string($_POST['product_name'] ?? '');
    $catId = $conn->real_escape_string($_POST['category_id'] ?? '');
    $subCatId = $conn->real_escape_string($_POST['sub_category_id'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $unit = $conn->real_escape_string($_POST['unit'] ?? 'pcs');
    $minOrder = intval($_POST['minimum_order'] ?? 1);
    
    if (empty($name) || empty($catId)) {
        echo json_encode(['success' => false, 'error' => 'Missing fields']);
        exit;
    }
    
    $prodId = generateId();
    $now = date('Y-m-d H:i:s');
    $subCatPart = $subCatId ? "'$subCatId'" : "NULL";
    
    $ok = $conn->query("INSERT INTO product (product_id, product_name, category_id, sub_category_id, unit, minimum_order, register_date) 
                        VALUES ('$prodId', '$name', '$catId', $subCatPart, '$unit', '$minOrder', '$now')");
    
    if ($ok) {
        $conn->query("INSERT INTO product_price (product_id, price) VALUES ('$prodId', '$price')");
        $conn->query("INSERT INTO product_stock (product_id, stock_quantity) VALUES ('$prodId', '$stock')");
        echo json_encode(['success' => true, 'id' => $prodId]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}

// DELETE PRODUCT
if ($action === 'delete_product') {
    $id = $conn->real_escape_string($_POST['id'] ?? '');
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'error' => 'Missing ID']);
        exit;
    }
    
    // Delete related data
    $conn->query("DELETE FROM product_picture WHERE product_id='$id'");
    $conn->query("DELETE FROM product_price WHERE product_id='$id'");
    $conn->query("DELETE FROM product_stock WHERE product_id='$id'");
    $ok = $conn->query("DELETE FROM product WHERE product_id='$id'");
    
    echo json_encode(['success' => (bool)$ok]);
    exit;
}

// Invalid action
echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);