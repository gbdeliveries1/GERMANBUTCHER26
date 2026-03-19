<?php
/**
 * Multi-Currency Helper Functions
 * GB Deliveries
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get all active currencies
 */
function getCurrencies($conn) {
    $currencies = [];
    $result = $conn->query("SELECT * FROM currency WHERE is_active = 1 ORDER BY is_default DESC, currency_name ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $currencies[] = $row;
        }
    }
    return $currencies;
}

/**
 * Get default currency
 */
function getDefaultCurrency($conn) {
    $result = $conn->query("SELECT * FROM currency WHERE is_default = 1 LIMIT 1");
    if ($result && $row = $result->fetch_assoc()) {
        return $row;
    }
    // Fallback to RWF
    return [
        'currency_code' => 'RWF',
        'currency_symbol' => 'RWF',
        'exchange_rate' => 1,
        'decimal_places' => 0,
        'position' => 'after'
    ];
}

/**
 * Get current selected currency
 */
function getCurrentCurrency($conn) {
    // Check session first
    if (isset($_SESSION['currency_code'])) {
        $code = $conn->real_escape_string($_SESSION['currency_code']);
        $result = $conn->query("SELECT * FROM currency WHERE currency_code = '$code' AND is_active = 1 LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            return $row;
        }
    }
    
    // Check cookie
    if (isset($_COOKIE['currency_code'])) {
        $code = $conn->real_escape_string($_COOKIE['currency_code']);
        $result = $conn->query("SELECT * FROM currency WHERE currency_code = '$code' AND is_active = 1 LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            $_SESSION['currency_code'] = $row['currency_code'];
            return $row;
        }
    }
    
    // Return default
    return getDefaultCurrency($conn);
}

/**
 * Set current currency
 */
function setCurrency($conn, $currencyCode) {
    $code = $conn->real_escape_string($currencyCode);
    $result = $conn->query("SELECT * FROM currency WHERE currency_code = '$code' AND is_active = 1 LIMIT 1");
    
    if ($result && $row = $result->fetch_assoc()) {
        $_SESSION['currency_code'] = $row['currency_code'];
        setcookie('currency_code', $row['currency_code'], time() + (86400 * 365), '/'); // 1 year
        return true;
    }
    return false;
}

/**
 * Convert price from base currency (RWF) to target currency
 */
function convertPrice($amount, $currency) {
    $rate = floatval($currency['exchange_rate']);
    if ($rate <= 0) $rate = 1;
    return $amount * $rate;
}

/**
 * Format price with currency symbol
 */
function formatPrice($amount, $currency = null, $conn = null) {
    global $conn;
    
    if ($currency === null) {
        $currency = getCurrentCurrency($conn);
    }
    
    $convertedAmount = convertPrice($amount, $currency);
    $decimals = intval($currency['decimal_places']);
    $symbol = $currency['currency_symbol'];
    $position = $currency['position'];
    
    $formattedNumber = number_format($convertedAmount, $decimals);
    
    if ($position === 'before') {
        return $symbol . $formattedNumber;
    } else {
        return $formattedNumber . ' ' . $symbol;
    }
}

/**
 * Format price for display (shorthand)
 */
function price($amount) {
    global $conn;
    return formatPrice($amount, null, $conn);
}

/**
 * Get currency by code
 */
function getCurrencyByCode($conn, $code) {
    $code = $conn->real_escape_string($code);
    $result = $conn->query("SELECT * FROM currency WHERE currency_code = '$code' LIMIT 1");
    if ($result && $row = $result->fetch_assoc()) {
        return $row;
    }
    return null;
}

/**
 * Update exchange rate
 */
function updateExchangeRate($conn, $currencyCode, $rate) {
    $code = $conn->real_escape_string($currencyCode);
    $rate = floatval($rate);
    return $conn->query("UPDATE currency SET exchange_rate = $rate WHERE currency_code = '$code'");
}

/**
 * Get exchange rate info text
 */
function getExchangeRateInfo($conn) {
    $currency = getCurrentCurrency($conn);
    $default = getDefaultCurrency($conn);
    
    if ($currency['currency_code'] === $default['currency_code']) {
        return '';
    }
    
    $rate = 1 / $currency['exchange_rate'];
    return '1 ' . $currency['currency_code'] . ' = ' . number_format($rate, 0) . ' ' . $default['currency_code'];
}
?>