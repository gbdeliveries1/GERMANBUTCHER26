<?php
$db_paths = ['../../on/on.php', '../on/on.php', '../../includes/db.php', '../db_connect.php'];
foreach ($db_paths as $path) { if (file_exists($path)) { require_once $path; break; } }

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=gbdeliveries_products_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Headers
fputcsv($output, ['Product ID', 'Product Name', 'Category Name', 'Price (RWF)', 'Stock Quantity', 'Min Order', 'Status']);

$sql = "
    SELECT 
        p.product_id, p.product_name, pc.category_name, p.product_minimum_order, p.status,
        pp.price, ps.stock_quantity
    FROM product p
    LEFT JOIN product_category pc ON p.category_id = pc.category_id
    LEFT JOIN product_price pp ON p.product_id = pp.product_id
    LEFT JOIN product_stock ps ON p.product_id = ps.product_id
    ORDER BY p.register_date DESC
";

$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        fputcsv($output, [
            $row['product_id'],
            $row['product_name'],
            $row['category_name'],
            $row['price'] ?? 0,
            $row['stock_quantity'] ?? 0,
            $row['product_minimum_order'],
            $row['status'] == 1 ? 'Active' : 'Hidden'
        ]);
    }
}
fclose($output);
exit;
?>