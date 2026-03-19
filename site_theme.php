<?php
if (!function_exists('site_setting')) {
    function site_setting($conn, $key, $default='') {
        $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key=? LIMIT 1");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $res = $stmt->get_result();
        if($row = $res->fetch_assoc()) return $row['setting_value'];
        return $default;
    }
}

$themeSiteName = site_setting($conn, 'site_name', 'GB Deliveries');
$themePrimary = site_setting($conn, 'primary_color', '#ff6a00');
$themeSecondary = site_setting($conn, 'secondary_color', '#1f2937');
$themeFont = site_setting($conn, 'font_family', 'Arial, sans-serif');
$themeBase = (int)site_setting($conn, 'font_size_base', '16');
$themeHeader = (int)site_setting($conn, 'header_font_size', '32');
?>
<style>
:root{
  --primary-color: <?php echo htmlspecialchars($themePrimary); ?>;
  --secondary-color: <?php echo htmlspecialchars($themeSecondary); ?>;
  --base-font-size: <?php echo $themeBase; ?>px;
  --header-font-size: <?php echo $themeHeader; ?>px;
}
body{
  font-family: <?php echo $themeFont; ?>;
  font-size: var(--base-font-size);
}
h1,h2,h3{ color: var(--secondary-color); }
.site-brand{ color: var(--primary-color); font-size: var(--header-font-size); }
.btn-primary{ background: var(--primary-color); border-color: var(--primary-color); }
</style>