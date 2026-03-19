<?php
if (file_exists('../on/on.php')) {
    include '../on/on.php';
} elseif (file_exists('on/on.php')) {
    include 'on/on.php';
}

$province = $_POST['province'] ?? '';
$district = $_POST['district'] ?? '';

if (!empty($district)) {
    $district = $conn->real_escape_string($district);
    $province = $conn->real_escape_string($province);
    
    // Get sectors from rw_location
    $sql = "SELECT DISTINCT sector FROM rw_location WHERE district='$district' ORDER BY sector ASC";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sector = $row['sector'];
            $sectorEsc = $conn->real_escape_string($sector);
            $fee = 0;
            
            // FIRST: Check sector_shipping_fee table (admin managed)
            $feeQuery = $conn->query("SELECT fee FROM sector_shipping_fee WHERE sector='$sectorEsc' LIMIT 1");
            if ($feeQuery && $feeRow = $feeQuery->fetch_assoc()) {
                $fee = intval($feeRow['fee']);
            }
            
            // SECOND: If no fee, check rw_location.delivery_fee
            if ($fee == 0) {
                $feeQuery = $conn->query("SELECT delivery_fee FROM rw_location WHERE sector='$sectorEsc' AND district='$district' LIMIT 1");
                if ($feeQuery && $feeRow = $feeQuery->fetch_assoc()) {
                    $fee = intval($feeRow['delivery_fee']);
                }
            }
            
            // THIRD: Default fee if still 0
            if ($fee == 0) {
                $fee = (stripos($province, 'Kigali') !== false || stripos($province, 'Umujyi') !== false) ? 1500 : 2500;
            }
            
            $feeFormatted = number_format($fee);
            echo '<option value="' . htmlspecialchars($sector) . '" data-fee="' . $fee . '">' . htmlspecialchars($sector) . ' (' . $feeFormatted . ' RWF)</option>';
        }
    }
}
?>