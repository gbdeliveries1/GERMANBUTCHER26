<?php
$db_paths = ['../../on/on.php', '../on/on.php', '../../includes/db.php', '../db_connect.php'];
foreach ($db_paths as $path) { if (file_exists($path)) { require_once $path; break; } }

header('Content-Type: application/json');

function logHistory($conn, $batch_id, $product_id, $field, $old_val, $new_val) {
    $stmt = $conn->prepare("INSERT INTO bulk_edit_history (batch_id, product_id, field_changed, old_value, new_value) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $batch_id, $product_id, $field, $old_val, $new_val);
    $stmt->execute();
}

$action = $_REQUEST['action'] ?? '';

// ==========================================
// 1. LOAD GRID DATA
// ==========================================
if ($action === 'load') {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 50;
    $offset = ($page - 1) * $limit;
    
    $search = $conn->real_escape_string($_GET['search'] ?? '');
    $cat = $conn->real_escape_string($_GET['cat'] ?? '');
    $stockFilter = $_GET['stock'] ?? '';

    $where = ["1=1"];
    if ($search) $where[] = "(p.product_name LIKE '%$search%' OR p.product_id LIKE '%$search%')";
    if ($cat) $where[] = "p.category_id = '$cat'";
    if ($stockFilter === 'in') $where[] = "ps.stock_quantity > 0";
    if ($stockFilter === 'out') $where[] = "(ps.stock_quantity <= 0 OR ps.stock_quantity IS NULL)";

    $whereClause = implode(" AND ", $where);

    // Total Count
    $tc = $conn->query("SELECT COUNT(p.product_id) as c FROM product p LEFT JOIN product_stock ps ON p.product_id = ps.product_id WHERE $whereClause")->fetch_assoc()['c'];

    // Main Query
    $sql = "
        SELECT 
            p.product_id, p.product_name, p.category_id, p.product_minimum_order, p.status,
            pp.price, 
            ps.stock_quantity as stock,
            (SELECT picture FROM product_picture WHERE product_id = p.product_id ORDER BY register_date DESC LIMIT 1) as picture
        FROM product p
        LEFT JOIN product_price pp ON p.product_id = pp.product_id
        LEFT JOIN product_stock ps ON p.product_id = ps.product_id
        WHERE $whereClause
        ORDER BY p.register_date DESC
        LIMIT $offset, $limit
    ";
    
    $result = $conn->query($sql);
    $products = [];
    if($result) { while($row = $result->fetch_assoc()) { $products[] = $row; } }

    echo json_encode(['products' => $products, 'total' => $tc]);
    exit;
}

// ==========================================
// 2. INLINE EDIT (Single Cell)
// ==========================================
if ($action === 'inline_edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $conn->real_escape_string($_POST['id']);
    $field = $_POST['field'];
    $val = $conn->real_escape_string($_POST['val']);
    $batch = 'inline_' . time();

    // Map fields to correct tables
    if (in_array($field, ['product_name', 'category_id', 'product_minimum_order', 'status'])) {
        $old = $conn->query("SELECT $field FROM product WHERE product_id='$id'")->fetch_assoc()[$field] ?? '';
        logHistory($conn, $batch, $id, $field, $old, $val);
        $conn->query("UPDATE product SET $field = '$val' WHERE product_id = '$id'");
    } 
    elseif ($field === 'price') {
        $old = $conn->query("SELECT price FROM product_price WHERE product_id='$id'")->fetch_assoc()['price'] ?? 0;
        logHistory($conn, $batch, $id, 'price', $old, $val);
        $conn->query("INSERT INTO product_price (product_id, price) VALUES ('$id', '$val') ON DUPLICATE KEY UPDATE price = '$val'");
    } 
    elseif ($field === 'stock_quantity') {
        $old = $conn->query("SELECT stock_quantity FROM product_stock WHERE product_id='$id'")->fetch_assoc()['stock_quantity'] ?? 0;
        logHistory($conn, $batch, $id, 'stock', $old, $val);
        $conn->query("INSERT INTO product_stock (product_id, stock_quantity) VALUES ('$id', '$val') ON DUPLICATE KEY UPDATE stock_quantity = '$val'");
    }

    echo json_encode(['status' => 'success']);
    exit;
}

// ==========================================
// 3. BULK ACTIONS
// ==========================================
if ($action === 'bulk_edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['bulk_type'];
    $val = $conn->real_escape_string($_POST['val']);
    $ids = json_decode($_POST['ids'], true);
    $batch = 'bulk_' . time();
    $affected = 0;

    if (!is_array($ids) || empty($ids)) { echo json_encode(['status'=>'error', 'message'=>'No IDs']); exit; }

    foreach ($ids as $id) {
        $id = $conn->real_escape_string($id);

        if ($type === 'price_inc' || $type === 'price_dec') {
            $old = $conn->query("SELECT price FROM product_price WHERE product_id='$id'")->fetch_assoc()['price'] ?? 0;
            $multiplier = ($type === 'price_inc') ? (1 + ($val/100)) : (1 - ($val/100));
            $new = round($old * $multiplier);
            logHistory($conn, $batch, $id, 'price', $old, $new);
            $conn->query("INSERT INTO product_price (product_id, price) VALUES ('$id', '$new') ON DUPLICATE KEY UPDATE price = '$new'");
            $affected++;
        }
        elseif ($type === 'price_flat') {
            $old = $conn->query("SELECT price FROM product_price WHERE product_id='$id'")->fetch_assoc()['price'] ?? 0;
            logHistory($conn, $batch, $id, 'price', $old, $val);
            $conn->query("INSERT INTO product_price (product_id, price) VALUES ('$id', '$val') ON DUPLICATE KEY UPDATE price = '$val'");
            $affected++;
        }
        elseif ($type === 'stock_flat') {
            $old = $conn->query("SELECT stock_quantity FROM product_stock WHERE product_id='$id'")->fetch_assoc()['stock_quantity'] ?? 0;
            logHistory($conn, $batch, $id, 'stock', $old, $val);
            $conn->query("INSERT INTO product_stock (product_id, stock_quantity) VALUES ('$id', '$val') ON DUPLICATE KEY UPDATE stock_quantity = '$val'");
            $affected++;
        }
        elseif ($type === 'category') {
            $old = $conn->query("SELECT category_id FROM product WHERE product_id='$id'")->fetch_assoc()['category_id'] ?? '';
            logHistory($conn, $batch, $id, 'category_id', $old, $val);
            $conn->query("UPDATE product SET category_id = '$val' WHERE product_id = '$id'");
            $affected++;
        }
        elseif ($type === 'delete') {
            // Note: Does not log to history because deleting removes base record.
            $conn->query("DELETE FROM product WHERE product_id = '$id'");
            $conn->query("DELETE FROM product_price WHERE product_id = '$id'");
            $conn->query("DELETE FROM product_stock WHERE product_id = '$id'");
            $affected++;
        }
        elseif ($type === 'duplicate') {
            // Generates a new ID
            $new_id = md5(uniqid(rand(), true));
            $conn->query("INSERT INTO product (product_id, product_name, category_id, product_minimum_order) 
                          SELECT '$new_id', CONCAT(product_name, ' (Copy)'), category_id, product_minimum_order 
                          FROM product WHERE product_id = '$id'");
            
            $conn->query("INSERT INTO product_price (product_id, price) 
                          SELECT '$new_id', price FROM product_price WHERE product_id = '$id'");
                          
            $conn->query("INSERT INTO product_stock (product_id, stock_quantity) 
                          SELECT '$new_id', stock_quantity FROM product_stock WHERE product_id = '$id'");
            $affected++;
        }
    }

    echo json_encode(['status' => 'success', 'affected' => $affected]);
    exit;
}

// ==========================================
// 4. UNDO LAST ACTION
// ==========================================
if ($action === 'undo_last') {
    // Get latest batch_id
    $last = $conn->query("SELECT batch_id FROM bulk_edit_history ORDER BY history_id DESC LIMIT 1");
    if($last && $last->num_rows > 0) {
        $batch = $last->fetch_assoc()['batch_id'];
        
        $hist = $conn->query("SELECT * FROM bulk_edit_history WHERE batch_id = '$batch' ORDER BY history_id DESC");
        while($r = $hist->fetch_assoc()) {
            $id = $r['product_id'];
            $f = $r['field_changed'];
            $old = $conn->real_escape_string($r['old_value']);
            
            if($f === 'price') $conn->query("UPDATE product_price SET price='$old' WHERE product_id='$id'");
            elseif($f === 'stock') $conn->query("UPDATE product_stock SET stock_quantity='$old' WHERE product_id='$id'");
            else $conn->query("UPDATE product SET $f='$old' WHERE product_id='$id'");
        }
        
        // Remove history so it can't be undone twice
        $conn->query("DELETE FROM bulk_edit_history WHERE batch_id = '$batch'");
        echo json_encode(['status'=>'success', 'message'=>"Reverted batch: $batch"]);
    } else {
        echo json_encode(['status'=>'error', 'message'=>'No recent actions to undo.']);
    }
    exit;
}
?>