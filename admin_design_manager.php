<?php
// require 'db_connect.php'; // Ensure your DB connection is included here

// Helper to fetch all current settings
$settings = [];
$result = $conn->query("SELECT setting_key, setting_value FROM site_design_settings");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Fallback defaults if table is empty
$def = function($key, $default) use ($settings) {
    return isset($settings[$key]) ? htmlspecialchars($settings[$key]) : $default;
};

// Google Fonts list
$fonts = ['Inter', 'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Poppins', 'Playfair Display', 'Merriweather'];
?>

<style>
    .design-manager { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 25px; font-family: sans-serif; }
    .design-manager h2 { margin-top: 0; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; color: #333; }
    .grid-layout { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px; }
    .form-group { display: flex; flex-direction: column; gap: 8px; }
    .form-group label { font-weight: 600; font-size: 14px; color: #555; }
    .form-group input[type="color"] { width: 100%; height: 45px; border: 1px solid #ddd; border-radius: 6px; cursor: pointer; padding: 2px; }
    .form-group input[type="number"], .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
    .section-title { font-size: 16px; font-weight: bold; color: #1f2937; margin: 20px 0 10px; padding-left: 10px; border-left: 4px solid #3b82f6; }
    .btn-save { background: #3b82f6; color: #fff; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 16px; width: 100%; transition: background 0.3s; }
    .btn-save:hover { background: #2563eb; }
    #save-status { display: none; padding: 10px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; text-align: center; }
    .status-success { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
    .status-error { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
</style>

<div class="design-manager">
    <h2>🎨 Site Design Manager</h2>
    
    <div id="save-status"></div>

    <form id="design-form">
        
        <div class="section-title">Colors & Theming</div>
        <div class="grid-layout">
            <div class="form-group">
                <label>Primary Color</label>
                <input type="color" name="primary_color" value="<?php echo $def('primary_color', '#ff4747'); ?>">
            </div>
            <div class="form-group">
                <label>Secondary Color</label>
                <input type="color" name="secondary_color" value="<?php echo $def('secondary_color', '#1f2937'); ?>">
            </div>
            <div class="form-group">
                <label>Button Color</label>
                <input type="color" name="button_color" value="<?php echo $def('button_color', '#ff4747'); ?>">
            </div>
        </div>

        <div class="section-title">Typography (Google Fonts)</div>
        <div class="grid-layout">
            <div class="form-group">
                <label>Font Family</label>
                <select name="font_family">
                    <?php foreach($fonts as $font): ?>
                        <option value="<?php echo $font; ?>" <?php echo $def('font_family', 'Inter') == $font ? 'selected' : ''; ?>><?php echo $font; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Base Font Size (px)</label>
                <input type="number" name="font_size_base" value="<?php echo $def('font_size_base', '16'); ?>" min="12" max="24">
            </div>
            <div class="form-group">
                <label>Heading Font Size (px)</label>
                <input type="number" name="font_size_heading" value="<?php echo $def('font_size_heading', '32'); ?>" min="20" max="64">
            </div>
        </div>

        <div class="section-title">Layout & Styling</div>
        <div class="grid-layout">
            <div class="form-group">
                <label>Container Max-Width (px)</label>
                <input type="number" name="container_width" value="<?php echo $def('container_width', '1200'); ?>" min="800" max="1920" step="10">
            </div>
            <div class="form-group">
                <label>Global Border Radius (px)</label>
                <input type="number" name="border_radius" value="<?php echo $def('border_radius', '8'); ?>" min="0" max="50">
            </div>
            <div class="form-group">
                <label>Global Shadows</label>
                <select name="box_shadow">
                    <option value="none" <?php echo $def('box_shadow', 'soft') == 'none' ? 'selected' : ''; ?>>None (Flat)</option>
                    <option value="soft" <?php echo $def('box_shadow', 'soft') == 'soft' ? 'selected' : ''; ?>>Soft & Subtle</option>
                    <option value="medium" <?php echo $def('box_shadow', 'soft') == 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="hard" <?php echo $def('box_shadow', 'soft') == 'hard' ? 'selected' : ''; ?>>Hard & Sharp</option>
                </select>
            </div>
            <div class="form-group">
                <label>Product Card Style</label>
                <select name="card_style">
                    <option value="flat" <?php echo $def('card_style', 'elevated') == 'flat' ? 'selected' : ''; ?>>Flat (No Borders)</option>
                    <option value="bordered" <?php echo $def('card_style', 'elevated') == 'bordered' ? 'selected' : ''; ?>>Bordered</option>
                    <option value="elevated" <?php echo $def('card_style', 'elevated') == 'elevated' ? 'selected' : ''; ?>>Elevated (Shadows)</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn-save" id="save-btn">💾 Save Design Settings</button>
    </form>
</div>

<script>
document.getElementById('design-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let btn = document.getElementById('save-btn');
    let statusDiv = document.getElementById('save-status');
    btn.innerHTML = 'Saving...';
    btn.disabled = true;

    let formData = new FormData(this);

    fetch('ajax_save_design.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.innerHTML = '💾 Save Design Settings';
        btn.disabled = false;
        
        statusDiv.style.display = 'block';
        if(data.status === 'success') {
            statusDiv.className = 'status-success';
            statusDiv.innerHTML = '✅ Settings saved successfully! Changes are live on the frontend.';
            setTimeout(() => { statusDiv.style.display = 'none'; }, 4000);
        } else {
            statusDiv.className = 'status-error';
            statusDiv.innerHTML = '❌ Error saving settings: ' + data.message;
        }
    })
    .catch(error => {
        btn.innerHTML = '💾 Save Design Settings';
        btn.disabled = false;
        statusDiv.style.display = 'block';
        statusDiv.className = 'status-error';
        statusDiv.innerHTML = '❌ Network error occurred.';
    });
});
</script>