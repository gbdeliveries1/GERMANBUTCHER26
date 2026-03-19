<?php
// Auto-create table and default sections specifically for this 3-column layout
$conn->query("CREATE TABLE IF NOT EXISTS homepage_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    content_data LONGTEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$check = $conn->query("SELECT COUNT(*) as c FROM homepage_sections")->fetch_assoc()['c'];
if ($check == 0) {
    $conn->query("INSERT INTO homepage_sections (section_key, name, content_data, sort_order, is_active) VALUES
    ('hot_items', 'Left Sidebar (Hot Items)', '{\"title\":\"Hot Items\"}', 1, 1),
    ('slider', 'Main Hero Slider', '{\"badge\":\"Best Deals\"}', 2, 1),
    ('quick_links', 'Quick Links (Next to Slider)', '{\"link1\":\"Order via WhatsApp\",\"link2\":\"Hot Deals\",\"link3\":\"Fast Delivery\",\"link4\":\"Quality Products\"}', 3, 1),
    ('category_blocks', 'Shop by Category', '{\"title\":\"Shop by Category\"}', 4, 1),
    ('featured_products', 'Featured Products', '{\"title\":\"Featured Products\"}', 5, 1),
    ('promo_banners', 'Promotional Banners', '{}', 6, 1),
    ('all_products', 'All Products Grid (Main)', '{\"title\":\"All Products\"}', 7, 1),
    ('features', 'Bottom Features (Fast Delivery, etc)', '{}', 8, 1),
    ('whatsapp_cta', 'Bottom WhatsApp CTA', '{\"title\":\"Quick Order via WhatsApp\",\"phone\":\"+250 783 654 454\"}', 9, 1),
    ('best_deals', 'Right Sidebar (Best Sellers)', '{\"title\":\"Best Sellers\"}', 10, 1)");
}

// Fetch all sections
$query = "SELECT * FROM homepage_sections ORDER BY sort_order ASC";
$result = $conn->query($query);
$sections = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row;
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<style>
    .builder-container { max-width: 1000px; margin: 0 auto; font-family: sans-serif; }
    .builder-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .section-list { list-style: none; padding: 0; margin: 0; }
    .section-item { background: #fff; border: 1px solid #ddd; margin-bottom: 10px; padding: 15px 20px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; cursor: grab; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .section-item:active { cursor: grabbing; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .section-info { display: flex; align-items: center; gap: 15px; }
    .drag-handle { color: #aaa; font-size: 20px; }
    .section-actions { display: flex; gap: 10px; align-items: center; }
    .btn-edit { background: #3b82f6; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 13px; }
    
    .switch { position: relative; display: inline-block; width: 44px; height: 24px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px; }
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: #10b981; }
    input:checked + .slider:before { transform: translateX(20px); }

    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
    .modal-content { background: #fff; padding: 25px; border-radius: 8px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
    .modal-header { display: flex; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    .close-modal { cursor: pointer; font-size: 20px; border: none; background: none; }
    
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
    .form-group input[type="text"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
</style>

<div class="builder-container">
    <div class="builder-header">
        <h2>🏗️ Homepage Builder</h2>
        <span id="save-status" style="color: #10b981; font-weight: bold; display:none;">Saved!</span>
    </div>

    <ul class="section-list" id="sortable-list">
        <?php foreach ($sections as $sec): ?>
            <li class="section-item" data-id="<?php echo $sec['id']; ?>">
                <div class="section-info">
                    <span class="drag-handle">☰</span>
                    <strong><?php echo htmlspecialchars($sec['name']); ?></strong>
                </div>
                <div class="section-actions">
                    <label class="switch">
                        <input type="checkbox" onchange="toggleSection(<?php echo $sec['id']; ?>, this.checked)" <?php echo $sec['is_active'] ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                    <button class="btn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($sec)); ?>)">✏️ Edit Content</button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="modal-overlay" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Edit Section</h3>
            <button class="close-modal" onclick="closeEditModal()">✖</button>
        </div>
        <form id="editForm" method="POST" action="ajax_homepage_builder.php">
            <input type="hidden" name="action" value="update_content">
            <input type="hidden" name="section_id" id="modalSectionId">
            <div id="dynamicFormFields"></div>
            <button type="submit" class="btn-edit" style="width:100%; padding:10px; font-size:16px; margin-top:10px;">💾 Save Changes</button>
        </form>
    </div>
</div>

<script>
var el = document.getElementById('sortable-list');
var sortable = Sortable.create(el, {
    handle: '.drag-handle',
    animation: 150,
    onEnd: function () {
        let order = [];
        document.querySelectorAll('.section-item').forEach((item, index) => {
            order.push({ id: item.getAttribute('data-id'), sort: index + 1 });
        });
        
        fetch('ajax_homepage_builder.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'reorder', data: order })
        }).then(() => showStatus('Order Saved!'));
    }
});

function toggleSection(id, isActive) {
    let formData = new FormData();
    formData.append('action', 'toggle');
    formData.append('id', id);
    formData.append('is_active', isActive ? 1 : 0);

    fetch('ajax_homepage_builder.php', {
        method: 'POST',
        body: formData
    }).then(() => showStatus('Status Updated!'));
}

function showStatus(text) {
    let el = document.getElementById('save-status');
    el.innerText = text;
    el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 2000);
}

function openEditModal(section) {
    document.getElementById('editModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Edit ' + section.name;
    document.getElementById('modalSectionId').value = section.id;
    
    let content = JSON.parse(section.content_data || '{}');
    let html = '';

    for (const [key, value] of Object.entries(content)) {
        let label = key.replace(/_/g, ' ').toUpperCase();
        html += `<div class="form-group">
                    <label>${label}</label>
                    <input type="text" name="text_${key}" value="${value}">
                 </div>`;
    }
    
    if(html === '') html = '<p>No editable text for this section.</p>';
    document.getElementById('dynamicFormFields').innerHTML = html;
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>