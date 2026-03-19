<?php
// Orders Section

// Handle status update
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $oid = $conn->real_escape_string($_POST['order_id']);
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE orders SET status='$status' WHERE order_id='$oid'");
    header("Location: ?page=admin_manager&manage=orders&success=Order status updated!");
    exit;
}

// Get orders
$filter = isset($_GET['status']) ? $_GET['status'] : '';
$where = $filter ? "AND status='$filter'" : "";

$orders = [];
$r = $conn->query("SELECT * FROM orders WHERE status != 'Pending_payment' $where ORDER BY order_date DESC LIMIT 100");
if($r) while($row = $r->fetch_assoc()) $orders[] = $row;

// Stats
$pending = 0; $processing = 0; $shipped = 0; $delivered = 0;
$r = $conn->query("SELECT status, COUNT(*) as c FROM orders WHERE status != 'Pending_payment' GROUP BY status");
if($r) while($row = $r->fetch_assoc()) {
    $s = strtolower($row['status']);
    if($s == 'pending') $pending = $row['c'];
    elseif($s == 'processing') $processing = $row['c'];
    elseif($s == 'shipped') $shipped = $row['c'];
    elseif($s == 'delivered') $delivered = $row['c'];
}
?>

<style>
.sec-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:15px}
.sec-title{margin:0;font-size:20px;font-weight:600;color:#1a1a2e}
.sec-actions{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.sec-search{padding:10px 15px;border:1px solid #ddd;border-radius:8px;font-size:13px;width:200px}
.sec-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:15px;margin-bottom:20px}
.sec-stat{background:#fff;border-radius:10px;padding:18px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.05);text-decoration:none;color:inherit;border:2px solid transparent}
.sec-stat:hover{transform:translateY(-2px)}
.sec-stat.active{border-color:#ff6000}
.sec-stat h3{margin:0;font-size:26px;font-weight:700}
.sec-stat p{margin:5px 0 0;font-size:11px;color:#666}
.sec-stat.yellow h3{color:#f39c12}
.sec-stat.blue h3{color:#3498db}
.sec-stat.cyan h3{color:#1abc9c}
.sec-stat.green h3{color:#27ae60}
.sec-card{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);overflow:hidden}
.sec-card-header{padding:18px 20px;border-bottom:1px solid #f0f0f0}
.sec-card-title{margin:0;font-size:15px;font-weight:600}
.sec-table{width:100%;border-collapse:collapse}
.sec-table th{background:#fafafa;padding:12px 15px;text-align:left;font-size:11px;font-weight:600;color:#666;text-transform:uppercase;border-bottom:2px solid #f0f0f0}
.sec-table td{padding:12px 15px;border-bottom:1px solid #f5f5f5;font-size:13px}
.sec-table tr:hover{background:#fafafa}
.sec-table-empty{text-align:center;padding:40px;color:#999}
.sec-badge{display:inline-block;padding:4px 10px;border-radius:15px;font-size:11px;font-weight:600}
.sec-badge-pending{background:#fff3cd;color:#856404}
.sec-badge-processing{background:#cce5ff;color:#004085}
.sec-badge-shipped{background:#d1ecf1;color:#0c5460}
.sec-badge-delivered{background:#d4edda;color:#155724}
.sec-badge-canceled{background:#f8d7da;color:#721c24}
.sec-select-sm{padding:6px 10px;border:1px solid #ddd;border-radius:6px;font-size:12px}
.sec-btn{padding:6px 12px;border-radius:6px;font-size:12px;border:none;cursor:pointer;background:#ff6000;color:#fff}
</style>

<div class="sec-header">
    <h1 class="sec-title">🛍️ Orders Management</h1>
    <div class="sec-actions">
        <input type="text" class="sec-search" placeholder="🔍 Search orders..." oninput="filterTable(this.value)">
    </div>
</div>

<div class="sec-stats">
    <a href="?page=admin_manager&manage=orders&status=Pending" class="sec-stat yellow <?php echo $filter=='Pending'?'active':''; ?>">
        <h3><?php echo $pending; ?></h3>
        <p>⏳ Pending</p>
    </a>
    <a href="?page=admin_manager&manage=orders&status=Processing" class="sec-stat blue <?php echo $filter=='Processing'?'active':''; ?>">
        <h3><?php echo $processing; ?></h3>
        <p>🔄 Processing</p>
    </a>
    <a href="?page=admin_manager&manage=orders&status=Shipped" class="sec-stat cyan <?php echo $filter=='Shipped'?'active':''; ?>">
        <h3><?php echo $shipped; ?></h3>
        <p>🚚 Shipped</p>
    </a>
    <a href="?page=admin_manager&manage=orders&status=Delivered" class="sec-stat green <?php echo $filter=='Delivered'?'active':''; ?>">
        <h3><?php echo $delivered; ?></h3>
        <p>✅ Delivered</p>
    </a>
    <a href="?page=admin_manager&manage=orders" class="sec-stat <?php echo !$filter?'active':''; ?>">
        <h3><?php echo count($orders); ?></h3>
        <p>📋 All Orders</p>
    </a>
</div>

<div class="sec-card">
    <div class="sec-card-header">
        <h2 class="sec-card-title">Orders (<?php echo count($orders); ?>)</h2>
    </div>
    <div style="overflow-x:auto;">
        <table class="sec-table" id="dataTable">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($orders)): ?>
                <tr><td colspan="7" class="sec-table-empty">📭 No orders found</td></tr>
                <?php else: foreach($orders as $o): 
                    $st = strtolower($o['status']);
                ?>
                <tr>
                    <td><strong style="color:#ff6000;">#<?php echo $o['order_number']; ?></strong></td>
                    <td><?php echo htmlspecialchars($o['first_name'].' '.$o['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($o['phone']); ?></td>
                    <td><strong><?php echo number_format($o['total_amount']); ?></strong> RWF</td>
                    <td><?php echo date('M d, Y', strtotime($o['order_date'])); ?></td>
                    <td><span class="sec-badge sec-badge-<?php echo $st; ?>"><?php echo $o['status']; ?></span></td>
                    <td>
                        <form method="POST" style="display:flex;gap:5px;">
                            <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                            <select name="status" class="sec-select-sm">
                                <option value="Pending" <?php echo $o['status']=='Pending'?'selected':''; ?>>Pending</option>
                                <option value="Processing" <?php echo $o['status']=='Processing'?'selected':''; ?>>Processing</option>
                                <option value="Shipped" <?php echo $o['status']=='Shipped'?'selected':''; ?>>Shipped</option>
                                <option value="Delivered" <?php echo $o['status']=='Delivered'?'selected':''; ?>>Delivered</option>
                                <option value="Canceled" <?php echo $o['status']=='Canceled'?'selected':''; ?>>Canceled</option>
                            </select>
                            <button type="submit" name="update_status" class="sec-btn">Update</button>
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