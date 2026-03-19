<?php
include "../on/on.php";

$province = $_POST['province'] ?? '';

if (!empty($province)) {
    $province = $conn->real_escape_string($province);
    $sql = "SELECT DISTINCT district FROM rw_location WHERE province='$province' ORDER BY district ASC";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . htmlspecialchars($row['district']) . '">' . htmlspecialchars($row['district']) . '</option>';
        }
    }
}
?>