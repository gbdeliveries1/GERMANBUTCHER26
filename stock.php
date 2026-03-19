<?php
// Stock Management Section

// Handle stock update
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $pid = $conn->real_escape_string($_POST['product_id']);
    $qty = intval($_POST['stock_quantity']);
    
    $r = $conn->query("SELECT * FROM product_stock WHERE product_id='$pid'");
    if($r && $r->num_rows > 0) {
        $conn->query("UPDATE product_stock SET stock_quantity='$qty' WHERE product_id='$pid'");
    } else {
        $conn->query("INSERT INTO product_stock (product_id, stock_quantity) VALUES ('$pid', '$qty')");
    }
    header("Location: ?page=admin_manager&manage=stock&success=Stock updated!");
    exit;
}

// Get products with stock
$products = [];
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

$where = "";
if($filter == 'out') $where = "HAVING stock = 0 OR stock IS NULL";
elseif($filter == 'low') $where = "HAVING stock > 0 AND stock < 10";
elseif($filter == 'ok') $where = "HAVING stock >= 10";

$r = $conn->query("SELECT p.product_id, p.product_name, c.category_name,
                   COALESCE(ps.stock_quantity, 0) as stock,
                   (SELECT picture FROM product_picture WHERE product_id = p.product_id LIMIT 1) as image
                   FROM product p
                   LEFT JOIN product_category c ON p.category_id = c.category_id
                   LEFT JOIN product_stock ps ON p.product_id = ps.product_id
                   GROUP BY p.product_id
                   $where
                   ORDER BY stock ASC, p.product_name
                   LIMIT 300");
if($r) while($row = $r->fetch_assoc()) $products[] = $row;

$outCount = 0; $lowCount = 0; $okCount = 0;
$r = $conn->query("SELECT COUNT(*) as c FROM product p LEFT JOIN product_stock ps ON p.product_id=ps.product_id WHERE ps.stock_quantity=0 OR ps.stock_quantity IS NULL");
if($r) $outCount = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) as c FROM product p LEFT JOIN product_stock ps ON p.product_id=ps.product_id WHERE ps.stock_quantity>0 AND ps.stock_quantity<10");
if($r) $lowCount = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) as c FROM product p LEFT JOIN product_stock ps ON p.product_id=ps.product_id WHERE ps.stock_quantity>=10");
if($r) $okCount = $r->fetch_assoc()['c'];
?>

<style>
.sec-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:15px}
.sec-title{margin:0;font-size:20px;font-weight:600;color:#1a1a2e}
.sec-actions{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.sec-search{padding:10px 15px;border:1px solid #ddd;border-radius:8px;font-size:13px;width:200px}
.sec-btn{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;border:none;cursor:pointer}
.sec-btn-primary{background:#ff6000;color:#fff}
.sec-btn-secondary{background:#f0f0f0;color:#333}
.sec-btn-success{background:#27ae60;color:#fff}
.sec-btn-sm{padding:6px 12px;font-size:12px}
.sec-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:15px;margin-bottom:20px}
.sec-stat{background:#fff;border-radius:10px;padding:20px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.05);text-decoration:none;color:inherit;transition:all 0.2s;border:2px solid transparent}
.sec-stat:hover{transform:translateY(-2px)}
.sec-stat.active{border-color:#ff6000}
.sec-stat h3{margin:0;font-size:28px;font-weight:700}
.sec-stat p{margin:5px 0 0;font-size:12px;color:#666}
.sec-stat.red h3{color:#e74c3c}
.sec-stat.orange h3{color:#f39c12}
.sec-stat.green h3{color:#27ae60}
.sec-card{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);overflow:hidden}
.sec-card-header{padding:18px 20px;border-bottom:1px solid #f0f0f0}
.sec-card-title{margin:0;font-size:15px;font-weight:600}
.sec-table{width:100%;border-collapse:collapse}
.sec-table th{background:#fafafa;padding:12px 15px;text-align:left;font-size:11px;font-weight:600;color:#666;text-transform:uppercase;border-bottom:2px solid #f0f0f0}
.sec-table td{padding:10px 15px;border-bottom:1px solid #f5f5f5;font-size:13px}
.sec-table tr:hover{background:#fafafa}
.sec-table-empty{text-align:center;padding:40px;color:#999}
.sec-badge{display:inline-block;padding:4px 10px;border-radius:15px;font-size:11px;font-weight:600}
.sec-badge-green{background:#e8f5e9;color:#388e3c}
.sec-badge-orange{background:#fff3e0;color:#f57c00}
.sec-badge-red{background:#ffebee;color:#c62828}
.sec-img{width:40px;height:40px;border-radius:6px;object-fit:cover;background:#f5f5f5}
.stock-input{width:80px;padding:8px 10px;border:1px solid #ddd;border-radius:6px;text-align:center;font-size:14px;font-weight:600}
.stock-input:focus{outline:none;border-color:#ff6000}
.stock-out{color:#e74c3c}
.stock-low{color:#f39c12}
.stock-ok{color:#27ae60}
</style>

<div class="sec-header">
    <h1 class="sec-title">📊 Stock Management</h1>
    <div class="sec-actions">
        <input type="text" class="sec-search" placeholder="🔍 Search..." oninput="filterTable(this.value)">
        <a href="index.php?page=bulk_editor" class="sec-btn sec-btn-success">✏️ Bulk Editor</a>
    </div>
</div>

<!-- Stats -->
<div class="sec-stats">
    <a href="?page=admin_manager&manage=stock&filter=out" class="sec-stat red <?php echo $filter=='out'?'active':''; ?>">
        <h3><?php echo $outCount; ?></h3>
        <p>⚠️ Out of Stock</p>
    </a>
    <a href="?page=admin_manager&manage=stock&filter=low" class="sec-stat orange <?php echo $filter=='low'?'active':''; ?>">
        <h3><?php echo $lowCount; ?></h3>
        <p>📉 Low Stock</p>
    </a>
    <a href="?page=admin_manager&manage=stock&filter=ok" class="sec-stat green <?php echo $filter=='ok'?'active':''; ?>">
        <h3><?php echo $okCount; ?></h3>
        <p>✅ In Stock</p>
    </a>
    <a href="?page=admin_manager&manage=stock" class="sec-stat <?php echo !$filter?'active':''; ?>">
        <h3><?php echo $outCount + $lowCount + $okCount; ?></h3>
        <p>📦 All Products</p>
    </a>
</div>

<div class="sec-card">
    <div class="sec-card-header">
        <h2 class="sec-card-title">Stock List (<?php echo count($products); ?> products)</h2>
    </div>
    <div style="overflow-x:auto;">
        <table class="sec-table" id="dataTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Current Stock</th>
                    <th>Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($products)): ?>
                <tr><td colspan="5" class="sec-table-empty">No products found</td></tr>
                <?php else: foreach($products as $p): 
                    $stock = intval($p['stock']);
                    if($stock == 0) { $stockClass = 'stock-out'; $badge = 'red'; $status = 'Out'; }
                    elseif($stock < 10) { $stockClass = 'stock-low'; $badge = 'orange'; $status = 'Low'; }
                    else { $stockClass = 'stock-ok'; $badge = 'green'; $status = 'OK'; }
                ?>
                <tr>
                    <td style="display:flex;align-items:center;gap:10px;">
                        <?php if($p['image']): ?>
                        <img src="/uploads/<?php echo htmlspecialchars($p['image']); ?>" class="sec-img">
                        <?php endif; ?>
                        <strong><?php echo htmlspecialchars(mb_strimwidth($p['product_name'], 0, 35, '...')); ?></strong>
                    </td>
                    <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                    <td class="<?php echo $stockClass; ?>" style="font-weight:700;font-size:16px;"><?php echo $stock; ?></td>
                    <td><span class="sec-badge sec-badge-<?php echo $badge; ?>"><?php echo $status; ?></span></td>
                    <td>
                        <form method="POST" style="display:flex;gap:8px;align-items:center;">
                            <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                            <input type="number" name="stock_quantity" value="<?php echo $stock; ?>" min="0" class="stock-input">
                            <button type="submit" name="update_stock" class="sec-btn sec-btn-sm sec-btn-primary">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterTable(val){var rows=document.querySelectorAll('#dataTable tbody tr');val=val.toLowerCase();rows.forEach(function(r){r.style.display=r.textContent.toLowerCase().includes(val)?'':'none'})}
</script>