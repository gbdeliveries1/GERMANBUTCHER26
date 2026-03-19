<?php
// Fetch current settings
$settings = [];
$res = $conn->query("SELECT setting_key, setting_value FROM site_design_settings WHERE setting_key LIKE 'card_%'");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
$get_set = function($key, $default) use ($settings) {
    return isset($settings[$key]) ? htmlspecialchars($settings[$key]) : $default;
};
?>

<style>
    .pcc-container { max-width: 1200px; margin: 0 auto; font-family: sans-serif; }
    .pcc-header { margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
    .pcc-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; }
    .pcc-panel { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .pcc-panel h3 { margin-top: 0; color: #ff5000; font-size: 16px; border-bottom: 1px solid #f0f0f0; padding-bottom: 8px; margin-bottom: 15px; }
    
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: 600; font-size: 13px; color: #444; margin-bottom: 6px; }
    .form-group select, .form-group input[type="text"], .form-group input[type="number"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
    .form-group input[type="color"] { width: 100%; height: 40px; border: 1px solid #ccc; border-radius: 6px; cursor: pointer; padding: 2px; }
    
    .btn-save-pcc { background: #ff5000; color: #fff; border: none; padding: 15px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 16px; width: 100%; margin-top: 20px; transition: 0.3s; }
    .btn-save-pcc:hover { background: #e04400; box-shadow: 0 4px 12px rgba(255,80,0,0.3); }
    
    #pcc-status { display: none; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; text-align: center; background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
</style>

<div class="pcc-container">
    <div class="pcc-header">
        <h2>🖼️ Product Card Customizer</h2>
        <p style="color: #666; margin:0;">Control how product thumbnails and grids look across your entire store.</p>
    </div>

    <div id="pcc-status"></div>

    <form id="pcc-form">
        <div class="pcc-grid">
            
            <!-- Layout -->
            <div class="pcc-panel">
                <h3>1. Layout & Dimensions</h3>
                <div class="form-group">
                    <label>Grid Columns (Desktop)</label>
                    <select name="card_grid_columns">
                        <option value="2" <?php echo $get_set('card_grid_columns', '4') == '2' ? 'selected' : ''; ?>>2 Columns (Very Large)</option>
                        <option value="3" <?php echo $get_set('card_grid_columns', '4') == '3' ? 'selected' : ''; ?>>3 Columns (Large)</option>
                        <option value="4" <?php echo $get_set('card_grid_columns', '4') == '4' ? 'selected' : ''; ?>>4 Columns (Standard)</option>
                        <option value="5" <?php echo $get_set('card_grid_columns', '4') == '5' ? 'selected' : ''; ?>>5 Columns (Compact)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Image Aspect Ratio</label>
                    <select name="card_image_ratio">
                        <option value="1/1" <?php echo $get_set('card_image_ratio', '1/1') == '1/1' ? 'selected' : ''; ?>>1:1 (Square)</option>
                        <option value="4/3" <?php echo $get_set('card_image_ratio', '1/1') == '4/3' ? 'selected' : ''; ?>>4:3 (Landscape)</option>
                        <option value="3/4" <?php echo $get_set('card_image_ratio', '1/1') == '3/4' ? 'selected' : ''; ?>>3:4 (Portrait)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Card Hover Effect</label>
                    <select name="card_hover_effect">
                        <option value="none" <?php echo $get_set('card_hover_effect', 'lift') == 'none' ? 'selected' : ''; ?>>None</option>
                        <option value="lift" <?php echo $get_set('card_hover_effect', 'lift') == 'lift' ? 'selected' : ''; ?>>Lift Up & Shadow</option>
                        <option value="glow" <?php echo $get_set('card_hover_effect', 'lift') == 'glow' ? 'selected' : ''; ?>>Border Glow</option>
                    </select>
                </div>
            </div>

            <!-- Image Settings -->
            <div class="pcc-panel">
                <h3>2. Image Behavior</h3>
                <div class="form-group">
                    <label>Image Zoom on Hover?</label>
                    <select name="card_image_zoom">
                        <option value="1" <?php echo $get_set('card_image_zoom', '1') == '1' ? 'selected' : ''; ?>>Yes, zoom in</option>
                        <option value="0" <?php echo $get_set('card_image_zoom', '1') == '0' ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Lazy Loading (Improves Speed)</label>
                    <select name="card_lazy_load">
                        <option value="1" <?php echo $get_set('card_lazy_load', '1') == '1' ? 'selected' : ''; ?>>Enabled</option>
                        <option value="0" <?php echo $get_set('card_lazy_load', '1') == '0' ? 'selected' : ''; ?>>Disabled</option>
                    </select>
                </div>
            </div>

            <!-- Display Info -->
            <div class="pcc-panel">
                <h3>3. Information Visibility</h3>
                <div class="form-group">
                    <label>Show Product Title?</label>
                    <select name="card_show_title"><option value="1" <?php echo $get_set('card_show_title','1')=='1'?'selected':'';?>>Yes</option><option value="0" <?php echo $get_set('card_show_title','1')=='0'?'selected':'';?>>No</option></select>
                </div>
                <div class="form-group">
                    <label>Show Price?</label>
                    <select name="card_show_price"><option value="1" <?php echo $get_set('card_show_price','1')=='1'?'selected':'';?>>Yes</option><option value="0" <?php echo $get_set('card_show_price','1')=='0'?'selected':'';?>>No</option></select>
                </div>
                <div class="form-group">
                    <label>Show Category Name?</label>
                    <select name="card_show_category"><option value="1" <?php echo $get_set('card_show_category','1')=='1'?'selected':'';?>>Yes</option><option value="0" <?php echo $get_set('card_show_category','1')=='0'?'selected':'';?>>No</option></select>
                </div>
                <div class="form-group">
                    <label>Show Star Rating?</label>
                    <select name="card_show_rating"><option value="1" <?php echo $get_set('card_show_rating','1')=='1'?'selected':'';?>>Yes</option><option value="0" <?php echo $get_set('card_show_rating','1')=='0'?'selected':'';?>>No</option></select>
                </div>
                <div class="form-group">
                    <label>Show Stock Quantity?</label>
                    <select name="card_show_stock"><option value="1" <?php echo $get_set('card_show_stock','0')=='1'?'selected':'';?>>Yes</option><option value="0" <?php echo $get_set('card_show_stock','0')=='0'?'selected':'';?>>No</option></select>
                </div>
            </div>

            <!-- Buttons & Badges -->
            <div class="pcc-panel">
                <h3>4. Buttons & Badges</h3>
                <div class="form-group">
                    <label>Add to Cart Button Style</label>
                    <select name="card_btn_style">
                        <option value="solid" <?php echo $get_set('card_btn_style', 'solid') == 'solid' ? 'selected' : ''; ?>>Solid Color Block</option>
                        <option value="outline" <?php echo $get_set('card_btn_style', 'solid') == 'outline' ? 'selected' : ''; ?>>Outline (Ghost)</option>
                        <option value="hidden" <?php echo $get_set('card_btn_style', 'solid') == 'hidden' ? 'selected' : ''; ?>>Hidden</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Show Quick View Icon?</label>
                    <select name="card_show_quickview"><option value="1" <?php echo $get_set('card_show_quickview','1')=='1'?'selected':'';?>>Yes</option><option value="0" <?php echo $get_set('card_show_quickview','1')=='0'?'selected':'';?>>No</option></select>
                </div>
                <div class="form-group">
                    <label>Show Wishlist Heart?</label>
                    <select name="card_show_wishlist"><option value="1" <?php echo $get_set('card_show_wishlist','1')=='1'?'selected':'';?>>Yes</option><option value="0" <?php echo $get_set('card_show_wishlist','1')=='0'?'selected':'';?>>No</option></select>
                </div>
                <div style="display:flex; gap:10px;">
                    <div class="form-group" style="flex:1;"><label>Sale Badge</label><select name="card_badge_sale"><option value="1" <?php echo $get_set('card_badge_sale','1')=='1'?'selected':'';?>>On</option><option value="0" <?php echo $get_set('card_badge_sale','1')=='0'?'selected':'';?>>Off</option></select></div>
                    <div class="form-group" style="flex:1;"><label>Hot Badge</label><select name="card_badge_hot"><option value="1" <?php echo $get_set('card_badge_hot','1')=='1'?'selected':'';?>>On</option><option value="0" <?php echo $get_set('card_badge_hot','1')=='0'?'selected':'';?>>Off</option></select></div>
                </div>
            </div>

            <!-- Design & Colors -->
            <div class="pcc-panel">
                <h3>5. UI Design & Colors</h3>
                <div class="form-group">
                    <label>Card Background Color</label>
                    <input type="color" name="card_bg_color" value="<?php echo $get_set('card_bg_color', '#ffffff'); ?>">
                </div>
                <div class="form-group">
                    <label>Card Border Radius (px)</label>
                    <input type="number" name="card_radius" value="<?php echo $get_set('card_radius', '8'); ?>" min="0" max="40">
                </div>
                <div class="form-group">
                    <label>Card Border</label>
                    <select name="card_border">
                        <option value="none" <?php echo $get_set('card_border', '1px solid #eeeeee') == 'none' ? 'selected' : ''; ?>>None</option>
                        <option value="1px solid #eeeeee" <?php echo $get_set('card_border', '1px solid #eeeeee') == '1px solid #eeeeee' ? 'selected' : ''; ?>>Light Grey Border</option>
                        <option value="1px solid #dddddd" <?php echo $get_set('card_border', '1px solid #eeeeee') == '1px solid #dddddd' ? 'selected' : ''; ?>>Darker Grey Border</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Card Drop Shadow</label>
                    <select name="card_shadow">
                        <option value="none" <?php echo $get_set('card_shadow', '0 4px 12px rgba(0,0,0,0.05)') == 'none' ? 'selected' : ''; ?>>No Shadow</option>
                        <option value="0 2px 5px rgba(0,0,0,0.05)" <?php echo $get_set('card_shadow', '0 4px 12px rgba(0,0,0,0.05)') == '0 2px 5px rgba(0,0,0,0.05)' ? 'selected' : ''; ?>>Light Shadow</option>
                        <option value="0 4px 12px rgba(0,0,0,0.08)" <?php echo $get_set('card_shadow', '0 4px 12px rgba(0,0,0,0.05)') == '0 4px 12px rgba(0,0,0,0.08)' ? 'selected' : ''; ?>>Medium Shadow</option>
                    </select>
                </div>
            </div>

        </div>

        <button type="submit" class="btn-save-pcc" id="btn-save">💾 Apply Card Design to Store</button>
    </form>
</div>

<script>
document.getElementById('pcc-form').addEventListener('submit', function(e) {
    e.preventDefault();
    let btn = document.getElementById('btn-save');
    let status = document.getElementById('pcc-status');
    btn.innerText = 'Saving Settings...'; btn.disabled = true;

    fetch('ajax_save_card_customizer.php', { method: 'POST', body: new FormData(this) })
    .then(res => res.json())
    .then(data => {
        btn.innerText = '💾 Apply Card Design to Store'; btn.disabled = false;
        if(data.status === 'success') {
            status.innerText = '✅ Product Card settings updated successfully!';
            status.style.display = 'block';
            setTimeout(() => status.style.display = 'none', 3000);
        } else { alert('Error saving settings.'); }
    }).catch(err => {
        btn.innerText = '💾 Apply Card Design to Store'; btn.disabled = false;
        alert('Network error.');
    });
});
</script>