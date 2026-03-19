<?php
session_start();
include "../../on/on.php";

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : '';

function jsonResponse($success, $data = [], $error = '') {
    echo json_encode(array_merge(['success' => $success, 'error' => $error], $data));
    exit;
}

function goBack($msg, $err = false, $section = 'dashboard') {
    // Check if it's an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        jsonResponse(!$err, ['message' => $msg], $err ? $msg : '');
    }
    $p = $err ? 'error' : 'success';
    header("Location: index.php?page=admin_manager&manage=$section&$p=" . urlencode($msg));
    exit;
}

function generateId() {
    return md5(time() . rand(1000,9999)) . md5(rand(1000,9999));
}

function uploadFile($file, $name = '') {
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!$ext) $ext = 'jpg';
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];
    if (!in_array($ext, $allowed)) return false;
    
    $hash = md5(time() . rand(1000,9999)) . md5(rand(1000,9999));
    $safe = preg_replace('/[^a-zA-Z0-9 ]/', '', $name);
    $fn = $hash . $safe . '.' . $ext;
    
    if (move_uploaded_file($file['tmp_name'], $dir . $fn)) {
        return $fn;
    }
    return false;
}

// ============ CATEGORIES ============
if ($action == 'add_category') {
    $name = $conn->real_escape_string($_POST['category_name']);
    $id = generateId();
    $now = date('Y-m-d H:i:s');
    $conn->query("INSERT INTO product_category (category_id, category_name, register_date) VALUES ('$id', '$name', '$now')");
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $fn = uploadFile($_FILES['image'], $name);
        if ($fn) $conn->query("UPDATE product_category SET picture='$fn' WHERE category_id='$id'");
    }
    goBack('Category added!', false, 'categories');
}

if ($action == 'update_category') {
    $id = $conn->real_escape_string($_POST['id']);
    $name = $conn->real_escape_string($_POST['category_name']);
    $conn->query("UPDATE product_category SET category_name='$name' WHERE category_id='$id'");
    goBack('Updated!', false, 'categories');
}

if ($action == 'upload_category_image') {
    $id = $conn->real_escape_string($_POST['id']);
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $r = $conn->query("SELECT category_name FROM product_category WHERE category_id='$id'");
        $row = $r->fetch_assoc();
        $fn = uploadFile($_FILES['image'], $row['category_name']);
        if ($fn) {
            $conn->query("UPDATE product_category SET picture='$fn' WHERE category_id='$id'");
            goBack('Image uploaded!', false, 'categories');
        }
    }
    goBack('Upload failed', true, 'categories');
}

if ($action == 'delete_category') {
    $id = $conn->real_escape_string($_POST['id']);
    $conn->query("DELETE FROM product_category WHERE category_id='$id'");
    goBack('Deleted!', false, 'categories');
}

// ============ SUB CATEGORIES ============
if ($action == 'add_subcategory') {
    $catId = $conn->real_escape_string($_POST['category_id']);
    $name = $conn->real_escape_string($_POST['sub_category_name']);
    $id = generateId();
    $now = date('Y-m-d H:i:s');
    $conn->query("INSERT INTO product_sub_category (sub_category_id, category_id, sub_category_name, register_date) VALUES ('$id', '$catId', '$name', '$now')");
    goBack('Added!', false, 'subcategories');
}

if ($action == 'update_subcategory') {
    $id = $conn->real_escape_string($_POST['id']);
    $field = $_POST['field'];
    $value = $conn->real_escape_string($_POST['value']);
    if ($field == 'name') {
        $conn->query("UPDATE product_sub_category SET sub_category_name='$value' WHERE sub_category_id='$id'");
    } elseif ($field == 'category') {
        $conn->query("UPDATE product_sub_category SET category_id='$value' WHERE sub_category_id='$id'");
    }
    goBack('Updated!', false, 'subcategories');
}

if ($action == 'delete_subcategory') {
    $id = $conn->real_escape_string($_POST['id']);
    $conn->query("DELETE FROM product_sub_category WHERE sub_category_id='$id'");
    goBack('Deleted!', false, 'subcategories');
}

// ============ PRODUCTS ============
if ($action == 'add_product') {
    $name = $conn->real_escape_string($_POST['product_name']);
    $catId = $conn->real_escape_string($_POST['category_id']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $prodId = generateId();
    $now = date('Y-m-d H:i:s');
    
    $conn->query("INSERT INTO product (product_id, product_name, category_id, register_date) VALUES ('$prodId', '$name', '$catId', '$now')");
    if ($price > 0) $conn->query("INSERT INTO product_price (product_id, price) VALUES ('$prodId', '$price')");
    $conn->query("INSERT INTO product_stock (product_id, stock_quantity) VALUES ('$prodId', '$stock')");
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $fn = uploadFile($_FILES['image'], $name);
        if ($fn) {
            $picId = generateId();
            $conn->query("INSERT INTO product_picture (picture_id, product_id, picture, register_date) VALUES ('$picId', '$prodId', '$fn', '$now')");
        }
    }
    goBack('Product added!', false, 'products');
}

if ($action == 'update_product') {
    $id = $conn->real_escape_string($_POST['id']);
    $field = $_POST['field'];
    $value = $conn->real_escape_string($_POST['value']);
    
    if ($field == 'name') {
        $conn->query("UPDATE product SET product_name='$value' WHERE product_id='$id'");
    } elseif ($field == 'category') {
        $conn->query("UPDATE product SET category_id='$value' WHERE product_id='$id'");
    } elseif ($field == 'price') {
        $r = $conn->query("SELECT * FROM product_price WHERE product_id='$id'");
        if ($r && $r->num_rows > 0) {
            $conn->query("UPDATE product_price SET price='$value' WHERE product_id='$id'");
        } else {
            $conn->query("INSERT INTO product_price (product_id, price) VALUES ('$id', '$value')");
        }
    } elseif ($field == 'stock') {
        $r = $conn->query("SELECT * FROM product_stock WHERE product_id='$id'");
        if ($r && $r->num_rows > 0) {
            $conn->query("UPDATE product_stock SET stock_quantity='$value' WHERE product_id='$id'");
        } else {
            $conn->query("INSERT INTO product_stock (product_id, stock_quantity) VALUES ('$id', '$value')");
        }
    }
    goBack('Updated!', false, 'products');
}

if ($action == 'upload_product_image') {
    $id = $conn->real_escape_string($_POST['id']);
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $r = $conn->query("SELECT product_name FROM product WHERE product_id='$id'");
        $row = $r->fetch_assoc();
        $fn = uploadFile($_FILES['image'], $row['product_name']);
        if ($fn) {
            $conn->query("DELETE FROM product_picture WHERE product_id='$id'");
            $picId = generateId();
            $now = date('Y-m-d H:i:s');
            $conn->query("INSERT INTO product_picture (picture_id, product_id, picture, register_date) VALUES ('$picId', '$id', '$fn', '$now')");
            goBack('Image uploaded!', false, 'products');
        }
    }
    goBack('Upload failed', true, 'products');
}

if ($action == 'delete_product') {
    $id = $conn->real_escape_string($_POST['id']);
    $conn->query("DELETE FROM product_picture WHERE product_id='$id'");
    $conn->query("DELETE FROM product_price WHERE product_id='$id'");
    $conn->query("DELETE FROM product_stock WHERE product_id='$id'");
    $conn->query("DELETE FROM product WHERE product_id='$id'");
    goBack('Deleted!', false, 'products');
}

// ============ STOCK ============
if ($action == 'update_stock') {
    $id = $conn->real_escape_string($_POST['id']);
    $qty = intval($_POST['quantity']);
    $r = $conn->query("SELECT * FROM product_stock WHERE product_id='$id'");
    if ($r && $r->num_rows > 0) {
        $conn->query("UPDATE product_stock SET stock_quantity='$qty' WHERE product_id='$id'");
    } else {
        $conn->query("INSERT INTO product_stock (product_id, stock_quantity) VALUES ('$id', '$qty')");
    }
    goBack('Stock updated!', false, 'stock');
}

if ($action == 'adjust_stock') {
    $id = $conn->real_escape_string($_POST['id']);
    $amount = intval($_POST['amount']);
    $r = $conn->query("SELECT stock_quantity FROM product_stock WHERE product_id='$id'");
    if ($r && $r->num_rows > 0) {
        $current = $r->fetch_assoc()['stock_quantity'];
        $new = max(0, $current + $amount);
        $conn->query("UPDATE product_stock SET stock_quantity='$new' WHERE product_id='$id'");
    } else {
        $new = max(0, $amount);
        $conn->query("INSERT INTO product_stock (product_id, stock_quantity) VALUES ('$id', '$new')");
    }
    goBack('Stock adjusted!', false, 'stock');
}

// ============ SHIPPING - UPDATE FIELD (AJAX Inline Edit) ============
if ($action == 'update_shipping_field') {
    $table = $_POST['table'] ?? '';
    $id = $conn->real_escape_string($_POST['id'] ?? '');
    $field = $conn->real_escape_string($_POST['field'] ?? '');
    $value = $conn->real_escape_string($_POST['value'] ?? '');
    
    // Whitelist allowed tables and fields
    $allowedTables = [
        'shipping_fee' => ['fee_id', ['location', 'country', 'province', 'district', 'fee']],
        'sector_shipping_fee' => ['fee_id', ['sector', 'district', 'province', 'fee']],
        'rw_location' => ['id', ['delivery_fee', 'province', 'district', 'sector']]
    ];
    
    if (!isset($allowedTables[$table])) {
        jsonResponse(false, [], 'Invalid table');
    }
    
    $idColumn = $allowedTables[$table][0];
    $allowedFields = $allowedTables[$table][1];
    
    if (!in_array($field, $allowedFields)) {
        jsonResponse(false, [], 'Invalid field');
    }
    
    $sql = "UPDATE `$table` SET `$field` = '$value' WHERE `$idColumn` = '$id'";
    
    if ($conn->query($sql)) {
        jsonResponse(true, ['updated' => $conn->affected_rows]);
    } else {
        jsonResponse(false, [], 'Database error: ' . $conn->error);
    }
}

// ============ SHIPPING - DELETE ITEM ============
if ($action == 'delete_shipping_item') {
    $table = $_POST['table'] ?? '';
    $id = $conn->real_escape_string($_POST['id'] ?? '');
    
    $allowedTables = [
        'shipping_fee' => 'fee_id',
        'sector_shipping_fee' => 'fee_id'
    ];
    
    if (!isset($allowedTables[$table])) {
        jsonResponse(false, [], 'Invalid table');
    }
    
    $idColumn = $allowedTables[$table];
    $sql = "DELETE FROM `$table` WHERE `$idColumn` = '$id'";
    
    if ($conn->query($sql)) {
        jsonResponse(true, ['deleted' => $conn->affected_rows]);
    } else {
        jsonResponse(false, [], 'Database error: ' . $conn->error);
    }
}

// ============ SHIPPING - ADD ZONE ============
if ($action == 'add_shipping_zone') {
    $location = $conn->real_escape_string($_POST['location'] ?? '');
    $country = $conn->real_escape_string($_POST['country'] ?? 'Rwanda');
    $province = $conn->real_escape_string($_POST['province'] ?? '');
    $district = $conn->real_escape_string($_POST['district'] ?? '');
    $fee = intval($_POST['fee'] ?? 0);
    $id = generateId();
    $now = date('Y-m-d H:i:s');
    
    // Check which columns exist
    $columns = [];
    $values = [];
    
    $result = $conn->query("SHOW COLUMNS FROM shipping_fee");
    $existingCols = [];
    while ($row = $result->fetch_assoc()) {
        $existingCols[] = $row['Field'];
    }
    
    $columns[] = 'fee_id';
    $values[] = "'$id'";
    
    if (in_array('location', $existingCols) && $location) {
        $columns[] = 'location';
        $values[] = "'$location'";
    }
    if (in_array('country', $existingCols)) {
        $columns[] = 'country';
        $values[] = "'$country'";
    }
    if (in_array('province', $existingCols)) {
        $columns[] = 'province';
        $values[] = "'$province'";
    }
    if (in_array('district', $existingCols)) {
        $columns[] = 'district';
        $values[] = "'$district'";
    }
    if (in_array('fee', $existingCols)) {
        $columns[] = 'fee';
        $values[] = "'$fee'";
    }
    if (in_array('register_date', $existingCols)) {
        $columns[] = 'register_date';
        $values[] = "'$now'";
    }
    
    $sql = "INSERT INTO shipping_fee (" . implode(',', $columns) . ") VALUES (" . implode(',', $values) . ")";
    
    if ($conn->query($sql)) {
        jsonResponse(true, ['id' => $id, 'message' => 'Zone added successfully']);
    } else {
        jsonResponse(false, [], 'Database error: ' . $conn->error);
    }
}

// ============ SHIPPING - ADD SECTOR FEE ============
if ($action == 'add_sector_fee') {
    $sector = $conn->real_escape_string($_POST['sector'] ?? '');
    $district = $conn->real_escape_string($_POST['district'] ?? '');
    $province = $conn->real_escape_string($_POST['province'] ?? '');
    $fee = intval($_POST['fee'] ?? 0);
    $id = generateId();
    $now = date('Y-m-d H:i:s');
    
    if (empty($sector)) {
        jsonResponse(false, [], 'Sector name is required');
    }
    
    $sql = "INSERT INTO sector_shipping_fee (fee_id, sector, district, province, fee, register_date) 
            VALUES ('$id', '$sector', '$district', '$province', '$fee', '$now')";
    
    if ($conn->query($sql)) {
        jsonResponse(true, ['id' => $id, 'message' => 'Sector fee added successfully']);
    } else {
        jsonResponse(false, [], 'Database error: ' . $conn->error);
    }
}

// ============ SHIPPING - BULK UPDATE DISTRICT FEE ============
if ($action == 'bulk_update_district_fee') {
    $province = $conn->real_escape_string($_POST['province'] ?? '');
    $district = $conn->real_escape_string($_POST['district'] ?? '');
    $fee = intval($_POST['fee'] ?? 0);
    
    if (empty($province) || empty($district)) {
        jsonResponse(false, [], 'Province and district are required');
    }
    
    // Check if rw_location has delivery_fee column
    $result = $conn->query("SHOW COLUMNS FROM rw_location LIKE 'delivery_fee'");
    if ($result && $result->num_rows > 0) {
        $sql = "UPDATE rw_location SET delivery_fee = '$fee' WHERE province = '$province' AND district = '$district'";
        if ($conn->query($sql)) {
            jsonResponse(true, ['count' => $conn->affected_rows, 'message' => 'Updated successfully']);
        } else {
            jsonResponse(false, [], 'Database error: ' . $conn->error);
        }
    } else {
        // Fall back to sector_shipping_fee table
        $now = date('Y-m-d H:i:s');
        $sectorsResult = $conn->query("SELECT DISTINCT sector FROM rw_location WHERE province='$province' AND district='$district'");
        $count = 0;
        
        while ($row = $sectorsResult->fetch_assoc()) {
            $sector = $conn->real_escape_string($row['sector']);
            // Check if exists
            $check = $conn->query("SELECT fee_id FROM sector_shipping_fee WHERE sector='$sector' AND district='$district'");
            if ($check && $check->num_rows > 0) {
                $conn->query("UPDATE sector_shipping_fee SET fee='$fee' WHERE sector='$sector' AND district='$district'");
            } else {
                $id = generateId();
                $conn->query("INSERT INTO sector_shipping_fee (fee_id, sector, district, province, fee, register_date) 
                              VALUES ('$id', '$sector', '$district', '$province', '$fee', '$now')");
            }
            $count++;
        }
        jsonResponse(true, ['count' => $count, 'message' => 'Updated successfully']);
    }
}

// ============ SHIPPING - BULK UPDATE RW FEES ============
if ($action == 'bulk_update_rw_fees') {
    $province = $conn->real_escape_string($_POST['province'] ?? '');
    $district = $conn->real_escape_string($_POST['district'] ?? '');
    $fee = intval($_POST['fee'] ?? 0);
    
    $where = "1=1";
    if ($province) $where .= " AND province = '$province'";
    if ($district) $where .= " AND district = '$district'";
    
    $sql = "UPDATE rw_location SET delivery_fee = '$fee' WHERE $where";
    
    if ($conn->query($sql)) {
        jsonResponse(true, ['count' => $conn->affected_rows, 'message' => 'Updated successfully']);
    } else {
        jsonResponse(false, [], 'Database error: ' . $conn->error);
    }
}

// ============ SHIPPING - BULK IMPORT SECTORS ============
if ($action == 'bulk_import_sectors') {
    $province = $conn->real_escape_string($_POST['province'] ?? '');
    $data = $_POST['data'] ?? '';
    
    $lines = explode("\n", $data);
    $imported = 0;
    $total = 0;
    $now = date('Y-m-d H:i:s');
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $total++;
        $parts = array_map('trim', explode(',', $line));
        
        if (count($parts) >= 3) {
            $sector = $conn->real_escape_string($parts[0]);
            $district = $conn->real_escape_string($parts[1]);
            $fee = intval($parts[2]);
            
            if (!empty($sector)) {
                $id = generateId();
                $sql = "INSERT INTO sector_shipping_fee (fee_id, sector, district, province, fee, register_date) 
                        VALUES ('$id', '$sector', '$district', '$province', '$fee', '$now')";
                if ($conn->query($sql)) {
                    $imported++;
                }
            }
        } elseif (count($parts) == 2) {
            // Format: Sector, Fee (use province from form)
            $sector = $conn->real_escape_string($parts[0]);
            $fee = intval($parts[1]);
            
            if (!empty($sector)) {
                $id = generateId();
                $sql = "INSERT INTO sector_shipping_fee (fee_id, sector, province, fee, register_date) 
                        VALUES ('$id', '$sector', '$province', '$fee', '$now')";
                if ($conn->query($sql)) {
                    $imported++;
                }
            }
        }
    }
    
    jsonResponse(true, ['imported' => $imported, 'total' => $total, 'message' => "$imported of $total imported"]);
}

// ============ LEGACY SHIPPING HANDLERS (for form submissions) ============
if ($action == 'add_shipping') {
    $country = $conn->real_escape_string($_POST['country'] ?? 'Rwanda');
    $province = $conn->real_escape_string($_POST['province'] ?? '');
    $location = $conn->real_escape_string($_POST['location'] ?? '');
    $district = $conn->real_escape_string($_POST['district'] ?? '');
    $fee = $conn->real_escape_string($_POST['fee'] ?? 0);
    $id = generateId();
    $now = date('Y-m-d H:i:s');
    
    $conn->query("INSERT INTO shipping_fee (fee_id, country, province, location, district, fee, register_date) 
                  VALUES ('$id', '$country', '$province', '$location', '$district', '$fee', '$now')");
    
    $redirect = $_POST['redirect'] ?? '?page=admin_manager&manage=shipping';
    header("Location: $redirect");
    exit;
}

if ($action == 'update_shipping') {
    $id = $conn->real_escape_string($_POST['id']);
    $field = $_POST['field'];
    $value = $conn->real_escape_string($_POST['value']);
    
    $allowedFields = ['country', 'province', 'location', 'district', 'fee'];
    if (in_array($field, $allowedFields)) {
        $conn->query("UPDATE shipping_fee SET $field='$value' WHERE fee_id='$id'");
    }
    goBack('Updated!', false, 'shipping');
}

if ($action == 'delete_shipping') {
    $id = $conn->real_escape_string($_POST['id']);
    $conn->query("DELETE FROM shipping_fee WHERE fee_id='$id'");
    goBack('Deleted!', false, 'shipping');
}

if ($action == 'add_sector_shipping') {
    $sector = $conn->real_escape_string($_POST['sector'] ?? '');
    $district = $conn->real_escape_string($_POST['district'] ?? '');
    $province = $conn->real_escape_string($_POST['province'] ?? '');
    $fee = $conn->real_escape_string($_POST['fee'] ?? 0);
    $id = generateId();
    $now = date('Y-m-d H:i:s');
    
    $conn->query("INSERT INTO sector_shipping_fee (fee_id, sector, district, province, fee, register_date) 
                  VALUES ('$id', '$sector', '$district', '$province', '$fee', '$now')");
    
    $redirect = $_POST['redirect'] ?? '?page=admin_manager&manage=shipping';
    header("Location: $redirect");
    exit;
}

if ($action == 'update_sector_shipping') {
    $id = $conn->real_escape_string($_POST['id']);
    $field = $_POST['field'];
    $value = $conn->real_escape_string($_POST['value']);
    
    $allowedFields = ['sector', 'district', 'province', 'fee'];
    if (in_array($field, $allowedFields)) {
        $conn->query("UPDATE sector_shipping_fee SET $field='$value' WHERE fee_id='$id'");
    }
    goBack('Updated!', false, 'shipping');
}

if ($action == 'delete_sector_shipping') {
    $id = $conn->real_escape_string($_POST['id']);
    $conn->query("DELETE FROM sector_shipping_fee WHERE fee_id='$id'");
    goBack('Deleted!', false, 'shipping');
}

if ($action == 'bulk_add_sectors') {
    $province = $conn->real_escape_string($_POST['province'] ?? '');
    $data = $_POST['sectors_data'] ?? '';
    $lines = explode("\n", $data);
    $added = 0;
    $now = date('Y-m-d H:i:s');
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        $parts = explode(',', $line);
        if (count($parts) >= 3) {
            $sector = $conn->real_escape_string(trim($parts[0]));
            $district = $conn->real_escape_string(trim($parts[1]));
            $fee = $conn->real_escape_string(trim($parts[2]));
            $id = generateId();
            if (!empty($sector)) {
                $conn->query("INSERT INTO sector_shipping_fee (fee_id, sector, district, province, fee, register_date) 
                              VALUES ('$id', '$sector', '$district', '$province', '$fee', '$now')");
                $added++;
            }
        }
    }
    
    $redirect = $_POST['redirect'] ?? '?page=admin_manager&manage=shipping';
    header("Location: $redirect&success=" . urlencode("$added sectors imported!"));
    exit;
}

// ============ ORDERS ============
if ($action == 'update_order_status') {
    $id = $conn->real_escape_string($_POST['id']);
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE orders SET status='$status' WHERE order_id='$id'");
    goBack('Order status updated!', false, 'orders');
}

// ============ USERS ============
if ($action == 'update_user_status') {
    $id = $conn->real_escape_string($_POST['id']);
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE user SET status='$status' WHERE user_id='$id'");
    goBack('User status updated!', false, 'users');
}

// ============ CART ITEM UPDATE (for frontend cart) ============
if ($action == 'UPDATE_CART_ITEM') {
    $itemId = $conn->real_escape_string($_POST['item_id']);
    $qty = floatval($_POST['product_quantity']);
    $price = floatval($_POST['price']);
    
    $sql = "UPDATE cart_item SET product_quantity='$qty', price='$price' WHERE item_id='$itemId'";
    if ($conn->query($sql)) {
        echo 'SUCCESS';
    } else {
        echo 'FAILED';
    }
    exit;
}

// If no action matched, return error
jsonResponse(false, [], 'Invalid action: ' . $action);
?>