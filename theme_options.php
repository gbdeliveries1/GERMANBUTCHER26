<?php
// sections/theme_options.php
// Theme Options - Flatsome-like customization for your custom PHP site

if (!isset($conn) || !($conn instanceof mysqli)) {
    echo "<div class='ali-card' style='padding:18px;'>DB connection missing.</div>";
    return;
}

// Ensure settings table exists
$conn->query("CREATE TABLE IF NOT EXISTS gb_settings (
    setting_key VARCHAR(120) PRIMARY KEY,
    setting_value LONGTEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Load current options
$defaults = [
    "font_family" => "inter",
    "base_font_size" => "14",
    "radius" => "12",

    "card_style" => "shadow",
    "card_image_ratio" => "1:1",
    "card_image_fit" => "cover",

    "product_grid_desktop" => "5",
    "product_grid_tablet" => "3",
    "product_grid_mobile" => "2",

    "container_width" => "1400",
    "header_layout" => "default",
    "btn_style" => "pill",
    "spacing" => "normal",

    "primary_color" => "#ff6000",
    "accent_color" => "#ff4747",
    "muted_color" => "#666666",
    "bg_color" => "#f5f5f5",
    "text_color" => "#191919",
    "price_color" => "#ff4747",

    "show_badges" => "1",
    "show_category_chip" => "1",
    "show_unit_chip" => "1",
    "show_quick_add" => "1"
];

$current = $defaults;

$res = $conn->query("SELECT setting_value FROM gb_settings WHERE setting_key='theme_options' LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $json = $row['setting_value'];
    $arr = json_decode($json, true);
    if (is_array($arr)) {
        $current = array_merge($current, $arr);
    }
}
?>

<div class="ali-card" style="padding:18px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div>
            <h2 style="margin:0;font-size:18px;">🎨 Appearance (Theme Options)</h2>
            <p style="margin:6px 0 0;color:#666;font-size:13px;">
                Customize font, colors, layout and product grid — like Flatsome Theme Options.
            </p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button class="ali-btn" type="button" onclick="gbThemeReset()">Reset</button>
            <button class="ali-btn ali-btn-primary" type="button" onclick="gbThemeSave()">Save Changes</button>
        </div>
    </div>

    <hr style="border:none;border-top:1px solid #eee;margin:16px 0;">

    <div style="display:grid;grid-template-columns:repeat(12,1fr);gap:14px;">
        <!-- Typography -->
        <div style="grid-column: span 12;" class="ali-card" >
            <div style="padding:14px;">
                <h3 style="margin:0 0 10px;font-size:14px;">Typography</h3>

                <div style="display:grid;grid-template-columns:repeat(12,1fr);gap:12px;">
                    <div style="grid-column: span 4;">
                        <label class="ali-label">Font Family</label>
                        <select id="font_family" class="ali-input">
                            <option value="system" <?php echo $current['font_family']=='system'?'selected':''; ?>>System</option>
                            <option value="inter" <?php echo $current['font_family']=='inter'?'selected':''; ?>>Inter</option>
                            <option value="roboto" <?php echo $current['font_family']=='roboto'?'selected':''; ?>>Roboto</option>
                            <option value="poppins" <?php echo $current['font_family']=='poppins'?'selected':''; ?>>Poppins</option>
                        </select>
                    </div>

                    <div style="grid-column: span 4;">
                        <label class="ali-label">Base Font Size (12–18)</label>
                        <input id="base_font_size" class="ali-input" type="number" min="12" max="18" value="<?php echo htmlspecialchars($current['base_font_size']); ?>">
                    </div>

                    <div style="grid-column: span 4;">
                        <label class="ali-label">Corner Radius (6–30)</label>
                        <input id="radius" class="ali-input" type="number" min="6" max="30" value="<?php echo htmlspecialchars($current['radius']); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Layout -->
        <div style="grid-column: span 12;" class="ali-card">
            <div style="padding:14px;">
                <h3 style="margin:0 0 10px;font-size:14px;">Layout</h3>

                <div style="display:grid;grid-template-columns:repeat(12,1fr);gap:12px;">
                    <div style="grid-column: span 4;">
                        <label class="ali-label">Container Width (980–1600)</label>
                        <input id="container_width" class="ali-input" type="number" min="980" max="1600" value="<?php echo htmlspecialchars($current['container_width']); ?>">
                    </div>

                    <div style="grid-column: span 4;">
                        <label class="ali-label">Header Layout</label>
                        <select id="header_layout" class="ali-input">
                            <option value="default" <?php echo $current['header_layout']=='default'?'selected':''; ?>>Default</option>
                            <option value="compact" <?php echo $current['header_layout']=='compact'?'selected':''; ?>>Compact</option>
                        </select>
                    </div>

                    <div style="grid-column: span 4;">
                        <label class="ali-label">Spacing</label>
                        <select id="spacing" class="ali-input">
                            <option value="compact" <?php echo $current['spacing']=='compact'?'selected':''; ?>>Compact</option>
                            <option value="normal" <?php echo $current['spacing']=='normal'?'selected':''; ?>>Normal</option>
                            <option value="spacious" <?php echo $current['spacing']=='spacious'?'selected':''; ?>>Spacious</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product cards -->
        <div style="grid-column: span 12;" class="ali-card">
            <div style="padding:14px;">
                <h3 style="margin:0 0 10px;font-size:14px;">Product Cards</h3>

                <div style="display:grid;grid-template-columns:repeat(12,1fr);gap:12px;">
                    <div style="grid-column: span 4;">
                        <label class="ali-label">Card Style</label>
                        <select id="card_style" class="ali-input">
                            <option value="shadow" <?php echo $current['card_style']=='shadow'?'selected':''; ?>>Shadow</option>
                            <option value="border" <?php echo $current['card_style']=='border'?'selected':''; ?>>Border</option>
                            <option value="flat" <?php echo $current['card_style']=='flat'?'selected':''; ?>>Flat</option>
                        </select>
                    </div>

                    <div style="grid-column: span 4;">
                        <label class="ali-label">Image Ratio</label>
                        <select id="card_image_ratio" class="ali-input">
                            <option value="1:1" <?php echo $current['card_image_ratio']=='1:1'?'selected':''; ?>>1:1</option>
                            <option value="4:3" <?php echo $current['card_image_ratio']=='4:3'?'selected':''; ?>>4:3</option>
                            <option value="16:9" <?php echo $current['card_image_ratio']=='16:9'?'selected':''; ?>>16:9</option>
                        </select>
                    </div>

                    <div style="grid-column: span 4;">
                        <label class="ali-label">Image Fit</label>
                        <select id="card_image_fit" class="ali-input">
                            <option value="cover" <?php echo $current['card_image_fit']=='cover'?'selected':''; ?>>Cover</option>
                            <option value="contain" <?php echo $current['card_image_fit']=='contain'?'selected':''; ?>>Contain</option>
                        </select>
                    </div>

                    <div style="grid-column: span 4;">
                        <label class="ali-label">Grid Desktop (2–6)</label>
                        <input id="product_grid_desktop" class="ali-input" type="number" min="2" max="6" value="<?php echo htmlspecialchars($current['product_grid_desktop']); ?>">
                    </div>
                    <div style="grid-column: span 4;">
                        <label class="ali-label">Grid Tablet (1–4)</label>
                        <input id="product_grid_tablet" class="ali-input" type="number" min="1" max="4" value="<?php echo htmlspecialchars($current['product_grid_tablet']); ?>">
                    </div>
                    <div style="grid-column: span 4;">
                        <label class="ali-label">Grid Mobile (1–3)</label>
                        <input id="product_grid_mobile" class="ali-input" type="number" min="1" max="3" value="<?php echo htmlspecialchars($current['product_grid_mobile']); ?>">
                    </div>

                    <div style="grid-column: span 4;">
                        <label class="ali-label">Button Style</label>
                        <select id="btn_style" class="ali-input">
                            <option value="pill" <?php echo $current['btn_style']=='pill'?'selected':''; ?>>Pill</option>
                            <option value="rounded" <?php echo $current['btn_style']=='rounded'?'selected':''; ?>>Rounded</option>
                            <option value="square" <?php echo $current['btn_style']=='square'?'selected':''; ?>>Square</option>
                        </select>
                    </div>

                    <div style="grid-column: span 8; display:flex;align-items:end;gap:14px;flex-wrap:wrap;">
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;">
                            <input type="checkbox" id="show_badges" <?php echo $current['show_badges']=='1'?'checked':''; ?>> Show badges (SALE/NEW)
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;">
                            <input type="checkbox" id="show_quick_add" <?php echo $current['show_quick_add']=='1'?'checked':''; ?>> Show quick add buttons
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colors -->
        <div style="grid-column: span 12;" class="ali-card">
            <div style="padding:14px;">
                <h3 style="margin:0 0 10px;font-size:14px;">Colors</h3>

                <div style="display:grid;grid-template-columns:repeat(12,1fr);gap:12px;">
                    <div style="grid-column: span 4;">
                        <label class="ali-label">Primary</label>
                        <input id="primary_color" class="ali-input" type="color" value="<?php echo htmlspecialchars($current['primary_color']); ?>">
                    </div>
                    <div style="grid-column: span 4;">
                        <label class="ali-label">Accent</label>
                        <input id="accent_color" class="ali-input" type="color" value="<?php echo htmlspecialchars($current['accent_color']); ?>">
                    </div>
                    <div style="grid-column: span 4;">
                        <label class="ali-label">Price</label>
                        <input id="price_color" class="ali-input" type="color" value="<?php echo htmlspecialchars($current['price_color']); ?>">
                    </div>

                    <div style="grid-column: span 4;">
                        <label class="ali-label">Background</label>
                        <input id="bg_color" class="ali-input" type="color" value="<?php echo htmlspecialchars($current['bg_color']); ?>">
                    </div>
                    <div style="grid-column: span 4;">
                        <label class="ali-label">Text</label>
                        <input id="text_color" class="ali-input" type="color" value="<?php echo htmlspecialchars($current['text_color']); ?>">
                    </div>
                    <div style="grid-column: span 4;">
                        <label class="ali-label">Muted</label>
                        <input id="muted_color" class="ali-input" type="color" value="<?php echo htmlspecialchars($current['muted_color']); ?>">
                    </div>
                </div>

                <div style="margin-top:14px;background:#f9fafb;border:1px solid #eee;border-radius:12px;padding:12px;">
                    <div style="font-weight:700;margin-bottom:8px;">Live Preview</div>
                    <div id="gbPreview" style="border-radius:12px;padding:14px;background:#fff;border:1px solid #eee;">
                        <div style="display:flex;gap:10px;align-items:center;justify-content:space-between;flex-wrap:wrap;">
                            <div style="font-weight:900;">GB Deliveries</div>
                            <button type="button" style="border:none;padding:10px 14px;border-radius:999px;background:var(--p);color:#fff;font-weight:900;">Primary Button</button>
                        </div>
                        <div style="margin-top:10px;color:var(--m);">Muted text example</div>
                        <div style="margin-top:8px;font-weight:900;color:var(--price);">Price: 12,500 RWF</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
/* Minimal admin UI helpers (works with your Ali admin style) */
.ali-card{background:#fff;border:1px solid #eee;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,.04);}
.ali-label{display:block;font-size:12px;font-weight:800;color:#444;margin-bottom:6px;}
.ali-input{width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:12px;font-size:13px;outline:none;}
.ali-input:focus{border-color:#ff6000;box-shadow:0 0 0 3px rgba(255,96,0,.12);}
.ali-btn{padding:10px 14px;border-radius:12px;border:1px solid #e5e5e5;background:#fff;font-weight:900;cursor:pointer;}
.ali-btn-primary{background:#ff6000;border-color:#ff6000;color:#fff;}
@media(max-width:900px){
  .ali-card{border-radius:12px;}
}
</style>

<script>
function gbThemeCollect(){
    const data = {
        action: 'SAVE_THEME_OPTIONS',

        font_family: document.getElementById('font_family').value,
        base_font_size: document.getElementById('base_font_size').value,
        radius: document.getElementById('radius').value,

        card_style: document.getElementById('card_style').value,
        card_image_ratio: document.getElementById('card_image_ratio').value,
        card_image_fit: document.getElementById('card_image_fit').value,

        product_grid_desktop: document.getElementById('product_grid_desktop').value,
        product_grid_tablet: document.getElementById('product_grid_tablet').value,
        product_grid_mobile: document.getElementById('product_grid_mobile').value,

        container_width: document.getElementById('container_width').value,
        header_layout: document.getElementById('header_layout').value,
        btn_style: document.getElementById('btn_style').value,
        spacing: document.getElementById('spacing').value,

        primary_color: document.getElementById('primary_color').value,
        accent_color: document.getElementById('accent_color').value,
        muted_color: document.getElementById('muted_color').value,
        bg_color: document.getElementById('bg_color').value,
        text_color: document.getElementById('text_color').value,
        price_color: document.getElementById('price_color').value,

        show_badges: document.getElementById('show_badges').checked ? '1' : '0',
        show_quick_add: document.getElementById('show_quick_add').checked ? '1' : '0'
    };
    return data;
}

function gbThemePreview(){
    const p = document.getElementById('primary_color').value;
    const m = document.getElementById('muted_color').value;
    const price = document.getElementById('price_color').value;
    const el = document.getElementById('gbPreview');
    if(!el) return;
    el.style.setProperty('--p', p);
    el.style.setProperty('--m', m);
    el.style.setProperty('--price', price);
}

['primary_color','muted_color','price_color'].forEach(id=>{
    const x = document.getElementById(id);
    if(x) x.addEventListener('input', gbThemePreview);
});
gbThemePreview();

function gbThemeSave(){
    const data = gbThemeCollect();
    Swal.fire({title:'Saving...', allowOutsideClick:false, didOpen:()=>Swal.showLoading()});

    $.ajax({
        type:'POST',
        url:'action/insert.php',
        data: data,
        dataType:'json',
        success:function(res){
            if(res && res.status === 'ok'){
                Swal.fire({icon:'success', title:'Saved', text:'Theme options updated. Refresh frontend to see changes.'});
            }else{
                Swal.fire({icon:'error', title:'Error', text: (res && res.message) ? res.message : 'Save failed'});
            }
        },
        error:function(xhr){
            Swal.fire({icon:'error', title:'Error', text:'Save failed. Check insert.php and server logs.'});
        }
    });
}

function gbThemeReset(){
    Swal.fire({
        icon:'warning',
        title:'Reset to defaults?',
        showCancelButton:true,
        confirmButtonText:'Yes, reset'
    }).then((r)=>{
        if(!r.isConfirmed) return;
        // quick reset to defaults
        document.getElementById('font_family').value = 'inter';
        document.getElementById('base_font_size').value = 14;
        document.getElementById('radius').value = 12;

        document.getElementById('card_style').value = 'shadow';
        document.getElementById('card_image_ratio').value = '1:1';
        document.getElementById('card_image_fit').value = 'cover';

        document.getElementById('product_grid_desktop').value = 5;
        document.getElementById('product_grid_tablet').value = 3;
        document.getElementById('product_grid_mobile').value = 2;

        document.getElementById('container_width').value = 1400;
        document.getElementById('header_layout').value = 'default';
        document.getElementById('btn_style').value = 'pill';
        document.getElementById('spacing').value = 'normal';

        document.getElementById('primary_color').value = '#ff6000';
        document.getElementById('accent_color').value = '#ff4747';
        document.getElementById('price_color').value = '#ff4747';
        document.getElementById('bg_color').value = '#f5f5f5';
        document.getElementById('text_color').value = '#191919';
        document.getElementById('muted_color').value = '#666666';

        document.getElementById('show_badges').checked = true;
        document.getElementById('show_quick_add').checked = true;

        gbThemePreview();
    });
}
</script>