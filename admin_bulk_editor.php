<?php
/**
 * Admin Bulk Editor - Spreadsheet Manager
 * Enhanced with Product Units System & Add New Product Full Modal
 * 
 * @version 2.6
 * @updated 2026-03-22
 * 
 * PRODUCTION-READY VERSION
 * - Fixed all JavaScript errors
 * - Complete file with all functions
 * - PHP 8 compatible
 */

// Defensive check for database connection
if (!isset($conn) || $conn === null) {
    if (isset($connection)) $conn = $connection;
    elseif (isset($db)) $conn = $db;
    elseif (isset($mysqli)) $conn = $mysqli;
    elseif (isset($link)) $conn = $link;
}

if (!isset($conn) || $conn === null) {
    $db_paths = [
        '../on/on.php',
        '../../on/on.php',
        '../includes/db.php',
        '../../includes/db.php',
        '../config/database.php',
        'db_connect.php',
    ];
    foreach ($db_paths as $path) {
        if (file_exists($path)) {
            @include_once $path;
            break;
        }
    }
    if (!isset($conn) && isset($connection)) $conn = $connection;
    if (!isset($conn) && isset($db)) $conn = $db;
}

if (!isset($conn) || $conn === null || ($conn instanceof mysqli && $conn->connect_error)) {
    echo '<div style="padding:20px; background:#fee2e2; color:#991b1b; border-radius:8px; margin:20px;">
        <h3>Database Connection Error</h3>
        <p>Could not establish database connection. Please check your configuration.</p>
    </div>';
    return;
}

// Fetch categories
$cats = [];
$res = @$conn->query("SELECT category_id, category_name FROM product_category ORDER BY category_name ASC");
if ($res && $res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        $cats[] = $r;
    }
}

// Fetch subcategories
$subcats_by_cat = [];
$subcats_by_cat_id = [];
$res_sub = @$conn->query("SELECT sc.sub_category_id, sc.sub_category_name, sc.category_id, c.category_name 
                          FROM product_sub_category sc 
                          JOIN product_category c ON sc.category_id = c.category_id 
                          ORDER BY c.category_name, sc.sub_category_name");
if ($res_sub && $res_sub->num_rows > 0) {
    while ($r = $res_sub->fetch_assoc()) {
        $cat_name = $r['category_name'] ?? 'Uncategorized';
        $cat_id = $r['category_id'] ?? '';
        if (!isset($subcats_by_cat[$cat_name])) {
            $subcats_by_cat[$cat_name] = [];
        }
        $subcats_by_cat[$cat_name][] = $r;
        
        if (!isset($subcats_by_cat_id[$cat_id])) {
            $subcats_by_cat_id[$cat_id] = [];
        }
        $subcats_by_cat_id[$cat_id][] = [
            'sub_category_id' => $r['sub_category_id'],
            'sub_category_name' => $r['sub_category_name']
        ];
    }
}

// Define product units
$all_units = [
    ['id' => 1, 'code' => 'piece', 'name' => 'Piece', 'symbol' => 'pc', 'type' => 'quantity'],
    ['id' => 2, 'code' => 'dozen', 'name' => 'Dozen', 'symbol' => 'dz', 'type' => 'quantity'],
    ['id' => 3, 'code' => 'pair', 'name' => 'Pair', 'symbol' => 'pr', 'type' => 'quantity'],
    ['id' => 4, 'code' => 'set', 'name' => 'Set', 'symbol' => 'set', 'type' => 'quantity'],
    ['id' => 10, 'code' => 'kg', 'name' => 'Kilogram', 'symbol' => 'kg', 'type' => 'weight'],
    ['id' => 11, 'code' => 'gram', 'name' => 'Gram', 'symbol' => 'g', 'type' => 'weight'],
    ['id' => 12, 'code' => 'lb', 'name' => 'Pound', 'symbol' => 'lb', 'type' => 'weight'],
    ['id' => 13, 'code' => 'oz', 'name' => 'Ounce', 'symbol' => 'oz', 'type' => 'weight'],
    ['id' => 20, 'code' => 'liter', 'name' => 'Liter', 'symbol' => 'L', 'type' => 'volume'],
    ['id' => 21, 'code' => 'ml', 'name' => 'Milliliter', 'symbol' => 'mL', 'type' => 'volume'],
    ['id' => 22, 'code' => 'gallon', 'name' => 'Gallon', 'symbol' => 'gal', 'type' => 'volume'],
    ['id' => 30, 'code' => 'box', 'name' => 'Box', 'symbol' => 'box', 'type' => 'packaging'],
    ['id' => 31, 'code' => 'bottle', 'name' => 'Bottle', 'symbol' => 'btl', 'type' => 'packaging'],
    ['id' => 32, 'code' => 'pack', 'name' => 'Pack', 'symbol' => 'pk', 'type' => 'packaging'],
    ['id' => 33, 'code' => 'plate', 'name' => 'Plate', 'symbol' => 'plt', 'type' => 'packaging'],
    ['id' => 34, 'code' => 'tray', 'name' => 'Tray', 'symbol' => 'tray', 'type' => 'packaging'],
    ['id' => 35, 'code' => 'carton', 'name' => 'Carton', 'symbol' => 'ctn', 'type' => 'packaging'],
    ['id' => 36, 'code' => 'bundle', 'name' => 'Bundle', 'symbol' => 'bdl', 'type' => 'packaging'],
    ['id' => 37, 'code' => 'roll', 'name' => 'Roll', 'symbol' => 'roll', 'type' => 'packaging'],
    ['id' => 38, 'code' => 'can', 'name' => 'Can', 'symbol' => 'can', 'type' => 'packaging'],
    ['id' => 39, 'code' => 'jar', 'name' => 'Jar', 'symbol' => 'jar', 'type' => 'packaging'],
    ['id' => 40, 'code' => 'bag', 'name' => 'Bag', 'symbol' => 'bag', 'type' => 'packaging'],
    ['id' => 41, 'code' => 'sachet', 'name' => 'Sachet', 'symbol' => 'sac', 'type' => 'packaging'],
    ['id' => 42, 'code' => 'tin', 'name' => 'Tin', 'symbol' => 'tin', 'type' => 'packaging'],
    ['id' => 43, 'code' => 'tube', 'name' => 'Tube', 'symbol' => 'tube', 'type' => 'packaging'],
    ['id' => 44, 'code' => 'container', 'name' => 'Container', 'symbol' => 'cont', 'type' => 'packaging'],
    ['id' => 45, 'code' => 'serving', 'name' => 'Serving', 'symbol' => 'srv', 'type' => 'packaging'],
    ['id' => 46, 'code' => 'portion', 'name' => 'Portion', 'symbol' => 'ptn', 'type' => 'packaging'],
];

$units_grouped = ['quantity' => [], 'weight' => [], 'volume' => [], 'packaging' => []];
foreach ($all_units as $u) {
    $type = $u['type'] ?? 'quantity';
    if (isset($units_grouped[$type])) {
        $units_grouped[$type][] = $u;
    }
}

$cats_json = json_encode($cats, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG);
$subcats_json = json_encode($subcats_by_cat_id, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG);

if (!function_exists('safe_html')) {
    function safe_html($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>
<style>
.be-container{background:#fff;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.05);padding:20px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;position:relative}
.be-toolbar{display:flex;flex-wrap:wrap;gap:15px;margin-bottom:15px;align-items:center;justify-content:space-between;background:#f8f9fa;padding:12px 15px;border-radius:6px;border:1px solid #eee}
.be-group{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.be-input,.be-select{padding:8px 12px;border:1px solid #ccc;border-radius:4px;font-size:13px;outline:none}
.be-input:focus,.be-select:focus{border-color:#ff5000}
.be-btn{background:#fff;border:1px solid #ddd;padding:8px 15px;border-radius:4px;cursor:pointer;font-size:13px;font-weight:600;transition:0.2s;white-space:nowrap}
.be-btn:hover{background:#f1f5f9;border-color:#cbd5e1}
.be-btn-primary{background:#10b981;color:#fff;border-color:#10b981}
.be-btn-primary:hover{background:#059669;border-color:#059669;color:#fff}
.be-btn-danger{background:#ef4444;color:#fff;border-color:#ef4444}
.be-btn-danger:hover{background:#dc2626;border-color:#dc2626;color:#fff}
.drawer-panel{display:none;background:#fdfdfd;border:1px solid #e5e7eb;border-radius:6px;padding:20px;margin-bottom:20px}
.drawer-panel.active{display:block}
.f-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:15px}
.f-item label{display:block;font-size:11px;font-weight:bold;color:#6b7280;margin-bottom:4px;text-transform:uppercase}
.f-item input,.f-item select{width:100%}
.f-actions{display:flex;justify-content:space-between;align-items:center;margin-top:20px;padding-top:15px;border-top:1px solid #eee;flex-wrap:wrap;gap:10px}
.col-item{display:flex;align-items:center;padding:8px 12px;border:1px solid #ddd;background:#fff;margin-bottom:5px;border-radius:4px;gap:10px;transition:0.2s}
.col-item.dragging{opacity:0.4;background:#e0e7ff;border-color:#3b82f6}
.col-item.unhideable{background:#f9fafb;opacity:0.7}
.drag-handle{cursor:grab;color:#9ca3af;font-size:16px;padding-right:5px}
.drag-handle:active{cursor:grabbing}
.be-table-wrapper{width:100%;overflow-x:auto;height:60vh;border:1px solid #d1d5db;border-radius:4px;position:relative;margin-bottom:60px}
.be-table{border-collapse:collapse;table-layout:fixed;width:max-content;min-width:100%;transition:0.2s ease}
.be-table th{background:#f3f4f6;padding:10px;text-align:left;font-size:12px;color:#374151;font-weight:700;position:relative;border-bottom:2px solid #9ca3af;border-right:1px solid #d1d5db;white-space:nowrap;user-select:none}
.be-table td{border-bottom:1px solid #e5e7eb;border-right:1px solid #e5e7eb;padding:0;background:#fff;height:45px;vertical-align:middle;transition:height 0.2s ease}
.be-table tr:hover td{background:#f9fafb}
.be-table.compact td{height:32px}
.be-table.compact .cell-input{padding:4px 8px;font-size:12px}
.be-table.relaxed td{height:60px}
.be-table.relaxed .cell-input{padding:12px 8px;font-size:14px}
th.th-pinned,td.td-pinned{position:sticky!important;z-index:10}
th.th-pinned{background:#e5e7eb;z-index:20;border-right:2px solid #9ca3af}
td.td-pinned{background:#fff;border-right:2px solid #cbd5e1}
.resizer{position:absolute;top:0;right:0;width:5px;cursor:col-resize;user-select:none;height:100%;z-index:25}
.resizer:hover,.resizing{background:#3b82f6}
.cell-input{width:100%;height:100%;border:2px solid transparent;padding:8px;font-size:13px;box-sizing:border-box;background:transparent;outline:none;transition:0.1s;border-radius:0;color:#111}
.cell-input:focus{background:#fff;border-color:#3b82f6;box-shadow:0 0 0 1px #3b82f6 inset;z-index:5;position:relative}
.be-chk{cursor:pointer;width:16px;height:16px;margin:0 auto;display:block}
textarea.cell-input{resize:none;overflow:hidden;height:100%;white-space:nowrap;text-overflow:ellipsis;line-height:28px}
textarea.cell-input:focus{white-space:normal;overflow:auto;height:80px;position:absolute;top:0;left:0;box-shadow:0 4px 12px rgba(0,0,0,0.15)}
.saving{background-image:linear-gradient(45deg,#f0f9ff 25%,#e0f2fe 25%,#e0f2fe 50%,#f0f9ff 50%,#f0f9ff 75%,#e0f2fe 75%,#e0f2fe 100%);background-size:20px 20px;animation:barberpole 1s linear infinite}
.saved{background:#d1fae5!important;transition:background 0.8s ease}
.error{background:#fee2e2!important;border-color:#ef4444!important}
@keyframes barberpole{100%{background-position:20px 0}}
.img-upload-container{position:relative;display:inline-block;width:35px;height:35px;margin-left:10px;margin-top:4px;border-radius:4px;cursor:pointer;border:1px solid #ddd;background:#f0f0f0;overflow:visible}
.be-thumb{width:100%;height:100%;object-fit:cover;border-radius:3px;display:block}
.img-overlay{position:absolute;inset:0;background:rgba(0,0,0,0.6);color:#fff;font-size:10px;font-weight:bold;display:flex;align-items:center;justify-content:center;border-radius:3px;opacity:0;transition:0.2s;pointer-events:none}
.img-upload-container:hover .img-overlay{opacity:1}
.img-upload-container:hover .be-thumb{transform:scale(4) translateX(15px);position:absolute;z-index:100;box-shadow:0 4px 15px rgba(0,0,0,0.3);border:2px solid #fff}
.img-upload-container:hover .img-overlay{z-index:101;transform:scale(4) translateX(15px)}
.be-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:9999;align-items:center;justify-content:center}
.be-modal-overlay.active{display:flex}
.be-modal-content{background:#fff;padding:25px;border-radius:8px;width:500px;max-width:95vw;max-height:90vh;overflow-y:auto;position:relative;box-shadow:0 10px 25px rgba(0,0,0,0.5)}
.be-modal-content.large{width:850px}
.be-modal-content.xlarge{width:700px}
.be-modal-close{position:absolute;top:12px;right:15px;cursor:pointer;font-size:24px;color:#666;line-height:1}
.be-modal-close:hover{color:#111}
#bulk-action-bar{position:absolute;bottom:10px;left:50%;transform:translateX(-50%) translateY(100px);background:#1f2937;color:#fff;padding:15px 25px;border-radius:8px;display:flex;gap:15px;align-items:center;box-shadow:0 10px 25px rgba(0,0,0,0.2);transition:0.3s cubic-bezier(0.4,0,0.2,1);z-index:50;opacity:0;pointer-events:none;flex-wrap:wrap}
#bulk-action-bar.show{transform:translateX(-50%) translateY(0);opacity:1;pointer-events:auto}
#bulk-action-bar select,#bulk-action-bar input{background:#374151;color:#fff;border:1px solid #4b5563;padding:8px 12px;border-radius:4px;font-size:13px}
#bulk-action-bar .be-btn-primary{background:#3b82f6;border-color:#3b82f6}
#toast-container{position:fixed;top:20px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:10px;align-items:flex-end}
.toast{background:#333;color:#fff;padding:12px 20px;border-radius:6px;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,0.15);display:flex;align-items:center;gap:10px;opacity:0;transform:translateY(-20px);transition:0.3s;max-height:50px;min-width:150px;white-space:nowrap}
.toast.show{opacity:1;transform:translateY(0)}
.toast.success{border-left:4px solid #10b981}
.toast.error{border-left:4px solid #ef4444}
.hist-table{width:100%;border-collapse:collapse;font-size:12px;text-align:left}
.hist-table th{background:#f3f4f6;padding:8px;border-bottom:2px solid #ddd}
.hist-table td{padding:8px;border-bottom:1px solid #eee}
.badge{padding:3px 6px;border-radius:4px;font-size:10px;font-weight:bold}
.badge.rb-yes{background:#dcfce7;color:#065f46}
.badge.rb-no{background:#f3f4f6;color:#4b5563}
.badge.rb-done{background:#fee2e2;color:#991b1b}
.search-wrap{position:relative;display:flex;align-items:center}
.search-wrap .clear-icon{position:absolute;right:10px;cursor:pointer;color:#9ca3af;display:none;padding:5px}
.search-wrap .clear-icon:hover{color:#ef4444}
.add-product-form{display:grid;grid-template-columns:1fr 1fr;gap:15px}
.add-product-form .form-group{display:flex;flex-direction:column}
.add-product-form .form-group.full-width{grid-column:1/-1}
.add-product-form label{font-size:12px;font-weight:600;color:#374151;margin-bottom:5px;text-transform:uppercase}
.add-product-form label .required{color:#ef4444}
.add-product-form input,.add-product-form select,.add-product-form textarea{padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;transition:0.2s}
.add-product-form input:focus,.add-product-form select:focus,.add-product-form textarea:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.1)}
.add-product-form textarea{resize:vertical;min-height:80px}
.add-product-form .image-upload-area{border:2px dashed #d1d5db;border-radius:8px;padding:20px;text-align:center;cursor:pointer;transition:0.2s;background:#f9fafb}
.add-product-form .image-upload-area:hover{border-color:#3b82f6;background:#eff6ff}
.add-product-form .image-upload-area img{max-width:150px;max-height:150px;object-fit:contain;margin-bottom:10px;border-radius:4px}
.add-product-form .form-actions{grid-column:1/-1;display:flex;justify-content:flex-end;gap:10px;margin-top:10px;padding-top:15px;border-top:1px solid #e5e7eb}
</style>

<div class="be-container">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;flex-wrap:wrap;gap:10px;">
        <h2 style="margin:0;"><i class="fas fa-table"></i> Spreadsheet Manager</h2>
        <div id="grid-info" style="font-size:13px;color:#666;">Loading...</div>
    </div>

    <div class="be-toolbar">
        <div class="be-group">
            <button class="be-btn be-btn-primary" onclick="openAddProductModal()"><i class="fas fa-plus"></i> Add Product</button>
            <button class="be-btn" onclick="toggleDrawer('filter-drawer')"><i class="fas fa-filter"></i> Filters</button>
            <button class="be-btn" onclick="toggleDrawer('column-drawer')"><i class="fas fa-columns"></i> Customise View</button>
            <div style="width:1px;height:20px;background:#ddd;margin:0 5px;"></div>
            <button class="be-btn" onclick="openExportModal()"><i class="fas fa-file-export"></i> Export</button>
            <button class="be-btn" onclick="openImportModal()"><i class="fas fa-file-import"></i> Import</button>
            <button class="be-btn" onclick="openHistoryModal()"><i class="fas fa-history"></i> Audit Log</button>
            <div style="width:1px;height:20px;background:#ddd;margin:0 5px;"></div>
            <select id="preset-loader" class="be-select" onchange="loadPreset(this.value)"><option value="">Load Preset...</option></select>
        </div>
        <div class="be-group">
            <select id="f-search-field" class="be-select" onchange="resetPageAndLoad()">
                <option value="all">All Fields</option>
                <option value="product_name">Name</option>
                <option value="sku">SKU</option>
                <option value="tags">Tags</option>
            </select>
            <div class="search-wrap">
                <input type="text" id="f-search" class="be-input" placeholder="Search (comma for multi)..." onkeyup="handleSearchInput()" style="padding-right:30px;width:220px;">
                <i class="fas fa-times clear-icon" id="clear-search-icon" onclick="clearSearchInput()"></i>
            </div>
            <select id="f-limit" class="be-select" onchange="resetPageAndLoad()" style="margin-left:10px;">
                <option value="25">25 per page</option>
                <option value="50" selected>50 per page</option>
                <option value="100">100 per page</option>
                <option value="250">250 per page</option>
                <option value="500">500 per page</option>
            </select>
            <button class="be-btn" onclick="changePage(-1)">← Prev</button>
            <span id="page-num" style="font-size:13px;font-weight:bold;">Page 1</span>
            <button class="be-btn" onclick="changePage(1)">Next →</button>
        </div>
    </div>

    <div id="filter-drawer" class="drawer-panel">
        <form id="filter-form" onsubmit="event.preventDefault();resetPageAndLoad();">
            <div class="f-grid">
                <div class="f-item">
                    <label>Category</label>
                    <select id="f-cat" class="be-select" onchange="updateSubcats(this.value)">
                        <option value="">All Categories</option>
                        <?php foreach($cats as $c): ?>
                        <option value="<?php echo safe_html($c['category_id']); ?>"><?php echo safe_html($c['category_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="f-item"><label>Subcategory</label><select id="f-subcat" class="be-select"><option value="">All</option></select></div>
                <div class="f-item"><label>Stock Status</label><select id="f-stock" class="be-select"><option value="">Any</option><option value="in">In Stock</option><option value="out">Out of Stock</option></select></div>
                <div class="f-item"><label>Price Range</label><div style="display:flex;gap:5px;"><input type="text" inputmode="decimal" id="f-price-min" class="be-input" placeholder="Min"><input type="text" inputmode="decimal" id="f-price-max" class="be-input" placeholder="Max"></div></div>
                <div class="f-item"><label>Date Created</label><div style="display:flex;gap:5px;"><input type="text" id="f-date-add-from" class="be-input" placeholder="From"><input type="text" id="f-date-add-to" class="be-input" placeholder="To"></div></div>
                <div class="f-item"><label>Date Updated</label><div style="display:flex;gap:5px;"><input type="text" id="f-date-up-from" class="be-input" placeholder="From"><input type="text" id="f-date-up-to" class="be-input" placeholder="To"></div></div>
                <div class="f-item"><label>Product Status</label><select id="f-status" class="be-select"><option value="">Any</option><option value="1">Active</option><option value="0">Disabled</option></select></div>
                <div class="f-item"><label>Visibility</label><select id="f-vis" class="be-select"><option value="">Any</option><option value="visible">Visible</option><option value="hidden">Hidden</option></select></div>
                <div class="f-item"><label>Featured</label><select id="f-feat" class="be-select"><option value="">Any</option><option value="1">Featured Only</option><option value="0">Not Featured</option></select></div>
                <div class="f-item"><label>Exact Match</label><label style="margin-top:8px;"><input type="checkbox" id="f-exact"> Enable</label></div>
            </div>
            <div class="f-actions">
                <div class="be-group"><button type="submit" class="be-btn be-btn-primary">Apply Filters</button><button type="button" class="be-btn" onclick="clearFilters()">Clear All</button></div>
                <div class="be-group"><input type="text" id="preset-name" class="be-input" placeholder="Preset name..."><button type="button" class="be-btn" onclick="savePreset()"><i class="fas fa-save"></i> Save</button></div>
            </div>
        </form>
    </div>

    <div id="column-drawer" class="drawer-panel" style="max-width:600px;">
        <div style="margin-bottom:15px;padding-bottom:15px;border-bottom:1px solid #eee;">
            <strong style="display:block;margin-bottom:10px;color:#374151;font-size:13px;"><i class="fas fa-layer-group"></i> Display Density</strong>
            <div style="display:flex;gap:15px;font-size:13px;">
                <label style="cursor:pointer;"><input type="radio" name="tbl_density" value="compact" onchange="applyDensity()"> Compact</label>
                <label style="cursor:pointer;"><input type="radio" name="tbl_density" value="normal" checked onchange="applyDensity()"> Normal</label>
                <label style="cursor:pointer;"><input type="radio" name="tbl_density" value="relaxed" onchange="applyDensity()"> Relaxed</label>
            </div>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <strong style="color:#374151;font-size:13px;"><i class="fas fa-columns"></i> Configure Columns</strong>
            <div style="font-size:12px;">
                <a href="#" onclick="toggleAllCols(true);return false;" style="color:#3b82f6;text-decoration:none;">Show All</a> | 
                <a href="#" onclick="toggleAllCols(false);return false;" style="color:#3b82f6;text-decoration:none;">Hide Unpinned</a>
            </div>
        </div>
        <ul id="col-list" style="list-style:none;padding:0;margin:0;max-height:350px;overflow-y:auto;border:1px solid #eee;border-radius:4px;padding:5px;"></ul>
        <div style="margin-top:15px;display:flex;gap:10px;">
            <button class="be-btn be-btn-primary" onclick="applyColumns()">Apply Layout</button>
            <button class="be-btn" onclick="resetColumns()">Reset Defaults</button>
        </div>
    </div>

    <div class="be-table-wrapper" id="table-scroll-wrap">
        <table class="be-table" id="be-table">
            <colgroup id="grid-colgroup"></colgroup>
            <thead><tr id="grid-head-row" style="top:0;position:sticky;z-index:15;"></tr></thead>
            <tbody id="grid-body"><tr><td style="text-align:center;padding:50px;">Loading Products...</td></tr></tbody>
        </table>
    </div>

    <div id="bulk-action-bar">
        <div style="font-weight:bold;" id="bulk-count">0 Selected</div>
        <div style="width:1px;height:20px;background:#4b5563;"></div>
        <label style="font-size:12px;display:flex;align-items:center;gap:5px;cursor:pointer;"><input type="checkbox" id="apply-all-filtered"> Select ALL Filtered</label>
        <div style="width:1px;height:20px;background:#4b5563;"></div>
        <select id="bulk-action-select" onchange="toggleBulkInput()">
            <option value="">Choose Action...</option>
            <optgroup label="Price & Stock">
                <option value="price_inc_perc">Increase Price (%)</option>
                <option value="price_dec_perc">Decrease Price (%)</option>
                <option value="price_exact">Set Exact Price</option>
                <option value="stock_inc">Increase Stock (+qty)</option>
                <option value="stock_dec">Decrease Stock (-qty)</option>
                <option value="stock_exact">Set Exact Stock</option>
            </optgroup>
            <optgroup label="Organization">
                <option value="cat_change">Change Category</option>
                <option value="subcat_change">Change Subcategory</option>
                <option value="unit_change">Change Unit</option>
                <option value="tags_add">Add Tags</option>
                <option value="tags_remove">Remove Tags</option>
            </optgroup>
            <optgroup label="Status & Visibility">
                <option value="status_enable">Set Status: Active</option>
                <option value="status_disable">Set Status: Disabled</option>
                <option value="vis_visible">Set Visibility: Visible</option>
                <option value="vis_hidden">Set Visibility: Hidden</option>
                <option value="feat_mark">Mark Featured</option>
                <option value="feat_unmark">Remove Featured</option>
            </optgroup>
            <optgroup label="Danger Zone">
                <option value="duplicate">Duplicate Selected</option>
                <option value="delete">Delete Completely</option>
            </optgroup>
        </select>
        <input type="text" id="bulk-val-input" placeholder="Value..." style="display:none;width:120px;">
        <select id="bulk-cat-input" style="display:none;">
            <?php foreach($cats as $c): ?>
            <option value="<?php echo safe_html($c['category_id']); ?>"><?php echo safe_html($c['category_name']); ?></option>
            <?php endforeach; ?>
        </select>
        <select id="bulk-subcat-input" style="display:none;max-width:150px;">
            <?php foreach($subcats_by_cat as $cat_name => $subs): ?>
            <optgroup label="<?php echo safe_html($cat_name); ?>">
                <?php foreach($subs as $s): ?>
                <option value="<?php echo safe_html($s['sub_category_id']); ?>"><?php echo safe_html($s['sub_category_name']); ?></option>
                <?php endforeach; ?>
            </optgroup>
            <?php endforeach; ?>
        </select>
        <select id="bulk-unit-input" style="display:none;max-width:150px;">
            <?php foreach($units_grouped as $type => $units): ?>
            <optgroup label="<?php echo ucfirst($type); ?>">
                <?php foreach($units as $u): ?>
                <option value="<?php echo safe_html($u['code']); ?>"><?php echo safe_html($u['name']); ?> (<?php echo safe_html($u['symbol']); ?>)</option>
                <?php endforeach; ?>
            </optgroup>
            <?php endforeach; ?>
        </select>
        <button class="be-btn be-btn-primary" onclick="executeBulkAction()"><i class="fas fa-bolt"></i> Apply</button>
    </div>
</div>

<!-- ADD PRODUCT MODAL -->
<div id="add-product-modal" class="be-modal-overlay">
    <div class="be-modal-content xlarge">
        <span class="be-modal-close" onclick="closeAddProductModal()">&times;</span>
        <h3 style="margin-top:0;margin-bottom:20px;color:#374151;"><i class="fas fa-plus-circle" style="color:#10b981;"></i> Add New Product</h3>
        <form id="add-product-form" class="add-product-form" onsubmit="return submitAddProduct(event);">
            <div class="form-group">
                <label>Product Name <span class="required">*</span></label>
                <input type="text" name="product_name" id="new-product-name" required placeholder="Enter product name">
            </div>
            <div class="form-group">
                <label>SKU / Product Code</label>
                <input type="text" name="sku" id="new-product-sku" placeholder="e.g. PROD-001">
            </div>
            <div class="form-group">
                <label>Category <span class="required">*</span></label>
                <select name="category_id" id="new-product-category" required onchange="updateAddProductSubcats(this.value)">
                    <option value="">-- Select Category --</option>
                    <?php foreach($cats as $c): ?>
                    <option value="<?php echo safe_html($c['category_id']); ?>"><?php echo safe_html($c['category_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Subcategory</label>
                <select name="sub_category_id" id="new-product-subcategory">
                    <option value="">-- Select Subcategory --</option>
                </select>
            </div>
            <div class="form-group">
                <label>Price <span class="required">*</span></label>
                <input type="number" name="price" id="new-product-price" step="0.01" min="0" required placeholder="0.00">
            </div>
            <div class="form-group">
                <label>Sale Price</label>
                <input type="number" name="sale_price" id="new-product-sale-price" step="0.01" min="0" placeholder="Leave empty if no sale">
            </div>
            <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" name="stock_quantity" id="new-product-stock" step="0.01" min="0" value="0" placeholder="0">
            </div>
            <div class="form-group">
                <label>Minimum Order</label>
                <input type="number" name="minimum_order" id="new-product-min-order" step="0.01" min="0.01" value="1" placeholder="1">
            </div>
            <div class="form-group">
                <label>Unit</label>
                <select name="units" id="new-product-units">
                    <option value="piece">Piece (pc)</option>
                    <?php foreach($units_grouped as $type => $units): ?>
                    <optgroup label="<?php echo ucfirst($type); ?>">
                        <?php foreach($units as $u): ?>
                        <option value="<?php echo safe_html($u['code']); ?>"><?php echo safe_html($u['name']); ?> (<?php echo safe_html($u['symbol']); ?>)</option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Weight (kg)</label>
                <input type="number" name="weight" id="new-product-weight" step="0.01" min="0" value="0" placeholder="0.00">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" id="new-product-status">
                    <option value="1">Active</option>
                    <option value="0">Disabled</option>
                </select>
            </div>
            <div class="form-group">
                <label>Visibility</label>
                <select name="visibility" id="new-product-visibility">
                    <option value="visible">Visible</option>
                    <option value="hidden">Hidden</option>
                </select>
            </div>
            <div class="form-group">
                <label>Featured</label>
                <select name="is_featured" id="new-product-featured">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tags</label>
                <input type="text" name="tags" id="new-product-tags" placeholder="tag1, tag2, tag3">
            </div>
            <div class="form-group full-width">
                <label>Short Description</label>
                <textarea name="short_description" id="new-product-description" placeholder="Enter a brief product description..."></textarea>
            </div>
            <div class="form-group full-width">
                <label>Product Image</label>
                <div class="image-upload-area" onclick="document.getElementById('new-product-image').click();">
                    <img id="new-product-image-preview" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Crect fill='%23eee' width='100' height='100'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%23999' font-size='12'%3ENo Image%3C/text%3E%3C/svg%3E" alt="Preview" style="display:block;margin:0 auto 10px;">
                    <div style="color:#6b7280;font-size:13px;"><i class="fas fa-cloud-upload-alt"></i> Click to upload</div>
                    <div style="color:#9ca3af;font-size:11px;margin-top:5px;">JPG, PNG, GIF, WEBP (Max 5MB)</div>
                </div>
                <input type="file" name="image" id="new-product-image" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;" onchange="previewNewProductImage(this)">
            </div>
            <div class="form-actions">
                <button type="button" class="be-btn" onclick="closeAddProductModal()">Cancel</button>
                <button type="submit" class="be-btn be-btn-primary" id="add-product-submit-btn"><i class="fas fa-save"></i> Create Product</button>
            </div>
        </form>
    </div>
</div>

<!-- MEDIA MODAL -->
<div id="media-modal" class="be-modal-overlay">
    <div class="be-modal-content" style="width:450px;text-align:center;">
        <span class="be-modal-close" onclick="document.getElementById('media-modal').classList.remove('active')">&times;</span>
        <h3 style="margin-top:0;margin-bottom:15px;color:#374151;">Media Manager</h3>
        <img id="media-preview-img" style="width:100%;height:280px;object-fit:contain;background:#f9fafb;margin-bottom:15px;border-radius:4px;border:1px solid #ddd;" src="">
        <div style="display:flex;gap:10px;justify-content:center;">
            <button class="be-btn be-btn-primary" onclick="document.getElementById('modal-file-upload').click()"><i class="fas fa-upload"></i> Upload / Replace</button>
            <button class="be-btn" style="color:#dc2626;border-color:#fca5a5;" onclick="removeProductImage()"><i class="fas fa-trash"></i> Remove</button>
        </div>
        <input type="file" id="modal-file-upload" style="display:none;" accept="image/png,image/jpeg,image/webp,image/gif" onchange="handleModalUpload(this)">
    </div>
</div>

<!-- EXPORT MODAL -->
<div id="export-modal" class="be-modal-overlay">
    <div class="be-modal-content">
        <span class="be-modal-close" onclick="document.getElementById('export-modal').classList.remove('active')">&times;</span>
        <h3 style="margin-top:0;margin-bottom:15px;">Export Products (CSV)</h3>
        <div style="margin-bottom:15px;">
            <label style="display:block;margin-bottom:8px;"><input type="radio" name="export_target" value="filtered" checked> Export ALL Filtered Results</label>
            <label style="display:block;"><input type="radio" name="export_target" value="selected" id="export-target-selected"> Export ONLY Selected Rows (<span id="export-sel-count">0</span>)</label>
        </div>
        <div style="margin-bottom:15px;font-weight:bold;">Columns to Export:</div>
        <div id="export-cols-wrapper" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;max-height:200px;overflow-y:auto;border:1px solid #eee;padding:10px;border-radius:4px;"></div>
        <div style="margin-top:20px;text-align:right;">
            <button class="be-btn be-btn-primary" onclick="executeExport()"><i class="fas fa-download"></i> Download CSV</button>
        </div>
    </div>
</div>

<!-- IMPORT MODAL -->
<div id="import-modal" class="be-modal-overlay">
    <div class="be-modal-content large">
        <span class="be-modal-close" onclick="document.getElementById('import-modal').classList.remove('active')">&times;</span>
        <h3 style="margin-top:0;margin-bottom:15px;">Import Products (CSV)</h3>
        <div id="import-step-1">
            <p>Select a CSV file to upload and map fields.</p>
            <input type="file" id="import-file" class="be-input" accept=".csv" style="width:100%;margin-bottom:15px;">
            <button class="be-btn be-btn-primary" onclick="uploadImportFile()">Upload & Preview Mapping</button>
        </div>
        <div id="import-step-2" style="display:none;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;flex-wrap:wrap;gap:10px;">
                <strong>Map CSV Columns</strong>
                <label>Match by: <select id="import-match-key" class="be-select"><option value="product_id">Product ID</option><option value="sku">SKU</option></select></label>
            </div>
            <div style="max-height:400px;overflow-y:auto;border:1px solid #eee;border-radius:4px;">
                <table class="be-table" style="min-width:100%;"><thead style="position:sticky;top:0;z-index:10;"><tr><th>CSV Column</th><th>Sample</th><th>Map To</th></tr></thead><tbody id="import-mapping-body"></tbody></table>
            </div>
            <div style="margin-top:20px;display:flex;justify-content:space-between;">
                <button class="be-btn" onclick="document.getElementById('import-step-2').style.display='none';document.getElementById('import-step-1').style.display='block';">← Back</button>
                <button class="be-btn be-btn-primary" onclick="executeImport()"><i class="fas fa-play"></i> Run Import</button>
            </div>
        </div>
        <div id="import-step-3" style="display:none;text-align:center;padding:40px 0;">
            <div id="import-loader"><i class="fas fa-spinner fa-spin fa-3x" style="color:#3b82f6;"></i><p style="margin-top:15px;">Processing...</p></div>
            <div id="import-results" style="display:none;">
                <h2 style="color:#10b981;"><i class="fas fa-check-circle"></i> Import Complete</h2>
                <div style="display:flex;justify-content:center;gap:30px;margin:20px 0;font-size:18px;flex-wrap:wrap;">
                    <div><b style="color:#3b82f6;" id="import-created">0</b> Created</div>
                    <div><b style="color:#10b981;" id="import-updated">0</b> Updated</div>
                    <div><b style="color:#ef4444;" id="import-failed">0</b> Failed</div>
                </div>
                <button class="be-btn" onclick="document.getElementById('import-modal').classList.remove('active');loadGrid();">Close & Reload</button>
            </div>
        </div>
    </div>
</div>

<!-- HISTORY MODAL -->
<div id="history-modal" class="be-modal-overlay">
    <div class="be-modal-content large">
        <span class="be-modal-close" onclick="document.getElementById('history-modal').classList.remove('active')">&times;</span>
        <h3 style="margin-top:0;margin-bottom:15px;">Audit Log & Rollbacks</h3>
        <p style="font-size:13px;color:#666;margin-bottom:15px;">View recent edits. Click 'Undo' to revert.</p>
        <div style="max-height:500px;overflow-y:auto;border:1px solid #ddd;border-radius:4px;">
            <table class="hist-table">
                <thead style="position:sticky;top:0;z-index:10;"><tr><th>ID</th><th>User</th><th>Action</th><th>Detail</th><th>Affected</th><th>Date</th><th>Status</th><th>Rollback</th></tr></thead>
                <tbody id="history-tbody"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="toast-container"></div>

<script>
/* ==========================================
   COMPLETE JAVASCRIPT - VERSION 2.6
========================================== */
var SCRIPT_VERSION = "2.6";
var columns = [];
var cachedData = { products: [], total: 0 };
var currentPage = 1;
var debounceTimer = null;
var updateBulkTimer = null;
var currentSort = 'register_date';
var sortDir = 'DESC';
var activeMediaProductId = null;
var tmpImportFile = '';

var categoriesData = <?php echo $cats_json; ?>;
var subcatsByCatId = <?php echo $subcats_json; ?>;

var fallbackSVG = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Crect fill='%23eee' width='40' height='40'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%23999' font-size='8'%3EN/A%3C/text%3E%3C/svg%3E";

var unitsList = [
    {code:'piece',name:'Piece',symbol:'pc',group:'Quantity'},
    {code:'dozen',name:'Dozen',symbol:'dz',group:'Quantity'},
    {code:'kg',name:'Kilogram',symbol:'kg',group:'Weight'},
    {code:'gram',name:'Gram',symbol:'g',group:'Weight'},
    {code:'lb',name:'Pound',symbol:'lb',group:'Weight'},
    {code:'liter',name:'Liter',symbol:'L',group:'Volume'},
    {code:'ml',name:'Milliliter',symbol:'mL',group:'Volume'},
    {code:'box',name:'Box',symbol:'box',group:'Packaging'},
    {code:'bottle',name:'Bottle',symbol:'btl',group:'Packaging'},
    {code:'pack',name:'Pack',symbol:'pk',group:'Packaging'},
    {code:'tray',name:'Tray',symbol:'tray',group:'Packaging'},
    {code:'can',name:'Can',symbol:'can',group:'Packaging'},
    {code:'jar',name:'Jar',symbol:'jar',group:'Packaging'},
    {code:'bag',name:'Bag',symbol:'bag',group:'Packaging'}
];

var defaultColumns = [
    {id:'_chk',label:'✔',db_field:'',visible:true,width:40,pinned:true,unhideable:true},
    {id:'image',label:'Image',db_field:'',visible:true,width:65,pinned:false},
    {id:'product_id',label:'ID',db_field:'product_id',visible:false,width:100,pinned:false},
    {id:'name',label:'Product Name',db_field:'product_name',visible:true,width:250,pinned:true},
    {id:'sku',label:'SKU',db_field:'sku',visible:true,width:120,pinned:false},
    {id:'category',label:'Category',db_field:'category_id',visible:true,width:140,pinned:false},
    {id:'subcategory',label:'Subcategory',db_field:'sub_category_id',visible:true,width:140,pinned:false},
    {id:'price',label:'Price',db_field:'price',visible:true,width:90,pinned:false},
    {id:'sale_price',label:'Sale Price',db_field:'sale_price',visible:true,width:90,pinned:false},
    {id:'stock',label:'Stock',db_field:'stock_quantity',visible:true,width:80,pinned:false},
    {id:'min_order',label:'Min Order',db_field:'minimum_order',visible:true,width:90,pinned:false},
    {id:'units',label:'Units',db_field:'units',visible:true,width:130,pinned:false},
    {id:'weight',label:'Weight',db_field:'weight',visible:false,width:80,pinned:false},
    {id:'status',label:'Status',db_field:'status',visible:true,width:100,pinned:false},
    {id:'visibility',label:'Visibility',db_field:'visibility',visible:true,width:100,pinned:false},
    {id:'featured',label:'Feat.',db_field:'is_featured',visible:true,width:70,pinned:false},
    {id:'tags',label:'Tags',db_field:'tags',visible:false,width:150,pinned:false},
    {id:'short_desc',label:'Short Desc',db_field:'short_description',visible:false,width:250,pinned:false},
    {id:'date_created',label:'Created',db_field:'register_date',visible:false,width:100,pinned:false},
    {id:'date_updated',label:'Updated',db_field:'updated_at',visible:false,width:100,pinned:false},
    {id:'actions',label:'Actions',db_field:'',visible:true,width:80,pinned:false,unhideable:true}
];

// UTILITY FUNCTIONS
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    var div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
}

function escapeAttr(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function showToast(msg, type) {
    var container = document.getElementById('toast-container');
    if (!container) return;
    var toast = document.createElement('div');
    toast.className = 'toast ' + (type || 'success');
    toast.innerHTML = '<i class="fas fa-' + (type === 'error' ? 'exclamation-circle' : 'check-circle') + '"></i> ' + escapeHtml(msg);
    container.appendChild(toast);
    setTimeout(function() { toast.classList.add('show'); }, 10);
    setTimeout(function() { 
        toast.classList.remove('show'); 
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}

// BUILD OPTIONS FUNCTIONS
function buildCatOptionsWithSelected(selectedCatId) {
    var html = '<option value="">Category...</option>';
    for (var i = 0; i < categoriesData.length; i++) {
        var c = categoriesData[i];
        var catId = String(c.category_id || '');
        var catName = escapeHtml(c.category_name || '');
        var selected = (catId === String(selectedCatId)) ? ' selected' : '';
        html += '<option value="' + escapeAttr(catId) + '"' + selected + '>' + catName + '</option>';
    }
    return html;
}

function buildUnitOptionsWithSelected(selectedUnit) {
    var selVal = String(selectedUnit || '').toLowerCase().trim();
    var html = '<option value="">-- Select --</option>';
    var groups = ['Quantity', 'Weight', 'Volume', 'Packaging'];
    for (var g = 0; g < groups.length; g++) {
        var grp = groups[g];
        html += '<optgroup label="' + grp + '">';
        for (var u = 0; u < unitsList.length; u++) {
            if (unitsList[u].group === grp) {
                var selected = (unitsList[u].code === selVal) ? ' selected' : '';
                html += '<option value="' + escapeAttr(unitsList[u].code) + '"' + selected + '>' + escapeHtml(unitsList[u].name) + ' (' + escapeHtml(unitsList[u].symbol) + ')</option>';
            }
        }
        html += '</optgroup>';
    }
    return html;
}

// COLUMN MANAGEMENT
function loadCols() {
    var savedVersion = localStorage.getItem('be_script_version');
    if (savedVersion !== SCRIPT_VERSION) {
        localStorage.removeItem('bulk_editor_columns');
        localStorage.setItem('be_script_version', SCRIPT_VERSION);
    }
    var saved = localStorage.getItem('bulk_editor_columns');
    if (saved) {
        try {
            var parsed = JSON.parse(saved);
            columns = defaultColumns.map(function(dc) {
                var match = parsed.find(function(p) { return p.id === dc.id; });
                return match ? Object.assign({}, dc, match) : dc;
            });
        } catch(e) {
            columns = JSON.parse(JSON.stringify(defaultColumns));
        }
    } else {
        columns = JSON.parse(JSON.stringify(defaultColumns));
    }
}

function saveCols() {
    localStorage.setItem('bulk_editor_columns', JSON.stringify(columns));
}

function applyDensity() {
    var density = document.querySelector('input[name="tbl_density"]:checked');
    if (!density) return;
    var table = document.getElementById('be-table');
    if (!table) return;
    table.classList.remove('compact', 'normal', 'relaxed');
    if (density.value !== 'normal') table.classList.add(density.value);
    localStorage.setItem('be_table_density', density.value);
}

function loadDensity() {
    var density = localStorage.getItem('be_table_density') || 'normal';
    var radio = document.querySelector('input[name="tbl_density"][value="' + density + '"]');
    if (radio) radio.checked = true;
    applyDensity();
}

// CELL BUILDERS
var cellBuilders = {
    '_chk': function(p) {
        return '<input type="checkbox" class="be-chk row-chk" value="' + escapeAttr(p.product_id || '') + '" onchange="scheduleBulkBarUpdate()">';
    },
    'image': function(p) {
        var img = fallbackSVG;
        if (p.picture && String(p.picture).trim() !== '') {
            img = String(p.picture).indexOf('http') === 0 ? p.picture : '../../uploads/' + p.picture;
        }
        return '<div class="img-upload-container" onclick="openMediaModal(\'' + escapeAttr(p.product_id || '') + '\', \'' + escapeAttr(img) + '\')"><img loading="lazy" src="' + escapeAttr(img) + '" class="be-thumb" onerror="this.src=\'' + fallbackSVG + '\'"><div class="img-overlay"><i class="fas fa-search-plus"></i></div></div>';
    },
    'product_id': function(p) {
        return '<div style="padding:0 8px;font-size:11px;color:#888;font-family:monospace;">' + escapeHtml(String(p.product_id || '').substring(0,8)) + '</div>';
    },
    'name': function(p) {
        return '<input type="text" class="cell-input" data-field="product_name" value="' + escapeAttr(p.product_name || '') + '" onchange="saveCell(this, \'' + escapeAttr(p.product_id || '') + '\')">';
    },
    'sku': function(p) {
        return '<input type="text" class="cell-input" data-field="sku" value="' + escapeAttr(p.sku || '') + '" placeholder="SKU" onchange="saveCell(this, \'' + escapeAttr(p.product_id || '') + '\')">';
    },
    'category': function(p) {
        return '<select class="cell-input" data-field="category_id" onchange="saveCell(this, \'' + escapeAttr(p.product_id || '') + '\')">' + buildCatOptionsWithSelected(p.category_id) + '</select>';
    },
    'subcategory': function(p) {
        return '<input type="text" class="cell-input" data-field="sub_category_id" value="' + escapeAttr(p.sub_category_id || '') + '" placeholder="SubCat ID" onchange="saveCell(this, \'' + escapeAttr(p.product_id || '') + '\')">';
    },
    'price': function(p) {
        return '<input type="text" inputmode="decimal" class="cell-input" data-field="price" value="' + escapeAttr(p.price != null ? p.price : '0') + '" onchange="saveCell(this, \'' + escapeAttr(p.product_id || '') + '\')">';
    },
    'sale_price': function(p) {
        return '<input type="text" inputmode="decimal" class="cell-input" data-field="sale_price" value="' + escapeAttr(p.sale_price || '') + '" placeholder="None" onchange="saveCell(this, \'' + escapeAttr(p.product_id || '') + '\')">';
    },
    'stock': function(p) {
        return '<input type="text" inputmode="decimal" class="cell-input" data-field="stock_quantity" value="' + escapeAttr(p.stock != null ? p.stock : '0') + '" onchange="saveCell(this, \'' + escapeAttr(p.product_id || '') + '\')">';
    },
    'weight': function(p) {
        return '<input type="text" inputmode="decimal" class="cell-input" data-field="weight" value="' + escapeAttr(p.weight != null ? p.weight : '0') + '" onchange="saveCell(this, \'' + escapeAttr(p.product_id || '') + '\')">';
    },
    'min_order': function(p) {
        return '<input type="text" inputmode="decimal" class="cell-input" data-field="minimum_order" value="' + escapeAttr(p.minimum_order != null ? p.minimum_order : '1') + '" onchange="saveCell(this, \'' + escapeAttr(p.product_id || '') + '\')">';
    },
    'units': function(p) {
        return '<select class="cell-input" data-field="units" onchange="saveCell(this, \'' + escapeAttr(p.product_id || '') + '\')">' + buildUnitOptionsWithSelected(p.units) + '</select>';
    },
    'status': function(p) {
        var statusVal = parseInt(p.status) || 0;
        return '<select class="cell-input" data-field="status" onchange="saveCell(this, \'' + escapeAttr(p.product_id || '') + '\')"><option value="1"' + (statusVal === 1 ? ' selected' : '') + '>Active</option><option value="0"' + (statusVal === 0 ? ' selected' : '') + '>Disabled</option></select>';
    },
    'visibility': function(p) {
        var visVal = String(p.visibility || 'visible').toLowerCase();
        return '<select class="cell-input" data-field="visibility" onchange="saveCell(this, \'' + escapeAttr(p.product_id || '') + '\')"><option value="visible"' + (visVal === 'visible' ? ' selected' : '') + '>Visible</option><option value="hidden"' + (visVal === 'hidden' ? ' selected' : '') + '>Hidden</option></select>';
    },
    'featured': function(p) {
        var featVal = parseInt(p.is_featured) || 0;
        return '<input type="checkbox" class="be-chk" data-field="is_featured" value="1"' + (featVal === 1 ? ' checked' : '') + ' onchange="saveCell(this, \'' + escapeAttr(p.product_id || '') + '\')">';
    },
    'tags': function(p) {
        return '<input type="text" class="cell-input" data-field="tags" value="' + escapeAttr(p.tags || '') + '" placeholder="tag1, tag2..." onchange="saveCell(this, \'' + escapeAttr(p.product_id || '') + '\')">';
    },
    'short_desc': function(p) {
        return '<textarea class="cell-input" data-field="short_description" onchange="saveCell(this, \'' + escapeAttr(p.product_id || '') + '\')" placeholder="Short description...">' + escapeHtml(p.short_description || '') + '</textarea>';
    },
    'date_created': function(p) {
        return '<div style="padding:0 8px;font-size:11px;color:#666;">' + (p.register_date ? String(p.register_date).split(' ')[0] : '') + '</div>';
    },
    'date_updated': function(p) {
        return '<div style="padding:0 8px;font-size:11px;color:#666;">' + (p.updated_at ? String(p.updated_at).split(' ')[0] : '') + '</div>';
    },
    'actions': function(p) {
        return '<div style="text-align:center;"><button class="be-btn" style="color:#ef4444;border-color:#fca5a5;padding:4px 10px;" onclick="deleteSingleProduct(\'' + escapeAttr(p.product_id || '') + '\')" title="Delete"><i class="fas fa-trash"></i></button></div>';
    }
};

// RENDER FUNCTIONS
function renderGridConfig() {
    var thead = document.getElementById('grid-head-row');
    var colgroup = document.getElementById('grid-colgroup');
    if (!thead || !colgroup) return;
    
    thead.innerHTML = '';
    colgroup.innerHTML = '';
    var leftOffset = 0;
    
    for (var i = 0; i < columns.length; i++) {
        var col = columns[i];
        if (!col.visible) continue;
        
        var c = document.createElement('col');
        c.style.width = col.width + 'px';
        colgroup.appendChild(c);
        
        var th = document.createElement('th');
        var sortIcon = '';
        
        if (col.db_field && col.id !== 'image' && col.id !== '_chk') {
            var activeClass = (currentSort === col.db_field) ? (sortDir === 'ASC' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort';
            var activeStyle = (currentSort === col.db_field) ? 'color:#ff5000;' : 'color:#a1a1aa;';
            sortIcon = '<i class="fas ' + activeClass + '" style="margin-left:6px;font-size:11px;' + activeStyle + '"></i>';
            th.style.cursor = 'pointer';
            th.title = "Click to sort by " + col.label;
            (function(colObj) {
                th.onclick = function(e) {
                    if (e.target.classList.contains('resizer') || e.target.classList.contains('be-chk')) return;
                    if (currentSort === colObj.db_field) sortDir = sortDir === 'ASC' ? 'DESC' : 'ASC';
                    else { currentSort = colObj.db_field; sortDir = 'ASC'; }
                    resetPageAndLoad();
                };
            })(col);
        }

        if (col.id === '_chk') {
            th.innerHTML = '<input type="checkbox" id="chk-all" class="be-chk" onclick="toggleAll(this)">';
        } else {
            th.innerHTML = '<div style="display:flex;align-items:center;"><span>' + escapeHtml(col.label) + '</span>' + sortIcon + '</div>';
        }
        
        if (col.pinned) {
            th.classList.add('th-pinned');
            th.style.left = leftOffset + 'px';
            leftOffset += col.width;
        }
        
        var resizer = document.createElement('div');
        resizer.className = 'resizer';
        (function(colObj) {
            resizer.addEventListener('mousedown', function(e) { initResize(e, colObj.id); });
        })(col);
        th.appendChild(resizer);
        thead.appendChild(th);
    }
}

function renderGridData() {
    var tbody = document.getElementById('grid-body');
    if (!tbody) return;
    
    if (!cachedData.products || cachedData.products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="100" style="text-align:center;padding:30px;">No products match.</td></tr>';
        return;
    }
    
    var html = '';
    for (var p = 0; p < cachedData.products.length; p++) {
        var prod = cachedData.products[p];
        if (!prod || !prod.product_id) continue;
        
        html += '<tr data-id="' + escapeAttr(prod.product_id) + '">';
        var leftOffset = 0;
        
        for (var c = 0; c < columns.length; c++) {
            var col = columns[c];
            if (!col.visible) continue;
            
            var style = col.pinned ? 'style="left:' + leftOffset + 'px;"' : '';
            var cls = col.pinned ? 'td-pinned' : '';
            if (col.pinned) leftOffset += col.width;
            
            var cellContent = '';
            try {
                cellContent = cellBuilders[col.id] ? cellBuilders[col.id](prod) : '';
            } catch (e) {
                console.error('Cell error:', col.id, e);
                cellContent = '<div style="color:red;font-size:10px;">Error</div>';
            }
            html += '<td class="' + cls + '" ' + style + '>' + cellContent + '</td>';
        }
        html += '</tr>';
    }
    
    tbody.innerHTML = html;
    
    var pageNumEl = document.getElementById('page-num');
    if (pageNumEl) pageNumEl.innerText = 'Page ' + currentPage;
    
    var gridInfoEl = document.getElementById('grid-info');
    if (gridInfoEl) gridInfoEl.innerText = 'Total: ' + (cachedData.total || 0) + ' Products';
    
    var chkAll = document.getElementById('chk-all');
    if (chkAll) chkAll.checked = false;
    
    updateBulkBar();
}

// RESIZE
var startX, startWidth, resizingCol;

function initResize(e, colId) {
    e.stopPropagation();
    e.preventDefault();
    resizingCol = columns.find(function(c) { return c.id === colId; });
    if (!resizingCol) return;
    startX = e.clientX;
    startWidth = resizingCol.width;
    document.addEventListener('mousemove', doResize);
    document.addEventListener('mouseup', stopResize);
}

function doResize(e) {
    if (!resizingCol) return;
    var newWidth = Math.max(40, startWidth + (e.clientX - startX));
    resizingCol.width = newWidth;
    var cIdx = columns.filter(function(c) { return c.visible; }).findIndex(function(c) { return c.id === resizingCol.id; });
    var colgroup = document.getElementById('grid-colgroup');
    if (cIdx > -1 && colgroup && colgroup.children[cIdx]) {
        colgroup.children[cIdx].style.width = newWidth + 'px';
    }
}

function stopResize() {
    document.removeEventListener('mousemove', doResize);
    document.removeEventListener('mouseup', stopResize);
    saveCols();
    renderGridConfig();
    renderGridData();
}

// COLUMN MANAGER
function toggleAllCols(show) {
    var items = document.querySelectorAll('#col-list .col-item');
    for (var i = 0; i < items.length; i++) {
        var checkbox = items[i].querySelector('.col-vis');
        if (checkbox && !checkbox.disabled) checkbox.checked = show;
    }
}

function renderColumnManager() {
    var list = document.getElementById('col-list');
    if (!list) return;
    list.innerHTML = '';
    
    for (var i = 0; i < columns.length; i++) {
        var c = columns[i];
        if (c.id === '_chk') continue;
        var li = document.createElement('li');
        li.className = 'col-item' + (c.unhideable ? ' unhideable' : '');
        li.draggable = true;
        li.dataset.id = c.id;
        li.innerHTML = '<span class="drag-handle">☰</span><label style="flex-grow:1;display:flex;align-items:center;gap:8px;"><input type="checkbox" class="col-vis" ' + (c.visible ? 'checked' : '') + ' ' + (c.unhideable ? 'disabled' : '') + '> ' + escapeHtml(c.label) + '</label><label style="font-size:11px;"><input type="checkbox" class="col-pin" ' + (c.pinned ? 'checked' : '') + '> Pin</label>';
        list.appendChild(li);
    }
}

function applyColumns() {
    var order = ['_chk'];
    var items = document.querySelectorAll('#col-list .col-item');
    for (var i = 0; i < items.length; i++) {
        var li = items[i];
        var id = li.dataset.id;
        var c = columns.find(function(x) { return x.id === id; });
        if (!c) continue;
        if (!c.unhideable) {
            var visChk = li.querySelector('.col-vis');
            if (visChk) c.visible = visChk.checked;
        }
        var pinChk = li.querySelector('.col-pin');
        if (pinChk) c.pinned = pinChk.checked;
        order.push(id);
    }
    columns.sort(function(a, b) { return order.indexOf(a.id) - order.indexOf(b.id); });
    saveCols();
    renderGridConfig();
    renderGridData();
    var drawer = document.getElementById('column-drawer');
    if (drawer) drawer.classList.remove('active');
}

function resetColumns() {
    columns = JSON.parse(JSON.stringify(defaultColumns));
    saveCols();
    renderColumnManager();
    applyColumns();
}

function toggleDrawer(id) {
    var drawers = document.querySelectorAll('.drawer-panel');
    for (var i = 0; i < drawers.length; i++) {
        if (drawers[i].id !== id) drawers[i].classList.remove('active');
    }
    var drawer = document.getElementById(id);
    if (drawer) {
        drawer.classList.toggle('active');
        if (id === 'column-drawer') renderColumnManager();
    }
}

// DATA LOADING
function handleSearchInput() {
    var searchInput = document.getElementById('f-search');
    var clearIcon = document.getElementById('clear-search-icon');
    if (searchInput && clearIcon) {
        clearIcon.style.display = searchInput.value.length > 0 ? 'block' : 'none';
    }
    debounceLoad();
}

function clearSearchInput() {
    var searchInput = document.getElementById('f-search');
    var clearIcon = document.getElementById('clear-search-icon');
    if (searchInput) searchInput.value = '';
    if (clearIcon) clearIcon.style.display = 'none';
    resetPageAndLoad();
}

function getFilterParams() {
    var p = new URLSearchParams();
    p.append('page', currentPage);
    
    var limitEl = document.getElementById('f-limit');
    if (limitEl) p.append('limit', limitEl.value);
    
    var searchFieldEl = document.getElementById('f-search-field');
    if (searchFieldEl) p.append('search_field', searchFieldEl.value);
    
    p.append('sort_by', currentSort);
    p.append('sort_dir', sortDir);
    
    var filterIds = ['search', 'cat', 'subcat', 'stock', 'price_min', 'price_max', 'date_add_from', 'date_add_to', 'date_up_from', 'date_up_to', 'status', 'vis', 'feat'];
    for (var i = 0; i < filterIds.length; i++) {
        var id = filterIds[i];
        var el = document.getElementById('f-' + id);
        if (el) p.append(id === 'vis' ? 'visibility' : id, el.value);
    }
    
    var exactEl = document.getElementById('f-exact');
    if (exactEl) p.append('exact_match', exactEl.checked);
    
    return p.toString();
}

function debounceLoad() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(resetPageAndLoad, 600);
}

function resetPageAndLoad() {
    currentPage = 1;
    loadGrid();
}

function changePage(dir) {
    var newPage = currentPage + dir;
    if (newPage < 1) newPage = 1;
    currentPage = newPage;
    loadGrid();
}

function loadGrid() {
    var tbody = document.getElementById('grid-body');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="100" style="text-align:center;padding:50px;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';
    }
    
    fetch('ajax_bulk_editor_pro.php?action=load&' + getFilterParams())
        .then(function(res) {
            if (!res.ok) throw new Error('Network error');
            return res.json();
        })
        .then(function(data) {
            if (data.status === 'error') {
                if (tbody) tbody.innerHTML = '<tr><td colspan="100" style="color:red;text-align:center;padding:30px;">Error: ' + escapeHtml(data.message || 'Unknown') + '</td></tr>';
                return;
            }
            cachedData = data || { products: [], total: 0 };
            if (!cachedData.products) cachedData.products = [];
            if (!cachedData.total) cachedData.total = 0;
            renderGridConfig();
            renderGridData();
        })
        .catch(function(e) {
            console.error('Load error:', e);
            if (tbody) tbody.innerHTML = '<tr><td colspan="100" style="color:red;text-align:center;padding:30px;">Network Error: ' + escapeHtml(e.message || 'Failed') + '</td></tr>';
        });
}

function saveCell(input, id) {
    if (!input || !id) return;
    var td = input.parentElement;
    if (td) td.classList.add('saving');
    
    var field = input.getAttribute('data-field');
    var val = input.type === 'checkbox' ? (input.checked ? 1 : 0) : input.value;
    
    var fd = new FormData();
    fd.append('action', 'inline_edit');
    fd.append('id', id);
    fd.append('field', field);
    fd.append('val', val);
    
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (td) td.classList.remove('saving');
            if (data.status === 'success') {
                if (td) td.classList.add('saved');
                showToast('Saved', 'success');
                setTimeout(function() { if (td) td.classList.remove('saved'); }, 1500);
            } else {
                if (td) td.classList.add('error');
                showToast(data.message || 'Error saving', 'error');
            }
        })
        .catch(function() {
            if (td) { td.classList.remove('saving'); td.classList.add('error'); }
            showToast('Network error', 'error');
        });
}

// FILTERS
function clearFilters() {
    var ids = ['f-search', 'f-cat', 'f-subcat', 'f-stock', 'f-price-min', 'f-price-max', 'f-date-add-from', 'f-date-add-to', 'f-date-up-from', 'f-date-up-to', 'f-status', 'f-vis', 'f-feat'];
    for (var i = 0; i < ids.length; i++) {
        var el = document.getElementById(ids[i]);
        if (el) el.value = '';
    }
    var exactEl = document.getElementById('f-exact');
    if (exactEl) exactEl.checked = false;
    resetPageAndLoad();
}

function updateSubcats(catId) {
    var subcatSelect = document.getElementById('f-subcat');
    if (!subcatSelect) return;
    subcatSelect.innerHTML = '<option value="">All</option>';
    if (!catId || !subcatsByCatId[catId]) return;
    for (var i = 0; i < subcatsByCatId[catId].length; i++) {
        var sub = subcatsByCatId[catId][i];
        var opt = document.createElement('option');
        opt.value = sub.sub_category_id;
        opt.textContent = sub.sub_category_name;
        subcatSelect.appendChild(opt);
    }
}

// PRESETS
function savePreset() {
    var nameEl = document.getElementById('preset-name');
    if (!nameEl || !nameEl.value.trim()) return alert('Enter preset name');
    var presets = JSON.parse(localStorage.getItem('be_presets') || '{}');
    presets[nameEl.value.trim()] = getFilterParams();
    localStorage.setItem('be_presets', JSON.stringify(presets));
    loadPresetDropdown();
    showToast('Preset saved', 'success');
    nameEl.value = '';
}

function loadPreset(name) {
    if (!name) return;
    var presets = JSON.parse(localStorage.getItem('be_presets') || '{}');
    if (!presets[name]) return;
    var params = new URLSearchParams(presets[name]);
    var mappings = {search:'f-search',cat:'f-cat',subcat:'f-subcat',stock:'f-stock',price_min:'f-price-min',price_max:'f-price-max',status:'f-status',visibility:'f-vis',feat:'f-feat'};
    for (var key in mappings) {
        var el = document.getElementById(mappings[key]);
        if (el && params.has(key)) el.value = params.get(key);
    }
    resetPageAndLoad();
}

function loadPresetDropdown() {
    var loader = document.getElementById('preset-loader');
    if (!loader) return;
    var presets = JSON.parse(localStorage.getItem('be_presets') || '{}');
    loader.innerHTML = '<option value="">Load Preset...</option>';
    for (var name in presets) {
        var opt = document.createElement('option');
        opt.value = name;
        opt.textContent = name;
        loader.appendChild(opt);
    }
}

// ADD PRODUCT MODAL
function openAddProductModal() {
    var form = document.getElementById('add-product-form');
    if (form) form.reset();
    var preview = document.getElementById('new-product-image-preview');
    if (preview) preview.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Crect fill='%23eee' width='100' height='100'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%23999' font-size='12'%3ENo Image%3C/text%3E%3C/svg%3E";
    var subcatSelect = document.getElementById('new-product-subcategory');
    if (subcatSelect) subcatSelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
    var modal = document.getElementById('add-product-modal');
    if (modal) modal.classList.add('active');
}

function closeAddProductModal() {
    var modal = document.getElementById('add-product-modal');
    if (modal) modal.classList.remove('active');
}

function updateAddProductSubcats(categoryId) {
    var subcatSelect = document.getElementById('new-product-subcategory');
    if (!subcatSelect) return;
    subcatSelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
    if (!categoryId || !subcatsByCatId[categoryId]) return;
    for (var i = 0; i < subcatsByCatId[categoryId].length; i++) {
        var sub = subcatsByCatId[categoryId][i];
        var opt = document.createElement('option');
        opt.value = sub.sub_category_id;
        opt.textContent = sub.sub_category_name;
        subcatSelect.appendChild(opt);
    }
}

function previewNewProductImage(input) {
    var preview = document.getElementById('new-product-image-preview');
    if (!preview || !input.files || !input.files[0]) return;
    var file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
        showToast('Image too large. Max 5MB.', 'error');
        input.value = '';
        return;
    }
    var reader = new FileReader();
    reader.onload = function(e) { preview.src = e.target.result; };
    reader.readAsDataURL(file);
}

function submitAddProduct(event) {
    event.preventDefault();
    var submitBtn = document.getElementById('add-product-submit-btn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    }
    var form = document.getElementById('add-product-form');
    if (!form) { showToast('Form not found', 'error'); return false; }
    var fd = new FormData(form);
    fd.append('action', 'add_product_full');
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fas fa-save"></i> Create Product'; }
            if (data.status === 'success') {
                showToast('Product created!', 'success');
                closeAddProductModal();
                loadGrid();
            } else {
                showToast(data.message || 'Failed', 'error');
            }
        })
        .catch(function() {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fas fa-save"></i> Create Product'; }
            showToast('Network error', 'error');
        });
    return false;
}

function deleteSingleProduct(id) {
    if (!id) return;
    if (!confirm("Delete this product? Cannot be undone.")) return;
    var fd = new FormData();
    fd.append('action', 'delete_product');
    fd.append('product_id', id);
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                showToast('Deleted', 'success');
                var tr = document.querySelector('tr[data-id="' + id + '"]');
                if (tr) tr.remove();
            } else {
                showToast(data.message || 'Error', 'error');
            }
        })
        .catch(function() { showToast('Network error', 'error'); });
}

// MEDIA MODAL
function openMediaModal(productId, imgUrl) {
    activeMediaProductId = productId;
    var previewImg = document.getElementById('media-preview-img');
    if (previewImg) previewImg.src = imgUrl || fallbackSVG;
    var modal = document.getElementById('media-modal');
    if (modal) modal.classList.add('active');
}

function handleModalUpload(input) {
    if (!input || !input.files || !input.files[0]) return;
    var fd = new FormData();
    fd.append('action', 'upload_image');
    fd.append('product_id', activeMediaProductId);
    fd.append('image', input.files[0]);
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                var newUrl = '../../uploads/' + data.filename + '?t=' + Date.now();
                var previewImg = document.getElementById('media-preview-img');
                if (previewImg) previewImg.src = newUrl;
                var tr = document.querySelector('tr[data-id="' + activeMediaProductId + '"]');
                if (tr) {
                    var thumb = tr.querySelector('.be-thumb');
                    if (thumb) thumb.src = newUrl;
                }
                showToast('Uploaded', 'success');
            } else {
                showToast(data.message || 'Upload failed', 'error');
            }
        })
        .catch(function() { showToast('Network error', 'error'); });
    input.value = '';
}

function removeProductImage() {
    if (!confirm("Remove image?")) return;
    var fd = new FormData();
    fd.append('action', 'remove_image');
    fd.append('product_id', activeMediaProductId);
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                var previewImg = document.getElementById('media-preview-img');
                if (previewImg) previewImg.src = fallbackSVG;
                var tr = document.querySelector('tr[data-id="' + activeMediaProductId + '"]');
                if (tr) {
                    var thumb = tr.querySelector('.be-thumb');
                    if (thumb) thumb.src = fallbackSVG;
                }
                showToast('Removed', 'success');
            } else {
                showToast(data.message || 'Error', 'error');
            }
        })
        .catch(function() { showToast('Network error', 'error'); });
}

// BULK ACTIONS
function scheduleBulkBarUpdate() {
    clearTimeout(updateBulkTimer);
    updateBulkTimer = setTimeout(updateBulkBar, 100);
}

function toggleAll(source) {
    if (!source) return;
    var chks = document.querySelectorAll('.row-chk');
    for (var i = 0; i < chks.length; i++) chks[i].checked = source.checked;
    scheduleBulkBarUpdate();
}

function updateBulkBar() {
    var checked = document.querySelectorAll('.row-chk:checked').length;
    var bar = document.getElementById('bulk-action-bar');
    var countEl = document.getElementById('bulk-count');
    if (countEl) countEl.innerText = checked + ' Selected';
    var applyAllEl = document.getElementById('apply-all-filtered');
    if (bar) {
        if (checked > 0 || (applyAllEl && applyAllEl.checked)) bar.classList.add('show');
        else bar.classList.remove('show');
    }
}

function toggleBulkInput() {
    var act = document.getElementById('bulk-action-select');
    if (!act) return;
    var valInput = document.getElementById('bulk-val-input');
    var catInput = document.getElementById('bulk-cat-input');
    var subcatInput = document.getElementById('bulk-subcat-input');
    var unitInput = document.getElementById('bulk-unit-input');
    if (valInput) valInput.style.display = 'none';
    if (catInput) catInput.style.display = 'none';
    if (subcatInput) subcatInput.style.display = 'none';
    if (unitInput) unitInput.style.display = 'none';
    var action = act.value;
    if (action.indexOf('price') !== -1 || action.indexOf('stock') !== -1 || action.indexOf('tags') !== -1) {
        if (valInput) valInput.style.display = 'block';
    } else if (action === 'cat_change') {
        if (catInput) catInput.style.display = 'block';
    } else if (action === 'subcat_change') {
        if (subcatInput) subcatInput.style.display = 'block';
    } else if (action === 'unit_change') {
        if (unitInput) unitInput.style.display = 'block';
    }
}

function executeBulkAction() {
    var actionSelect = document.getElementById('bulk-action-select');
    if (!actionSelect) return;
    var action = actionSelect.value;
    if (!action) return alert("Select an action.");
    var applyAllEl = document.getElementById('apply-all-filtered');
    var isAll = applyAllEl ? applyAllEl.checked : false;
    var chkEls = document.querySelectorAll('.row-chk:checked');
    var chks = [];
    for (var i = 0; i < chkEls.length; i++) chks.push(chkEls[i].value);
    var countToEdit = isAll ? (cachedData.total || 0) : chks.length;
    if (countToEdit === 0) return alert("Select products.");
    if (countToEdit > 50 && !confirm("Edit " + countToEdit + " products?")) return;
    var val = '';
    var valInput = document.getElementById('bulk-val-input');
    var catInput = document.getElementById('bulk-cat-input');
    var subcatInput = document.getElementById('bulk-subcat-input');
    var unitInput = document.getElementById('bulk-unit-input');
    if (valInput && valInput.style.display === 'block') val = valInput.value;
    else if (catInput && catInput.style.display === 'block') val = catInput.value;
    else if (subcatInput && subcatInput.style.display === 'block') val = subcatInput.value;
    else if (unitInput && unitInput.style.display === 'block') val = unitInput.value;
    if (action === 'delete' && !confirm("DELETE permanently?")) return;
    var fd = new FormData();
    fd.append('action', 'bulk_process');
    fd.append('bulk_action', action);
    fd.append('val', val);
    fd.append('apply_all_filtered', isAll);
    if (isAll) {
        var p = new URLSearchParams(getFilterParams());
        for (var pair of p.entries()) fd.append(pair[0], pair[1]);
    } else {
        fd.append('ids', JSON.stringify(chks));
    }
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                showToast('Modified ' + (data.affected || 0) + ' products.', 'success');
                if (applyAllEl) applyAllEl.checked = false;
                loadGrid();
            } else {
                showToast(data.message || 'Error', 'error');
            }
        })
        .catch(function() { showToast('Network error', 'error'); });
}

// EXPORT
function openExportModal() {
    var checked = document.querySelectorAll('.row-chk:checked').length;
    var selCountEl = document.getElementById('export-sel-count');
    if (selCountEl) selCountEl.innerText = checked;
    var targetSelectedEl = document.getElementById('export-target-selected');
    if (targetSelectedEl) targetSelectedEl.disabled = checked === 0;
    if (checked === 0) {
        var filteredRadio = document.querySelector('input[name="export_target"][value="filtered"]');
        if (filteredRadio) filteredRadio.checked = true;
    }
    var html = '';
    for (var i = 0; i < columns.length; i++) {
        var c = columns[i];
        if (c.id === '_chk' || c.id === 'image' || c.id === 'actions' || !c.db_field) continue;
        html += '<label style="display:flex;align-items:center;gap:5px;"><input type="checkbox" class="export-col-chk" value="' + escapeAttr(c.db_field) + '" checked> ' + escapeHtml(c.label) + '</label>';
    }
    var colsWrapper = document.getElementById('export-cols-wrapper');
    if (colsWrapper) colsWrapper.innerHTML = html;
    var modal = document.getElementById('export-modal');
    if (modal) modal.classList.add('active');
}

function executeExport() {
    var typeRadio = document.querySelector('input[name="export_target"]:checked');
    if (!typeRadio) return;
    var type = typeRadio.value;
    var colEls = document.querySelectorAll('.export-col-chk:checked');
    var cols = [];
    for (var i = 0; i < colEls.length; i++) cols.push(colEls[i].value);
    if (cols.length === 0) return alert("Select columns.");
    var fd = new FormData();
    fd.append('action', 'export_csv');
    fd.append('export_type', type);
    fd.append('export_cols', JSON.stringify(cols));
    if (type === 'selected') {
        var chkEls = document.querySelectorAll('.row-chk:checked');
        var chks = [];
        for (var j = 0; j < chkEls.length; j++) chks.push(chkEls[j].value);
        fd.append('ids', JSON.stringify(chks));
    } else {
        var p = new URLSearchParams(getFilterParams());
        for (var pair of p.entries()) fd.append(pair[0], pair[1]);
    }
    var modal = document.getElementById('export-modal');
    if (modal) modal.classList.remove('active');
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(function(res) { return res.blob(); })
        .then(function(blob) {
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'export_' + Date.now() + '.csv';
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        })
        .catch(function() { alert("Export Failed"); });
}

// IMPORT
var dbFieldsOptions = '<option value="">-- Ignore --</option><option value="product_id">Product ID</option><option value="sku">SKU</option><option value="product_name">Product Name</option><option value="category_id">Category ID</option><option value="sub_category_id">Subcategory ID</option><option value="price">Price</option><option value="sale_price">Sale Price</option><option value="stock_quantity">Stock</option><option value="minimum_order">Min Order</option><option value="units">Units</option><option value="weight">Weight</option><option value="status">Status</option><option value="is_featured">Featured</option><option value="visibility">Visibility</option><option value="short_description">Description</option><option value="tags">Tags</option>';

function openImportModal() {
    var step1 = document.getElementById('import-step-1');
    var step2 = document.getElementById('import-step-2');
    var step3 = document.getElementById('import-step-3');
    var fileInput = document.getElementById('import-file');
    if (step1) step1.style.display = 'block';
    if (step2) step2.style.display = 'none';
    if (step3) step3.style.display = 'none';
    if (fileInput) fileInput.value = '';
    var modal = document.getElementById('import-modal');
    if (modal) modal.classList.add('active');
}

function uploadImportFile() {
    var fileInput = document.getElementById('import-file');
    if (!fileInput || !fileInput.files || !fileInput.files[0]) return alert("Select a CSV.");
    var fd = new FormData();
    fd.append('action', 'import_csv_upload');
    fd.append('csv_file', fileInput.files[0]);
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                tmpImportFile = data.tmp_file;
                var html = '';
                var headers = data.headers || [];
                var samples = data.samples || [];
                for (var i = 0; i < headers.length; i++) {
                    var h = headers[i];
                    var sampleText = '';
                    for (var s = 0; s < samples.length; s++) {
                        if (samples[s][i]) sampleText += escapeHtml(samples[s][i]) + ', ';
                    }
                    if (sampleText.length > 30) sampleText = sampleText.substring(0, 30) + '...';
                    html += '<tr><td style="font-weight:bold;">' + escapeHtml(h) + '</td><td style="color:#666;font-size:11px;">' + sampleText + '</td><td><select class="be-select import-map-sel" data-index="' + i + '">' + dbFieldsOptions + '</select></td></tr>';
                }
                var mappingBody = document.getElementById('import-mapping-body');
                if (mappingBody) mappingBody.innerHTML = html;
                var step1 = document.getElementById('import-step-1');
                var step2 = document.getElementById('import-step-2');
                if (step1) step1.style.display = 'none';
                if (step2) step2.style.display = 'block';
            } else {
                alert(data.message || 'Upload failed');
            }
        })
        .catch(function() { alert('Upload error'); });
}

function executeImport() {
    var matchKeyEl = document.getElementById('import-match-key');
    if (!matchKeyEl) return;
    var matchKey = matchKeyEl.value;
    var mapping = {};
    var selEls = document.querySelectorAll('.import-map-sel');
    for (var i = 0; i < selEls.length; i++) {
        mapping[selEls[i].getAttribute('data-index')] = selEls[i].value;
    }
    var step2 = document.getElementById('import-step-2');
    var step3 = document.getElementById('import-step-3');
    var loaderEl = document.getElementById('import-loader');
    var resultsEl = document.getElementById('import-results');
    if (step2) step2.style.display = 'none';
    if (step3) step3.style.display = 'block';
    if (loaderEl) loaderEl.style.display = 'block';
    if (resultsEl) resultsEl.style.display = 'none';
    var fd = new FormData();
    fd.append('action', 'import_csv_process');
    fd.append('tmp_file', tmpImportFile);
    fd.append('match_key', matchKey);
    fd.append('mapping', JSON.stringify(mapping));
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (loaderEl) loaderEl.style.display = 'none';
            if (data.status === 'success') {
                var createdEl = document.getElementById('import-created');
                var updatedEl = document.getElementById('import-updated');
                var failedEl = document.getElementById('import-failed');
                if (createdEl) createdEl.innerText = data.stats ? data.stats.created : 0;
                if (updatedEl) updatedEl.innerText = data.stats ? data.stats.updated : 0;
                if (failedEl) failedEl.innerText = data.stats ? data.stats.failed : 0;
                if (resultsEl) resultsEl.style.display = 'block';
            } else {
                alert("Error: " + (data.message || 'Unknown'));
                var modal = document.getElementById('import-modal');
                if (modal) modal.classList.remove('active');
            }
        })
        .catch(function() {
            alert("Server error");
            var modal = document.getElementById('import-modal');
            if (modal) modal.classList.remove('active');
        });
}

// HISTORY
function openHistoryModal() {
    var modal = document.getElementById('history-modal');
    var tbody = document.getElementById('history-tbody');
    if (modal) modal.classList.add('active');
    if (tbody) tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">Loading...</td></tr>';
    fetch('ajax_bulk_editor_pro.php?action=load_history')
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.status !== 'success') return;
            var html = '';
            var history = data.history || [];
            for (var i = 0; i < history.length; i++) {
                var h = history[i];
                var rbBtn = '';
                var badge = '';
                if (h.is_rolled_back == 1) {
                    badge = '<span class="badge rb-done">Rolled Back</span>';
                } else if (h.can_rollback == 1) {
                    badge = '<span class="badge rb-yes">Supported</span>';
                    rbBtn = '<button class="be-btn" style="padding:4px 8px;font-size:11px;" onclick="executeRollback(' + h.history_id + ')"><i class="fas fa-undo"></i> Undo</button>';
                } else {
                    badge = '<span class="badge rb-no">No Data</span>';
                }
                html += '<tr><td>#' + h.history_id + '</td><td>' + escapeHtml(h.admin_user || '') + '</td><td><b>' + escapeHtml(h.action_type || '') + '</b></td><td>' + escapeHtml(h.action_detail || '') + '</td><td>' + (h.affected_rows || 0) + '</td><td>' + (h.created_at || '') + '</td><td>' + badge + '</td><td>' + rbBtn + '</td></tr>';
            }
            if (tbody) tbody.innerHTML = html || '<tr><td colspan="8" style="text-align:center;">No history.</td></tr>';
        })
        .catch(function() {
            if (tbody) tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:red;">Error loading</td></tr>';
        });
}

function executeRollback(hid) {
    if (!confirm("Rollback this action?")) return;
    var fd = new FormData();
    fd.append('action', 'rollback_history');
    fd.append('history_id', hid);
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                showToast('Rollback successful', 'success');
                openHistoryModal();
                loadGrid();
            } else {
                showToast(data.message || 'Error', 'error');
            }
        })
        .catch(function() { showToast('Network error', 'error'); });
}

// INIT
document.addEventListener('DOMContentLoaded', function() {
    loadCols();
    loadDensity();
    loadPresetDropdown();
    loadGrid();
    
    var applyAllEl = document.getElementById('apply-all-filtered');
    if (applyAllEl) applyAllEl.addEventListener('change', scheduleBulkBarUpdate);
    
    var colList = document.getElementById('col-list');
    if (colList) {
        colList.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
    }
});
</script>
