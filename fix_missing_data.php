<?php
session_start();
include "../../on/on.php";

if (!isset($_SESSION['GBDELIVERING_ADMIN_USER_2021'])) {
    die("Not logged in");
}

echo "<h2>Fixing Missing Data</h2>";

// Fix products without prices (set to 0)
$result = $conn->query("
    SELECT p.product_id, p.product_name 
    FROM product p 
    LEFT JOIN product_price pp ON p.product_id = pp.product_id 
    WHERE pp.product_id IS NULL
");

$count = 0;
while ($row = $result->fetch_assoc()) {
    $pid = $conn->real_escape_string($row['product_id']);
    $conn->query("INSERT INTO product_price (product_id, price) VALUES ('$pid', 0)");
    echo "Added price for: " . htmlspecialchars($row['product_name']) . "<br>";
    $count++;
}
echo "<strong>Fixed $count products without prices</strong><br><br>";

// Fix products without stock (set to 0)
$result = $conn->query("
    SELECT p.product_id, p.product_name 
    FROM product p 
    LEFT JOIN product_stock ps ON p.product_id = ps.product_id 
    WHERE ps.product_id IS NULL
");

$count = 0;
while ($row = $result->fetch_assoc()) {
    $pid = $conn->real_escape_string($row['product_id']);
    $conn->query("INSERT INTO product_stock (product_id, stock_quantity) VALUES ('$pid', 0)");
    echo "Added stock for: " . htmlspecialchars($row['product_name']) . "<br>";
    $count++;
}
echo "<strong>Fixed $count products without stock</strong><br><br>";

echo "<a href='index.php?products'>← Back to Products</a>";
?>