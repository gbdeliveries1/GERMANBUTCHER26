<?php
session_start();
include "../../on/on.php";

echo "<h2>Table Structure Check</h2>";

// Check product_price structure
echo "<h3>product_price table:</h3>";
$result = $conn->query("SHOW CREATE TABLE product_price");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<pre>" . htmlspecialchars($row['Create Table']) . "</pre>";
}

// Check product_stock structure
echo "<h3>product_stock table:</h3>";
$result = $conn->query("SHOW CREATE TABLE product_stock");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<pre>" . htmlspecialchars($row['Create Table']) . "</pre>";
}

// Check for empty product_ids in price table
echo "<h3>Empty product_ids in product_price:</h3>";
$result = $conn->query("SELECT * FROM product_price WHERE product_id = '' OR product_id IS NULL");
echo "Found: " . $result->num_rows . " rows<br>";

// Check for empty product_ids in stock table  
echo "<h3>Empty product_ids in product_stock:</h3>";
$result = $conn->query("SELECT * FROM product_stock WHERE product_id = '' OR product_id IS NULL");
echo "Found: " . $result->num_rows . " rows<br>";

// Option to clean up
if (isset($_GET['cleanup'])) {
    $conn->query("DELETE FROM product_price WHERE product_id = '' OR product_id IS NULL");
    $conn->query("DELETE FROM product_stock WHERE product_id = '' OR product_id IS NULL");
    echo "<p style='color:green;'>✅ Cleaned up empty entries!</p>";
}

echo "<br><a href='check_tables.php?cleanup=1'>🧹 Clean up empty entries</a>";
echo "<br><br><a href='test_actions.php'>← Back to Test</a>";
?>