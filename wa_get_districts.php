
<?php
include 'db_conn.php';

if (isset($_POST['province'])) {
    $province = $_POST['province'];
    
    // Districts data based on province
    $districts = [];
    
    switch ($province) {
        case 'City of Kigali':
        case 'Kigali':
        case 'Umujyi wa Kigali':
            $districts = ['Gasabo', 'Kicukiro', 'Nyarugenge'];
            break;
            
        case 'Eastern Province':
        case 'Intara y\'Iburasirazuba':
        case 'East':
            $districts = ['Bugesera', 'Gatsibo', 'Kayonza', 'Kirehe', 'Ngoma', 'Nyagatare', 'Rwamagana'];
            break;
            
        case 'Northern Province':
        case 'Intara y\'Amajyaruguru':
        case 'North':
            $districts = ['Burera', 'Gakenke', 'Gicumbi', 'Musanze', 'Rulindo'];
            break;
            
        case 'Southern Province':
        case 'Intara y\'Amajyepfo':
        case 'South':
            $districts = ['Gisagara', 'Huye', 'Kamonyi', 'Muhanga', 'Nyamagabe', 'Nyanza', 'Nyaruguru', 'Ruhango'];
            break;
            
        case 'Western Province':
        case 'Intara y\'Iburengerazuba':
        case 'West':
            $districts = ['Karongi', 'Ngororero', 'Nyabihu', 'Nyamasheke', 'Rubavu', 'Rusizi', 'Rutsiro'];
            break;
            
        default:
            // Try to fetch from database as fallback
            $province_escaped = mysqli_real_escape_string($conn, $province);
            $sql = "SELECT DISTINCT district FROM rw_location WHERE province='$province_escaped' ORDER BY district ASC";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $districts[] = $row['district'];
                }
            }
            break;
    }
    
    // Output options
    $output = '';
    foreach ($districts as $district) {
        $output .= '<option value="' . htmlspecialchars($district) . '">' . htmlspecialchars($district) . '</option>';
    }
    
    echo $output;
}
?>