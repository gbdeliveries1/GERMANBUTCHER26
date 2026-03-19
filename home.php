<?php
if (!isset($conn) || !($conn instanceof mysqli)) {
    throw new RuntimeException('Database connection ($conn) is not initialized.');
}

// Categories
$cats = [];
if ($catStmt = $conn->prepare("SELECT category_id, category_name FROM product_category ORDER BY category_name")) {
    $catStmt->execute();
    $catRes = $catStmt->get_result();
    while ($row = $catRes->fetch_assoc()) {
        $cats[$row['category_id']] = $row['category_name'];
    }
    $catStmt->close();
}

// Unit options
$units = ['kg', 'g', 'lb', 'oz', 'pcs', 'pack', 'box', 'bottle', 'liter', 'ml', 'dozen', 'pair', 'set', 'unit'];

// Total count
$total = 0;
if ($cntStmt = $conn->prepare("SELECT COUNT(*) AS c FROM product")) {
    $cntStmt->execute();
    $cntRes = $cntStmt->get_result();
    if ($cntRes && $cntRes->num_rows) {
        $total = (int) $cntRes->fetch_assoc()['c'];
    }
    $cntStmt->close();
}

// Products - including unit and minimum_order
$products = [];
$sql = "
    SELECT 
        p.product_id,
        p.product_name,
        p.category_id,
        p.unit,
        p.minimum_order,
        COALESCE(pp.price, 0) AS price,
        COALESCE(ps.stock_quantity, 0) AS stock_quantity,
        (
            SELECT pic.picture 
            FROM product_picture pic 
            WHERE pic.product_id = p.product_id 
            LIMIT 1
        ) AS picture
    FROM product p
    LEFT JOIN product_price pp ON pp.product_id = p.product_id
    LEFT JOIN product_stock ps ON ps.product_id = p.product_id
    ORDER BY p.product_name
    LIMIT 300
";
if ($prodStmt = $conn->prepare($sql)) {
    $prodStmt->execute();
    $prodRes = $prodStmt->get_result();
    while ($row = $prodRes->fetch_assoc()) {
        $products[] = $row;
    }
    $prodStmt->close();
}

$inStockCount = count(array_filter($products, function($p) { return $p['stock_quantity'] > 0; }));
$outStockCount = count(array_filter($products, function($p) { return $p['stock_quantity'] == 0; }));
?>
<div class="content-wrapper">
<style>
.bear-header{background:linear-gradient(135deg,#1e3a5f 0%,#2d5a87 100%);color:#fff;padding:20px 25px;margin:-15px -15px 0 -15px}
.bear-header h2{margin:0;font-size:22px;font-weight:600;display:flex;align-items:center;gap:10px}
.bear-header p{margin:6px 0 0;opacity:0.85;font-size:13px}
.bear-stats{display:flex;gap:15px;margin-top:15px;flex-wrap:wrap}
.bear-stat{background:rgba(255,255,255,0.15);padding:10px 16px;border-radius:8px;text-align:center;min-width:80px}
.bear-stat strong{display:block;font-size:18px;margin-bottom:2px}
.bear-stat span{font-size:11px;opacity:0.85}
.bear-toolbar{background:#fff;padding:12px 18px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin:0 -15px}
.bear-search{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.bear-search input{padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;width:180px}
.bear-search input:focus{border-color:#3b82f6;outline:none}
.bear-search select{padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;background:#fff}
.bear-btn{padding:8px 16px;border:none;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px}
.bear-btn-primary{background:#3b82f6;color:#fff}
.bear-btn-primary:hover{background:#2563eb}
.bear-btn-success{background:#10b981;color:#fff}
.bear-btn-success:hover{background:#059669}
.bear-status{padding:6px 14px;border-radius:20px;font-size:12px;font-weight:500;background:#dcfce7;color:#166534;display:flex;align-items:center;gap:5px}
.bear-table-wrap{background:#fff;margin:0 -15px;overflow-x:auto}
.bear-table{width:100%;border-collapse:collapse;min-width:1100px}
.bear-table th{background:#f8fafc;padding:10px 8px;text-align:left;font-size:10px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #e2e8f0;white-space:nowrap}
.bear-table td{padding:8px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.bear-table tr:hover{background:#fafbfc}
.bear-input{width:100%;padding:7px 8px;border:1px solid #e2e8f0;border-radius:5px;font-size:13px}
.bear-input:focus{border-color:#3b82f6;outline:none;box-shadow:0 0 0 2px rgba(59,130,246,0.1)}
.bear-input-sm{width:70px;text-align:center}
.bear-input-md{width:90px;text-align:right}
.bear-select{width:100%;padding:7px 8px;border:1px solid #e2e8f0;border-radius:5px;font-size:13px;background:#fff}
.bear-select:focus{border-color:#3b82f6;outline:none}
.bear-select-sm{width:75px}
.bear-img-cell{position:relative;width:50px}
.bear-img{width:45px;height:45px;object-fit:cover;border-radius:6px;border:1px solid #e2e8f0}
.bear-img-empty{width:45px;height:45px;background:#f1f5f9;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:16px;border:1px dashed #cbd5e1}
.bear-img-upload{position:absolute;bottom:-3px;right:-3px;width:20px;height:20px;background:#3b82f6;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;border:2px solid #fff}
.bear-img-upload input{position:absolute;width:100%;height:100%;opacity:0;cursor:pointer}
.bear-badge{padding:4px 8px;border-radius:12px;font-size:10px;font-weight:600;white-space:nowrap}
.bear-badge-green{background:#dcfce7;color:#166534}
.bear-badge-yellow{background:#fef3c7;color:#92400e}
.bear-badge-red{background:#fee2e2;color:#991b1b}
.bear-del-btn{background:#fee2e2;color:#dc2626;border:none;padding:6px 8px;border-radius:5px;cursor:pointer;font-size:11px}
.bear-del-btn:hover{background:#fecaca}
.bear-footer{background:#f8fafc;padding:12px 18px;margin:0 -15px;border-top:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}
.bear-footer span{color:#64748b;font-size:12px}
.bear-add-form{background:#f8fafc;padding:20px;margin:0 -15px;border-bottom:2px solid #e5e7eb;display:none}
.bear-add-form.show{display:block}
.bear-add-form h3{margin:0 0 15px 0;font-size:16px;color:#1e293b;display:flex;align-items:center;gap:8px}
.bear-form-row{display:flex;gap:12px;margin-bottom:12px;flex-wrap:wrap}
.bear-form-group{flex:1;min-width:120px}
.bear-form-group label{display:block;margin-bottom:5px;font-size:12px;font-weight:600;color:#374151}
.bear-form-group input,.bear-form-group select{width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:5px;font-size:13px}
.bear-form-group input:focus,.bear-form-group select:focus{border-color:#3b82f6;outline:none}
.bear-form-actions{margin-top:15px;display:flex;gap:10px}
</style>

<div class="bear-header">
    <h2>📦 Bulk Product Editor</h2>
    <p>Edit products inline • Changes save automatically on field change</p>
    <div class="bear-stats">
        <div class="bear-stat">
            <strong><?php echo $total; ?></strong>
            <span>Total</span>
        </div>
        <div class="bear-stat">
            <strong><?php echo $inStockCount; ?></strong>
            <span>In Stock</span>
        </div>
        <div class="bear-stat">
            <strong><?php echo $outStockCount; ?></strong>
            <span>Out of Stock</span>
        </div>
        <div class="bear-stat">
            <strong><?php echo count($cats); ?></strong>
            <span>Categories</span>
        </div>
    </div>
</div>

<div class="bear-toolbar">
    <div class="bear-search">
        <input type="text" id="searchInput" placeholder="🔍 Search..." onkeyup="filterProducts()">
        <select id="catFilter" onchange="filterProducts()">
            <option value="">All Categories</option>
            <?php foreach($cats as $cid => $cn): ?>
                <option value="<?php echo htmlspecialchars($cid); ?>"><?php echo htmlspecialchars($cn); ?></option>
            <?php endforeach; ?>
        </select>
        <select id="stockFilter" onchange="filterProducts()">
            <option value="">All Stock</option>
            <option value="in">In Stock</option>
            <option value="low">Low (&lt;10)</option>
            <option value="out">Out</option>
        </select>
    </div>
    <div style="display:flex;align-items:center;gap:12px">
        <span class="bear-status">● Saved</span>
        <button type="button" class="bear-btn bear-btn-primary" onclick="toggleAddForm()">+ Add Product</button>
    </div>
</div>

<!-- Add Product Form -->
<div class="bear-add-form" id="addFormSection">
    <h3>➕ Add New Product</h3>
    <form action="save_product.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <div class="bear-form-row">
            <div class="bear-form-group" style="flex:2;">
                <label>Product Name *</label>
                <input type="text" name="product_name" required placeholder="Enter product name">
            </div>
            <div class="bear-form-group">
                <label>Category *</label>
                <select name="category_id" required>
                    <option value="">Select</option>
                    <?php foreach($cats as $cid => $cn): ?>
                        <option value="<?php echo htmlspecialchars($cid); ?>"><?php echo htmlspecialchars($cn); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="bear-form-row">
            <div class="bear-form-group">
                <label>Price (RWF) *</label>
                <input type="number" name="price" required min="0" value="0">
            </div>
            <div class="bear-form-group">
                <label>Stock *</label>
                <input type="number" name="stock" required min="0" value="0">
            </div>
            <div class="bear-form-group">
                <label>Unit *</label>
                <select name="unit" required>
                    <?php foreach($units as $u): ?>
                        <option value="<?php echo $u; ?>" <?php echo $u === 'pcs' ? 'selected' : ''; ?>><?php echo $u; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="bear-form-group">
                <label>Min. Order</label>
                <input type="number" name="minimum_order" min="1" value="1">
            </div>
            <div class="bear-form-group">
                <label>Image</label>
                <input type="file" name="product_image" accept="image/*">
            </div>
        </div>
        <div class="bear-form-actions">
            <button type="submit" class="bear-btn bear-btn-success">✓ Save Product</button>
            <button type="button" class="bear-btn" style="background:#e5e7eb;color:#374151" onclick="toggleAddForm()">Cancel</button>
        </div>
    </form>
</div>

<div class="bear-table-wrap">
    <table class="bear-table">
        <thead>
            <tr>
                <th>IMG</th>
                <th style="min-width:180px">Product Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Unit</th>
                <th>Min</th>
                <th>Status</th>
                <th>Del</th>
            </tr>
        </thead>
        <tbody id="productBody">
        <?php foreach ($products as $p):
            $pid   = (string) $p['product_id'];
            $pn    = $p['product_name'] ?? '';
            $pc    = $p['category_id'] ?? '';
            $price = (int) $p['price'];
            $stock = (int) $p['stock_quantity'];
            $unit  = $p['unit'] ?? 'pcs';
            if (empty($unit)) $unit = 'pcs';
            $minOrder = (int) ($p['minimum_order'] ?? 1);
            if ($minOrder < 1) $minOrder = 1;
            $img   = $p['picture'] ?? '';

            if ($stock === 0) { $badge = 'red'; $status = 'Out'; }
            elseif ($stock < 10) { $badge = 'yellow'; $status = 'Low'; }
            else { $badge = 'green'; $status = 'OK'; }

            $dataName = strtolower($pn);
            $imgPath = '/uploads/' . rawurlencode($img);
            $fileOnDisk = $_SERVER['DOCUMENT_ROOT'] . $imgPath;
        ?>
            <tr class="prod-row"
                data-name="<?php echo htmlspecialchars($dataName, ENT_QUOTES, 'UTF-8'); ?>"
                data-cat="<?php echo htmlspecialchars($pc, ENT_QUOTES, 'UTF-8'); ?>"
                data-stock="<?php echo $stock; ?>">
                <td>
                    <div class="bear-img-cell">
                        <?php if($img && is_file($fileOnDisk)): ?>
                            <img src="<?php echo htmlspecialchars($imgPath); ?>" class="bear-img" alt="">
                        <?php else: ?>
                            <div class="bear-img-empty">📷</div>
                        <?php endif; ?>
                        <form action="save_product.php" method="POST" enctype="multipart/form-data" class="bear-img-upload">
                            <input type="hidden" name="action" value="upload">
                            <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid); ?>">
                            <input type="file" name="image" accept="image/*" onchange="this.form.submit()">
                            <span style="color:#fff;font-size:8px;">+</span>
                        </form>
                    </div>
                </td>
                <td>
                    <form action="save_product.php" method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid); ?>">
                        <input type="hidden" name="field" value="name">
                        <input type="text" name="value" value="<?php echo htmlspecialchars($pn, ENT_QUOTES, 'UTF-8'); ?>" class="bear-input" onchange="this.form.submit()">
                    </form>
                </td>
                <td>
                    <form action="save_product.php" method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid); ?>">
                        <input type="hidden" name="field" value="category">
                        <select name="value" class="bear-select" onchange="this.form.submit()">
                            <?php foreach($cats as $cid => $cn): ?>
                                <option value="<?php echo htmlspecialchars($cid); ?>" <?php echo ($cid == $pc ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($cn); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
                <td>
                    <form action="save_product.php" method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid); ?>">
                        <input type="hidden" name="field" value="price">
                        <input type="number" name="value" value="<?php echo $price; ?>" min="0" class="bear-input bear-input-md" onchange="this.form.submit()">
                    </form>
                </td>
                <td>
                    <form action="save_product.php" method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid); ?>">
                        <input type="hidden" name="field" value="stock">
                        <input type="number" name="value" value="<?php echo $stock; ?>" min="0" class="bear-input bear-input-sm" onchange="this.form.submit()">
                    </form>
                </td>
                <td>
                    <form action="save_product.php" method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid); ?>">
                        <input type="hidden" name="field" value="unit">
                        <select name="value" class="bear-select bear-select-sm" onchange="this.form.submit()">
                            <?php foreach($units as $u): ?>
                                <option value="<?php echo $u; ?>" <?php echo ($u === $unit ? 'selected' : ''); ?>><?php echo $u; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
                <td>
                    <form action="save_product.php" method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid); ?>">
                        <input type="hidden" name="field" value="minimum_order">
                        <input type="number" name="value" value="<?php echo $minOrder; ?>" min="1" class="bear-input bear-input-sm" onchange="this.form.submit()">
                    </form>
                </td>
                <td><span class="bear-badge bear-badge-<?php echo $badge; ?>"><?php echo $status; ?></span></td>
                <td>
                    <form action="save_product.php" method="POST" onsubmit="return confirm('Delete?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid); ?>">
                        <button type="submit" class="bear-del-btn">🗑</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="bear-footer">
    <span>Showing <?php echo count($products); ?> of <?php echo $total; ?> products</span>
    <span>💡 Click any field to edit inline</span>
</div>
</div>

<script>
function toggleAddForm() {
    var form = document.getElementById('addFormSection');
    form.classList.toggle('show');
    if (form.classList.contains('show')) {
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function filterProducts() {
    var search = document.getElementById('searchInput').value.toLowerCase();
    var cat = document.getElementById('catFilter').value;
    var stockF = document.getElementById('stockFilter').value;
    var rows = document.querySelectorAll('.prod-row');

    for (var i = 0; i < rows.length; i++) {
        var row = rows[i];
        var name = row.getAttribute('data-name') || '';
        var rowCat = row.getAttribute('data-cat');
        var stock = parseInt(row.getAttribute('data-stock'), 10);

        var showName = (search === '' || name.indexOf(search) > -1);
        var showCat = (cat === '' || rowCat === cat);
        var showStock = true;

        if (stockF === 'out') { showStock = (stock === 0); }
        else if (stockF === 'low') { showStock = (stock > 0 && stock < 10); }
        else if (stockF === 'in') { showStock = (stock >= 10); }

        row.style.display = (showName && showCat && showStock) ? '' : 'none';
    }
}
</script>