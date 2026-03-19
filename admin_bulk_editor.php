<?php
/**
 * Admin Bulk Editor - Spreadsheet Manager
 * Enhanced with Product Units System
 * 
 * @version 2.4
 * @updated 2026-03-17
 * 
 * SAFE VERSION - No external dependencies
 */

// Fetch categories
$cats = [];
$res = $conn->query("SELECT category_id, category_name FROM product_category ORDER BY category_name ASC");
if($res) { while($r = $res->fetch_assoc()) { $cats[] = $r; } }

// Fetch subcategories grouped by category
$subcats_by_cat = [];
$res_sub = $conn->query("SELECT sc.sub_category_id, sc.sub_category_name, c.category_name FROM product_sub_category sc JOIN product_category c ON sc.category_id = c.category_id ORDER BY c.category_name, sc.sub_category_name");
if($res_sub) { while($r = $res_sub->fetch_assoc()) { $subcats_by_cat[$r['category_name']][] = $r; } }

// [NEW] Define all product units directly (no external file needed)
$all_units = [
    // Quantity Units
    ['id' => 1, 'code' => 'piece', 'name' => 'Piece', 'symbol' => 'pc', 'type' => 'quantity'],
    ['id' => 2, 'code' => 'dozen', 'name' => 'Dozen', 'symbol' => 'dz', 'type' => 'quantity'],
    ['id' => 3, 'code' => 'pair', 'name' => 'Pair', 'symbol' => 'pr', 'type' => 'quantity'],
    ['id' => 4, 'code' => 'set', 'name' => 'Set', 'symbol' => 'set', 'type' => 'quantity'],
    
    // Weight Units
    ['id' => 10, 'code' => 'kg', 'name' => 'Kilogram', 'symbol' => 'kg', 'type' => 'weight'],
    ['id' => 11, 'code' => 'gram', 'name' => 'Gram', 'symbol' => 'g', 'type' => 'weight'],
    ['id' => 12, 'code' => 'lb', 'name' => 'Pound', 'symbol' => 'lb', 'type' => 'weight'],
    ['id' => 13, 'code' => 'oz', 'name' => 'Ounce', 'symbol' => 'oz', 'type' => 'weight'],
    
    // Volume Units
    ['id' => 20, 'code' => 'liter', 'name' => 'Liter', 'symbol' => 'L', 'type' => 'volume'],
    ['id' => 21, 'code' => 'ml', 'name' => 'Milliliter', 'symbol' => 'mL', 'type' => 'volume'],
    ['id' => 22, 'code' => 'gallon', 'name' => 'Gallon', 'symbol' => 'gal', 'type' => 'volume'],
    
    // Packaging Units
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

// Group units by type for organized dropdowns
$units_grouped = [
    'quantity' => [],
    'weight' => [],
    'volume' => [],
    'packaging' => []
];
foreach ($all_units as $u) {
    $units_grouped[$u['type']][] = $u;
}

// Create JSON for JavaScript
$units_json = json_encode(array_column($all_units, null, 'id'));
?>
<style>
    .be-container { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 20px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; position: relative; }
    
    .be-toolbar { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px; align-items: center; justify-content: space-between; background: #f8f9fa; padding: 12px 15px; border-radius: 6px; border: 1px solid #eee; }
    .be-group { display: flex; gap: 10px; align-items: center; }
    .be-input, .be-select { padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; outline: none; }
    .be-input:focus, .be-select:focus { border-color: #ff5000; }
    .be-btn { background: #fff; border: 1px solid #ddd; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 600; transition: 0.2s; white-space: nowrap; }
    .be-btn:hover { background: #f1f5f9; border-color: #cbd5e1; }
    .be-btn-primary { background: #10b981; color: #fff; border-color: #10b981; }
    .be-btn-primary:hover { background: #059669; border-color: #059669; color: #fff; }

    .drawer-panel { display: none; background: #fdfdfd; border: 1px solid #e5e7eb; border-radius: 6px; padding: 20px; margin-bottom: 20px; }
    .drawer-panel.active { display: block; }
    .f-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; }
    .f-item label { display: block; font-size: 11px; font-weight: bold; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; }
    .f-item input, .f-item select { width: 100%; }
    .f-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; }

    .col-item { display: flex; align-items: center; padding: 8px 12px; border: 1px solid #ddd; background: #fff; margin-bottom: 5px; border-radius: 4px; gap: 10px; transition: 0.2s; }
    .col-item.dragging { opacity: 0.4; background: #e0e7ff; border-color: #3b82f6; }
    .col-item.unhideable { background: #f9fafb; opacity: 0.7; }
    .drag-handle { cursor: grab; color: #9ca3af; font-size: 16px; padding-right: 5px; }
    .drag-handle:active { cursor: grabbing; }

    .be-table-wrapper { width: 100%; overflow-x: auto; height: 60vh; border: 1px solid #d1d5db; border-radius: 4px; position: relative; margin-bottom: 60px; }
    .be-table { border-collapse: collapse; table-layout: fixed; width: max-content; min-width: 100%; transition: 0.2s ease; }
    .be-table th { background: #f3f4f6; padding: 10px; text-align: left; font-size: 12px; color: #374151; font-weight: 700; position: relative; border-bottom: 2px solid #9ca3af; border-right: 1px solid #d1d5db; white-space: nowrap; user-select: none; }
    .be-table td { border-bottom: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; padding: 0; background: #fff; height: 45px; vertical-align: middle; transition: height 0.2s ease; }
    .be-table tr:hover td { background: #f9fafb; }
    
    /* Display Density Classes */
    .be-table.compact td { height: 32px; }
    .be-table.compact .cell-input { padding: 4px 8px; font-size: 12px; }
    .be-table.relaxed td { height: 60px; }
    .be-table.relaxed .cell-input { padding: 12px 8px; font-size: 14px; }
    
    th.th-pinned, td.td-pinned { position: sticky !important; z-index: 10; }
    th.th-pinned { background: #e5e7eb; z-index: 20; border-right: 2px solid #9ca3af; }
    td.td-pinned { background: #fff; border-right: 2px solid #cbd5e1; }
    
    .resizer { position: absolute; top: 0; right: 0; width: 5px; cursor: col-resize; user-select: none; height: 100%; z-index: 25; }
    .resizer:hover, .resizing { background: #3b82f6; }

    .cell-input { width: 100%; height: 100%; border: 2px solid transparent; padding: 8px; font-size: 13px; box-sizing: border-box; background: transparent; outline: none; transition: 0.1s; border-radius: 0; color: #111; }
    .cell-input:focus { background: #fff; border-color: #3b82f6; box-shadow: 0 0 0 1px #3b82f6 inset; z-index: 5; position: relative; }
    .be-chk { cursor: pointer; width: 16px; height: 16px; margin: 0 auto; display: block; }
    textarea.cell-input { resize: none; overflow: hidden; height: 100%; white-space: nowrap; text-overflow: ellipsis; line-height: 28px; }
    textarea.cell-input:focus { white-space: normal; overflow: auto; height: 80px; position: absolute; top: 0; left: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

    .saving { background-image: linear-gradient(45deg, #f0f9ff 25%, #e0f2fe 25%, #e0f2fe 50%, #f0f9ff 50%, #f0f9ff 75%, #e0f2fe 75%, #e0f2fe 100%); background-size: 20px 20px; animation: barberpole 1s linear infinite; }
    .saved { background: #d1fae5 !important; transition: background 0.8s ease; }
    .error { background: #fee2e2 !important; border-color: #ef4444 !important; }
    @keyframes barberpole { 100% { background-position: 20px 0; } }

    .img-upload-container { position: relative; display: inline-block; width: 35px; height: 35px; margin-left:10px; margin-top: 4px; border-radius: 4px; cursor: pointer; border: 1px solid #ddd; background: #f0f0f0; overflow: visible; }
    .be-thumb { width: 100%; height: 100%; object-fit: cover; border-radius: 3px; display: block; }
    .img-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.6); color: #fff; font-size: 10px; font-weight: bold; display: flex; align-items: center; justify-content: center; border-radius: 3px; opacity: 0; transition: 0.2s; pointer-events: none; }
    .img-upload-container:hover .img-overlay { opacity: 1; }
    .img-upload-container:hover .be-thumb { transform: scale(4) translateX(15px); position: absolute; z-index: 100; box-shadow: 0 4px 15px rgba(0,0,0,0.3); border: 2px solid #fff; }
    .img-upload-container:hover .img-overlay { z-index: 101; transform: scale(4) translateX(15px); }

    /* Modals */
    .be-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:9999; align-items:center; justify-content:center; }
    .be-modal-overlay.active { display:flex; }
    .be-modal-content { background:#fff; padding:25px; border-radius:8px; width:500px; max-height: 90vh; overflow-y:auto; position:relative; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
    .be-modal-content.large { width: 850px; }
    .be-modal-close { position:absolute; top:12px; right:15px; cursor:pointer; font-size:24px; color:#666; line-height:1; }
    .be-modal-close:hover { color:#111; }

    #bulk-action-bar { position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%) translateY(100px); background: #1f2937; color: #fff; padding: 15px 25px; border-radius: 8px; display: flex; gap: 15px; align-items: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2); transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); z-index: 50; opacity: 0; pointer-events: none; }
    #bulk-action-bar.show { transform: translateX(-50%) translateY(0); opacity: 1; pointer-events: auto; }
    #bulk-action-bar select, #bulk-action-bar input { background: #374151; color: #fff; border: 1px solid #4b5563; padding: 8px 12px; border-radius: 4px; font-size: 13px; }
    #bulk-action-bar .be-btn-primary { background: #3b82f6; border-color: #3b82f6; }

    /* FIXED TOAST SIZE CSS */
    #toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; align-items: flex-end; }
    .toast { background: #333; color: #fff; padding: 12px 20px; border-radius: 6px; font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 10px; opacity: 0; transform: translateY(-20px); transition: 0.3s; height: fit-content !important; max-height: 50px; min-width: 150px; white-space: nowrap; }
    .toast.show { opacity: 1; transform: translateY(0); }
    .toast.success { border-left: 4px solid #10b981; }
    .toast.error { border-left: 4px solid #ef4444; }

    /* History Table */
    .hist-table { width:100%; border-collapse:collapse; font-size:12px; text-align:left; }
    .hist-table th { background:#f3f4f6; padding:8px; border-bottom:2px solid #ddd; }
    .hist-table td { padding:8px; border-bottom:1px solid #eee; }
    .badge { padding:3px 6px; border-radius:4px; font-size:10px; font-weight:bold; }
    .badge.rb-yes { background:#dcfce7; color:#065f46; }
    .badge.rb-no { background:#f3f4f6; color:#4b5563; }
    .badge.rb-done { background:#fee2e2; color:#991b1b; }
    
    /* Search clear icon */
    .search-wrap { position: relative; display: flex; align-items: center; }
    .search-wrap .clear-icon { position: absolute; right: 10px; cursor: pointer; color: #9ca3af; display: none; padding: 5px; }
    .search-wrap .clear-icon:hover { color: #ef4444; }
</style>

<div class="be-container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 15px;">
        <h2 style="margin:0;"><i class="fas fa-table"></i> Spreadsheet Manager</h2>
        <div id="grid-info" style="font-size: 13px; color: #666;">Loading...</div>
    </div>

    <!-- MAIN TOOLBAR -->
    <div class="be-toolbar">
        <div class="be-group">
            <button class="be-btn be-btn-primary" onclick="addNewProduct()"><i class="fas fa-plus"></i> Add Product</button>
            <button class="be-btn" onclick="toggleDrawer('filter-drawer')"><i class="fas fa-filter"></i> Filters</button>
            <button class="be-btn" onclick="toggleDrawer('column-drawer')"><i class="fas fa-columns"></i> Customise View</button>
            <div style="width:1px; height:20px; background:#ddd; margin:0 5px;"></div>
            <button class="be-btn" onclick="openExportModal()"><i class="fas fa-file-export"></i> Export</button>
            <button class="be-btn" onclick="openImportModal()"><i class="fas fa-file-import"></i> Import</button>
            <button class="be-btn" onclick="openHistoryModal()"><i class="fas fa-history"></i> Audit Log</button>
            <div style="width:1px; height:20px; background:#ddd; margin:0 5px;"></div>
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
                <input type="text" id="f-search" class="be-input" placeholder="Search (comma for multi)..." onkeyup="handleSearchInput()" style="padding-right: 30px; width: 220px;">
                <i class="fas fa-times clear-icon" id="clear-search-icon" onclick="clearSearchInput()"></i>
            </div>
            <select id="f-limit" class="be-select" onchange="resetPageAndLoad()" style="margin-left: 10px;">
                <option value="25">25 per page</option>
                <option value="50" selected>50 per page</option>
                <option value="100">100 per page</option>
                <option value="250">250 per page</option>
                <option value="500">500 per page</option>
            </select>
            <button class="be-btn" onclick="changePage(-1)">← Prev</button>
            <span id="page-num" style="font-size:13px; font-weight:bold;">Page 1</span>
            <button class="be-btn" onclick="changePage(1)">Next →</button>
        </div>
    </div>

    <!-- FILTER DRAWER -->
    <div id="filter-drawer" class="drawer-panel">
        <form id="filter-form" onsubmit="event.preventDefault(); resetPageAndLoad();">
            <div class="f-grid">
                <div class="f-item"><label>Category</label><select id="f-cat" class="be-select" onchange="updateSubcats(this.value)"><option value="">All Categories</option><?php foreach($cats as $c): ?><option value="<?php echo $c['category_id']; ?>"><?php echo htmlspecialchars($c['category_name']); ?></option><?php endforeach; ?></select></div>
                <div class="f-item"><label>Subcategory</label><select id="f-subcat" class="be-select"><option value="">All</option></select></div>
                <div class="f-item"><label>Stock Status</label><select id="f-stock" class="be-select"><option value="">Any</option><option value="in">In Stock</option><option value="out">Out of Stock</option></select></div>
                <div class="f-item"><label>Price Range</label><div style="display:flex; gap:5px;"><input type="text" inputmode="decimal" id="f-price-min" class="be-input" placeholder="Min"><input type="text" inputmode="decimal" id="f-price-max" class="be-input" placeholder="Max"></div></div>
                <div class="f-item"><label>Date Created (YYYY-MM-DD)</label><div style="display:flex; gap:5px;"><input type="text" id="f-date-add-from" class="be-input" placeholder="From"><input type="text" id="f-date-add-to" class="be-input" placeholder="To"></div></div>
                <div class="f-item"><label>Date Updated (YYYY-MM-DD)</label><div style="display:flex; gap:5px;"><input type="text" id="f-date-up-from" class="be-input" placeholder="From"><input type="text" id="f-date-up-to" class="be-input" placeholder="To"></div></div>
                <div class="f-item"><label>Product Status</label><select id="f-status" class="be-select"><option value="">Any</option><option value="1">Active</option><option value="0">Disabled</option></select></div>
                <div class="f-item"><label>Visibility</label><select id="f-vis" class="be-select"><option value="">Any</option><option value="visible">Visible</option><option value="hidden">Hidden</option></select></div>
                <div class="f-item"><label>Featured</label><select id="f-feat" class="be-select"><option value="">Any</option><option value="1">Featured Only</option><option value="0">Not Featured</option></select></div>
                <div class="f-item"><label>Exact Match Search</label><label style="margin-top:8px;"><input type="checkbox" id="f-exact"> Enable</label></div>
            </div>
            <div class="f-actions">
                <div class="be-group"><button type="submit" class="be-btn be-btn-primary">Apply Filters</button><button type="button" class="be-btn" onclick="clearFilters()">Clear All</button></div>
                <div class="be-group"><input type="text" id="preset-name" class="be-input" placeholder="Preset name..."><button type="button" class="be-btn" onclick="savePreset()"><i class="fas fa-save"></i> Save Preset</button></div>
            </div>
        </form>
    </div>

    <!-- COLUMN MANAGER / VIEW OPTIONS DRAWER -->
    <div id="column-drawer" class="drawer-panel" style="max-width: 600px;">
        <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
            <strong style="display:block; margin-bottom: 10px; color:#374151; font-size:13px;"><i class="fas fa-layer-group"></i> Table Display Density</strong>
            <div style="display: flex; gap: 15px; font-size:13px;">
                <label style="cursor:pointer;"><input type="radio" name="tbl_density" value="compact" onchange="applyDensity()"> Compact</label>
                <label style="cursor:pointer;"><input type="radio" name="tbl_density" value="normal" checked onchange="applyDensity()"> Normal</label>
                <label style="cursor:pointer;"><input type="radio" name="tbl_density" value="relaxed" onchange="applyDensity()"> Relaxed</label>
            </div>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <strong style="color:#374151; font-size:13px;"><i class="fas fa-columns"></i> Configure Columns</strong>
            <div style="font-size:12px;">
                <a href="#" onclick="toggleAllCols(true); return false;" style="color:#3b82f6; text-decoration:none;">Show All</a> | 
                <a href="#" onclick="toggleAllCols(false); return false;" style="color:#3b82f6; text-decoration:none;">Hide Unpinned</a>
            </div>
        </div>
        <div style="margin-bottom:10px; font-size:11px; color:#6b7280;">Drag to reorder. Check to Show or Pin left. Click apply to save your view.</div>
        <ul id="col-list" style="list-style:none; padding:0; margin:0; max-height:350px; overflow-y:auto; border:1px solid #eee; border-radius:4px; padding:5px;"></ul>
        <div style="margin-top:15px; display:flex; gap:10px;">
            <button class="be-btn be-btn-primary" onclick="applyColumns()">Apply Layout</button>
            <button class="be-btn" onclick="resetColumns()">Reset Defaults</button>
        </div>
    </div>

    <!-- THE GRID -->
    <div class="be-table-wrapper" id="table-scroll-wrap">
        <table class="be-table" id="be-table">
            <colgroup id="grid-colgroup"></colgroup>
            <thead><tr id="grid-head-row" style="top:0; position:sticky; z-index:15;"></tr></thead>
            <tbody id="grid-body"><tr><td style="text-align:center; padding:50px;">Loading Products...</td></tr></tbody>
        </table>
    </div>

    <!-- BULK ACTION BAR -->
    <div id="bulk-action-bar">
        <div style="font-weight:bold;" id="bulk-count">0 Selected</div>
        <div style="width:1px; height:20px; background:#4b5563;"></div>
        <label style="font-size:12px; display:flex; align-items:center; gap:5px; cursor:pointer;"><input type="checkbox" id="apply-all-filtered"> Select ALL Filtered</label>
        <div style="width:1px; height:20px; background:#4b5563;"></div>
        
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
                <option value="tags_add">Add Tags</option>
                <option value="tags_remove">Remove Tags</option>
            </optgroup>
            <optgroup label="Status, Visibility & Media">
                <option value="status_enable">Set Status: Active</option>
                <option value="status_disable">Set Status: Disabled</option>
                <option value="vis_visible">Set Visibility: Visible</option>
                <option value="vis_hidden">Set Visibility: Hidden</option>
                <option value="feat_mark">Mark Featured</option>
                <option value="feat_unmark">Remove Featured</option>
                <option value="assign_placeholder">Assign Placeholder to Missing</option>
            </optgroup>
            <optgroup label="Danger Zone">
                <option value="duplicate">Duplicate Selected</option>
                <option value="delete">Delete Completely</option>
            </optgroup>
        </select>
        
        <input type="text" id="bulk-val-input" placeholder="Value..." style="display:none; width: 120px;">
        <select id="bulk-cat-input" style="display:none;"><?php foreach($cats as $c): echo "<option value='{$c['category_id']}'>".htmlspecialchars($c['category_name'])."</option>"; endforeach; ?></select>
        <select id="bulk-subcat-input" style="display:none; max-width: 150px;">
            <?php foreach($subcats_by_cat as $cat_name => $subs): ?>
                <optgroup label="<?php echo htmlspecialchars($cat_name); ?>"><?php foreach($subs as $s): echo "<option value='{$s['sub_category_id']}'>".htmlspecialchars($s['sub_category_name'])."</option>"; endforeach; ?></optgroup>
            <?php endforeach; ?>
        </select>
        
        <button class="be-btn be-btn-primary" onclick="executeBulkAction()"><i class="fas fa-bolt"></i> Apply</button>
    </div>
</div>

<!-- MEDIA MODAL -->
<div id="media-modal" class="be-modal-overlay">
    <div class="be-modal-content" style="width:450px; text-align:center;">
        <span class="be-modal-close" onclick="document.getElementById('media-modal').classList.remove('active')">&times;</span>
        <h3 style="margin-top:0; margin-bottom:15px; color:#374151;">Media Manager</h3>
        <img id="media-preview-img" style="width:100%; height:280px; object-fit:contain; background:#f9fafb; margin-bottom:15px; border-radius:4px; border:1px solid #ddd;" src="">
        <div style="display:flex; gap:10px; justify-content:center;">
            <button class="be-btn be-btn-primary" onclick="document.getElementById('modal-file-upload').click()"><i class="fas fa-upload"></i> Upload / Replace</button>
            <button class="be-btn" style="color:#dc2626; border-color:#fca5a5;" onclick="removeProductImage()"><i class="fas fa-trash"></i> Remove</button>
        </div>
        <input type="file" id="modal-file-upload" style="display:none;" accept="image/png, image/jpeg, image/webp, image/gif" onchange="handleModalUpload(this)">
    </div>
</div>

<!-- EXPORT MODAL -->
<div id="export-modal" class="be-modal-overlay">
    <div class="be-modal-content">
        <span class="be-modal-close" onclick="document.getElementById('export-modal').classList.remove('active')">&times;</span>
        <h3 style="margin-top:0; margin-bottom:15px;">Export Products (CSV)</h3>
        <div style="margin-bottom:15px;">
            <label style="display:block; margin-bottom:8px;"><input type="radio" name="export_target" value="filtered" checked> Export ALL Filtered Results</label>
            <label style="display:block;"><input type="radio" name="export_target" value="selected" id="export-target-selected"> Export ONLY Selected Rows (<span id="export-sel-count">0</span>)</label>
        </div>
        <div style="margin-bottom:15px; font-weight:bold;">Columns to Export:</div>
        <div id="export-cols-wrapper" style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; max-height:200px; overflow-y:auto; border:1px solid #eee; padding:10px; border-radius:4px;"></div>
        <div style="margin-top:20px; text-align:right;">
            <button class="be-btn be-btn-primary" onclick="executeExport()"><i class="fas fa-download"></i> Download CSV</button>
        </div>
    </div>
</div>

<!-- IMPORT MODAL -->
<div id="import-modal" class="be-modal-overlay">
    <div class="be-modal-content large">
        <span class="be-modal-close" onclick="document.getElementById('import-modal').classList.remove('active')">&times;</span>
        <h3 style="margin-top:0; margin-bottom:15px;">Import Products (CSV)</h3>
        <div id="import-step-1">
            <p>Select a CSV file to upload and map fields.</p>
            <input type="file" id="import-file" class="be-input" accept=".csv" style="width:100%; margin-bottom:15px;">
            <button class="be-btn be-btn-primary" onclick="uploadImportFile()">Upload & Preview Mapping</button>
        </div>
        <div id="import-step-2" style="display:none;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <strong>Map CSV Columns</strong>
                <label>Identify existing products by: 
                    <select id="import-match-key" class="be-select">
                        <option value="product_id">Product ID</option>
                        <option value="sku">SKU</option>
                    </select>
                </label>
            </div>
            <div style="max-height: 400px; overflow-y: auto; border: 1px solid #eee; border-radius:4px;">
                <table class="be-table" style="min-width:100%;">
                    <thead style="position:sticky; top:0; z-index:10;"><tr><th>CSV Column</th><th>Sample Data</th><th>Map to Database Field</th></tr></thead>
                    <tbody id="import-mapping-body"></tbody>
                </table>
            </div>
            <div style="margin-top:20px; display:flex; justify-content:space-between;">
                <button class="be-btn" onclick="document.getElementById('import-step-2').style.display='none'; document.getElementById('import-step-1').style.display='block';">← Back</button>
                <button class="be-btn be-btn-primary" onclick="executeImport()"><i class="fas fa-play"></i> Run Import</button>
            </div>
        </div>
        <div id="import-step-3" style="display:none; text-align:center; padding: 40px 0;">
            <div id="import-loader"><i class="fas fa-spinner fa-spin fa-3x" style="color:#3b82f6;"></i><p style="margin-top:15px;">Processing... Please wait.</p></div>
            <div id="import-results" style="display:none;">
                <h2 style="color:#10b981;"><i class="fas fa-check-circle"></i> Import Complete</h2>
                <div style="display:flex; justify-content:center; gap:30px; margin:20px 0; font-size:18px;">
                    <div><b style="color:#3b82f6;" id="import-created">0</b> Created</div>
                    <div><b style="color:#10b981;" id="import-updated">0</b> Updated</div>
                    <div><b style="color:#ef4444;" id="import-failed">0</b> Failed</div>
                </div>
                <button class="be-btn" onclick="document.getElementById('import-modal').classList.remove('active'); loadGrid();">Close & Reload Grid</button>
            </div>
        </div>
    </div>
</div>

<!-- HISTORY / ROLLBACK MODAL -->
<div id="history-modal" class="be-modal-overlay">
    <div class="be-modal-content large">
        <span class="be-modal-close" onclick="document.getElementById('history-modal').classList.remove('active')">&times;</span>
        <h3 style="margin-top:0; margin-bottom:15px;">Audit Log & Rollbacks</h3>
        <p style="font-size:13px; color:#666; margin-bottom:15px;">View recent system edits. Click 'Undo' to revert accidental bulk edits.</p>
        <div style="max-height: 500px; overflow-y:auto; border:1px solid #ddd; border-radius:4px;">
            <table class="hist-table">
                <thead style="position:sticky; top:0; z-index:10;">
                    <tr><th>ID</th><th>User</th><th>Action Type</th><th>Detail</th><th>Affected</th><th>Date</th><th>Status</th><th>Rollback</th></tr>
                </thead>
                <tbody id="history-tbody"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="toast-container"></div>

<script>
/* ==========================================
   STATE VARIABLES & CONFIG
   Version 2.4 - Units dropdown added
========================================== */
const SCRIPT_VERSION = "2.4";
let columns = [];
let cachedData = { products: [], total: 0 };
let currentPage = 1;
let debounceTimer;
let updateBulkTimer;

let currentSort = 'product_id';
let sortDir = 'DESC';

const catOptions = `<?php foreach($cats as $c){ echo "<option value='{$c['category_id']}'>".htmlspecialchars(addslashes($c['category_name']))."</option>"; } ?>`;

// [NEW] Units data from PHP
const unitsData = <?php echo $units_json; ?>;

// [NEW] Generate unit select options HTML
const unitOptionsHtml = `<option value="">-- Select --</option>
<optgroup label="Quantity">
<?php foreach($units_grouped['quantity'] as $u): ?>
<option value="<?php echo $u['code']; ?>"><?php echo $u['name']; ?> (<?php echo $u['symbol']; ?>)</option>
<?php endforeach; ?>
</optgroup>
<optgroup label="Weight">
<?php foreach($units_grouped['weight'] as $u): ?>
<option value="<?php echo $u['code']; ?>"><?php echo $u['name']; ?> (<?php echo $u['symbol']; ?>)</option>
<?php endforeach; ?>
</optgroup>
<optgroup label="Volume">
<?php foreach($units_grouped['volume'] as $u): ?>
<option value="<?php echo $u['code']; ?>"><?php echo $u['name']; ?> (<?php echo $u['symbol']; ?>)</option>
<?php endforeach; ?>
</optgroup>
<optgroup label="Packaging">
<?php foreach($units_grouped['packaging'] as $u): ?>
<option value="<?php echo $u['code']; ?>"><?php echo $u['name']; ?> (<?php echo $u['symbol']; ?>)</option>
<?php endforeach; ?>
</optgroup>`;

const fallbackSVG = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2240%22%20height%3D%2240%22%20viewBox%3D%220%200%2040%2040%22%3E%3Crect%20width%3D%2240%22%20height%3D%2240%22%20fill%3D%22%23eeeeee%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20dominant-baseline%3D%22middle%22%20text-anchor%3D%22middle%22%20font-family%3D%22sans-serif%22%20font-size%3D%2210%22%20fill%3D%22%23999999%22%3EN%2FA%3C%2Ftext%3E%3C%2Fsvg%3E';

// [UPDATED] Column config - units now uses dropdown
const defaultColumns = [
    { id: '_chk', label: '✔', db_field:'', visible: true, width: 40, pinned: true, unhideable: true },
    { id: 'image', label: 'Image', db_field:'', visible: true, width: 65, pinned: false },
    { id: 'product_id', label: 'ID', db_field: 'product_id', visible: false, width: 100, pinned: false },
    { id: 'name', label: 'Product Name', db_field: 'product_name', visible: true, width: 250, pinned: true },
    { id: 'sku', label: 'SKU', db_field: 'sku', visible: true, width: 120, pinned: false },
    { id: 'category', label: 'Category', db_field: 'category_id', visible: true, width: 140, pinned: false },
    { id: 'subcategory', label: 'Subcategory', db_field: 'sub_category_id', visible: true, width: 140, pinned: false },
    { id: 'price', label: 'Price', db_field: 'price', visible: true, width: 90, pinned: false },
    { id: 'sale_price', label: 'Sale Price', db_field: 'sale_price', visible: true, width: 90, pinned: false },
    { id: 'stock', label: 'Stock', db_field: 'stock_quantity', visible: true, width: 80, pinned: false },
    { id: 'min_order', label: 'Min Order', db_field: 'minimum_order', visible: true, width: 90, pinned: false },
    { id: 'units', label: 'Units', db_field: 'units', visible: true, width: 120, pinned: false },
    { id: 'weight', label: 'Weight', db_field: 'weight', visible: false, width: 80, pinned: false },
    { id: 'status', label: 'Status', db_field: 'status', visible: true, width: 100, pinned: false },
    { id: 'visibility', label: 'Visibility', db_field: 'visibility', visible: true, width: 100, pinned: false },
    { id: 'featured', label: 'Feat.', db_field: 'is_featured', visible: true, width: 70, pinned: false },
    { id: 'tags', label: 'Tags', db_field: 'tags', visible: false, width: 150, pinned: false },
    { id: 'short_desc', label: 'Short Desc', db_field: 'short_description', visible: false, width: 250, pinned: false },
    { id: 'date_created', label: 'Created', db_field: 'register_date', visible: false, width: 100, pinned: false },
    { id: 'date_updated', label: 'Updated', db_field: 'updated_at', visible: false, width: 100, pinned: false },
    { id: 'actions', label: 'Actions', db_field: '', visible: true, width: 80, pinned: false, unhideable: true }
];

function loadCols() {
    let savedVersion = localStorage.getItem('be_script_version');
    if(savedVersion !== SCRIPT_VERSION) {
        localStorage.removeItem('bulk_editor_columns');
        localStorage.setItem('be_script_version', SCRIPT_VERSION);
    }
    let saved = localStorage.getItem('bulk_editor_columns');
    if(saved) {
        try {
            let parsed = JSON.parse(saved);
            columns = defaultColumns.map(dc => { let match = parsed.find(p => p.id === dc.id); return match ? {...dc, ...match} : dc; });
            columns.sort((a,b) => { let ai = parsed.findIndex(p=>p.id===a.id); let bi = parsed.findIndex(p=>p.id===b.id); return (ai===-1?999:ai) - (bi===-1?999:bi); });
        } catch(e) { columns = JSON.parse(JSON.stringify(defaultColumns)); }
    } else columns = JSON.parse(JSON.stringify(defaultColumns));
}
function saveCols() { localStorage.setItem('bulk_editor_columns', JSON.stringify(columns)); }

/* ==========================================
   DISPLAY DENSITY
========================================== */
function loadDensity() {
    let density = localStorage.getItem('be_table_density') || 'normal';
    let radio = document.querySelector(`input[name="tbl_density"][value="${density}"]`);
    if(radio) radio.checked = true;
    applyDensity();
}

function applyDensity() {
    let density = document.querySelector('input[name="tbl_density"]:checked');
    if (!density) return;
    let table = document.getElementById('be-table');
    table.classList.remove('compact', 'normal', 'relaxed');
    if(density.value !== 'normal') table.classList.add(density.value);
    localStorage.setItem('be_table_density', density.value);
}

/* ==========================================
   DYNAMIC RENDER ENGINE
   [UPDATED] Units cell now uses dropdown select
========================================== */
const cellBuilders = {
    '_chk': (p) => `<input type="checkbox" class="be-chk row-chk" value="${p.product_id}" onchange="scheduleBulkBarUpdate()">`,
    'image': (p) => {
        let img = fallbackSVG;
        if (p.picture && p.picture.trim() !== '') {
            img = p.picture.startsWith('http') ? p.picture : '../../uploads/' + p.picture;
        }
        return `<div class="img-upload-container" onclick="openMediaModal('${p.product_id}', '${img}')"><img loading="lazy" src="${img}" class="be-thumb" onerror="this.src='${fallbackSVG}'"><div class="img-overlay"><i class="fas fa-search-plus"></i></div></div>`;
    },
    'product_id': (p) => `<div style="padding:0 8px; font-size:11px; color:#888; font-family:monospace;">${String(p.product_id).substring(0,8)}</div>`,
    'name': (p) => `<input type="text" class="cell-input" data-field="product_name" value="${escapeHtml(p.product_name)}" onchange="saveCell(this, '${p.product_id}')">`,
    'sku': (p) => `<input type="text" class="cell-input" data-field="sku" value="${escapeHtml(p.sku || '')}" placeholder="SKU" onchange="saveCell(this, '${p.product_id}')">`,
    'category': (p) => `<select class="cell-input" data-field="category_id" onchange="saveCell(this, '${p.product_id}')"><option value="">Category...</option>${catOptions.replace(new RegExp(`value='${p.category_id}'`), `value='${p.category_id}' selected`)}</select>`,
    'subcategory': (p) => `<input type="text" class="cell-input" data-field="sub_category_id" value="${p.sub_category_id || ''}" placeholder="SubCat ID" onchange="saveCell(this, '${p.product_id}')">`,
    'price': (p) => `<input type="text" inputmode="decimal" class="cell-input" data-field="price" value="${p.price || 0}" onchange="saveCell(this, '${p.product_id}')">`,
    'sale_price': (p) => `<input type="text" inputmode="decimal" class="cell-input" data-field="sale_price" value="${p.sale_price || ''}" placeholder="None" onchange="saveCell(this, '${p.product_id}')">`,
    'stock': (p) => `<input type="text" inputmode="decimal" class="cell-input" data-field="stock_quantity" value="${p.stock || 0}" onchange="saveCell(this, '${p.product_id}')">`,
    'weight': (p) => `<input type="text" inputmode="decimal" class="cell-input" data-field="weight" value="${p.weight || 0}" onchange="saveCell(this, '${p.product_id}')">`,
    'min_order': (p) => `<input type="text" inputmode="decimal" class="cell-input" data-field="minimum_order" value="${p.minimum_order || 1}" onchange="saveCell(this, '${p.product_id}')">`,
    
    // [UPDATED] Units - Changed from text input to dropdown select
    'units': (p) => {
        let currentUnit = (p.units || '').toLowerCase().trim();
        let opts = unitOptionsHtml;
        if (currentUnit) {
            // Try to select the matching option
            opts = opts.replace(new RegExp(`value="${currentUnit}"`), `value="${currentUnit}" selected`);
        }
        return `<select class="cell-input" data-field="units" onchange="saveCell(this, '${p.product_id}')">${opts}</select>`;
    },
    
    'status': (p) => `<select class="cell-input" data-field="status" onchange="saveCell(this, '${p.product_id}')"><option value="1" ${p.status == 1 ? 'selected':''}>Active</option><option value="0" ${p.status == 0 ? 'selected':''}>Disabled</option></select>`,
    'visibility': (p) => `<select class="cell-input" data-field="visibility" onchange="saveCell(this, '${p.product_id}')"><option value="visible" ${p.visibility == 'visible' ? 'selected':''}>Visible</option><option value="hidden" ${p.visibility == 'hidden' ? 'selected':''}>Hidden</option></select>`,
    'featured': (p) => `<input type="checkbox" class="be-chk" data-field="is_featured" value="1" ${p.is_featured == 1 ? 'checked':''} onchange="saveCell(this, '${p.product_id}')">`,
    'tags': (p) => `<input type="text" class="cell-input" data-field="tags" value="${escapeHtml(p.tags || '')}" placeholder="tag1, tag2..." onchange="saveCell(this, '${p.product_id}')">`,
    'short_desc': (p) => `<textarea class="cell-input" data-field="short_description" onchange="saveCell(this, '${p.product_id}')" placeholder="Short description...">${escapeHtml(p.short_description || '')}</textarea>`,
    'date_created': (p) => `<div style="padding:0 8px; font-size:11px; color:#666;">${p.register_date ? p.register_date.split(' ')[0] : ''}</div>`,
    'date_updated': (p) => `<div style="padding:0 8px; font-size:11px; color:#666;">${p.updated_at ? p.updated_at.split(' ')[0] : ''}</div>`,
    'actions': (p) => `<div style="text-align:center;"><button class="be-btn" style="color:#ef4444; border-color:#fca5a5; padding:4px 10px;" onclick="deleteSingleProduct('${p.product_id}')" title="Delete Product"><i class="fas fa-trash"></i></button></div>`
};

function renderGridConfig() {
    let thead = document.getElementById('grid-head-row');
    let colgroup = document.getElementById('grid-colgroup');
    thead.innerHTML = '';
    colgroup.innerHTML = '';
    let leftOffset = 0;
    
    columns.forEach(col => {
        if(!col.visible) return;
        let c = document.createElement('col');
        c.style.width = col.width + 'px';
        colgroup.appendChild(c);
        
        let th = document.createElement('th');
        let sortIcon = '';
        
        if(col.db_field && col.id !== 'image' && col.id !== '_chk') {
            let activeClass = (currentSort === col.db_field) ? (sortDir === 'ASC' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort';
            let activeStyle = (currentSort === col.db_field) ? 'color: #ff5000;' : 'color: #a1a1aa;';
            sortIcon = `<i class="fas ${activeClass}" style="margin-left:6px; font-size:11px; ${activeStyle}"></i>`;
            th.style.cursor = 'pointer';
            th.title = "Click to sort by " + col.label;
            
            th.onclick = (e) => {
                if(e.target.classList.contains('resizer') || e.target.classList.contains('be-chk')) return;
                if(currentSort === col.db_field) sortDir = sortDir === 'ASC' ? 'DESC' : 'ASC';
                else { currentSort = col.db_field; sortDir = 'ASC'; }
                resetPageAndLoad();
            };
        }

        if(col.id === '_chk') {
            th.innerHTML = `<input type="checkbox" id="chk-all" class="be-chk" onclick="toggleAll(this)">`;
        } else {
            th.innerHTML = `<div style="display:flex; align-items:center;"><span>${col.label}</span>${sortIcon}</div>`;
        }
        
        if(col.pinned) {
            th.classList.add('th-pinned');
            th.style.left = leftOffset + 'px';
            leftOffset += col.width;
        }
        
        let resizer = document.createElement('div');
        resizer.className = 'resizer';
        resizer.addEventListener('mousedown', (e) => initResize(e, col.id));
        
        th.appendChild(resizer);
        thead.appendChild(th);
    });
}

function renderGridData() {
    let tbody = document.getElementById('grid-body');
    if(!cachedData.products || cachedData.products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="100" style="text-align:center; padding:30px;">No products match.</td></tr>';
        return;
    }
    
    let html = '';
    cachedData.products.forEach(p => {
        html += `<tr data-id="${p.product_id}">`;
        let leftOffset = 0;
        columns.forEach(col => {
            if(!col.visible) return;
            let style = col.pinned ? `style="left:${leftOffset}px;"` : '';
            let cls = col.pinned ? 'td-pinned' : '';
            if(col.pinned) leftOffset += col.width;
            html += `<td class="${cls}" ${style}>${cellBuilders[col.id] ? cellBuilders[col.id](p) : ''}</td>`;
        });
        html += `</tr>`;
    });
    
    tbody.innerHTML = html;
    document.getElementById('page-num').innerText = `Page ${currentPage}`;
    document.getElementById('grid-info').innerText = `Total: ${cachedData.total} Products`;
    let chkAll = document.getElementById('chk-all');
    if (chkAll) chkAll.checked = false;
    updateBulkBar();
}

/* ==========================================
   COLUMN MANAGER & RESIZING
========================================== */
let startX, startWidth, resizingCol;

function initResize(e, colId) {
    e.stopPropagation();
    e.preventDefault();
    resizingCol = columns.find(c => c.id === colId);
    startX = e.clientX;
    startWidth = resizingCol.width;
    document.addEventListener('mousemove', doResize);
    document.addEventListener('mouseup', stopResize);
}

function doResize(e) {
    let newWidth = Math.max(40, startWidth + (e.clientX - startX));
    resizingCol.width = newWidth;
    let cIdx = columns.filter(c=>c.visible).findIndex(c=>c.id === resizingCol.id);
    if(cIdx > -1) document.getElementById('grid-colgroup').children[cIdx].style.width = newWidth + 'px';
}

function stopResize() {
    document.removeEventListener('mousemove', doResize);
    document.removeEventListener('mouseup', stopResize);
    saveCols();
    renderGridConfig();
    renderGridData();
}

function toggleAllCols(show) {
    document.querySelectorAll('#col-list .col-item').forEach(li => {
        let checkbox = li.querySelector('.col-vis');
        if(!checkbox.disabled) checkbox.checked = show;
    });
}

function renderColumnManager() {
    let list = document.getElementById('col-list');
    list.innerHTML = '';
    columns.forEach(c => {
        if(c.id === '_chk') return;
        let li = document.createElement('li');
        li.className = 'col-item' + (c.unhideable ? ' unhideable' : '');
        li.draggable = true;
        li.dataset.id = c.id;
        li.innerHTML = `<span class="drag-handle">☰</span><label style="flex-grow:1; display:flex; align-items:center; gap:8px;"><input type="checkbox" class="col-vis" ${c.visible ? 'checked':''} ${c.unhideable ? 'disabled':''}> ${c.label}</label><label style="font-size:11px;"><input type="checkbox" class="col-pin" ${c.pinned ? 'checked':''}> Pin</label>`;
        list.appendChild(li);
        li.addEventListener('dragstart', () => li.classList.add('dragging'));
        li.addEventListener('dragend', () => li.classList.remove('dragging'));
    });
}

const colList = document.getElementById('col-list');
if (colList) {
    colList.addEventListener('dragover', e => {
        e.preventDefault();
        const afterEl = [...colList.querySelectorAll('.col-item:not(.dragging)')].reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = e.clientY - box.top - box.height / 2;
            return (offset < 0 && offset > closest.offset) ? { offset: offset, element: child } : closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
        const draggable = document.querySelector('.dragging');
        if (afterEl == null) colList.appendChild(draggable);
        else colList.insertBefore(draggable, afterEl);
    });
}

function applyColumns() {
    let order = ['_chk'];
    document.querySelectorAll('#col-list .col-item').forEach(li => {
        let id = li.dataset.id;
        let c = columns.find(x => x.id === id);
        if(!c.unhideable) c.visible = li.querySelector('.col-vis').checked;
        c.pinned = li.querySelector('.col-pin').checked;
        order.push(id);
    });
    columns.sort((a,b) => order.indexOf(a.id) - order.indexOf(b.id));
    saveCols();
    renderGridConfig();
    renderGridData();
    document.getElementById('column-drawer').classList.remove('active');
}

function resetColumns() {
    columns = JSON.parse(JSON.stringify(defaultColumns));
    saveCols();
    renderColumnManager();
    applyColumns();
}

function toggleDrawer(id) {
    document.querySelectorAll('.drawer-panel').forEach(d => {
        if(d.id !== id) d.classList.remove('active');
    });
    document.getElementById(id).classList.toggle('active');
    if (id === 'column-drawer') renderColumnManager();
}

/* ==========================================
   DATA LOADING, FILTERS & SAVING
========================================== */
function handleSearchInput() {
    let val = document.getElementById('f-search').value;
    document.getElementById('clear-search-icon').style.display = val.length > 0 ? 'block' : 'none';
    debounceLoad();
}

function clearSearchInput() {
    document.getElementById('f-search').value = '';
    document.getElementById('clear-search-icon').style.display = 'none';
    resetPageAndLoad();
}

function getFilterParams() {
    let p = new URLSearchParams();
    p.append('page', currentPage);
    if(document.getElementById('f-limit')) p.append('limit', document.getElementById('f-limit').value);
    if(document.getElementById('f-search-field')) p.append('search_field', document.getElementById('f-search-field').value);
    
    p.append('sort_by', currentSort);
    p.append('sort_dir', sortDir);
    
    ['search','cat','subcat','stock','price_min','price_max','date_add_from','date_add_to','date_up_from','date_up_to','status','vis','feat'].forEach(id => {
        let el = document.getElementById('f-'+id);
        if(el) p.append(id === 'vis' ? 'visibility' : id, el.value);
    });
    
    let exactEl = document.getElementById('f-exact');
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

function loadGrid() {
    document.getElementById('grid-body').innerHTML = '<tr><td colspan="100" style="text-align:center; padding:50px;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';
    fetch(`ajax_bulk_editor_pro.php?action=load&${getFilterParams()}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'error') {
                document.getElementById('grid-body').innerHTML = `<tr><td colspan="100" style="color:red; text-align:center; padding:30px;">Error: ${data.message}</td></tr>`;
                return;
            }
            cachedData = data;
            renderGridConfig();
            renderGridData();
        })
        .catch(e => {
            console.error('Load error:', e);
            document.getElementById('grid-body').innerHTML = `<tr><td colspan="100" style="color:red; text-align:center;">Network Error</td></tr>`;
        });
}

function saveCell(input, id) {
    let td = input.parentElement;
    td.classList.add('saving');
    let field = input.getAttribute('data-field');
    let val = input.type === 'checkbox' ? (input.checked ? 1 : 0) : input.value;
    
    let fd = new FormData();
    fd.append('action', 'inline_edit');
    fd.append('id', id);
    fd.append('field', field);
    fd.append('val', val);
    
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            td.classList.remove('saving');
            if(data.status === 'success') {
                td.classList.add('saved');
                showToast('Saved', 'success');
                setTimeout(() => td.classList.remove('saved'), 1500);
            } else {
                td.classList.add('error');
                showToast(data.message || 'Error saving', 'error');
            }
        })
        .catch(err => {
            td.classList.remove('saving');
            td.classList.add('error');
            showToast('Network error', 'error');
        });
}

function addNewProduct() {
    let fd = new FormData();
    fd.append('action', 'add_product');
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(d => {
            if(d.status === 'success') {
                showToast('Draft Created!', 'success');
                loadGrid();
            }
        });
}

function deleteSingleProduct(id) {
    if(!confirm("DANGER! Delete this product completely? This cannot be undone.")) return;
    let fd = new FormData();
    fd.append('action', 'delete_product');
    fd.append('product_id', id);
    document.body.style.cursor = 'wait';
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            document.body.style.cursor = 'default';
            if(data.status === 'success') {
                showToast('Product deleted.', 'success');
                let tr = document.querySelector(`tr[data-id="${id}"]`);
                if(tr) tr.remove();
            } else {
                showToast(data.message || 'Error', 'error');
            }
        });
}

/* ==========================================
   MEDIA MODAL
========================================== */
let activeMediaProductId = null;

function openMediaModal(productId, imgUrl) {
    activeMediaProductId = productId;
    document.getElementById('media-preview-img').src = imgUrl;
    document.getElementById('media-modal').classList.add('active');
}

function handleModalUpload(input) {
    if (!input.files || !input.files[0]) return;
    let fd = new FormData();
    fd.append('action', 'upload_image');
    fd.append('product_id', activeMediaProductId);
    fd.append('image', input.files[0]);
    document.body.style.cursor = 'wait';
    
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            document.body.style.cursor = 'default';
            if(data.status === 'success') {
                let newUrl = '../../uploads/' + data.filename + '?t=' + new Date().getTime();
                document.getElementById('media-preview-img').src = newUrl;
                let tr = document.querySelector(`tr[data-id="${activeMediaProductId}"]`);
                if(tr) {
                    let thumb = tr.querySelector('.be-thumb');
                    if(thumb) thumb.src = newUrl;
                }
                showToast('Uploaded successfully.', 'success');
            } else {
                showToast(data.message || 'Upload failed', 'error');
            }
        });
    input.value = '';
}

function removeProductImage() {
    if(!confirm("Remove image?")) return;
    let fd = new FormData();
    fd.append('action', 'remove_image');
    fd.append('product_id', activeMediaProductId);
    document.body.style.cursor = 'wait';
    
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            document.body.style.cursor = 'default';
            if(data.status === 'success') {
                document.getElementById('media-preview-img').src = fallbackSVG;
                let tr = document.querySelector(`tr[data-id="${activeMediaProductId}"]`);
                if(tr) {
                    let thumb = tr.querySelector('.be-thumb');
                    if(thumb) thumb.src = fallbackSVG;
                }
                showToast('Removed.', 'success');
            } else {
                showToast(data.message || 'Error', 'error');
            }
        });
}

/* ==========================================
   BULK ACTIONS
========================================== */
function scheduleBulkBarUpdate() {
    clearTimeout(updateBulkTimer);
    updateBulkTimer = setTimeout(updateBulkBar, 100);
}

function toggleAll(source) {
    document.querySelectorAll('.row-chk').forEach(cb => cb.checked = source.checked);
    scheduleBulkBarUpdate();
}

function updateBulkBar() {
    let checked = document.querySelectorAll('.row-chk:checked').length;
    let bar = document.getElementById('bulk-action-bar');
    document.getElementById('bulk-count').innerText = `${checked} Selected`;
    let applyAllEl = document.getElementById('apply-all-filtered');
    if(checked > 0 || (applyAllEl && applyAllEl.checked)) {
        bar.classList.add('show');
    } else {
        bar.classList.remove('show');
    }
}

let applyAllEl = document.getElementById('apply-all-filtered');
if (applyAllEl) {
    applyAllEl.addEventListener('change', scheduleBulkBarUpdate);
}

function toggleBulkInput() {
    let act = document.getElementById('bulk-action-select').value;
    document.getElementById('bulk-val-input').style.display = 'none';
    document.getElementById('bulk-cat-input').style.display = 'none';
    document.getElementById('bulk-subcat-input').style.display = 'none';
    
    if (act.includes('price') || act.includes('stock') || act.includes('tags')) {
        document.getElementById('bulk-val-input').style.display = 'block';
    } else if (act === 'cat_change') {
        document.getElementById('bulk-cat-input').style.display = 'block';
    } else if (act === 'subcat_change') {
        document.getElementById('bulk-subcat-input').style.display = 'block';
    }
}

function executeBulkAction() {
    let action = document.getElementById('bulk-action-select').value;
    if(!action) return alert("Select an action.");
    
    let isAll = document.getElementById('apply-all-filtered').checked;
    let chks = Array.from(document.querySelectorAll('.row-chk:checked')).map(c => c.value);
    
    let countToEdit = isAll ? cachedData.total : chks.length;
    if(countToEdit === 0) return alert("Select products.");
    if(countToEdit > 50) {
        if(!confirm(`WARNING! You are about to mass-edit ${countToEdit} products. Continue?`)) return;
    }
    
    let val = '';
    if(document.getElementById('bulk-val-input').style.display === 'block') {
        val = document.getElementById('bulk-val-input').value;
    } else if(document.getElementById('bulk-cat-input').style.display === 'block') {
        val = document.getElementById('bulk-cat-input').value;
    } else if(document.getElementById('bulk-subcat-input').style.display === 'block') {
        val = document.getElementById('bulk-subcat-input').value;
    }
    
    if(action === 'delete' && !confirm(`DANGER! Delete completely? Cannot be rolled back.`)) return;
    
    let fd = new FormData();
    fd.append('action', 'bulk_process');
    fd.append('bulk_action', action);
    fd.append('val', val);
    fd.append('apply_all_filtered', isAll);
    
    if(isAll) {
        let p = new URLSearchParams(getFilterParams());
        for(let pair of p.entries()) fd.append(pair[0], pair[1]);
    } else {
        fd.append('ids', JSON.stringify(chks));
    }
    
    document.body.style.cursor = 'wait';
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            document.body.style.cursor = 'default';
            if(data.status === 'success') {
                showToast(`Modified ${data.affected} products.`, 'success');
                document.getElementById('apply-all-filtered').checked = false;
                loadGrid();
            } else {
                showToast(data.message || 'Error', 'error');
            }
        });
}

/* ==========================================
   EXPORT CSV
========================================== */
function openExportModal() {
    let checked = document.querySelectorAll('.row-chk:checked').length;
    document.getElementById('export-sel-count').innerText = checked;
    document.getElementById('export-target-selected').disabled = checked === 0;
    if(checked === 0) document.querySelector('input[name="export_target"][value="filtered"]').checked = true;
    
    let html = '';
    columns.forEach(c => {
        if(c.id === '_chk' || c.id === 'image' || c.id === 'actions' || !c.db_field) return;
        html += `<label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="export-col-chk" value="${c.db_field}" checked> ${c.label}</label>`;
    });
    document.getElementById('export-cols-wrapper').innerHTML = html;
    document.getElementById('export-modal').classList.add('active');
}

function executeExport() {
    let type = document.querySelector('input[name="export_target"]:checked').value;
    let cols = Array.from(document.querySelectorAll('.export-col-chk:checked')).map(c => c.value);
    if(cols.length === 0) return alert("Select at least one column.");

    let fd = new FormData();
    fd.append('action', 'export_csv');
    fd.append('export_type', type);
    fd.append('export_cols', JSON.stringify(cols));
    
    if(type === 'selected') {
        let chks = Array.from(document.querySelectorAll('.row-chk:checked')).map(c => c.value);
        fd.append('ids', JSON.stringify(chks));
    } else {
        let p = new URLSearchParams(getFilterParams());
        for(let pair of p.entries()) fd.append(pair[0], pair[1]);
    }

    document.getElementById('export-modal').classList.remove('active');
    document.body.style.cursor = 'wait';
    
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(res => res.blob())
        .then(blob => {
            document.body.style.cursor = 'default';
            let url = window.URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = 'product_export_' + new Date().getTime() + '.csv';
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        })
        .catch(err => {
            document.body.style.cursor = 'default';
            alert("Export Failed");
        });
}

/* ==========================================
   IMPORT CSV
========================================== */
let tmpImportFile = '';
const dbFieldsOptions = `<option value="">-- Ignore Column --</option><option value="product_id">Product ID (Match Key)</option><option value="sku">SKU (Match Key)</option><option value="product_name">Product Name</option><option value="category_id">Category ID</option><option value="sub_category_id">Subcategory ID</option><option value="price">Price</option><option value="sale_price">Sale Price</option><option value="stock_quantity">Stock</option><option value="minimum_order">Minimum Order</option><option value="units">Units</option><option value="weight">Weight</option><option value="status">Status (1 or 0)</option><option value="is_featured">Featured (1 or 0)</option><option value="visibility">Visibility (visible/hidden)</option><option value="short_description">Short Description</option><option value="tags">Tags</option>`;

function openImportModal() {
    document.getElementById('import-step-1').style.display = 'block';
    document.getElementById('import-step-2').style.display = 'none';
    document.getElementById('import-step-3').style.display = 'none';
    document.getElementById('import-file').value = '';
    document.getElementById('import-modal').classList.add('active');
}

function uploadImportFile() {
    let file = document.getElementById('import-file').files[0];
    if(!file) return alert("Select a CSV file.");
    
    let fd = new FormData();
    fd.append('action', 'import_csv_upload');
    fd.append('csv_file', file);
    document.body.style.cursor = 'wait';
    
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            document.body.style.cursor = 'default';
            if(data.status === 'success') {
                tmpImportFile = data.tmp_file;
                let html = '';
                data.headers.forEach((h, i) => {
                    let sampleText = data.samples.map(r => escapeHtml(r[i] || '')).join(', ');
                    if(sampleText.length > 30) sampleText = sampleText.substring(0, 30) + '...';
                    let autoSelect = '';
                    let hl = h.toLowerCase();
                    if(hl.includes('name') || hl.includes('title')) autoSelect = 'product_name';
                    else if(hl === 'id' || hl === 'product_id') autoSelect = 'product_id';
                    else if(hl.includes('sku')) autoSelect = 'sku';
                    else if(hl.includes('price') && !hl.includes('sale')) autoSelect = 'price';
                    else if(hl.includes('sale')) autoSelect = 'sale_price';
                    else if(hl.includes('stock') || hl.includes('qty')) autoSelect = 'stock_quantity';
                    else if(hl.includes('unit')) autoSelect = 'units';
                    else if(hl.includes('min')) autoSelect = 'minimum_order';
                    html += `<tr><td style="font-weight:bold;">${escapeHtml(h)}</td><td style="color:#666; font-size:11px;">${sampleText}</td><td><select class="be-select import-map-sel" data-index="${i}">${dbFieldsOptions.replace(`value="${autoSelect}"`, `value="${autoSelect}" selected`)}</select></td></tr>`;
                });
                document.getElementById('import-mapping-body').innerHTML = html;
                document.getElementById('import-step-1').style.display = 'none';
                document.getElementById('import-step-2').style.display = 'block';
            } else {
                alert(data.message || 'Upload failed');
            }
        });
}

function executeImport() {
    let matchKey = document.getElementById('import-match-key').value;
    let mapping = {};
    document.querySelectorAll('.import-map-sel').forEach(sel => {
        mapping[sel.getAttribute('data-index')] = sel.value;
    });
    if(!Object.values(mapping).includes(matchKey)) {
        return alert(`Map a column to your chosen Match Key (${matchKey})!`);
    }

    document.getElementById('import-step-2').style.display = 'none';
    document.getElementById('import-step-3').style.display = 'block';
    document.getElementById('import-loader').style.display = 'block';
    document.getElementById('import-results').style.display = 'none';
    
    let fd = new FormData();
    fd.append('action', 'import_csv_process');
    fd.append('tmp_file', tmpImportFile);
    fd.append('match_key', matchKey);
    fd.append('mapping', JSON.stringify(mapping));
    
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            document.getElementById('import-loader').style.display = 'none';
            if(data.status === 'success') {
                document.getElementById('import-created').innerText = data.stats.created;
                document.getElementById('import-updated').innerText = data.stats.updated;
                document.getElementById('import-failed').innerText = data.stats.failed;
                document.getElementById('import-results').style.display = 'block';
            } else {
                alert("Error: " + (data.message || 'Unknown error'));
                document.getElementById('import-modal').classList.remove('active');
            }
        })
        .catch(err => {
            alert("Server error.");
            document.getElementById('import-modal').classList.remove('active');
        });
}

/* ==========================================
   HISTORY & ROLLBACK ENGINE
========================================== */
function openHistoryModal() {
    document.getElementById('history-modal').classList.add('active');
    document.getElementById('history-tbody').innerHTML = '<tr><td colspan="8" style="text-align:center;">Loading logs...</td></tr>';
    
    fetch('ajax_bulk_editor_pro.php?action=load_history')
        .then(res => res.json())
        .then(data => {
            if(data.status !== 'success') return;
            let html = '';
            data.history.forEach(h => {
                let rbBtn = '';
                let badge = '';
                if(h.is_rolled_back == 1) {
                    badge = '<span class="badge rb-done">Rolled Back</span>';
                } else if(h.can_rollback == 1) {
                    badge = '<span class="badge rb-yes">Supported</span>';
                    rbBtn = `<button class="be-btn" style="padding:4px 8px; font-size:11px;" onclick="executeRollback(${h.history_id})"><i class="fas fa-undo"></i> Undo</button>`;
                } else {
                    badge = '<span class="badge rb-no">No Data</span>';
                }
                html += `<tr><td>#${h.history_id}</td><td>${escapeHtml(h.admin_user)}</td><td><b>${escapeHtml(h.action_type)}</b></td><td>${escapeHtml(h.action_detail)}</td><td>${h.affected_rows} rows</td><td>${h.created_at}</td><td>${badge}</td><td>${rbBtn}</td></tr>`;
            });
            document.getElementById('history-tbody').innerHTML = html || '<tr><td colspan="8" style="text-align:center;">No history found.</td></tr>';
        });
}

function executeRollback(hid) {
    if(!confirm("Are you sure you want to rollback this action? This will overwrite current database values with the historic values.")) return;
    
    let fd = new FormData();
    fd.append('action', 'rollback_history');
    fd.append('history_id', hid);
    document.body.style.cursor = 'wait';
    
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            document.body.style.cursor = 'default';
            if(data.status === 'success') {
                showToast('Rollback Successful', 'success');
                openHistoryModal();
                loadGrid();
            } else {
                showToast(data.message || 'Error', 'error');
            }
        });
}

/* ==========================================
   UTILITIES & PRESETS
========================================== */
function updateSubcats(cat_id) {
    if(!cat_id) {
        document.getElementById('f-subcat').innerHTML = '<option value="">All</option>';
        return;
    }
    fetch(`ajax_bulk_editor_pro.php?action=get_subcats&cat_id=${cat_id}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('f-subcat').innerHTML = '<option value="">All</option>' + 
                data.subcats.map(s => `<option value="${s.sub_category_id}">${escapeHtml(s.sub_category_name)}</option>`).join('');
        });
}

function fetchPresets() {
    fetch('ajax_bulk_editor_pro.php?action=load_presets')
        .then(res => res.json())
        .then(data => {
            document.getElementById('preset-loader').innerHTML = '<option value="">Load Preset...</option>' + 
                data.presets.map(x => `<option value='${x.filter_data}'>${escapeHtml(x.preset_name)}</option>`).join('');
        })
        .catch(e => console.log('Presets not available'));
}

function savePreset() {
    let name = document.getElementById('preset-name').value.trim();
    if(!name) return alert("Enter name.");
    
    let fd = new FormData();
    fd.append('action', 'save_preset');
    fd.append('preset_name', name);
    fd.append('filter_data', getFilterParams());
    
    fetch('ajax_bulk_editor_pro.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(d => {
            showToast('Saved!', 'success');
            fetchPresets();
        });
}

function loadPreset(ds) {
    if(!ds) {
        document.getElementById('filter-form').reset();
        resetPageAndLoad();
        return;
    }
    let p = new URLSearchParams(ds);
    ['search','exact_match','cat','stock','price_min','price_max','date_add_from','date_add_to','date_up_from','date_up_to','status','vis','feat'].forEach(id => {
        let el = document.getElementById('f-'+id);
        if(el) {
            if(el.type==='checkbox') el.checked = p.get(id)==='true';
            else el.value = p.get(id==='vis'?'visibility':id) || '';
        }
    });
    updateSubcats(p.get('cat'));
    setTimeout(() => {
        document.getElementById('f-subcat').value = p.get('subcat') || '';
        resetPageAndLoad();
    }, 200);
}

function clearFilters() {
    document.getElementById('filter-form').reset();
    document.getElementById('preset-loader').value='';
    updateSubcats('');
    document.getElementById('f-search').value = '';
    document.getElementById('clear-search-icon').style.display = 'none';
    resetPageAndLoad();
}

function changePage(dir) {
    if(currentPage + dir > 0) {
        currentPage += dir;
        loadGrid();
    }
}

function showToast(msg, type) {
    let c = document.getElementById('toast-container');
    let t = document.createElement('div');
    t.className = `toast ${type} show`;
    t.innerHTML = `<i class="fas ${type==='success'?'fa-check-circle':'fa-exclamation-circle'}" style="color:${type==='success'?'#10b981':'#ef4444'};"></i> ${msg}`;
    c.appendChild(t);
    setTimeout(() => {
        t.classList.remove('show');
        setTimeout(() => t.remove(), 300);
    }, 3000);
}

function escapeHtml(u) {
    return (u||'').toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/* ==========================================
   INITIALIZATION
========================================== */
document.addEventListener('DOMContentLoaded', () => {
    loadDensity();
    loadCols();
    fetchPresets();
    loadGrid();
});
</script>