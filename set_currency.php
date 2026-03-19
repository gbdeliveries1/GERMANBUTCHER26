<?php
session_start();

if (file_exists(__DIR__ . '/../on/on.php')) {
    include __DIR__ . '/../on/on.php';
} elseif (file_exists('on/on.php')) {
    include 'on/on.php';
}

include_once 'currency.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (isset($_POST['currency_code'])) {
    $code = $_POST['currency_code'];
    
    if (setCurrency($conn, $code)) {
        $currency = getCurrentCurrency($conn);
        $response['success'] = true;
        $response['message'] = 'Currency changed to ' . $currency['currency_name'];
        $response['currency'] = $currency;
    } else {
        $response['message'] = 'Invalid currency';
    }
} else {
    $response['message'] = 'No currency specified';
}

echo json_encode($response);
?>