<?php
// Function to safely fetch settings for frontend
if(!function_exists('getSiteSetting')) {
    function getSiteSetting($conn, $key, $default = '') {
        $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            return $row['setting_value'];
        }
        return $default;
    }
}

$theme_primary = getSiteSetting($conn, 'primary_color', '#ff4747');
$theme_secondary = getSiteSetting($conn, 'secondary_color', '#333333');
$theme_font = getSiteSetting($conn, 'font_family', 'Arial, sans-serif');
$theme_base_size = getSiteSetting($conn, 'font_size_base', '14') . 'px';
$theme_heading_size = getSiteSetting($conn, 'font_size_headings', '24') . 'px';
?>

<style>
    /* CSS Variables injected from Admin Settings */
    :root {
        --site-primary-color: <?php echo htmlspecialchars($theme_primary); ?>;
        --site-secondary-color: <?php echo htmlspecialchars($theme_secondary); ?>;
        --site-font-family: <?php echo htmlspecialchars($theme_font); ?>;
        --site-base-font-size: <?php echo htmlspecialchars($theme_base_size); ?>;
        --site-heading-font-size: <?php echo htmlspecialchars($theme_heading_size); ?>;
    }

    body {
        font-family: var(--site-font-family);
        font-size: var(--site-base-font-size);
        color: var(--site-secondary-color);
    }

    h1, h2, h3, h4, h5, h6 {
        font-family: var(--site-font-family);
        color: var(--site-secondary-color);
    }
    
    h1 {
        font-size: var(--site-heading-font-size);
    }

    a {
        color: var(--site-primary-color);
    }

    .btn, button {
        background-color: var(--site-primary-color);
        color: #fff;
        font-family: var(--site-font-family);
    }
    
    .btn:hover, button:hover {
        opacity: 0.9;
    }

    .header-nav {
        background-color: var(--site-secondary-color);
    }
</style>