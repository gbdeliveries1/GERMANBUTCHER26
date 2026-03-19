<?php
// Fetch the JSON arrays from the DB
$info_blocks_json = $conn->query("SELECT setting_value FROM site_design_settings WHERE setting_key = 'ux_product_info_blocks'")->fetch_assoc()['setting_value'] ?? '[]';
$bottom_blocks_json = $conn->query("SELECT setting_value FROM site_design_settings WHERE setting_key = 'ux_product_bottom_blocks'")->fetch_assoc()['setting_value'] ?? '[]';

$info_blocks = json_decode($info_blocks_json, true);
$bottom_blocks = json_decode($bottom_blocks_json, true);
?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<style>
    .ux-builder { max-width: 1000px; margin: 0 auto; font-family: sans-serif; display: flex; gap: 30px; }
    .ux-zone { background: #f8f9fa; padding: 20px; border-radius: 8px; border: 2px dashed #ccc; flex: 1; }
    .ux-zone h3 { margin-top: 0; color: #ff5000; font-size: 16px; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
    
    .ux-list { list-style: none; padding: 0; margin: 0; min-height: 100px; }
    .ux-item { background: #fff; border: 1px solid #ddd; margin-bottom: 10px; padding: 12px 15px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; cursor: grab; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .ux-item:active { cursor: grabbing; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-color: #ff5000; }
    
    .ux-info { display: flex; align-items: center; gap: 10px; font-weight: 600; font-size: 14px; color: #333; }
    .drag-handle { color: #aaa; font-size: 18px; cursor: grab; }
    
    /* Toggle Switch */
    .switch { position: relative; display: inline-block; width: 40px; height: 20px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px; }
    .slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: #ff5000; }
    input:checked + .slider:before { transform: translateX(20px); }

    .btn-save-ux { background: #212121; color: #fff; border: none; padding: 15px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 16px; width: 100%; margin-top: 20px; transition: 0.3s; }
    .btn-save-ux:hover { background: #000; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
</style>

<div style="max-width: 1000px; margin: 0 auto;">
    <h2 style="margin-bottom: 5px;">🧩 Advanced Product UX Builder</h2>
    <p style="color: #666; margin-bottom: 25px;">Drag and drop the elements below to restructure your Product Detail page. Use the switches to hide elements you don't need.</p>

    <div class="ux-builder">
        
        <!-- ZONE 1: Product Info Column -->
        <div class="ux-zone">
            <h3><i class="fas fa-align-right"></i> Right Column (Info Area)</h3>
            <ul class="ux-list" id="sortable-info">
                <?php foreach ($info_blocks as $block): ?>
                <li class="ux-item" data-id="<?php echo $block['id']; ?>" data-name="<?php echo $block['name']; ?>">
                    <div class="ux-info"><span class="drag-handle">☰</span> <?php echo $block['name']; ?></div>
                    <label class="switch">
                        <input type="checkbox" class="toggle-active" <?php echo $block['active'] ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- ZONE 2: Bottom Sections -->
        <div class="ux-zone">
            <h3><i class="fas fa-stream"></i> Bottom Layout (Full Width)</h3>
            <ul class="ux-list" id="sortable-bottom">
                <?php foreach ($bottom_blocks as $block): ?>
                <li class="ux-item" data-id="<?php echo $block['id']; ?>" data-name="<?php echo $block['name']; ?>">
                    <div class="ux-info"><span class="drag-handle">☰</span> <?php echo $block['name']; ?></div>
                    <label class="switch">
                        <input type="checkbox" class="toggle-active" <?php echo $block['active'] ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>

    <button class="btn-save-ux" onclick="saveUXLayout()">💾 Publish UX Layout</button>
</div>

<script>
// Init Drag and Drop for both zones
Sortable.create(document.getElementById('sortable-info'), { animation: 150, handle: '.drag-handle' });
Sortable.create(document.getElementById('sortable-bottom'), { animation: 150, handle: '.drag-handle' });

function saveUXLayout() {
    let btn = document.querySelector('.btn-save-ux');
    btn.innerText = 'Publishing...';
    
    // Scrape Zone 1 Layout
    let infoBlocks = [];
    document.querySelectorAll('#sortable-info .ux-item').forEach(item => {
        infoBlocks.push({
            id: item.getAttribute('data-id'),
            name: item.getAttribute('data-name'),
            active: item.querySelector('.toggle-active').checked ? 1 : 0
        });
    });

    // Scrape Zone 2 Layout
    let bottomBlocks = [];
    document.querySelectorAll('#sortable-bottom .ux-item').forEach(item => {
        bottomBlocks.push({
            id: item.getAttribute('data-id'),
            name: item.getAttribute('data-name'),
            active: item.querySelector('.toggle-active').checked ? 1 : 0
        });
    });

    // Send via AJAX Form Data
    let formData = new FormData();
    formData.append('action', 'save_product_ux');
    formData.append('info_blocks', JSON.stringify(infoBlocks));
    formData.append('bottom_blocks', JSON.stringify(bottomBlocks));

    fetch('ajax_product_ux.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            btn.style.background = '#22c55e';
            btn.innerText = '✅ UX Published Successfully!';
            setTimeout(() => { btn.style.background = '#212121'; btn.innerText = '💾 Publish UX Layout'; }, 3000);
        }
    });
}
</script>