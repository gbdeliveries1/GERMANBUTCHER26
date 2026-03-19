<?php
// Fetch current settings
$settings = [];
$res = $conn->query("SELECT setting_key, setting_value FROM site_design_settings WHERE setting_key LIKE 'pc_%'");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
// Helper to get setting with default fallback
$get_set = function($key, $default) use ($settings) {
    return isset($settings[$key]) ? htmlspecialchars($settings[$key], ENT_QUOTES, 'UTF-8') : $default;
};
?>

<style>
    /* Admin Customizer Scoped Styles */
    .pc-wrapper { 
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
        max-width: 1200px; 
        margin: 0 auto; 
        color: #374151;
    }
    
    .pc-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        padding: 20px 25px;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    .pc-header h2 { margin: 0; font-size: 22px; color: #111827; display: flex; align-items: center; gap: 10px; }
    
    .pc-layout {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 25px;
    }

    /* Form Section */
    .pc-card { 
        background: #fff; 
        padding: 25px; 
        border-radius: 10px; 
        box-shadow: 0 1px 3px rgba(0,0,0,0.05); 
    }
    .pc-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
        gap: 25px; 
    }
    
    .pc-section-title {
        color: #111827; 
        font-size: 16px; 
        border-bottom: 2px solid #f3f4f6; 
        padding-bottom: 8px; 
        margin-bottom: 15px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pc-section-title i { color: #ff5000; }

    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .form-group label { font-weight: 600; font-size: 13px; color: #4b5563; }
    .form-group select, .form-group input[type="number"] { 
        width: 100%; 
        padding: 10px 12px; 
        border: 1px solid #d1d5db; 
        border-radius: 6px; 
        font-size: 14px; 
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-group select:focus, .form-group input[type="number"]:focus {
        border-color: #ff5000;
        box-shadow: 0 0 0 3px rgba(255,80,0,0.1);
    }
    
    .color-picker-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .form-group input[type="color"] { 
        width: 45px; 
        height: 45px; 
        border: 1px solid #d1d5db; 
        border-radius: 6px; 
        cursor: pointer; 
        padding: 2px; 
        background: #fff;
    }
    .color-hex { font-family: monospace; font-size: 13px; color: #6b7280; font-weight: bold; }

    /* Action Bar */
    .pc-actions {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .btn-save { 
        background: linear-gradient(135deg, #ff5000, #ff6a33); 
        color: #fff; 
        border: none; 
        padding: 14px 30px; 
        border-radius: 8px; 
        cursor: pointer; 
        font-weight: 800; 
        font-size: 15px; 
        transition: 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 6px rgba(255,80,0,0.2);
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(255,80,0,0.3); }
    .btn-save:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

    /* Notification Toast */
    #save-status { 
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px; 
        border-radius: 8px; 
        font-weight: 700; 
        font-size: 14px;
        background: #d1fae5; 
        color: #065f46; 
        border: 1px solid #34d399;
        opacity: 0;
        visibility: hidden;
        transition: 0.3s;
        transform: translateY(10px);
    }
    #save-status.show { opacity: 1; visibility: visible; transform: translateY(0); }
    #save-status.error { background: #fee2e2; color: #991b1b; border-color: #f87171; }

    /* =========================================
       LIVE PREVIEW CARD STYLES 
       ========================================= */
    .pc-preview-panel {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        border: 1px dashed #d1d5db;
        position: sticky;
        top: 20px;
    }
    .pc-preview-header {
        text-align: center;
        font-size: 12px;
        font-weight: bold;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 20px;
    }

    /* The Mock Card */
    #live-preview-card {
        background: #fff;
        margin: 0 auto;
        max-width: 260px;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
        --card-radius: 8px;
        --price-color: #ff5000;
        --btn-color: #ff5000;
        border-radius: var(--card-radius);
    }
    
    /* Layout Logic */
    #live-preview-card.elevated { box-shadow: 0 10px 20px rgba(0,0,0,0.08); border: 1px solid transparent; }
    #live-preview-card.bordered { box-shadow: none; border: 1px solid #e5e7eb; }
    #live-preview-card.minimal { box-shadow: none; border: 1px solid transparent; background: transparent; }

    .mock-img-area {
        aspect-ratio: 1;
        background: #f3f4f6;
        border-radius: var(--card-radius) var(--card-radius) 0 0;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 40px;
    }
    #live-preview-card.minimal .mock-img-area { border-radius: var(--card-radius); }
    
    .mock-badge {
        position: absolute;
        top: 10px; left: 10px;
        background: #ef4444;
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        padding: 4px 8px;
        border-radius: 4px;
        transition: 0.3s;
    }

    .mock-info { padding: 15px; display: flex; flex-direction: column; gap: 8px; }
    .mock-title { font-size: 14px; font-weight: 600; color: #111827; }
    .mock-rating { color: #fbbf24; font-size: 10px; }
    .mock-price { font-size: 18px; font-weight: 800; color: var(--price-color); transition: color 0.3s; }
    
    .mock-btn {
        width: 100%;
        padding: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-top: 5px;
        transition: all 0.3s ease;
    }
    
    /* Button Styles Logic */
    .mock-btn.solid { background: var(--btn-color); color: #fff; border: 1px solid var(--btn-color); border-radius: 6px; }
    .mock-btn.outline { background: transparent; color: var(--btn-color); border: 2px solid var(--btn-color); border-radius: 6px; }
    .mock-btn.pill { background: var(--btn-color); color: #fff; border: 1px solid var(--btn-color); border-radius: 50px; }

    @media (max-width: 992px) {
        .pc-layout { grid-template-columns: 1fr; }
        .pc-preview-panel { display: none; /* Hide preview on mobile to save space */ }
    }
</style>

<div class="pc-wrapper">
    
    <div class="pc-header">
        <h2><i class="fas fa-paint-roller" style="color:#ff5000;"></i> Shop UI Customizer</h2>
    </div>

    <div class="pc-layout">
        
        <!-- SETTINGS FORM -->
        <div class="pc-card">
            <form id="product-customizer-form">
                <div class="pc-grid">
                    
                    <!-- 1. Product Card Settings -->
                    <div>
                        <h3 class="pc-section-title"><i class="fas fa-th-large"></i> Product Grid View</h3>
                        
                        <div class="form-group">
                            <label>Card Layout Style</label>
                            <select name="pc_card_layout" id="inp-layout">
                                <option value="elevated" <?php echo $get_set('pc_card_layout', 'elevated') == 'elevated' ? 'selected' : ''; ?>>Elevated (Shadows)</option>
                                <option value="bordered" <?php echo $get_set('pc_card_layout', 'elevated') == 'bordered' ? 'selected' : ''; ?>>Bordered (Lines)</option>
                                <option value="minimal" <?php echo $get_set('pc_card_layout', 'elevated') == 'minimal' ? 'selected' : ''; ?>>Minimal (No borders)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Card Border Radius (px)</label>
                            <input type="number" name="pc_card_radius" id="inp-radius" value="<?php echo $get_set('pc_card_radius', '8'); ?>" min="0" max="40">
                        </div>

                        <div class="form-group">
                            <label>Show Product Badges (Hot/Sale)?</label>
                            <select name="pc_badge_display" id="inp-badge">
                                <option value="show" <?php echo $get_set('pc_badge_display', 'show') == 'show' ? 'selected' : ''; ?>>Yes, show badges</option>
                                <option value="hide" <?php echo $get_set('pc_badge_display', 'show') == 'hide' ? 'selected' : ''; ?>>No, hide them</option>
                            </select>
                        </div>
                    </div>

                    <!-- 2. Colors & Typography -->
                    <div>
                        <h3 class="pc-section-title"><i class="fas fa-palette"></i> Colors & Buttons</h3>

                        <div class="form-group">
                            <label>Price Text Color</label>
                            <div class="color-picker-wrapper">
                                <input type="color" name="pc_price_color" id="inp-price-color" value="<?php echo $get_set('pc_price_color', '#ff5000'); ?>" oninput="document.getElementById('hex-price').innerText=this.value">
                                <span class="color-hex" id="hex-price"><?php echo strtoupper($get_set('pc_price_color', '#FF5000')); ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Add-to-Cart Button Color</label>
                            <div class="color-picker-wrapper">
                                <input type="color" name="pc_btn_color" id="inp-btn-color" value="<?php echo $get_set('pc_btn_color', '#ff5000'); ?>" oninput="document.getElementById('hex-btn').innerText=this.value">
                                <span class="color-hex" id="hex-btn"><?php echo strtoupper($get_set('pc_btn_color', '#FF5000')); ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Add-to-Cart Button Style</label>
                            <select name="pc_btn_style" id="inp-btn-style">
                                <option value="solid" <?php echo $get_set('pc_btn_style', 'solid') == 'solid' ? 'selected' : ''; ?>>Solid Block</option>
                                <option value="outline" <?php echo $get_set('pc_btn_style', 'solid') == 'outline' ? 'selected' : ''; ?>>Outline (Ghost)</option>
                                <option value="pill" <?php echo $get_set('pc_btn_style', 'solid') == 'pill' ? 'selected' : ''; ?>>Rounded Pill</option>
                            </select>
                        </div>
                    </div>

                    <!-- 3. Product Detail Page Settings -->
                    <div>
                        <h3 class="pc-section-title"><i class="fas fa-info-circle"></i> Product Detail Page</h3>

                        <div class="form-group">
                            <label>Gallery Thumbnail Position</label>
                            <select name="pc_gallery_layout">
                                <option value="bottom" <?php echo $get_set('pc_gallery_layout', 'bottom') == 'bottom' ? 'selected' : ''; ?>>Below Main Image</option>
                                <option value="left" <?php echo $get_set('pc_gallery_layout', 'bottom') == 'left' ? 'selected' : ''; ?>>Left of Main Image</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Information Tabs Layout</label>
                            <select name="pc_tab_style">
                                <option value="horizontal" <?php echo $get_set('pc_tab_style', 'horizontal') == 'horizontal' ? 'selected' : ''; ?>>Horizontal Tabs</option>
                                <option value="accordion" <?php echo $get_set('pc_tab_style', 'horizontal') == 'accordion' ? 'selected' : ''; ?>>Vertical Accordion</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Related Products Count</label>
                            <input type="number" name="pc_related_count" value="<?php echo $get_set('pc_related_count', '4'); ?>" min="0" max="12">
                        </div>
                    </div>

                </div>

                <div class="pc-actions">
                    <div id="save-status"><i class="fas fa-check-circle"></i> <span>Settings saved successfully!</span></div>
                    <button type="submit" class="btn-save" id="btn-save-pc"><i class="fas fa-save"></i> Save Settings</button>
                </div>
            </form>
        </div>

        <!-- LIVE PREVIEW PANEL -->
        <div class="pc-preview-panel d-none d-lg-block">
            <div class="pc-preview-header">Live Preview</div>
            
            <div id="live-preview-card" class="mock-card">
                <div class="mock-img-area">
                    <i class="fas fa-image"></i>
                    <span class="mock-badge" id="mock-badge"><i class="fas fa-fire"></i> HOT</span>
                </div>
                <div class="mock-info">
                    <div class="mock-title">Premium Sample Product</div>
                    <div class="mock-rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    </div>
                    <div class="mock-price">15,000 RWF</div>
                    <button class="mock-btn" id="mock-btn"><i class="fas fa-shopping-cart"></i> Add to cart</button>
                </div>
            </div>
            
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- LIVE PREVIEW ENGINE ---
    const form = document.getElementById('product-customizer-form');
    const previewCard = document.getElementById('live-preview-card');
    const mockBadge = document.getElementById('mock-badge');
    const mockBtn = document.getElementById('mock-btn');

    function updateLivePreview() {
        const fd = new FormData(form);
        
        // Update Class for Layout
        previewCard.className = fd.get('pc_card_layout');
        
        // Update CSS Variables for Radius & Colors
        previewCard.style.setProperty('--card-radius', fd.get('pc_card_radius') + 'px');
        previewCard.style.setProperty('--price-color', fd.get('pc_price_color'));
        previewCard.style.setProperty('--btn-color', fd.get('pc_btn_color'));
        
        // Update Badge Display
        mockBadge.style.display = fd.get('pc_badge_display') === 'show' ? 'block' : 'none';
        
        // Update Button Style Class
        mockBtn.className = 'mock-btn ' + fd.get('pc_btn_style');
    }

    // Trigger update on any input change inside the form
    form.addEventListener('input', updateLivePreview);
    form.addEventListener('change', updateLivePreview); // Fallback for selects
    
    // Initialize preview immediately on load
    updateLivePreview();

    // --- FORM SUBMISSION ENGINE ---
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btn-save-pc');
        const statusBox = document.getElementById('save-status');
        const statusText = statusBox.querySelector('span');
        const statusIcon = statusBox.querySelector('i');
        
        // Loading State
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...'; 
        btn.disabled = true;
        statusBox.classList.remove('show', 'error');

        fetch('ajax_product_customizer.php', {
            method: 'POST', 
            body: new FormData(this)
        })
        .then(res => {
            if (!res.ok) throw new Error('Network error.');
            return res.json();
        })
        .then(data => {
            btn.innerHTML = '<i class="fas fa-save"></i> Save Settings'; 
            btn.disabled = false;
            
            if(data.status === 'success') {
                statusBox.classList.remove('error');
                statusIcon.className = 'fas fa-check-circle';
                statusText.innerText = 'Settings published live!';
                statusBox.classList.add('show');
            } else {
                throw new Error(data.message || 'Error saving settings.');
            }
        })
        .catch(err => {
            btn.innerHTML = '<i class="fas fa-save"></i> Save Settings'; 
            btn.disabled = false;
            
            statusBox.classList.add('error');
            statusIcon.className = 'fas fa-exclamation-triangle';
            statusText.innerText = err.message;
            statusBox.classList.add('show');
        })
        .finally(() => {
            // Auto hide toast after 3.5 seconds
            setTimeout(() => {
                statusBox.classList.remove('show');
            }, 3500);
        });
    });
});
</script>