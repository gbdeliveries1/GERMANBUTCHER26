<?php
// Fetch current settings
$settings = [];
$res = $conn->query("SELECT setting_key, setting_value FROM site_design_settings");
if ($res) { while ($row = $res->fetch_assoc()) { $settings[$row['setting_key']] = $row['setting_value']; } }
$get_set = function($key, $default) use ($settings) { return $settings[$key] ?? $default; };

$active_theme = $get_set('active_theme', 'default');

// Scan Theme Directory
$themes_dir = '../../themes/';
$available_themes = [];
if (is_dir($themes_dir)) {
    $dirs = array_filter(glob($themes_dir . '*'), 'is_dir');
    foreach ($dirs as $dir) {
        $theme_slug = basename($dir);
        $json_path = $dir . '/theme.json';
        if (file_exists($json_path)) {
            $theme_data = json_decode(file_get_contents($json_path), true);
            $theme_data['slug'] = $theme_slug;
            $available_themes[] = $theme_data;
        }
    }
}
?>

<style>
    .tm-container { max-width: 1200px; margin: 0 auto; font-family: sans-serif; }
    .tm-tabs { display: flex; border-bottom: 2px solid #eee; margin-bottom: 20px; }
    .tm-tab { padding: 12px 25px; cursor: pointer; font-weight: bold; color: #666; border-bottom: 3px solid transparent; margin-bottom: -2px; transition: 0.3s; }
    .tm-tab.active { color: #ff5000; border-color: #ff5000; }
    .tm-content { display: none; }
    .tm-content.active { display: block; }
    
    /* Theme Grid */
    .theme-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
    .theme-card { background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; position: relative; transition: 0.3s; }
    .theme-card.active-card { border: 2px solid #10b981; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2); }
    .theme-thumb { height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #aaa; font-size: 14px; }
    .theme-info { padding: 15px; }
    .theme-title { margin: 0 0 5px 0; font-size: 18px; color: #333; }
    .theme-desc { font-size: 13px; color: #666; margin-bottom: 15px; }
    .btn-activate { background: #ff5000; color: #fff; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%; }
    .active-badge { background: #10b981; color: #fff; text-align: center; padding: 8px 15px; border-radius: 4px; font-weight: bold; display: block; }

    /* Customizer Form */
    .cust-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
    .cust-panel { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; }
    .cust-panel h3 { margin-top: 0; color: #ff5000; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; font-size: 16px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: 600; font-size: 13px; color: #444; margin-bottom: 5px; }
    .form-group input[type="text"], .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    .form-group input[type="color"] { width: 100%; height: 40px; border: 1px solid #ccc; border-radius: 4px; padding: 2px; cursor: pointer; }
    .logo-preview { max-width: 150px; background: #f9f9f9; padding: 10px; border: 1px dashed #ccc; margin-top: 10px; border-radius: 4px; }
    
    .btn-save { background: #ff5000; color: #fff; border: none; padding: 15px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 16px; width: 100%; margin-top: 20px; }
</style>

<div class="tm-container">
    <h2>🎨 Theme & Appearance Manager</h2>
    
    <div class="tm-tabs">
        <div class="tm-tab active" onclick="switchTab('themes', this)">Available Themes</div>
        <div class="tm-tab" onclick="switchTab('customizer', this)">Theme Customizer</div>
    </div>

    <!-- TAB 1: THEME LIBRARY -->
    <div id="tab-themes" class="tm-content active">
        <div class="theme-grid">
            <?php foreach($available_themes as $theme): $is_active = ($theme['slug'] === $active_theme); ?>
            <div class="theme-card <?php echo $is_active ? 'active-card' : ''; ?>">
                <div class="theme-thumb">No Preview Image</div>
                <div class="theme-info">
                    <h3 class="theme-title"><?php echo htmlspecialchars($theme['name']); ?> <small style="color:#999;font-size:12px;">v<?php echo $theme['version']; ?></small></h3>
                    <p class="theme-desc"><?php echo htmlspecialchars($theme['description']); ?></p>
                    
                    <?php if($is_active): ?>
                        <span class="active-badge">✓ Active Theme</span>
                    <?php else: ?>
                        <button class="btn-activate" onclick="activateTheme('<?php echo $theme['slug']; ?>')">Activate Theme</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($available_themes)): ?>
                <p>No themes found in the <code>/themes/</code> directory.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- TAB 2: CUSTOMIZER -->
    <div id="tab-customizer" class="tm-content">
        <form id="customizer-form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_customizer">
            
            <div class="cust-grid">
                <!-- Colors & Branding -->
                <div class="cust-panel">
                    <h3>Branding & Colors</h3>
                    
                    <div class="form-group">
                        <label>Site Logo</label>
                        <input type="file" name="site_logo" accept="image/*" style="padding: 5px; border: 1px solid #ccc; width: 100%;">
                        <?php if($get_set('site_logo', '')): ?>
                            <img src="../../<?php echo $get_set('site_logo', ''); ?>" class="logo-preview">
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Primary Color (Buttons, Links)</label>
                        <input type="color" name="theme_primary_color" value="<?php echo $get_set('theme_primary_color', '#ff5000'); ?>">
                    </div>

                    <div class="form-group">
                        <label>Secondary Color (Accents)</label>
                        <input type="color" name="theme_secondary_color" value="<?php echo $get_set('theme_secondary_color', '#ff9f43'); ?>">
                    </div>

                    <div class="form-group">
                        <label>Background Color</label>
                        <input type="color" name="theme_bg_color" value="<?php echo $get_set('theme_bg_color', '#f5f5f5'); ?>">
                    </div>
                </div>

                <!-- Layout & Typography -->
                <div class="cust-panel">
                    <h3>Layout & Typography</h3>

                    <div class="form-group">
                        <label>Global Font Family</label>
                        <select name="theme_font">
                            <option value="Open Sans" <?php echo $get_set('theme_font','')=='Open Sans'?'selected':'';?>>Open Sans</option>
                            <option value="Roboto" <?php echo $get_set('theme_font','')=='Roboto'?'selected':'';?>>Roboto</option>
                            <option value="Poppins" <?php echo $get_set('theme_font','')=='Poppins'?'selected':'';?>>Poppins</option>
                            <option value="Montserrat" <?php echo $get_set('theme_font','')=='Montserrat'?'selected':'';?>>Montserrat (Modern)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Header Layout Style</label>
                        <select name="theme_header_style">
                            <option value="header-classic" <?php echo $get_set('theme_header_style','')=='header-classic'?'selected':'';?>>Classic (Logo Left, Menu Right)</option>
                            <option value="header-centered" <?php echo $get_set('theme_header_style','')=='header-centered'?'selected':'';?>>Centered (Logo Center, Menu Below)</option>
                            <option value="header-minimal" <?php echo $get_set('theme_header_style','')=='header-minimal'?'selected':'';?>>Minimal (Hidden Search)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Footer Layout Style</label>
                        <select name="theme_footer_style">
                            <option value="footer-4-col" <?php echo $get_set('theme_footer_layout','')=='footer-4-col'?'selected':'';?>>4 Columns (Standard)</option>
                            <option value="footer-3-col" <?php echo $get_set('theme_footer_layout','')=='footer-3-col'?'selected':'';?>>3 Columns (Wide)</option>
                            <option value="footer-centered" <?php echo $get_set('theme_footer_layout','')=='footer-centered'?'selected':'';?>>Simple Centered</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-save" id="btn-save-cust">💾 Save Customizer Settings</button>
        </form>
    </div>
</div>

<script>
function switchTab(tabId, el) {
    document.querySelectorAll('.tm-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tm-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + tabId).classList.add('active');
    el.classList.add('active');
}

function activateTheme(slug) {
    if(confirm('Activate this theme? It will apply default layout settings for this theme.')) {
        let fd = new FormData();
        fd.append('action', 'activate_theme');
        fd.append('slug', slug);
        fetch('ajax_theme_manager.php', { method: 'POST', body: fd })
        .then(res => res.json()).then(data => { window.location.reload(); });
    }
}

document.getElementById('customizer-form').addEventListener('submit', function(e) {
    e.preventDefault();
    let btn = document.getElementById('btn-save-cust');
    btn.innerText = 'Saving...'; btn.disabled = true;

    fetch('ajax_theme_manager.php', { method: 'POST', body: new FormData(this) })
    .then(res => res.json())
    .then(data => {
        btn.innerText = '💾 Save Customizer Settings'; btn.disabled = false;
        if(data.status === 'success') { alert('Theme customized successfully!'); window.location.reload(); }
    });
});
</script>