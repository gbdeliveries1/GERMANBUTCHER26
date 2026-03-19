<?php
// Products Section

// Get categories
$categories = [];
$r = $conn->query("SELECT category_id, category_name FROM product_category ORDER BY category_name");
if($r) while($row = $r->fetch_assoc()) $categories[] = $row;

// Get products with details
$products = [];
$r = $conn->query("SELECT p.*, c.category_name, pp.price, ps.stock_quantity,
                   (SELECT picture FROM product_picture WHERE product_id = p.product_id LIMIT 1) as image
                   FROM product p
                   LEFT JOIN product_category c ON p.category_id = c.category_id
                   LEFT JOIN product_price pp ON p.product_id = pp.product_id
                   LEFT JOIN product_stock ps ON p.product_id = ps.product_id
                   ORDER BY p.product_name
                   LIMIT 200");
if($r) while($row = $r->fetch_assoc()) $products[] = $row;

$totalProducts = count($products);
?>

<style>
.sec-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:15px}
.sec-title{margin:0;font-size:20px;font-weight:600;color:#1a1a2e}
.sec-actions{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.sec-search{padding:10px 15px;border:1px solid #ddd;border-radius:8px;font-size:13px;width:200px}
.sec-select{padding:10px 15px;border:1px solid #ddd;border-radius:8px;font-size:13px;background:#fff}
.sec-btn{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;border:none;cursor:pointer}
.sec-btn-primary{background:#ff6000;color:#fff}
.sec-btn-secondary{background:#f0f0f0;color:#333}
.sec-btn-success{background:#27ae60;color:#fff}
.sec-btn-sm{padding:6px 12px;font-size:12px}
.sec-card{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);overflow:hidden}
.sec-card-header{padding:18px 20px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center}
.sec-card-title{margin:0;font-size:15px;font-weight:600}
.sec-table{width:100%;border-collapse:collapse}
.sec-table th{background:#fafafa;padding:12px 15px;text-align:left;font-size:11px;font-weight:600;color:#666;text-transform:uppercase;border-bottom:2px solid #f0f0f0}
.sec-table td{padding:12px 15px;border-bottom:1px solid #f5f5f5;font-size:13px}
.sec-table tr:hover{background:#fafafa}
.sec-table-empty{text-align:center;padding:40px;color:#999}
.sec-badge{display:inline-block;padding:4px 10px;border-radius:15px;font-size:11px;font-weight:600}
.sec-badge-green{background:#e8f5e9;color:#388e3c}
.sec-badge-orange{background:#fff3e0;color:#f57c00}
.sec-badge-red{background:#ffebee;color:#c62828}
.sec-badge-blue{background:#e3f2fd;color:#1976d2}
.sec-img{width:45px;height:45px;border-radius:8px;object-fit:cover;background:#f5f5f5}
.stock-ok{color:#27ae60;font-weight:600}
.stock-low{color:#f39c12;font-weight:600}
.stock-out{color:#e74c3c;font-weight:600}
</style>

<div class="sec-header">
    <h1 class="sec-title">📦 Products Management</h1>
    <div class="sec-actions">
        <input type="text" class="sec-search" placeholder="🔍 Search products..." oninput="filterTable(this.value)">
        <select class="sec-select" onchange="filterByCategory(this.value)">
            <option value="">All Categories</option>
            <?php foreach($categories as $c): ?>
            <option value="<?php echo htmlspecialchars($c['category_name']); ?>"><?php echo htmlspecialchars($c['category_name']); ?></option>
            <?php endforeach; ?>
        </select>
        <a href="index.php?page=bulk_editor" class="sec-btn sec-btn-success">✏️ Bulk Editor</a>
        <a href="index.php?products" class="sec-btn sec-btn-primary">+ Add Product</a>
    </div>
</div>

<div class="sec-card">
    <div class="sec-card-header">
        <h2 class="sec-card-title">All Products (<?php echo $totalProducts; ?>)</h2>
        <a href="index.php?page=bulk_editor" class="sec-btn sec-btn-sm sec-btn-secondary">Open Bulk Editor →</a>
    </div>
    <div style="overflow-x:auto;">
        <table class="sec-table" id="dataTable">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price (RWF)</th>
                    <th>Stock</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($products)): ?>
                <tr><td colspan="6" class="sec-table-empty">📦 No products yet</td></tr>
                <?php else: foreach($products as $p): 
                    $stock = intval($p['stock_quantity']);
                    if($stock == 0) { $stockClass = 'stock-out'; $badge = 'red'; $status = 'Out'; }
                    elseif($stock < 10) { $stockClass = 'stock-low'; $badge = 'orange'; $status = 'Low'; }
                    else { $stockClass = 'stock-ok'; $badge = 'green'; $status = 'OK'; }
                ?>
                <tr data-cat="<?php echo htmlspecialchars($p['category_name']); ?>">
                    <td>
                        <?php if($p['image']): ?>
                        <img src="/uploads/<?php echo htmlspecialchars($p['image']); ?>" class="sec-img" onerror="this.src='/uploads/default.png'">
                        <?php else: ?>
                        <div class="sec-img" style="display:flex;align-items:center;justify-content:center;font-size:18px;">📦</div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars(mb_strimwidth($p['product_name'], 0, 40, '...')); ?></strong></td>
                    <td><span class="sec-badge sec-badge-blue"><?php echo htmlspecialchars($p['category_name']); ?></span></td>
                    <td><strong><?php echo number_format($p['price']); ?></strong></td>
                    <td class="<?php echo $stockClass; ?>"><?php echo $stock; ?></td>
                    <td><span class="sec-badge sec-badge-<?php echo $badge; ?>"><?php echo $status; ?></span></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterTable(val){var rows=document.querySelectorAll('#dataTable tbody tr');val=val.toLowerCase();rows.forEach(function(r){r.style.display=r.textContent.toLowerCase().includes(val)?'':'none'})}
function filterByCategory(cat){var rows=document.querySelectorAll('#dataTable tbody tr');rows.forEach(function(r){r.style.display=(!cat||r.dataset.cat===cat)?'':'none'})}
</script>