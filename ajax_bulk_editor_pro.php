<?php
/**
 * AJAX Bulk Editor Pro Handler
 * Enhanced with Product Units System & Sorting
 * 
 * @version 2.5
 * @updated 2026-03-17
 */

error_reporting(0);
ini_set('display_errors', 0);

$action = $_REQUEST['action'] ?? '';

// Only set JSON header if we are NOT exporting a CSV file
if ($action !== 'export_csv') {
    header('Content-Type: application/json');
}

try {
    // 1. Database Connection - Enhanced path detection
    $db_paths = [
        '../../on/on.php',
        '../on/on.php', 
        '../../../on/on.php',
        '../../includes/db.php',
        '../includes/db.php',
        '../../config/database.php',
        '../config/database.php',
        '../../db_connect.php',
        '../db_connect.php',
        'db_connect.php',
    ];
    
    // Also try document root paths
    $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if ($doc_root) {
        array_unshift($db_paths, $doc_root . '/on/on.php');
        array_unshift($db_paths, $doc_root . '/user/on/on.php');
    }
    
    $conn_found = false;
    foreach ($db_paths as $path) { 
        if (file_exists($path)) { 
            require_once $path; 
            $conn_found = true; 
            break; 
        } 
    }
    
    // Check for alternative variable names
    if (!isset($conn) || $conn === null) {
        if (isset($connection)) $conn = $connection;
        elseif (isset($db)) $conn = $db;
        elseif (isset($mysqli)) $conn = $mysqli;
        elseif (isset($link)) $conn = $link;
    }
    
    if (!$conn_found || !isset($conn) || $conn === null) {
        throw new Exception("Database connection file not found.");
    }
    
    if ($conn->connect_error) {
        throw new Exception("Database connection error: " . $conn->connect_error);
    }

    mysqli_report(MYSQLI_REPORT_OFF);

    $current_user = 'gbdeliveries1';

    // 2. Auto-Heal Database Schema & PERFORMANCE INDEXES
    if (!function_exists('addColumnIfMissing')) {
        function addColumnIfMissing($conn, $table, $column, $definition) {
            $res = @$conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
            if ($res && $res->num_rows == 0) @$conn->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    }

    if (!function_exists('fixColumnType')) {
        function fixColumnType($conn, $table, $column, $definition) {
            $res = @$conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();
                if (strpos(strtolower($row['Type']), 'int') !== false) {
                    @$conn->query("ALTER TABLE `$table` MODIFY COLUMN `$column` $definition");
                }
            }
        }
    }

    if (!function_exists('addIndexIfMissing')) {
        function addIndexIfMissing($conn, $table, $index_name, $columns) {
            $res = @$conn->query("SHOW INDEX FROM `$table` WHERE Key_name = '$index_name'");
            if ($res && $res->num_rows == 0) {
                @$conn->query("ALTER TABLE `$table` ADD INDEX `$index_name` ($columns)");
            }
        }
    }
    
    // Schema Upgrades
    addColumnIfMissing($conn, 'product', 'status', 'TINYINT(1) DEFAULT 1');
    addColumnIfMissing($conn, 'product', 'sku', 'VARCHAR(100) DEFAULT NULL');
    addColumnIfMissing($conn, 'product', 'is_featured', 'TINYINT(1) DEFAULT 0');
    addColumnIfMissing($conn, 'product', 'visibility', "VARCHAR(50) DEFAULT 'visible'");
    addColumnIfMissing($conn, 'product', 'weight', 'DECIMAL(10,2) DEFAULT 0.00');
    addColumnIfMissing($conn, 'product', 'short_description', 'TEXT');
    
    // UUID / Hash support for subcategories
    addColumnIfMissing($conn, 'product', 'sub_category_id', 'VARCHAR(100) DEFAULT NULL');
    fixColumnType($conn, 'product', 'sub_category_id', 'VARCHAR(100) DEFAULT NULL');

    // New Fields: Minimum Order & Units (default to 'piece')
    addColumnIfMissing($conn, 'product', 'minimum_order', 'DECIMAL(10,2) DEFAULT 1.00');
    addColumnIfMissing($conn, 'product', 'units', "VARCHAR(50) DEFAULT 'piece'");

    // Upgrade Stock to support Decimal numbers
    fixColumnType($conn, 'product_stock', 'stock_quantity', 'DECIMAL(10,2) DEFAULT 0.00');

    addColumnIfMissing($conn, 'product', 'tags', 'TEXT'); 
    addColumnIfMissing($conn, 'product', 'updated_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'); 
    addColumnIfMissing($conn, 'product_price', 'sale_price', 'DECIMAL(10,2) DEFAULT NULL');

    // Performance Indexes
    addIndexIfMissing($conn, 'product', 'idx_product_status', 'status');
    addIndexIfMissing($conn, 'product', 'idx_product_category', 'category_id');
    addIndexIfMissing($conn, 'product', 'idx_product_subcat', 'sub_category_id');
    addIndexIfMissing($conn, 'product', 'idx_product_sku', 'sku');
    addIndexIfMissing($conn, 'product_price', 'idx_pp_product_id', 'product_id');
    addIndexIfMissing($conn, 'product_stock', 'idx_ps_product_id', 'product_id');
    addIndexIfMissing($conn, 'product_picture', 'idx_pic_pid_reg', 'product_id, register_date');

    @$conn->query("CREATE TABLE IF NOT EXISTS bulk_filter_presets (
        preset_id INT AUTO_INCREMENT PRIMARY KEY,
        preset_name VARCHAR(100) NOT NULL,
        filter_data TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    @$conn->query("CREATE TABLE IF NOT EXISTS bulk_edit_history (
        history_id INT AUTO_INCREMENT PRIMARY KEY,
        admin_user VARCHAR(100) NOT NULL,
        action_type VARCHAR(50) NOT NULL,
        action_detail VARCHAR(100) NOT NULL,
        affected_rows INT NOT NULL,
        rollback_data LONGTEXT,
        can_rollback TINYINT(1) DEFAULT 1,
        is_rolled_back TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Database Helpers
    if (!function_exists('safeUpsert')) {
        function safeUpsert($conn, $table, $product_id, $field, $val) {
            $check = $conn->query("SELECT * FROM `$table` WHERE product_id = '$product_id'");
            if ($check && $check->num_rows > 0) {
                if (!$conn->query("UPDATE `$table` SET `$field` = $val WHERE product_id = '$product_id'")) throw new Exception($conn->error);
            } else {
                $pk_res = $conn->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
                $pk_col = ($pk_res && $pk_res->num_rows > 0) ? $pk_res->fetch_assoc()['Column_name'] : false;
                $is_auto = false;
                if ($pk_col) {
                    $col_res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$pk_col'");
                    if ($col_res && $col_res->num_rows > 0) {
                        if (strpos($col_res->fetch_assoc()['Extra'], 'auto_increment') !== false) $is_auto = true;
                    }
                }
                if ($pk_col && $pk_col !== 'product_id' && !$is_auto) {
                    $new_pk = md5(uniqid(rand(), true));
                    $sql = "INSERT INTO `$table` (`$pk_col`, product_id, `$field`) VALUES ('$new_pk', '$product_id', $val)";
                } else {
                    $sql = "INSERT INTO `$table` (product_id, `$field`) VALUES ('$product_id', $val)";
                }
                if (!$conn->query($sql)) throw new Exception($conn->error);
            }
        }
    }

    if (!function_exists('getOldVal')) {
        function getOldVal($conn, $table, $field, $id) {
            $res = $conn->query("SELECT `$field` FROM `$table` WHERE product_id = '$id'");
            return ($res && $res->num_rows > 0) ? $res->fetch_assoc()[$field] : null;
        }
    }

    if (!function_exists('buildFilterWhere')) {
        function buildFilterWhere($conn) {
            $where = ["1=1"];
            $search = $conn->real_escape_string($_REQUEST['search'] ?? '');
            $exact = ($_REQUEST['exact_match'] ?? 'false') === 'true';
            if ($search) {
                if ($exact) $where[] = "(p.product_name = '$search' OR p.sku = '$search' OR p.product_id = '$search')";
                else $where[] = "(p.product_name LIKE '%$search%' OR p.sku LIKE '%$search%' OR p.product_id LIKE '%$search%')";
            }
            if (!empty($_REQUEST['cat'])) $where[] = "p.category_id = '" . $conn->real_escape_string($_REQUEST['cat']) . "'";
            if (!empty($_REQUEST['subcat'])) $where[] = "p.sub_category_id = '" . $conn->real_escape_string($_REQUEST['subcat']) . "'";
            if (isset($_REQUEST['status']) && $_REQUEST['status'] !== '') $where[] = "p.status = '" . (int)$_REQUEST['status'] . "'";
            if (isset($_REQUEST['visibility']) && $_REQUEST['visibility'] !== '') $where[] = "p.visibility = '" . $conn->real_escape_string($_REQUEST['visibility']) . "'";
            // Support both 'feat' and 'featured' parameter names
            $feat = $_REQUEST['feat'] ?? $_REQUEST['featured'] ?? null;
            if (isset($feat) && $feat !== '') $where[] = "p.is_featured = '" . (int)$feat . "'";
            $stock = $_REQUEST['stock'] ?? '';
            if ($stock === 'in') $where[] = "ps.stock_quantity > 0";
            if ($stock === 'out') $where[] = "(ps.stock_quantity <= 0 OR ps.stock_quantity IS NULL)";
            if (!empty($_REQUEST['price_min'])) $where[] = "pp.price >= " . (float)$_REQUEST['price_min'];
            if (!empty($_REQUEST['price_max'])) $where[] = "pp.price <= " . (float)$_REQUEST['price_max'];
            if (!empty($_REQUEST['date_add_from'])) $where[] = "DATE(p.register_date) >= '" . $conn->real_escape_string($_REQUEST['date_add_from']) . "'";
            if (!empty($_REQUEST['date_add_to'])) $where[] = "DATE(p.register_date) <= '" . $conn->real_escape_string($_REQUEST['date_add_to']) . "'";
            if (!empty($_REQUEST['date_up_from'])) $where[] = "DATE(p.updated_at) >= '" . $conn->real_escape_string($_REQUEST['date_up_from']) . "'";
            if (!empty($_REQUEST['date_up_to'])) $where[] = "DATE(p.updated_at) <= '" . $conn->real_escape_string($_REQUEST['date_up_to']) . "'";
            return implode(" AND ", $where);
        }
    }

    // ==========================================
    // DATA LOAD (GRID) - Enhanced with Sorting
    // ==========================================
    if ($action === 'load') {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit_param = (int)($_GET['limit'] ?? 50);
        $limit = in_array($limit_param, [25, 50, 100, 250, 500]) ? $limit_param : 50;
        $offset = ($page - 1) * $limit;
        
        $whereClause = buildFilterWhere($conn);
        
        // Sorting support
        $sort_by = $_GET['sort_by'] ?? 'register_date';
        $sort_dir = strtoupper($_GET['sort_dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        
        // Whitelist sort columns for security
        $allowed_sorts = [
            'product_id', 'product_name', 'sku', 'price', 'stock', 
            'register_date', 'updated_at', 'category_id', 'status', 
            'units', 'minimum_order', 'visibility', 'is_featured'
        ];
        if (!in_array($sort_by, $allowed_sorts)) $sort_by = 'register_date';
        
        // Map sort column to actual table.column
        $sort_column = 'p.register_date';
        if ($sort_by === 'price') $sort_column = 'pp.price';
        elseif ($sort_by === 'stock' || $sort_by === 'stock_quantity') $sort_column = 'ps.stock_quantity';
        elseif (in_array($sort_by, ['product_id', 'product_name', 'sku', 'category_id', 'status', 'units', 'register_date', 'updated_at', 'minimum_order', 'visibility', 'is_featured'])) {
            $sort_column = "p.$sort_by";
        }
        
        $sql = "SELECT p.product_id, p.product_name, p.category_id, p.sub_category_id, p.status, 
                p.sku, p.is_featured, p.visibility, p.weight, p.short_description, p.tags, p.register_date, p.updated_at,
                p.minimum_order, p.units,
                pp.price, pp.sale_price, ps.stock_quantity as stock,
                (SELECT picture FROM product_picture WHERE product_id = p.product_id ORDER BY register_date DESC LIMIT 1) as picture
            FROM product p 
            LEFT JOIN product_price pp ON p.product_id = pp.product_id 
            LEFT JOIN product_stock ps ON p.product_id = ps.product_id
            WHERE $whereClause 
            ORDER BY $sort_column $sort_dir 
            LIMIT $offset, $limit";
            
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception("Query error: " . $conn->error);
        }
        
        $products = []; 
        while($row = $result->fetch_assoc()) {
            // Set default unit if empty
            if (empty($row['units'])) {
                $row['units'] = 'piece';
            }
            $products[] = $row;
        }
        
        $tc_res = $conn->query("SELECT COUNT(p.product_id) as c FROM product p LEFT JOIN product_stock ps ON p.product_id = ps.product_id LEFT JOIN product_price pp ON p.product_id = pp.product_id WHERE $whereClause");
        $tc = $tc_res ? $tc_res->fetch_assoc()['c'] : 0;
        
        echo json_encode(['status' => 'success', 'products' => $products, 'total' => (int)$tc]); 
        exit;
    }

    // ==========================================
    // TRANSACTION-SAFE BULK ACTIONS + HISTORY LOG
    // ==========================================
    if ($action === 'bulk_process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $bulk_action = $_POST['bulk_action'];
        $val = $conn->real_escape_string($_POST['val'] ?? '');
        $apply_all = ($_POST['apply_all_filtered'] ?? 'false') === 'true';
        
        $target_ids = [];
        if ($apply_all) {
            $whereClause = buildFilterWhere($conn);
            $q = $conn->query("SELECT p.product_id FROM product p LEFT JOIN product_stock ps ON p.product_id = ps.product_id LEFT JOIN product_price pp ON p.product_id = pp.product_id WHERE $whereClause");
            if($q) while($r = $q->fetch_assoc()) $target_ids[] = $r['product_id'];
        } else {
            $target_ids = json_decode($_POST['ids'] ?? '[]');
        }

        if (empty($target_ids)) throw new Exception("No products selected.");

        $rollback_batch = [];
        $can_rollback = !in_array($bulk_action, ['delete', 'duplicate', 'assign_placeholder']);

        $conn->begin_transaction();
        try {
            $affected = 0;
            foreach ($target_ids as $id) {
                $id = $conn->real_escape_string($id);

                if (strpos($bulk_action, 'price_') !== false) {
                    $old_price = getOldVal($conn, 'product_price', 'price', $id) ?? 0;
                    if($can_rollback) $rollback_batch[] = ['id'=>$id, 't'=>'product_price', 'f'=>'price', 'v'=>$old_price];
                    
                    if ($bulk_action === 'price_inc_perc') safeUpsert($conn, 'product_price', $id, 'price', round($old_price * (1 + (float)$val / 100), 2));
                    elseif ($bulk_action === 'price_dec_perc') safeUpsert($conn, 'product_price', $id, 'price', round($old_price * (1 - (float)$val / 100), 2));
                    elseif ($bulk_action === 'price_exact') safeUpsert($conn, 'product_price', $id, 'price', (float)$val);
                } 
                elseif (strpos($bulk_action, 'stock_') !== false) {
                    $old_stk = getOldVal($conn, 'product_stock', 'stock_quantity', $id) ?? 0;
                    if($can_rollback) $rollback_batch[] = ['id'=>$id, 't'=>'product_stock', 'f'=>'stock_quantity', 'v'=>$old_stk];
                    
                    if ($bulk_action === 'stock_exact') safeUpsert($conn, 'product_stock', $id, 'stock_quantity', (float)$val);
                    elseif ($bulk_action === 'stock_inc') safeUpsert($conn, 'product_stock', $id, 'stock_quantity', $old_stk + (float)$val);
                    elseif ($bulk_action === 'stock_dec') safeUpsert($conn, 'product_stock', $id, 'stock_quantity', max(0, $old_stk - (float)$val));
                }
                elseif ($bulk_action === 'cat_change') { 
                    if($can_rollback) {
                        $rollback_batch[] = ['id'=>$id, 't'=>'product', 'f'=>'category_id', 'v'=>getOldVal($conn, 'product', 'category_id', $id)];
                        $rollback_batch[] = ['id'=>$id, 't'=>'product', 'f'=>'sub_category_id', 'v'=>getOldVal($conn, 'product', 'sub_category_id', $id)];
                    }
                    $conn->query("UPDATE product SET category_id='$val', sub_category_id=NULL WHERE product_id='$id'"); 
                }
                elseif ($bulk_action === 'subcat_change') { 
                    if($can_rollback) {
                        $rollback_batch[] = ['id'=>$id, 't'=>'product', 'f'=>'category_id', 'v'=>getOldVal($conn, 'product', 'category_id', $id)];
                        $rollback_batch[] = ['id'=>$id, 't'=>'product', 'f'=>'sub_category_id', 'v'=>getOldVal($conn, 'product', 'sub_category_id', $id)];
                    }
                    $conn->query("UPDATE product SET sub_category_id='$val', category_id=(SELECT category_id FROM product_sub_category WHERE sub_category_id='$val' LIMIT 1) WHERE product_id='$id'"); 
                }
                elseif ($bulk_action === 'status_enable' || $bulk_action === 'status_disable') { 
                    if($can_rollback) $rollback_batch[] = ['id'=>$id, 't'=>'product', 'f'=>'status', 'v'=>getOldVal($conn, 'product', 'status', $id)];
                    $conn->query("UPDATE product SET status=".($bulk_action==='status_enable'?1:0)." WHERE product_id='$id'"); 
                }
                elseif ($bulk_action === 'vis_visible' || $bulk_action === 'vis_hidden') {
                    $vis_val = ($bulk_action === 'vis_visible') ? 'visible' : 'hidden';
                    if($can_rollback) $rollback_batch[] = ['id'=>$id, 't'=>'product', 'f'=>'visibility', 'v'=>getOldVal($conn, 'product', 'visibility', $id)];
                    $conn->query("UPDATE product SET visibility='$vis_val' WHERE product_id='$id'"); 
                }
                elseif ($bulk_action === 'feat_mark' || $bulk_action === 'feat_unmark') { 
                    if($can_rollback) $rollback_batch[] = ['id'=>$id, 't'=>'product', 'f'=>'is_featured', 'v'=>getOldVal($conn, 'product', 'is_featured', $id)];
                    $conn->query("UPDATE product SET is_featured=".($bulk_action==='feat_mark'?1:0)." WHERE product_id='$id'"); 
                }
                elseif ($bulk_action === 'tags_add' || $bulk_action === 'tags_remove') {
                    $curr = getOldVal($conn, 'product', 'tags', $id) ?? '';
                    if($can_rollback) $rollback_batch[] = ['id'=>$id, 't'=>'product', 'f'=>'tags', 'v'=>$curr];
                    
                    $tags_arr = array_filter(array_map('trim', explode(',', $curr)));
                    $input_tags = array_filter(array_map('trim', explode(',', $val)));
                    if ($bulk_action === 'tags_add') $tags_arr = array_unique(array_merge($tags_arr, $input_tags));
                    else $tags_arr = array_diff($tags_arr, $input_tags);
                    
                    $conn->query("UPDATE product SET tags='".$conn->real_escape_string(implode(', ', $tags_arr))."' WHERE product_id='$id'");
                }
                // [NEW] Bulk unit change
                elseif ($bulk_action === 'unit_change') {
                    if($can_rollback) $rollback_batch[] = ['id'=>$id, 't'=>'product', 'f'=>'units', 'v'=>getOldVal($conn, 'product', 'units', $id)];
                    $conn->query("UPDATE product SET units='$val' WHERE product_id='$id'"); 
                }
                elseif ($bulk_action === 'assign_placeholder') {
                    $pic_check = $conn->query("SELECT picture_id FROM product_picture WHERE product_id='$id'");
                    if ($pic_check && $pic_check->num_rows == 0) {
                        $pic_id = md5(uniqid(rand(), true));
                        $conn->query("INSERT INTO product_picture (picture_id, product_id, picture, register_date) VALUES ('$pic_id', '$id', 'no-image.png', NOW())");
                    }
                }
                elseif ($bulk_action === 'delete') {
                    $conn->query("DELETE FROM product WHERE product_id='$id'");
                    $conn->query("DELETE FROM product_price WHERE product_id='$id'");
                    $conn->query("DELETE FROM product_stock WHERE product_id='$id'");
                    $conn->query("DELETE FROM product_picture WHERE product_id='$id'");
                }
                elseif ($bulk_action === 'duplicate') {
                    $new_id = md5(uniqid(rand(), true));
                    $conn->query("INSERT INTO product (product_id, product_name, category_id, sub_category_id, status, sku, is_featured, visibility, weight, short_description, tags, minimum_order, units, register_date) 
                                  SELECT '$new_id', CONCAT(product_name, ' (Copy)'), category_id, sub_category_id, 0, sku, is_featured, visibility, weight, short_description, tags, minimum_order, units, NOW() FROM product WHERE product_id='$id'");
                    $conn->query("INSERT INTO product_price (product_id, price, sale_price) SELECT '$new_id', price, sale_price FROM product_price WHERE product_id='$id'");
                    $conn->query("INSERT INTO product_stock (product_id, stock_quantity) SELECT '$new_id', stock_quantity FROM product_stock WHERE product_id='$id'");
                }
                $affected++;
            }

            $rb_json = $can_rollback ? $conn->real_escape_string(json_encode($rollback_batch)) : '';
            $rb_flag = $can_rollback ? 1 : 0;
            $conn->query("INSERT INTO bulk_edit_history (admin_user, action_type, action_detail, affected_rows, rollback_data, can_rollback) 
                          VALUES ('$current_user', 'bulk_process', '$bulk_action', $affected, '$rb_json', $rb_flag)");

            $conn->commit();
            echo json_encode(['status' => 'success', 'affected' => $affected]);
        } catch (Exception $e) {
            $conn->rollback();
            throw new Exception("Bulk Transaction Failed: " . $e->getMessage());
        }
        exit;
    }

    // ==========================================
    // HISTORY & ROLLBACK ENGINE
    // ==========================================
    if ($action === 'load_history') {
        $res = $conn->query("SELECT history_id, admin_user, action_type, action_detail, affected_rows, created_at, can_rollback, is_rolled_back FROM bulk_edit_history ORDER BY history_id DESC LIMIT 50");
        $history = []; if($res) while($r = $res->fetch_assoc()) $history[] = $r;
        echo json_encode(['status' => 'success', 'history' => $history]); exit;
    }

    if ($action === 'rollback_history' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $hid = (int)$_POST['history_id'];
        $check = $conn->query("SELECT * FROM bulk_edit_history WHERE history_id = $hid LIMIT 1");
        if (!$check || $check->num_rows == 0) throw new Exception("History record not found.");
        
        $row = $check->fetch_assoc();
        if ($row['is_rolled_back'] == 1) throw new Exception("Already rolled back.");
        if ($row['can_rollback'] == 0 || empty($row['rollback_data'])) throw new Exception("This action cannot be rolled back.");

        $rb_data = json_decode($row['rollback_data'], true);
        if(!is_array($rb_data)) throw new Exception("Corrupt rollback data.");

        $conn->begin_transaction();
        try {
            foreach($rb_data as $op) {
                $id = $conn->real_escape_string($op['id']);
                $t = $conn->real_escape_string($op['t']);
                $f = $conn->real_escape_string($op['f']);
                $v = $op['v'] === null ? "NULL" : "'" . $conn->real_escape_string($op['v']) . "'";
                
                if (in_array($t, ['product_price', 'product_stock'])) {
                    safeUpsert($conn, $t, $id, $f, $v === "NULL" ? 'NULL' : str_replace("'", "", $v));
                } else {
                    $conn->query("UPDATE `$t` SET `$f` = $v WHERE product_id = '$id'");
                }
            }
            $conn->query("UPDATE bulk_edit_history SET is_rolled_back = 1 WHERE history_id = $hid");
            
            $conn->query("INSERT INTO bulk_edit_history (admin_user, action_type, action_detail, affected_rows, can_rollback) 
                          VALUES ('$current_user', 'rollback', 'Rolled back log #$hid', {$row['affected_rows']}, 0)");

            $conn->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $conn->rollback();
            throw new Exception("Rollback Failed: " . $e->getMessage());
        }
        exit;
    }

    // ==========================================
    // INLINE EDITS + LOGGING
    // ==========================================
    if ($action === 'inline_edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $conn->real_escape_string($_POST['id']); 
        $field = $_POST['field']; 
        $val = $_POST['val'];
        
        $t = 'product';
        if (in_array($field, ['price', 'sale_price'])) $t = 'product_price';
        elseif ($field === 'stock_quantity') $t = 'product_stock';
        
        $old_val = getOldVal($conn, $t, $field, $id);
        $rb_json = $conn->real_escape_string(json_encode([['id'=>$id, 't'=>$t, 'f'=>$field, 'v'=>$old_val]]));

        if ($t === 'product') { 
            $conn->query("UPDATE product SET `$field` = '" . $conn->real_escape_string($val) . "' WHERE product_id = '$id'"); 
        } else { 
            safeUpsert($conn, $t, $id, $field, empty($val) && $val!=='0' ? "NULL" : "'" . $conn->real_escape_string($val) . "'"); 
        }

        $conn->query("INSERT INTO bulk_edit_history (admin_user, action_type, action_detail, affected_rows, rollback_data, can_rollback) 
                      VALUES ('$current_user', 'inline_edit', '$field', 1, '$rb_json', 1)");

        echo json_encode(['status' => 'success']); 
        exit;
    }

    // ==========================================
    // CSV EXPORT (includes units)
    // ==========================================
    if ($action === 'export_csv' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $type = $_POST['export_type'] ?? 'filtered';
        $cols = json_decode($_POST['export_cols'] ?? '[]', true);
        if(empty($cols)) $cols = ['product_id', 'product_name', 'sku', 'price', 'stock', 'units'];

        $target_ids = [];
        if ($type === 'selected') {
            $target_ids = json_decode($_POST['ids'] ?? '[]');
            if(empty($target_ids)) die("No products selected.");
            $id_list = implode("','", array_map([$conn, 'real_escape_string'], $target_ids));
            $whereClause = "p.product_id IN ('$id_list')";
        } else {
            $whereClause = buildFilterWhere($conn);
        }

        $sql = "SELECT p.*, pp.price, pp.sale_price, ps.stock_quantity as stock 
                FROM product p LEFT JOIN product_price pp ON p.product_id = pp.product_id LEFT JOIN product_stock ps ON p.product_id = ps.product_id 
                WHERE $whereClause ORDER BY p.register_date DESC";
        $result = $conn->query($sql);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="products_export_' . date('Ymd_His') . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, $cols);

        $row_count = 0;
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Set default unit if empty
                if (empty($row['units'])) $row['units'] = 'piece';
                
                $line = [];
                foreach ($cols as $c) { $line[] = $row[$c] ?? ''; }
                fputcsv($output, $line);
                $row_count++;
            }
        }
        
        $conn->query("INSERT INTO bulk_edit_history (admin_user, action_type, action_detail, affected_rows, can_rollback) VALUES ('$current_user', 'export', '$type', $row_count, 0)");
        
        fclose($output);
        exit;
    }

    // ==========================================
    // CSV IMPORT UPLOAD & MAP
    // ==========================================
    if ($action === 'import_csv_upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) throw new Exception("No valid CSV file uploaded.");
        $file = $_FILES['csv_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') throw new Exception("Only .csv files are allowed.");

        $upload_dir = '../../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $tmp_name = 'tmp_import_' . time() . '_' . rand(100,999) . '.csv';
        $target = $upload_dir . $tmp_name;

        if (!move_uploaded_file($file['tmp_name'], $target)) throw new Exception("Failed to save temp CSV.");

        $handle = fopen($target, "r");
        $headers = fgetcsv($handle);
        $sample_rows = [];
        for ($i=0; $i<3; $i++) { $row = fgetcsv($handle); if($row) $sample_rows[] = $row; }
        fclose($handle);

        echo json_encode(['status' => 'success', 'tmp_file' => $tmp_name, 'headers' => $headers, 'samples' => $sample_rows]);
        exit;
    }

    // ==========================================
    // CSV IMPORT PROCESS (includes units)
    // ==========================================
    if ($action === 'import_csv_process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $tmp_file = $_POST['tmp_file'];
        $match_key = $_POST['match_key']; 
        $mapping = json_decode($_POST['mapping'], true); 
        
        $target = '../../uploads/' . basename($tmp_file);
        if(!file_exists($target)) throw new Exception("Temp CSV not found.");

        $handle = fopen($target, "r");
        fgetcsv($handle); 

        $stats = ['created' => 0, 'updated' => 0, 'failed' => 0];
        $cat_res = $conn->query("SELECT category_id FROM product_category LIMIT 1");
        $default_cat = ($cat_res && $cat_res->num_rows > 0) ? $cat_res->fetch_assoc()['category_id'] : '';

        $conn->begin_transaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $data = [];
                foreach ($mapping as $csv_idx => $db_col) {
                    if ($db_col !== '' && isset($row[$csv_idx])) {
                        $data[$db_col] = trim($row[$csv_idx]);
                    }
                }
                if (empty($data)) continue;

                $lookup_val = $conn->real_escape_string($data[$match_key] ?? '');
                if ($lookup_val === '') { $stats['failed']++; continue; }

                $check = $conn->query("SELECT product_id FROM product WHERE `$match_key` = '$lookup_val' LIMIT 1");
                $exists = ($check && $check->num_rows > 0);
                
                $pid = $exists ? $check->fetch_assoc()['product_id'] : md5(uniqid(rand(), true));

                $prod_data = []; $price_val = null; $sale_val = null; $stock_val = null;
                foreach ($data as $col => $val) {
                    if ($col === 'price') $price_val = $val;
                    elseif ($col === 'sale_price') $sale_val = $val;
                    elseif ($col === 'stock_quantity') $stock_val = $val;
                    elseif ($col !== 'product_id') $prod_data[$col] = $conn->real_escape_string($val);
                }

                if ($exists) {
                    if (!empty($prod_data)) {
                        $sets = []; foreach($prod_data as $c=>$v) $sets[] = "`$c`='$v'";
                        $conn->query("UPDATE product SET " . implode(', ', $sets) . " WHERE product_id='$pid'");
                    }
                    if ($price_val !== null) safeUpsert($conn, 'product_price', $pid, 'price', (float)$price_val);
                    if ($sale_val !== null) safeUpsert($conn, 'product_price', $pid, 'sale_price', empty($sale_val) ? "NULL" : "'".(float)$sale_val."'");
                    if ($stock_val !== null) safeUpsert($conn, 'product_stock', $pid, 'stock_quantity', (float)$stock_val);
                    $stats['updated']++;
                } else {
                    if (!isset($prod_data['product_name'])) $prod_data['product_name'] = 'Imported Product';
                    if (!isset($prod_data['category_id'])) $prod_data['category_id'] = $default_cat;
                    if (!isset($prod_data['units'])) $prod_data['units'] = 'piece'; // Default unit
                    if ($match_key === 'sku') $prod_data['sku'] = $lookup_val;

                    $cols = ['product_id', 'register_date']; $vals = ["'$pid'", "NOW()"];
                    foreach ($prod_data as $c => $v) { $cols[] = "`$c`"; $vals[] = "'$v'"; }
                    $conn->query("INSERT INTO product (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ")");
                    
                    if ($price_val !== null) safeUpsert($conn, 'product_price', $pid, 'price', (float)$price_val);
                    if ($sale_val !== null) safeUpsert($conn, 'product_price', $pid, 'sale_price', empty($sale_val) ? "NULL" : "'".(float)$sale_val."'");
                    if ($stock_val !== null) safeUpsert($conn, 'product_stock', $pid, 'stock_quantity', (float)$stock_val);
                    $stats['created']++;
                }
            }
            
            $conn->query("INSERT INTO bulk_edit_history (admin_user, action_type, action_detail, affected_rows, can_rollback) VALUES ('$current_user', 'import', 'CSV Import', ".($stats['created']+$stats['updated']).", 0)");

            $conn->commit();
            fclose($handle);
            @unlink($target);
            echo json_encode(['status' => 'success', 'stats' => $stats]);
        } catch (Exception $e) {
            $conn->rollback();
            fclose($handle);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ==========================================
    // MEDIA & UTILITIES
    // ==========================================
    if ($action === 'upload_image' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $product_id = $conn->real_escape_string($_POST['product_id']);
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) throw new Exception("No valid image file received.");
        $file = $_FILES['image']; $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) throw new Exception("Invalid file extension.");
        if (function_exists('finfo_open')) { 
            $finfo = finfo_open(FILEINFO_MIME_TYPE); 
            $mime = finfo_file($finfo, $file['tmp_name']); 
            finfo_close($finfo); 
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) throw new Exception("Invalid MIME type detected."); 
        }
        $upload_dir = '../../uploads/'; 
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $new_filename = 'prod_' . $product_id . '_' . time() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
            $old_res = $conn->query("SELECT picture FROM product_picture WHERE product_id='$product_id'");
            if ($old_res) while($row = $old_res->fetch_assoc()) { 
                $old_file = $upload_dir . $row['picture']; 
                if(!empty($row['picture']) && file_exists($old_file) && is_file($old_file)) @unlink($old_file); 
            }
            $conn->query("DELETE FROM product_picture WHERE product_id='$product_id'");
            $pic_id = md5(uniqid(rand(), true));
            if ($conn->query("INSERT INTO product_picture (picture_id, product_id, picture, register_date) VALUES ('$pic_id', '$product_id', '$new_filename', NOW())")) {
                $conn->query("INSERT INTO bulk_edit_history (admin_user, action_type, action_detail, affected_rows, can_rollback) VALUES ('$current_user', 'media', 'upload', 1, 0)");
                echo json_encode(['status' => 'success', 'filename' => $new_filename]);
            } else throw new Exception("Failed to insert picture.");
        } else throw new Exception("Failed to save the physical file.");
        exit;
    }

    if ($action === 'remove_image' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $product_id = $conn->real_escape_string($_POST['product_id']);
        $old_res = $conn->query("SELECT picture FROM product_picture WHERE product_id='$product_id'");
        if ($old_res) while($row = $old_res->fetch_assoc()) { 
            $old_file = '../../uploads/' . $row['picture']; 
            if(!empty($row['picture']) && file_exists($old_file) && is_file($old_file)) @unlink($old_file); 
        }
        $conn->query("DELETE FROM product_picture WHERE product_id='$product_id'");
        $conn->query("INSERT INTO bulk_edit_history (admin_user, action_type, action_detail, affected_rows, can_rollback) VALUES ('$current_user', 'media', 'remove', 1, 0)");
        echo json_encode(['status' => 'success']); 
        exit;
    }

    // Inline Row Deletion
    if ($action === 'delete_product' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $conn->real_escape_string($_POST['product_id']);
        $conn->query("DELETE FROM product WHERE product_id='$id'");
        $conn->query("DELETE FROM product_price WHERE product_id='$id'");
        $conn->query("DELETE FROM product_stock WHERE product_id='$id'");
        $conn->query("DELETE FROM product_picture WHERE product_id='$id'");
        $conn->query("INSERT INTO bulk_edit_history (admin_user, action_type, action_detail, affected_rows, can_rollback) VALUES ('$current_user', 'delete', 'Single Product', 1, 0)");
        echo json_encode(['status' => 'success']); 
        exit;
    }

    // Add New Product (with default unit)
    if ($action === 'add_product') {
        $new_id = md5(uniqid(rand(), true)); 
        $cat_res = $conn->query("SELECT category_id FROM product_category LIMIT 1"); 
        $cat_id = ($cat_res && $cat_res->num_rows > 0) ? $cat_res->fetch_assoc()['category_id'] : '';
        if (!$conn->query("INSERT INTO product (product_id, product_name, category_id, status, units, register_date) VALUES ('$new_id', 'New Product Draft', '$cat_id', 0, 'piece', NOW())")) throw new Exception($conn->error);
        $conn->query("INSERT INTO bulk_edit_history (admin_user, action_type, action_detail, affected_rows, can_rollback) VALUES ('$current_user', 'add_product', 'draft', 1, 0)");
        echo json_encode(['status' => 'success']); 
        exit;
    }

    if ($action === 'get_subcats') {
        $cat_id = $conn->real_escape_string($_GET['cat_id']);
        $res = $conn->query("SELECT sub_category_id, sub_category_name FROM product_sub_category WHERE category_id = '$cat_id'");
        $subcats = []; if($res) while($r = $res->fetch_assoc()) $subcats[] = $r;
        echo json_encode(['status' => 'success', 'subcats' => $subcats]); 
        exit;
    }

    if ($action === 'save_preset') {
        $name = $conn->real_escape_string($_POST['preset_name']); 
        $data = $conn->real_escape_string($_POST['filter_data']);
        $conn->query("INSERT INTO bulk_filter_presets (preset_name, filter_data) VALUES ('$name', '$data')");
        echo json_encode(['status' => 'success']); 
        exit;
    }

    if ($action === 'load_presets') {
        $res = $conn->query("SELECT * FROM bulk_filter_presets ORDER BY preset_name ASC");
        $presets = []; if($res) while($r = $res->fetch_assoc()) $presets[] = $r;
        echo json_encode(['status' => 'success', 'presets' => $presets]); 
        exit;
    }

    // No action matched
    if (empty($action)) {
        echo json_encode(['status' => 'error', 'message' => 'No action specified']);
        exit;
    }

} catch (Exception $e) {
    http_response_code(200);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
} catch (Error $e) {
    http_response_code(200);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>