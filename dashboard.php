<?php
// Dashboard Section - Self Contained with All Styles
$r = $conn->query("SELECT COUNT(*) as c FROM product p LEFT JOIN product_stock ps ON p.product_id=ps.product_id WHERE ps.stock_quantity<10 AND ps.stock_quantity>0");
$lowStock = $r ? $r->fetch_assoc()['c'] : 0;

$r = $conn->query("SELECT COALESCE(SUM(total_amount),0) as t FROM orders WHERE status='Delivered'");
$totalRevenue = $r ? $r->fetch_assoc()['t'] : 0;

$r = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='Delivered'");
$deliveredOrders = $r ? $r->fetch_assoc()['c'] : 0;

$today = date('Y-m-d');
$r = $conn->query("SELECT COUNT(*) as c FROM orders WHERE DATE(order_date)='$today'");
$todayOrders = $r ? $r->fetch_assoc()['c'] : 0;

$r = $conn->query("SELECT COALESCE(SUM(total_amount),0) as t FROM orders WHERE DATE(order_date)='$today'");
$todayRevenue = $r ? $r->fetch_assoc()['t'] : 0;

// Recent orders
$recentOrders = [];
$result = $conn->query("SELECT * FROM orders WHERE status != 'Pending_payment' ORDER BY order_date DESC LIMIT 6");
if($result) while($row = $result->fetch_assoc()) $recentOrders[] = $row;

// Low stock products
$lowStockProducts = [];
$result = $conn->query("SELECT p.product_name, ps.stock_quantity,
                       (SELECT picture FROM product_picture WHERE product_id = p.product_id LIMIT 1) as image
                       FROM product p 
                       LEFT JOIN product_stock ps ON p.product_id = ps.product_id 
                       WHERE ps.stock_quantity < 10 
                       ORDER BY ps.stock_quantity ASC LIMIT 6");
if($result) while($row = $result->fetch_assoc()) $lowStockProducts[] = $row;
?>

<style>
/* Dashboard Specific Styles */
.db-welcome{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-radius:12px;padding:20px 25px;color:#fff;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px}
.db-welcome-left h2{margin:0 0 5px;font-size:20px;font-weight:600}
.db-welcome-left p{margin:0;opacity:0.9;font-size:13px}
.db-welcome-right{display:flex;gap:15px}
.db-welcome-stat{text-align:center;background:rgba(255,255,255,0.2);padding:10px 18px;border-radius:8px}
.db-welcome-stat strong{display:block;font-size:20px;font-weight:700}
.db-welcome-stat span{font-size:10px;opacity:0.9;text-transform:uppercase}

.db-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:15px;margin-bottom:20px}
.db-stat{background:#fff;border-radius:10px;padding:18px;display:flex;align-items:center;gap:15px;box-shadow:0 2px 8px rgba(0,0,0,0.05);text-decoration:none;color:inherit;transition:all 0.2s}
.db-stat:hover{transform:translateY(-2px);box-shadow:0 4px 15px rgba(0,0,0,0.1)}
.db-stat-icon{width:45px;height:45px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff}
.db-stat-icon.orange{background:linear-gradient(135deg,#ff6000,#ff8533)}
.db-stat-icon.blue{background:linear-gradient(135deg,#3498db,#2980b9)}
.db-stat-icon.green{background:linear-gradient(135deg,#27ae60,#2ecc71)}
.db-stat-icon.purple{background:linear-gradient(135deg,#9b59b6,#8e44ad)}
.db-stat-icon.yellow{background:linear-gradient(135deg,#f39c12,#f1c40f)}
.db-stat-icon.red{background:linear-gradient(135deg,#e74c3c,#c0392b)}
.db-stat-icon.cyan{background:linear-gradient(135deg,#00bcd4,#0097a7)}
.db-stat-info h3{margin:0;font-size:22px;font-weight:700;color:#1a1a2e}
.db-stat-info p{margin:3px 0 0;font-size:12px;color:#888}

.db-quick{background:#fff;border-radius:10px;padding:15px 20px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.05)}
.db-quick-title{margin:0 0 15px;font-size:14px;font-weight:600;color:#333;display:flex;align-items:center;gap:8px}
.db-quick-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(100px,1fr));gap:10px}
.db-quick-btn{display:flex;flex-direction:column;align-items:center;padding:15px 10px;background:#f8f9fa;border-radius:8px;text-decoration:none;color:#333;transition:all 0.2s;border:2px solid transparent}
.db-quick-btn:hover{background:#fff;border-color:#ff6000;transform:translateY(-2px)}
.db-quick-btn span:first-child{font-size:24px;margin-bottom:8px}
.db-quick-btn span:last-child{font-size:11px;font-weight:500;text-align:center}

.db-row{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
@media(max-width:900px){.db-row{grid-template-columns:1fr}}

.db-card{background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.05);overflow:hidden}
.db-card-header{padding:15px 18px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center}
.db-card-title{margin:0;font-size:14px;font-weight:600;color:#333;display:flex;align-items:center;gap:8px}
.db-card-link{font-size:12px;color:#ff6000;text-decoration:none}
.db-card-link:hover{text-decoration:underline}

.db-table{width:100%;border-collapse:collapse}
.db-table th{background:#fafafa;padding:10px 15px;text-align:left;font-size:10px;font-weight:600;color:#888;text-transform:uppercase;border-bottom:1px solid #f0f0f0}
.db-table td{padding:10px 15px;border-bottom:1px solid #f5f5f5;font-size:13px;color:#333}
.db-table tr:hover{background:#fafafa}
.db-table-empty{text-align:center;padding:30px;color:#999}

.db-badge{display:inline-block;padding:4px 10px;border-radius:12px;font-size:10px;font-weight:600}
.db-badge-success{background:#d4edda;color:#155724}
.db-badge-warning{background:#fff3cd;color:#856404}
.db-badge-danger{background:#f8d7da;color:#721c24}
.db-badge-info{background:#cce5ff;color:#004085}

.db-summary{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;margin-bottom:20px}
.db-summary-card{background:#fff;border-radius:10px;padding:15px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05)}
.db-summary-icon{width:45px;height:45px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff}
.db-summary-info h4{margin:0;font-size:20px;font-weight:700}
.db-summary-info p{margin:3px 0 0;font-size:11px;color:#888}

.db-tips{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
.db-tip{padding:12px 15px;border-radius:8px;border-left:4px solid}
.db-tip.red{background:#fff5f5;border-color:#e74c3c}
.db-tip.orange{background:#fff8e6;border-color:#f39c12}
.db-tip.green{background:#e8f8f0;border-color:#27ae60}
.db-tip.blue{background:#e8f4ff;border-color:#3498db}
.db-tip strong{display:block;font-size:12px;margin-bottom:5px}
.db-tip p{margin:0;font-size:11px;color:#666;line-height:1.4}
.db-tip a{color:inherit;font-weight:600}

.db-img{width:32px;height:32px;border-radius:6px;object-fit:cover;background:#f5f5f5}
.stock-out{color:#e74c3c;font-weight:600}
.stock-low{color:#f39c12;font-weight:600}
</style>

<!-- Welcome Banner -->
<div class="db-welcome">
    <div class="db-welcome-left">
        <h2>Welcome back, Admin! 👋</h2>
        <p>Here's what's happening with your store today.</p>
    </div>
    <div class="db-welcome-right">
        <div class="db-welcome-stat">
            <strong><?php echo $todayOrders; ?></strong>
            <span>Today Orders</span>
        </div>
        <div class="db-welcome-stat">
            <strong><?php echo number_format($todayRevenue); ?></strong>
            <span>Today Revenue</span>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="db-stats">
    <a href="?page=admin_manager&manage=orders" class="db-stat">
        <div class="db-stat-icon orange">📦</div>
        <div class="db-stat-info">
            <h3><?php echo number_format($totalOrders); ?></h3>
            <p>Total Orders</p>
        </div>
    </a>
    <a href="?page=admin_manager&manage=products" class="db-stat">
        <div class="db-stat-icon blue">🛍️</div>
        <div class="db-stat-info">
            <h3><?php echo number_format($totalProducts); ?></h3>
            <p>Products</p>
        </div>
    </a>
    <a href="?page=admin_manager&manage=payments" class="db-stat">
        <div class="db-stat-icon green">💰</div>
        <div class="db-stat-info">
            <h3><?php echo number_format($totalRevenue); ?></h3>
            <p>Revenue</p>
        </div>
    </a>
    <a href="?page=admin_manager&manage=users" class="db-stat">
        <div class="db-stat-icon purple">👥</div>
        <div class="db-stat-info">
            <h3><?php echo number_format($totalUsers); ?></h3>
            <p>Customers</p>
        </div>
    </a>
    <a href="?page=admin_manager&manage=orders" class="db-stat">
        <div class="db-stat-icon yellow">⏳</div>
        <div class="db-stat-info">
            <h3><?php echo number_format($pendingOrders); ?></h3>
            <p>Pending</p>
        </div>
    </a>
    <a href="?page=admin_manager&manage=stock" class="db-stat">
        <div class="db-stat-icon red">⚠️</div>
        <div class="db-stat-info">
            <h3><?php echo number_format($outOfStock); ?></h3>
            <p>Out of Stock</p>
        </div>
    </a>
</div>

<!-- Quick Actions -->
<div class="db-quick">
    <h3 class="db-quick-title">⚡ Quick Actions</h3>
    <div class="db-quick-grid">
        <a href="index.php?orders" class="db-quick-btn">
            <span>📋</span>
            <span>Orders</span>
        </a>
        <a href="index.php?page=bulk_editor" class="db-quick-btn">
            <span>✏️</span>
            <span>Bulk Editor</span>
        </a>
        <a href="?page=admin_manager&manage=products" class="db-quick-btn">
            <span>➕</span>
            <span>Add Product</span>
        </a>
        <a href="?page=admin_manager&manage=stock" class="db-quick-btn">
            <span>📊</span>
            <span>Stock</span>
        </a>
        <a href="?page=admin_manager&manage=categories" class="db-quick-btn">
            <span>🏷️</span>
            <span>Categories</span>
        </a>
        <a href="?page=admin_manager&manage=shipping" class="db-quick-btn">
            <span>🚚</span>
            <span>Shipping</span>
        </a>
        <a href="index.php?last-orders" class="db-quick-btn">
            <span>🕐</span>
            <span>Recent</span>
        </a>
        <a href="?page=admin_manager&manage=payments" class="db-quick-btn">
            <span>💳</span>
            <span>Payments</span>
        </a>
    </div>
</div>

<!-- Two Column: Recent Orders & Low Stock -->
<div class="db-row">
    <!-- Recent Orders -->
    <div class="db-card">
        <div class="db-card-header">
            <h3 class="db-card-title">📋 Recent Orders</h3>
            <a href="?page=admin_manager&manage=orders" class="db-card-link">View All →</a>
        </div>
        <table class="db-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if(empty($recentOrders)): ?>
            <tr><td colspan="4" class="db-table-empty">📭 No orders yet</td></tr>
            <?php else: foreach($recentOrders as $o): 
                $st = strtolower($o['status'] ?? 'pending');
                if($st == 'delivered') $badge = 'success';
                elseif($st == 'processing' || $st == 'shipped') $badge = 'info';
                elseif($st == 'canceled') $badge = 'danger';
                else $badge = 'warning';
            ?>
            <tr>
                <td><strong style="color:#ff6000;">#<?php echo $o['order_number']; ?></strong></td>
                <td><?php echo htmlspecialchars(mb_strimwidth($o['first_name'].' '.$o['last_name'], 0, 15, '..')); ?></td>
                <td><?php echo number_format($o['total_amount']); ?></td>
                <td><span class="db-badge db-badge-<?php echo $badge; ?>"><?php echo ucfirst($o['status']); ?></span></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Low Stock -->
    <div class="db-card">
        <div class="db-card-header">
            <h3 class="db-card-title">⚠️ Low Stock Alert</h3>
            <a href="?page=admin_manager&manage=stock" class="db-card-link">Manage →</a>
        </div>
        <table class="db-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Stock</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if(empty($lowStockProducts)): ?>
            <tr><td colspan="3" class="db-table-empty">✅ All stocked!</td></tr>
            <?php else: foreach($lowStockProducts as $p): 
                $stk = intval($p['stock_quantity']);
                $badge = $stk == 0 ? 'danger' : 'warning';
                $status = $stk == 0 ? 'Out' : 'Low';
                $stkClass = $stk == 0 ? 'stock-out' : 'stock-low';
            ?>
            <tr>
                <td style="display:flex;align-items:center;gap:8px;">
                    <?php if($p['image']): ?>
                    <img src="/uploads/<?php echo htmlspecialchars($p['image']); ?>" class="db-img" onerror="this.style.display='none'">
                    <?php endif; ?>
                    <span style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <?php echo htmlspecialchars($p['product_name']); ?>
                    </span>
                </td>
                <td class="<?php echo $stkClass; ?>"><?php echo $stk; ?></td>
                <td><span class="db-badge db-badge-<?php echo $badge; ?>"><?php echo $status; ?></span></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Summary Cards -->
<div class="db-summary">
    <div class="db-summary-card">
        <div class="db-summary-icon" style="background:linear-gradient(135deg,#27ae60,#2ecc71);">✅</div>
        <div class="db-summary-info">
            <h4 style="color:#27ae60;"><?php echo number_format($deliveredOrders); ?></h4>
            <p>Delivered Orders</p>
        </div>
    </div>
    <div class="db-summary-card">
        <div class="db-summary-icon" style="background:linear-gradient(135deg,#f39c12,#f1c40f);">📉</div>
        <div class="db-summary-info">
            <h4 style="color:#f39c12;"><?php echo number_format($lowStock); ?></h4>
            <p>Low Stock Items</p>
        </div>
    </div>
    <div class="db-summary-card">
        <div class="db-summary-icon" style="background:linear-gradient(135deg,#9b59b6,#8e44ad);">📁</div>
        <div class="db-summary-info">
            <h4 style="color:#9b59b6;"><?php echo number_format($totalCats); ?></h4>
            <p>Categories</p>
        </div>
    </div>
</div>

<!-- Tips -->
<div class="db-tips">
    <?php if($outOfStock > 0): ?>
    <div class="db-tip red">
        <strong style="color:#e74c3c;">⚠️ Stock Alert</strong>
        <p><?php echo $outOfStock; ?> products out of stock. <a href="?page=admin_manager&manage=stock">Update now</a></p>
    </div>
    <?php endif; ?>
    
    <?php if($pendingOrders > 0): ?>
    <div class="db-tip orange">
        <strong style="color:#f39c12;">📦 Pending Orders</strong>
        <p><?php echo $pendingOrders; ?> orders waiting. <a href="?page=admin_manager&manage=orders">Process</a></p>
    </div>
    <?php endif; ?>
    
    <div class="db-tip green">
        <strong style="color:#27ae60;">✨ Tip</strong>
        <p>Use <a href="index.php?page=bulk_editor">Bulk Editor</a> to update products quickly.</p>
    </div>
    
    <div class="db-tip blue">
        <strong style="color:#3498db;">📊 Revenue</strong>
        <p><strong><?php echo number_format($totalRevenue); ?> RWF</strong> from <?php echo $deliveredOrders; ?> orders.</p>
    </div>
</div>