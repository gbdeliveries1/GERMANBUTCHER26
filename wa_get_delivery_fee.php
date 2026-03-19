<?php
include 'db_conn.php';

header('Content-Type: application/json');

$province = isset($_POST['province']) ? $_POST['province'] : '';
$district = isset($_POST['district']) ? $_POST['district'] : '';
$sector = isset($_POST['sector']) ? $_POST['sector'] : '';

$delivery_fee = 0;
$delivery_time = '1-2 days';

if ($province && $district && $sector) {
    
    // Check if Kigali
    $is_kigali = false;
    $kigali_names = ['City of Kigali', 'Kigali', 'Umujyi wa Kigali'];
    foreach ($kigali_names as $name) {
        if (stripos($province, $name) !== false || stripos($province, 'kigali') !== false) {
            $is_kigali = true;
            break;
        }
    }
    
    // Kigali districts
    $kigali_districts = ['Gasabo', 'Kicukiro', 'Nyarugenge'];
    if (in_array($district, $kigali_districts)) {
        $is_kigali = true;
    }
    
    if ($is_kigali) {
        // Kigali City - lower fee, same day delivery
        $delivery_fee = 2000;
        $delivery_time = 'Same day';
        
        // Some sectors might have different fees
        $premium_sectors = ['Kacyiru', 'Kimihurura', 'Nyarutarama', 'Gacuriro', 'Kibagabaga', 'Remera', 'Kimironko'];
        if (in_array($sector, $premium_sectors)) {
            $delivery_fee = 2500;
            $delivery_time = 'Same day (2-4 hours)';
        }
        
        // Outer Kigali sectors
        $outer_sectors = ['Jabana', 'Nduba', 'Rusororo', 'Rutunga', 'Bumbogo', 'Gikomero', 'Jali', 'Ndera', 'Masaka', 'Gahanga', 'Mageragere', 'Kanyinya'];
        if (in_array($sector, $outer_sectors)) {
            $delivery_fee = 6000;
            $delivery_time = 'Same day';
        }
    } else {
        // Outside Kigali
        $delivery_fee = 50000;
        $delivery_time = '1-2 days';
        
        // Nearby provinces to Kigali
        $nearby_districts = ['Rwamagana', 'Bugesera', 'Kamonyi', 'Muhanga', 'Rulindo', 'Gakenke'];
        if (in_array($district, $nearby_districts)) {
            $delivery_fee = 50000;
            $delivery_time = 'Next day';
        }
        
        // Far districts
        $far_districts = ['Nyagatare', 'Kirehe', 'Rusizi', 'Rubavu', 'Nyabihu', 'Burera', 'Nyaruguru', 'Gisagara'];
        if (in_array($district, $far_districts)) {
            $delivery_fee = 400000;
            $delivery_time = '2-3 days';
        }
        
        // Very far districts
        $very_far_districts = ['Nyamasheke', 'Karongi', 'Rutsiro'];
        if (in_array($district, $very_far_districts)) {
            $delivery_fee = 400000;
            $delivery_time = '2-3 days';
        }
    }
    
    // Try to get from database if exists (override hardcoded)
    $sector_escaped = mysqli_real_escape_string($conn, $sector);
    $district_escaped = mysqli_real_escape_string($conn, $district);
    
    // Check delivery_fee table
    $sql = "SELECT * FROM delivery_fee WHERE sector='$sector_escaped' LIMIT 1";
    $result = @$conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (isset($row['fee'])) $delivery_fee = $row['fee'];
        elseif (isset($row['delivery_fee'])) $delivery_fee = $row['delivery_fee'];
        elseif (isset($row['amount'])) $delivery_fee = $row['amount'];
        
        if (isset($row['delivery_time'])) $delivery_time = $row['delivery_time'];
        elseif (isset($row['time'])) $delivery_time = $row['time'];
    }
    
    // Check shipping table
    $sql = "SELECT * FROM shipping WHERE sector='$sector_escaped' OR district='$district_escaped' LIMIT 1";
    $result = @$conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (isset($row['fee'])) $delivery_fee = $row['fee'];
        elseif (isset($row['shipping_fee'])) $delivery_fee = $row['shipping_fee'];
        elseif (isset($row['amount'])) $delivery_fee = $row['amount'];
        
        if (isset($row['delivery_time'])) $delivery_time = $row['delivery_time'];
    }
}

echo json_encode([
    'success' => true,
    'delivery_fee' => (int)$delivery_fee,
    'delivery_time' => $delivery_time,
    'province' => $province,
    'district' => $district,
    'sector' => $sector
]);
?>