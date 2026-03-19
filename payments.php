<?php
// Payments Section
$payments = [];
$result = $conn->query("
    SELECT pt.*, u.first_name, u.last_name
    FROM payment_transaction pt
    LEFT JOIN orders o ON pt.order_id = o.order_id
    LEFT JOIN user u ON o.user_id = u.user_id
    ORDER BY pt.register_date DESC
    LIMIT 150
");
if($result) while($row = $result->fetch_assoc()) $payments[] = $row;

// Calculate totals
$r = $conn->query("SELECT COALESCE(SUM(amount),0) as t FROM payment_transaction WHERE status='completed'");
$totalCompleted = $r ? $r->fetch_assoc()['t'] : 0;
$r = $conn->query("SELECT COALESCE(SUM(amount),0) as t FROM payment_transaction WHERE status='pending'");
$totalPending = $r ? $r->fetch_assoc()['t'] : 0;
?>

<!-- Payment Stats -->
<div class="ali-stats" style="margin-bottom:20px;">
    <div class="ali-stat-card">
        <div class="ali-stat-icon green">💰</div>
        <div class="ali-stat-info">
            <h3><?php echo number_format($totalCompleted); ?> RWF</h3>
            <p>Completed Payments</p>
        </div>
    </div>
    <div class="ali-stat-card">
        <div class="ali-stat-icon yellow">⏳</div>
        <div class="ali-stat-info">
            <h3><?php echo number_format($totalPending); ?> RWF</h3>
            <p>Pending Payments</p>
        </div>
    </div>
</div>

<div class="ali-card">
    <div class="ali-card-header">
        <h2 class="ali-card-title">💳 Payment Transactions <span class="count"><?php echo count($payments); ?></span></h2>
        <div class="ali-card-actions">
            <div class="ali-search">
                <span class="ali-search-icon">🔍</span>
                <input type="text" placeholder="Search..." onkeyup="filterTable(this,'payTable')">
            </div>
            <select class="ali-select" style="width:150px" onchange="filterBySelect(this,'payTable','status')">
                <option value="">All Status</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
            </select>
        </div>
    </div>
    
    <div class="ali-table-wrap">
        <table class="ali-table" id="payTable">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Customer</th>
                    <th>Order</th>
                    <th style="width:130px">Amount</th>
                    <th style="width:110px">Status</th>
                    <th style="width:130px">Date</th>
                </tr>
            </thead>
            <tbody>
            <?php if(empty($payments)): ?>
            <tr><td colspan="6" class="ali-empty"><div class="ali-empty-icon">💳</div><p>No payments yet</p></td></tr>
            <?php else: ?>
            <?php foreach($payments as $pay): 
                $status = $pay['status'] ?? 'pending';
                if($status == 'completed') $badge = 'success';
                elseif($status == 'pending') $badge = 'warning';
                else $badge = 'danger';
            ?>
            <tr data-status="<?php echo $status; ?>">
                <td><code style="background:#f5f5f5;padding:4px 8px;border-radius:4px;font-size:11px;"><?php echo substr($pay['transaction_id'] ?? $pay['id'] ?? '', 0, 12); ?>...</code></td>
                <td><?php echo htmlspecialchars(($pay['first_name'] ?? 'Guest') . ' ' . ($pay['last_name'] ?? '')); ?></td>
                <td>
                    <?php if($pay['order_id']): ?>
                    <a href="?page=admin_manager&manage=orders" style="color:#2681ff;">#<?php echo substr($pay['order_id'], 0, 8); ?></a>
                    <?php else: ?>
                    -
                    <?php endif; ?>
                </td>
                <td><strong style="color:#00b578;"><?php echo number_format($pay['amount'] ?? 0); ?> RWF</strong></td>
                <td><span class="ali-badge ali-badge-<?php echo $badge; ?>"><?php echo ucfirst($status); ?></span></td>
                <td style="color:#999;font-size:12px;"><?php echo date('M d, Y H:i', strtotime($pay['register_date'])); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>