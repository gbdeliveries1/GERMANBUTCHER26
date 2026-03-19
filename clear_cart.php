<?php
session_start();

// Database connection
if (file_exists(__DIR__ . '/../on/on.php')) {
    include __DIR__ . '/../on/on.php';
} elseif (file_exists(__DIR__ . '/db_conn.php')) {
    include __DIR__ . '/db_conn.php';
} elseif (file_exists('db_conn.php')) {
    include 'db_conn.php';
}

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

$cart_id = '';

// Get cart_id from POST
if (isset($_POST['cart_id']) && !empty($_POST['cart_id']) && $_POST['cart_id'] !== '0') {
    $cart_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
}

// Fallback: get from session
if (empty($cart_id) && isset($_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'])) {
    $customer_id = mysqli_real_escape_string($conn, $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021']);
    $result = $conn->query("SELECT cart_id FROM cart WHERE customer_id='$customer_id' ORDER BY register_date DESC LIMIT 1");
    if ($result && $row = $result->fetch_assoc()) {
        $cart_id = $row['cart_id'];
    }
}

if (!empty($cart_id) && $cart_id !== '0') {
    // Update cart_item status
    $conn->query("UPDATE cart_item SET status='REMOVED' WHERE cart_id='$cart_id' AND status='ACTIVE'");
    
    // Delete cart items
    $conn->query("DELETE FROM cart_item WHERE cart_id='$cart_id'");
    
    // Update cart status
    $conn->query("UPDATE cart SET status='COMPLETED' WHERE cart_id='$cart_id'");
    
    $response['success'] = true;
    $response['message'] = 'Cart cleared';
    $response['cart_id'] = $cart_id;
} else {
    $response['message'] = 'No cart found';
}

echo json_encode($response);
exit;
?>