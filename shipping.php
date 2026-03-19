<?php
// Shipping Management - AJAX Based (No Page Reload)

// Get shipping fees
$shipping = [];
$result = $conn->query("SELECT * FROM shipping_fee ORDER BY fee_id DESC");
if ($result) while ($row = $result->fetch_assoc()) $shipping[] = $row;

// Get sector fees
$sectors = [];
$result = $conn->query("SELECT * FROM sector_shipping_fee ORDER BY sector ASC");
if ($result) while ($row = $result->fetch_assoc()) $sectors[] = $row;

// Check rw_location
$hasRwLocation = false;
$rwProvinces = [];
$checkTable = $conn->query("SHOW TABLES LIKE 'rw_location'");
if ($checkTable && $checkTable->num_rows > 0) {
    $hasRwLocation = true;
    $col = $conn->query("SHOW COLUMNS FROM rw_location LIKE 'delivery_fee'");
    if (!$col || $col->num_rows == 0) {
        $conn->query("ALTER TABLE rw_location ADD COLUMN delivery_fee INT DEFAULT 0");
    }
    $r = $conn->query("SELECT DISTINCT province FROM rw_location WHERE province != '' ORDER BY province");
    if ($r) while ($row = $r->fetch_assoc()) $rwProvinces[] = $row['province'];
}
?>

<style>
.stab{display:inline-block;padding:12px 24px;background:#f5f5f5;border:none;border-radius:8px 8px 0 0;cursor:pointer;font-size:14px;font-weight:600;color:#666;margin-right:5px}
.stab.active{background:#ff6000;color:#fff}
.stab:hover{background:#eee}
.stab.active:hover{background:#e55500}
.scard{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.08);margin-bottom:20px;overflow:hidden}
.scard-head{padding:18px 22px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;background:#fafafa;flex-wrap:wrap;gap:10px}
.scard-head h2{margin:0;font-size:17px;color:#333}
.scard-head .cnt{background:#ff6000;color:#fff;padding:3px 12px;border-radius:15px;font-size:12px;margin-left:10px}
.stable{width:100%;border-collapse:collapse}
.stable th{background:#f5f5f5;padding:12px 16px;text-align:left;font-size:12px;color:#666;text-transform:uppercase;border-bottom:2px solid #eee}
.stable td{padding:10px 16px;border-bottom:1px solid #f0f0f0;vertical-align:middle}
.stable tr:hover{background:#fafafa}
.sinput{padding:8px 12px;border:1px solid #ddd;border-radius:6px;font-size:14px}
.sinput:focus{outline:none;border-color:#ff6000}
.sbtn{padding:10px 20px;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600}
.sbtn-primary{background:#ff6000;color:#fff}
.sbtn-primary:hover{background:#e55500}
.sbtn-success{background:#27ae60;color:#fff}
.sbtn-success:hover{background:#219a52}
.sbtn-danger{background:#fff;color:#e74c3c;border:1px solid #e74c3c}
.sbtn-danger:hover{background:#e74c3c;color:#fff}
.sbtn-sm{padding:6px 12px;font-size:12px}
.sform{padding:20px;background:#f8f9fa;border-bottom:1px solid #eee;display:none}
.sform.show{display:block}
.sform-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px}
.sform-group{margin-bottom:0}
.sform-group label{display:block;margin-bottom:6px;font-size:13px;font-weight:600}
.sempty{text-align:center;padding:40px;color:#999}
.scontent{display:none}
.scontent.active{display:block}
.swrap{max-height:500px;overflow:auto}

/* Fee input - editable inline */
.fee-input{width:90px;padding:8px 10px;border:2px solid #e8e8e8;border-radius:6px;text-align:right;font-weight:700;font-size:14px;color:#27ae60;background:#fafafa;transition:all 0.2s}
.fee-input:hover{border-color:#ccc;background:#fff}
.fee-input:focus{outline:none;border-color:#ff6000;background:#fff;box-shadow:0 0 0 3px rgba(255,96,0,0.1)}
.fee-input.saving{border-color:#f39c12;background:#fffbf0}
.fee-input.saved{border-color:#27ae60;background:#e8f5e9}
.fee-input.error{border-color:#e74c3c;background:#ffebee}

/* Text input - editable inline */
.text-input{width:100%;padding:8px 12px;border:2px solid transparent;border-radius:6px;font-size:14px;background:#fafafa;transition:all 0.2s}
.text-input:hover{border-color:#e0e0e0;background:#fff}
.text-input:focus{outline:none;border-color:#ff6000;background:#fff}
.text-input.saving{border-color:#f39c12;background:#fffbf0}
.text-input.saved{border-color:#27ae60;background:#e8f5e9}

.alert-info{background:#e3f2fd;border-left:4px solid #2196f3;color:#1565c0;padding:15px 20px;border-radius:0 8px 8px 0;margin-bottom:20px;font-size:14px}
.alert-info strong{color:#0d47a1}

.sfilters{padding:15px 20px;background:#f8f9fa;border-bottom:1px solid #eee;display:flex;gap:15px;flex-wrap:wrap;align-items:flex-end}
.sfilter label{display:block;font-size:11px;margin-bottom:4px;color:#666;text-transform:uppercase}
.sfilter select{padding:8px 12px;border:1px solid #ddd;border-radius:6px;min-width:160px}

.spaging{padding:15px;text-align:center;border-top:1px solid #eee}
.spaging a{padding:6px 14px;border:1px solid #ddd;border-radius:6px;margin:0 3px;text-decoration:none;color:#666}
.spaging a:hover{border-color:#ff6000;color:#ff6000}

.search-box{position:relative}
.search-box input{padding:8px 12px 8px 35px;border:1px solid #ddd;border-radius:6px;font-size:13px;width:200px}
.search-box input:focus{outline:none;border-color:#ff6000}
.search-box::before{content:'🔍';position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:12px}

#toast{position:fixed;bottom:20px;right:20px;padding:14px 24px;border-radius:10px;color:#fff;font-size:14px;display:none;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,0.2)}
#toast.success{background:#27ae60;display:block}
#toast.error{background:#e74c3c;display:block}

.loc-prov{color:#1976d2;font-weight:500}
.loc-dist{color:#7b1fa2;font-weight:500}
.loc-sect{color:#388e3c;font-weight:600}
</style>

<!-- Info -->
<div class="alert-info">
    <strong>📍 How it works:</strong> Frontend uses <strong>Sector Fees</strong> first. If sector not found, it uses <strong>Rwanda Locations</strong> as fallback.
</div>

<!-- Tabs -->
<div style="margin-bottom:0">
    <button class="stab active" onclick="showTab('sectors',this)">📍 Sector Fees (<?php echo count($sectors); ?>)</button>
    <button class="stab" onclick="showTab('zones',this)">🌍 Shipping Zones (<?php echo count($shipping); ?>)</button>
    <?php if ($hasRwLocation): ?>
    <button class="stab" onclick="showTab('rw',this)">🗺️ Rwanda Locations</button>
    <?php endif; ?>
</div>

<!-- Tab 1: Sector Fees -->
<div class="scontent active" id="tab-sectors">
    <div class="scard">
        <div class="scard-head">
            <h2>📍 Sector Delivery Fees <span class="cnt"><?php echo count($sectors); ?></span></h2>
            <div style="display:flex;gap:10px;align-items:center">
                <div class="search-box">
                    <input type="text" id="sectorSearch" placeholder="Search sector..." onkeyup="filterSectorTable()">
                </div>
                <button class="sbtn sbtn-primary" onclick="toggleForm('addSectorForm')">+ Add Sector</button>
            </div>
        </div>
        
        <div class="sform" id="addSectorForm">
            <h4 style="margin:0 0 15px;color:#333">➕ Add New Sector Fee</h4>
            <div class="sform-grid">
                <div class="sform-group">
                    <label>Province</label>
                    <select class="sinput" id="newProvince" onchange="loadNewDistricts()" style="width:100%">
                        <option value="">Select Province</option>
                        <?php foreach ($rwProvinces as $p): ?>
                        <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sform-group">
                    <label>District</label>
                    <select class="sinput" id="newDistrict" onchange="loadNewSectors()" style="width:100%">
                        <option value="">Select District</option>
                    </select>
                </div>
                <div class="sform-group">
                    <label>Sector *</label>
                    <select class="sinput" id="newSector" style="width:100%">
                        <option value="">Select Sector</option>
                    </select>
                </div>
                <div class="sform-group">
                    <label>Fee (RWF) *</label>
                    <input type="number" class="sinput" id="newFee" min="0" placeholder="1500" style="width:100%">
                </div>
            </div>
            <div style="margin-top:15px">
                <button class="sbtn sbtn-success" onclick="addSectorFee()">✓ Save</button>
                <button class="sbtn" style="background:#eee;margin-left:10px" onclick="toggleForm('addSectorForm')">Cancel</button>
            </div>
        </div>
        
        <div class="swrap">
            <table class="stable" id="sectorTable">
                <thead>
                    <tr>
                        <th>Sector</th>
                        <th>District</th>
                        <th>Province</th>
                        <th width="140">Fee (RWF)</th>
                        <th width="70"></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($sectors)): ?>
                <tr><td colspan="5" class="sempty">No sector fees yet. Add sectors to set delivery fees.</td></tr>
                <?php else: foreach ($sectors as $sec): ?>
                <tr data-id="<?php echo $sec['fee_id']; ?>">
                    <td>
                        <input type="text" class="text-input" value="<?php echo htmlspecialchars($sec['sector'] ?? ''); ?>" 
                               data-id="<?php echo $sec['fee_id']; ?>" data-field="sector" data-table="sector_shipping_fee"
                               onchange="updateField(this)" style="font-weight:600;color:#388e3c">
                    </td>
                    <td>
                        <input type="text" class="text-input" value="<?php echo htmlspecialchars($sec['district'] ?? ''); ?>"
                               data-id="<?php echo $sec['fee_id']; ?>" data-field="district" data-table="sector_shipping_fee"
                               onchange="updateField(this)" style="color:#7b1fa2">
                    </td>
                    <td>
                        <input type="text" class="text-input" value="<?php echo htmlspecialchars($sec['province'] ?? ''); ?>"
                               data-id="<?php echo $sec['fee_id']; ?>" data-field="province" data-table="sector_shipping_fee"
                               onchange="updateField(this)" style="color:#1976d2">
                    </td>
                    <td>
                        <input type="number" class="fee-input" value="<?php echo intval($sec['fee']); ?>" min="0"
                               data-id="<?php echo $sec['fee_id']; ?>" data-field="fee" data-table="sector_shipping_fee"
                               onchange="updateField(this)">
                    </td>
                    <td>
                        <button class="sbtn sbtn-danger sbtn-sm" onclick="deleteRow('sector_shipping_fee','<?php echo $sec['fee_id']; ?>',this)">🗑</button>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Bulk Add -->
    <div class="scard">
        <div class="scard-head">
            <h2>📥 Bulk Import</h2>
            <button class="sbtn sbtn-sm" style="background:#eee" onclick="toggleForm('bulkForm')">Show/Hide</button>
        </div>
        <div class="sform" id="bulkForm">
            <p style="color:#666;font-size:13px;margin:0 0 15px">Format: <code style="background:#fff;padding:2px 8px;border-radius:4px">Sector, District, Fee</code> (one per line)</p>
            <div class="sform-group">
                <label>Province (for all)</label>
                <select class="sinput" id="bulkProvince" style="max-width:300px">
                    <option value="">Select Province</option>
                    <?php foreach ($rwProvinces as $p): ?>
                    <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="sform-group" style="margin-top:15px">
                <label>Sectors Data</label>
                <textarea class="sinput" id="bulkData" rows="5" style="width:100%;font-family:monospace" placeholder="Muhima, Nyarugenge, 1500&#10;Kimisagara, Nyarugenge, 1500"></textarea>
            </div>
            <div style="margin-top:15px">
                <button class="sbtn sbtn-success" onclick="importBulk()">📥 Import</button>
            </div>
        </div>
    </div>
    
    <!-- Quick Add from District -->
    <?php if ($hasRwLocation): ?>
    <div class="scard">
        <div class="scard-head">
            <h2>⚡ Quick Add All Sectors from District</h2>
        </div>
        <div style="padding:20px">
            <div style="display:flex;gap:15px;flex-wrap:wrap;align-items:flex-end">
                <div class="sform-group">
                    <label>Province</label>
                    <select class="sinput" id="quickProv" onchange="loadQuickDist()" style="min-width:200px">
                        <option value="">Select</option>
                        <?php foreach ($rwProvinces as $p): ?>
                        <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sform-group">
                    <label>District</label>
                    <select class="sinput" id="quickDist" style="min-width:200px">
                        <option value="">Select</option>
                    </select>
                </div>
                <div class="sform-group">
                    <label>Default Fee</label>
                    <input type="number" class="sinput" id="quickFee" min="0" placeholder="1500" style="width:120px">
                </div>
                <button class="sbtn sbtn-primary" onclick="quickAddAll()">+ Add All</button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Tab 2: Shipping Zones -->
<div class="scontent" id="tab-zones">
    <div class="scard">
        <div class="scard-head">
            <h2>🌍 Shipping Zones <span class="cnt"><?php echo count($shipping); ?></span></h2>
            <button class="sbtn sbtn-primary" onclick="toggleForm('addZoneForm')">+ Add Zone</button>
        </div>
        
        <div class="sform" id="addZoneForm">
            <div class="sform-grid">
                <div class="sform-group">
                    <label>Country *</label>
                    <input type="text" class="sinput" id="zoneCountry" value="Rwanda" style="width:100%">
                </div>
                <div class="sform-group">
                    <label>Province</label>
                    <input type="text" class="sinput" id="zoneProvince" placeholder="e.g. Kigali" style="width:100%">
                </div>
                <div class="sform-group">
                    <label>Fee (RWF) *</label>
                    <input type="number" class="sinput" id="zoneFee" min="0" placeholder="1000" style="width:100%">
                </div>
            </div>
            <div style="margin-top:15px">
                <button class="sbtn sbtn-success" onclick="addZone()">✓ Save</button>
                <button class="sbtn" style="background:#eee;margin-left:10px" onclick="toggleForm('addZoneForm')">Cancel</button>
            </div>
        </div>
        
        <table class="stable">
            <thead><tr><th>Country</th><th>Province</th><th width="140">Fee (RWF)</th><th width="70"></th></tr></thead>
            <tbody>
            <?php if (empty($shipping)): ?>
            <tr><td colspan="4" class="sempty">No shipping zones yet</td></tr>
            <?php else: foreach ($shipping as $sh): ?>
            <tr>
                <td>
                    <input type="text" class="text-input" value="<?php echo htmlspecialchars($sh['country'] ?? ''); ?>"
                           data-id="<?php echo $sh['fee_id']; ?>" data-field="country" data-table="shipping_fee"
                           onchange="updateField(this)">
                </td>
                <td>
                    <input type="text" class="text-input" value="<?php echo htmlspecialchars($sh['province'] ?? ''); ?>"
                           data-id="<?php echo $sh['fee_id']; ?>" data-field="province" data-table="shipping_fee"
                           onchange="updateField(this)">
                </td>
                <td>
                    <input type="number" class="fee-input" value="<?php echo intval($sh['fee']); ?>" min="0"
                           data-id="<?php echo $sh['fee_id']; ?>" data-field="fee" data-table="shipping_fee"
                           onchange="updateField(this)">
                </td>
                <td>
                    <button class="sbtn sbtn-danger sbtn-sm" onclick="deleteRow('shipping_fee','<?php echo $sh['fee_id']; ?>',this)">🗑</button>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($hasRwLocation): 
$rwTotal = 0; $rwWithFee = 0;
$r = $conn->query("SELECT COUNT(*) as c FROM rw_location"); if ($r && $row = $r->fetch_assoc()) $rwTotal = $row['c'];
$r = $conn->query("SELECT COUNT(*) as c FROM rw_location WHERE delivery_fee > 0"); if ($r && $row = $r->fetch_assoc()) $rwWithFee = $row['c'];

$fProv = isset($_GET['fprov']) ? $conn->real_escape_string($_GET['fprov']) : '';
$fDist = isset($_GET['fdist']) ? $conn->real_escape_string($_GET['fdist']) : '';
$page = max(1, intval($_GET['rwp'] ?? 1));
$limit = 30; $offset = ($page - 1) * $limit;

$where = "1=1";
if ($fProv) $where .= " AND province='$fProv'";
if ($fDist) $where .= " AND district='$fDist'";

$rwLocations = [];
$r = $conn->query("SELECT * FROM rw_location WHERE $where ORDER BY province,district,sector LIMIT $offset,$limit");
if ($r) while ($row = $r->fetch_assoc()) $rwLocations[] = $row;

$totalRows = 0;
$r = $conn->query("SELECT COUNT(*) as c FROM rw_location WHERE $where");
if ($r && $row = $r->fetch_assoc()) $totalRows = $row['c'];
$totalPages = max(1, ceil($totalRows / $limit));

$rwDistricts = [];
if ($fProv) {
    $r = $conn->query("SELECT DISTINCT district FROM rw_location WHERE province='$fProv' ORDER BY district");
    if ($r) while ($row = $r->fetch_assoc()) $rwDistricts[] = $row['district'];
}
?>
<!-- Tab 3: Rwanda Locations -->
<div class="scontent" id="tab-rw">
    <div style="display:flex;gap:15px;margin-bottom:20px;flex-wrap:wrap">
        <div style="background:#fff;padding:18px 25px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);text-align:center">
            <div style="font-size:24px;font-weight:700"><?php echo number_format($rwTotal); ?></div>
            <div style="font-size:11px;color:#888">TOTAL</div>
        </div>
        <div style="background:#fff;padding:18px 25px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);text-align:center">
            <div style="font-size:24px;font-weight:700;color:#27ae60"><?php echo number_format($rwWithFee); ?></div>
            <div style="font-size:11px;color:#888">WITH FEE</div>
        </div>
        <div style="background:#fff;padding:18px 25px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);text-align:center">
            <div style="font-size:24px;font-weight:700;color:#9b59b6"><?php echo $rwTotal > 0 ? round(($rwWithFee/$rwTotal)*100) : 0; ?>%</div>
            <div style="font-size:11px;color:#888">COVERAGE</div>
        </div>
    </div>

    <div class="scard">
        <div style="padding:15px 20px;background:#fff8f0;border-bottom:1px solid #ffe0b0">
            <strong style="color:#e65100">⚡ Bulk Update:</strong>
            <div style="display:flex;gap:10px;margin-top:10px;flex-wrap:wrap;align-items:flex-end">
                <select class="sinput" id="rwBulkProv" onchange="loadRwBulkDist()" style="min-width:180px">
                    <option value="">All Provinces</option>
                    <?php foreach ($rwProvinces as $p): ?>
                    <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="sinput" id="rwBulkDist" style="min-width:180px">
                    <option value="">All Districts</option>
                </select>
                <input type="number" class="sinput" id="rwBulkFee" min="0" placeholder="Fee" style="width:100px">
                <button class="sbtn sbtn-primary" onclick="rwBulkUpdate()">Apply</button>
            </div>
        </div>
        
        <div class="sfilters">
            <div class="sfilter">
                <label>Province</label>
                <select id="rwFilterProv" onchange="loadRwFilterDist()">
                    <option value="">All</option>
                    <?php foreach ($rwProvinces as $p): ?>
                    <option value="<?php echo htmlspecialchars($p); ?>" <?php echo $fProv==$p?'selected':''; ?>><?php echo htmlspecialchars($p); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="sfilter">
                <label>District</label>
                <select id="rwFilterDist">
                    <option value="">All</option>
                    <?php foreach ($rwDistricts as $d): ?>
                    <option value="<?php echo htmlspecialchars($d); ?>" <?php echo $fDist==$d?'selected':''; ?>><?php echo htmlspecialchars($d); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="sbtn sbtn-primary sbtn-sm" onclick="rwFilter()">Filter</button>
            <button class="sbtn sbtn-sm" style="background:#eee" onclick="rwClear()">Clear</button>
        </div>
        
        <div class="swrap">
            <table class="stable">
                <thead><tr><th>#</th><th>Province</th><th>District</th><th>Sector</th><th width="130">Fee</th></tr></thead>
                <tbody>
                <?php if (empty($rwLocations)): ?>
                <tr><td colspan="5" class="sempty">No locations</td></tr>
                <?php else: $n=$offset; foreach ($rwLocations as $loc): $n++; ?>
                <tr>
                    <td style="color:#999"><?php echo $n; ?></td>
                    <td class="loc-prov"><?php echo htmlspecialchars($loc['province'] ?? ''); ?></td>
                    <td class="loc-dist"><?php echo htmlspecialchars($loc['district'] ?? ''); ?></td>
                    <td class="loc-sect"><?php echo htmlspecialchars($loc['sector'] ?? ''); ?></td>
                    <td>
                        <input type="number" class="fee-input" style="width:80px;color:<?php echo intval($loc['delivery_fee'])>0?'#27ae60':'#ccc'; ?>"
                               value="<?php echo intval($loc['delivery_fee'] ?? 0); ?>" min="0"
                               data-id="<?php echo $loc['id'] ?? $loc['location_id'] ?? ''; ?>" 
                               data-field="delivery_fee" data-table="rw_location"
                               onchange="updateField(this)">
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="spaging">
            <?php $base = '?page=admin_manager&manage=shipping'; if ($fProv) $base .= '&fprov='.urlencode($fProv); if ($fDist) $base .= '&fdist='.urlencode($fDist); ?>
            <?php if ($page > 1): ?><a href="<?php echo $base; ?>&rwp=1">«</a><a href="<?php echo $base; ?>&rwp=<?php echo $page-1; ?>">‹</a><?php endif; ?>
            <span style="padding:0 15px;color:#888">Page <?php echo $page; ?>/<?php echo $totalPages; ?></span>
            <?php if ($page < $totalPages): ?><a href="<?php echo $base; ?>&rwp=<?php echo $page+1; ?>">›</a><a href="<?php echo $base; ?>&rwp=<?php echo $totalPages; ?>">»</a><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div id="toast"></div>

<script>
function showTab(id, btn) {
    document.querySelectorAll('.stab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.scontent').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + id).classList.add('active');
}

function toggleForm(id) {
    document.getElementById(id).classList.toggle('show');
}

function showToast(msg, isError) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.className = isError ? 'error' : 'success';
    setTimeout(() => t.className = '', 3000);
}

function filterSectorTable() {
    var filter = document.getElementById('sectorSearch').value.toLowerCase();
    var rows = document.querySelectorAll('#sectorTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
}

// AJAX update field - no page reload
function updateField(input) {
    var id = input.dataset.id;
    var field = input.dataset.field;
    var table = input.dataset.table;
    var value = input.value;
    
    input.classList.add('saving');
    input.classList.remove('saved', 'error');
    
    $.post('admin_save.php', {
        action: 'update_shipping_field',
        table: table,
        id: id,
        field: field,
        value: value
    }, function(res) {
        input.classList.remove('saving');
        try {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            if (r.success) {
                input.classList.add('saved');
                showToast('✓ Saved');
                setTimeout(() => input.classList.remove('saved'), 2000);
            } else {
                input.classList.add('error');
                showToast('✗ ' + (r.error || 'Failed'), true);
            }
        } catch(e) {
            input.classList.add('error');
            showToast('✗ Error', true);
        }
    }).fail(function() {
        input.classList.remove('saving');
        input.classList.add('error');
        showToast('✗ Connection error', true);
    });
}

// Delete row
function deleteRow(table, id, btn) {
    if (!confirm('Delete this item?')) return;
    
    var row = btn.closest('tr');
    row.style.opacity = '0.5';
    
    $.post('admin_save.php', {
        action: 'delete_shipping_item',
        table: table,
        id: id
    }, function(res) {
        try {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            if (r.success) {
                row.style.transition = 'all 0.3s';
                row.style.transform = 'translateX(50px)';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
                showToast('✓ Deleted');
            } else {
                row.style.opacity = '1';
                showToast('✗ ' + (r.error || 'Failed'), true);
            }
        } catch(e) {
            row.style.opacity = '1';
            showToast('✗ Error', true);
        }
    }).fail(function() {
        row.style.opacity = '1';
        showToast('✗ Connection error', true);
    });
}

// Add sector fee
function loadNewDistricts() {
    var p = document.getElementById('newProvince').value;
    var s = document.getElementById('newDistrict');
    s.innerHTML = '<option value="">Select District</option>';
    document.getElementById('newSector').innerHTML = '<option value="">Select Sector</option>';
    if (!p) return;
    $.post('includes/get_districts.php', {province: p}, h => s.innerHTML = '<option value="">Select District</option>' + h);
}

function loadNewSectors() {
    var p = document.getElementById('newProvince').value;
    var d = document.getElementById('newDistrict').value;
    var s = document.getElementById('newSector');
    s.innerHTML = '<option value="">Select Sector</option>';
    if (!d) return;
    $.post('includes/get_sectors_list.php', {province: p, district: d}, h => s.innerHTML = '<option value="">Select Sector</option>' + h);
}

function addSectorFee() {
    var sector = document.getElementById('newSector').value;
    var district = document.getElementById('newDistrict').value;
    var province = document.getElementById('newProvince').value;
    var fee = document.getElementById('newFee').value;
    
    if (!sector) { showToast('Select sector', true); return; }
    if (!fee) { showToast('Enter fee', true); return; }
    
    $.post('admin_save.php', {
        action: 'add_sector_shipping',
        sector: sector,
        district: district,
        province: province,
        fee: fee
    }, function(res) {
        try {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            if (r.success) {
                showToast('✓ Added');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('✗ ' + (r.error || 'Failed'), true);
            }
        } catch(e) {
            // May redirect, reload anyway
            setTimeout(() => location.reload(), 500);
        }
    }).fail(() => setTimeout(() => location.reload(), 500));
}

// Add zone
function addZone() {
    var country = document.getElementById('zoneCountry').value;
    var province = document.getElementById('zoneProvince').value;
    var fee = document.getElementById('zoneFee').value;
    
    if (!country) { showToast('Enter country', true); return; }
    if (!fee) { showToast('Enter fee', true); return; }
    
    $.post('admin_save.php', {
        action: 'add_shipping',
        country: country,
        province: province,
        fee: fee
    }, function() {
        showToast('✓ Added');
        setTimeout(() => location.reload(), 1000);
    }).fail(() => setTimeout(() => location.reload(), 500));
}

// Bulk import
function importBulk() {
    var province = document.getElementById('bulkProvince').value;
    var data = document.getElementById('bulkData').value;
    
    if (!data.trim()) { showToast('Enter data', true); return; }
    
    $.post('admin_save.php', {
        action: 'bulk_add_sectors',
        province: province,
        sectors_data: data
    }, function() {
        showToast('✓ Imported');
        setTimeout(() => location.reload(), 1000);
    }).fail(() => setTimeout(() => location.reload(), 500));
}

// Quick add all from district
function loadQuickDist() {
    var p = document.getElementById('quickProv').value;
    var s = document.getElementById('quickDist');
    s.innerHTML = '<option value="">Select</option>';
    if (!p) return;
    $.post('includes/get_districts.php', {province: p}, h => s.innerHTML = '<option value="">Select</option>' + h);
}

function quickAddAll() {
    var p = document.getElementById('quickProv').value;
    var d = document.getElementById('quickDist').value;
    var f = document.getElementById('quickFee').value;
    
    if (!d) { showToast('Select district', true); return; }
    if (!f) { showToast('Enter fee', true); return; }
    
    if (!confirm('Add all sectors from ' + d + ' with fee ' + parseInt(f).toLocaleString() + ' RWF?')) return;
    
    $.post('admin_save.php', {
        action: 'quick_add_district_sectors',
        province: p,
        district: d,
        fee: f
    }, function(res) {
        try {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            showToast('✓ Added ' + (r.added || 0) + ' sectors');
            setTimeout(() => location.reload(), 1500);
        } catch(e) {
            setTimeout(() => location.reload(), 1000);
        }
    }).fail(() => setTimeout(() => location.reload(), 500));
}

// RW Location functions
function loadRwBulkDist() {
    var p = document.getElementById('rwBulkProv').value;
    var s = document.getElementById('rwBulkDist');
    s.innerHTML = '<option value="">All Districts</option>';
    if (!p) return;
    $.post('includes/get_districts.php', {province: p}, h => s.innerHTML = '<option value="">All Districts</option>' + h);
}

function rwBulkUpdate() {
    var p = document.getElementById('rwBulkProv').value;
    var d = document.getElementById('rwBulkDist').value;
    var f = document.getElementById('rwBulkFee').value;
    
    if (!f) { showToast('Enter fee', true); return; }
    if (!confirm('Apply ' + parseInt(f).toLocaleString() + ' RWF to ' + (d || p || 'ALL') + '?')) return;
    
    $.post('admin_save.php', {
        action: 'bulk_update_rw_fees',
        province: p,
        district: d,
        fee: f
    }, function(res) {
        try {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            showToast('✓ Updated ' + (r.count || 0) + ' locations');
            setTimeout(() => location.reload(), 1500);
        } catch(e) { showToast('✗ Error', true); }
    }).fail(() => showToast('✗ Error', true));
}

function loadRwFilterDist() {
    var p = document.getElementById('rwFilterProv').value;
    var s = document.getElementById('rwFilterDist');
    s.innerHTML = '<option value="">All</option>';
    if (!p) return;
    $.post('includes/get_districts.php', {province: p}, h => s.innerHTML = '<option value="">All</option>' + h);
}

function rwFilter() {
    var p = document.getElementById('rwFilterProv').value;
    var d = document.getElementById('rwFilterDist').value;
    var url = '?page=admin_manager&manage=shipping&rwp=1';
    if (p) url += '&fprov=' + encodeURIComponent(p);
    if (d) url += '&fdist=' + encodeURIComponent(d);
    location.href = url;
}

function rwClear() {
    location.href = '?page=admin_manager&manage=shipping';
}

// Auto switch tab if filters
<?php if ($fProv || $fDist): ?>
document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.stab')[2].click());
<?php endif; ?>
</script>