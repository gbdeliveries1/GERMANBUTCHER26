<?php
/**
 * admin_styles.php (UPDATED)
 * - Fixes your 404 problem by NOT relying on missing local vendor files
 * - Keeps your existing AliExpress-like admin UI classes (ali-*)
 * - Adds optional DataTables CDN styling (only applies if your sections use DataTables)
 * - Adds small Flatsome-like polish: sticky top header support, better mobile table scroll, nicer forms
 *
 * NOTE:
 * - If your admin pages already include <head> with FontAwesome, you can remove it here.
 * - If you do NOT use DataTables at all, you can remove the DataTables CDN lines safely.
 */
?>

<!-- Font Awesome (your admin uses "fas" icons in many places) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Optional: Material Design Icons (fixes your materialdesignicons 404) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">

<!-- Optional: Bootstrap 4 (ONLY if your sections rely on bootstrap classes like .btn .table .modal) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<!-- Optional: DataTables Bootstrap4 CSS (fixes datatables 404) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-buttons-bs4@2.4.2/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-select-bs4@1.7.0/css/select.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-fixedheader-bs4@3.4.0/css/fixedHeader.bootstrap4.min.css">

<!-- jQuery (needed by many admin actions + DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Optional: Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Optional: DataTables JS -->
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons-bs4@2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-select@1.7.0/js/dataTables.select.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-select-bs4@1.7.0/js/select.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-fixedheader@3.4.0/js/dataTables.fixedHeader.min.js"></script>

<style>
:root {
    --ali-primary: #ff6a00;
    --ali-primary-dark: #e62e04;
    --ali-success: #00b578;
    --ali-warning: #ff9f00;
    --ali-danger: #e62e04;
    --ali-info: #2681ff;
    --ali-dark: #191919;
    --ali-gray: #666;
    --ali-gray-light: #999;
    --ali-border: #e8e8e8;
    --ali-bg: #f5f5f5;
    --ali-white: #fff;

    --ali-shadow: 0 1px 3px rgba(0,0,0,0.05);
    --ali-shadow-lg: 0 10px 26px rgba(0,0,0,0.10);
}

* { box-sizing: border-box; }

html, body { height: 100%; }

.ali-admin {
    background: var(--ali-bg);
    min-height: 100vh;
    margin: -15px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
}

/* ========== Header ========== */
.ali-header {
    background: linear-gradient(135deg, var(--ali-primary-dark) 0%, var(--ali-primary) 100%);
    padding: 18px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: var(--ali-white);
    box-shadow: 0 2px 10px rgba(230, 46, 4, 0.3);
}

.ali-header-left h1 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
}

.ali-header-subtitle {
    font-size: 12px;
    opacity: 0.9;
}

.ali-header-right {
    display: flex;
    gap: 30px;
}

.ali-header-stat { text-align: center; }

.ali-header-num {
    display: block;
    font-size: 22px;
    font-weight: 700;
}

.ali-header-label {
    font-size: 11px;
    opacity: 0.9;
    text-transform: uppercase;
}

/* ========== Navigation ========== */
.ali-nav {
    background: var(--ali-white);
    display: flex;
    padding: 0 15px;
    border-bottom: 1px solid var(--ali-border);
    overflow-x: auto;
    gap: 5px;

    /* nicer scroll */
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
}

.ali-nav-item {
    padding: 14px 18px;
    text-decoration: none;
    color: var(--ali-gray);
    font-size: 13px;
    font-weight: 500;
    border-bottom: 3px solid transparent;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    transition: all 0.2s;
}

.ali-nav-item:hover { color: var(--ali-primary); background: #fff8f5; }

.ali-nav-item.active { color: var(--ali-primary); border-bottom-color: var(--ali-primary); }

.ali-nav-icon { font-size: 16px; }

.ali-nav-badge {
    background: var(--ali-bg);
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    color: var(--ali-gray);
}

.ali-nav-badge-red { background: #ffe8e8; color: var(--ali-danger); }
.ali-nav-badge-orange { background: #fff3e0; color: #e65100; }

/* ========== Main Content ========== */
.ali-main {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

/* ========== Alerts ========== */
.ali-alert {
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 600;
    box-shadow: var(--ali-shadow);
}

.ali-alert-success {
    background: #e8fff0;
    color: var(--ali-success);
    border: 1px solid #b8f0d0;
}

.ali-alert-error {
    background: #ffe8e8;
    color: var(--ali-danger);
    border: 1px solid #ffc0c0;
}

/* ========== Stats Grid ========== */
.ali-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.ali-stat-card {
    background: var(--ali-white);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--ali-shadow);
    transition: all 0.2s;
    text-decoration: none;
    color: inherit;
    border: 1px solid rgba(0,0,0,0.03);
}

.ali-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--ali-shadow-lg);
}

.ali-stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
}

.ali-stat-icon.orange { background: #fff0e8; }
.ali-stat-icon.blue   { background: #e8f3ff; }
.ali-stat-icon.green  { background: #e8fff0; }
.ali-stat-icon.red    { background: #ffe8e8; }
.ali-stat-icon.purple { background: #f3e8ff; }
.ali-stat-icon.yellow { background: #fffce8; }

.ali-stat-info h3 {
    margin: 0;
    font-size: 26px;
    font-weight: 800;
    color: var(--ali-dark);
}

.ali-stat-info p {
    margin: 4px 0 0;
    font-size: 13px;
    color: var(--ali-gray);
}

/* ========== Card ========== */
.ali-card {
    background: var(--ali-white);
    border-radius: 12px;
    box-shadow: var(--ali-shadow);
    margin-bottom: 20px;
    overflow: hidden;
    border: 1px solid rgba(0,0,0,0.03);
}

.ali-card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--ali-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.ali-card-title {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: var(--ali-dark);
    display: flex;
    align-items: center;
    gap: 8px;
}

.ali-card-title .count {
    background: var(--ali-bg);
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    color: var(--ali-gray);
}

.ali-card-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

/* ========== Search ========== */
.ali-search {
    display: flex;
    align-items: center;
    background: var(--ali-bg);
    border-radius: 999px;
    padding: 0 12px;
    border: 1px solid transparent;
    transition: all 0.2s;
}

.ali-search:focus-within {
    background: var(--ali-white);
    border-color: var(--ali-primary);
    box-shadow: 0 0 0 3px rgba(255, 106, 0, 0.10);
}

.ali-search-icon { color: var(--ali-gray-light); font-size: 14px; }

.ali-search input {
    border: none;
    background: transparent;
    padding: 10px 12px;
    font-size: 13px;
    width: 200px;
    outline: none;
}

/* ========== Buttons ========== */
.ali-btn {
    padding: 10px 18px;
    border: none;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.ali-btn-primary {
    background: linear-gradient(135deg, var(--ali-primary-dark), var(--ali-primary));
    color: var(--ali-white);
}

.ali-btn-primary:hover {
    box-shadow: 0 6px 14px rgba(255, 106, 0, 0.28);
    transform: translateY(-1px);
}

.ali-btn-secondary { background: var(--ali-bg); color: var(--ali-dark); }
.ali-btn-secondary:hover { background: #ececec; }

.ali-btn-success { background: var(--ali-success); color: var(--ali-white); }

.ali-btn-danger {
    background: var(--ali-white);
    color: var(--ali-danger);
    border: 1px solid #ffcdd2;
}

.ali-btn-danger:hover { background: #fff5f5; }

.ali-btn-sm { padding: 7px 12px; font-size: 12px; border-radius: 9px; }

/* ========== Table ========== */
.ali-table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.ali-table {
    width: 100%;
    border-collapse: collapse;
}

.ali-table th {
    background: #fafafa;
    padding: 13px 16px;
    text-align: left;
    font-size: 11px;
    font-weight: 800;
    color: var(--ali-gray);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid var(--ali-border);
    white-space: nowrap;
}

.ali-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--ali-border);
    font-size: 13px;
    color: var(--ali-dark);
    vertical-align: middle;
}

.ali-table tr:last-child td { border-bottom: none; }
.ali-table tr:hover { background: #fafafa; }

/* Make any raw table safer on mobile */
.ali-main table { max-width: 100%; }

/* ========== Form Elements ========== */
.ali-input,
.ali-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--ali-border);
    border-radius: 10px;
    font-size: 13px;
    transition: all 0.2s;
    background: var(--ali-white);
}

.ali-input:focus,
.ali-select:focus {
    outline: none;
    border-color: var(--ali-primary);
    box-shadow: 0 0 0 3px rgba(255, 106, 0, 0.10);
}

.ali-input:hover,
.ali-select:hover { border-color: #cfcfcf; }

/* Add Form */
.ali-add-form {
    background: #fafafa;
    padding: 20px;
    border-bottom: 1px solid var(--ali-border);
    display: none;
}

.ali-add-form.show { display: block; }

.ali-add-form h3 {
    margin: 0 0 16px;
    font-size: 15px;
    font-weight: 700;
    color: var(--ali-dark);
}

.ali-form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
}

.ali-form-group label {
    display: block;
    margin-bottom: 6px;
    font-size: 12px;
    font-weight: 800;
    color: var(--ali-dark);
}

.ali-form-group label span { color: var(--ali-danger); }

.ali-form-actions {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--ali-border);
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Badges */
.ali-badge {
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    display: inline-block;
    white-space: nowrap;
}

.ali-badge-success { background: #e8fff0; color: var(--ali-success); }
.ali-badge-warning { background: #fff8e8; color: #b86e00; }
.ali-badge-danger  { background: #ffe8e8; color: var(--ali-danger); }
.ali-badge-info    { background: #e8f3ff; color: var(--ali-info); }
.ali-badge-gray    { background: var(--ali-bg); color: var(--ali-gray); }

/* Status Select */
.ali-status {
    padding: 6px 12px;
    border-radius: 999px;
    border: none;
    font-size: 11px;
    font-weight: 800;
    cursor: pointer;
}

.ali-status-pending     { background: #fff8e8; color: #b86e00; }
.ali-status-processing  { background: #e8f3ff; color: var(--ali-info); }
.ali-status-completed   { background: #e8fff0; color: var(--ali-success); }
.ali-status-cancelled   { background: #ffe8e8; color: var(--ali-danger); }

/* Images */
.ali-img {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    object-fit: cover;
    background: var(--ali-bg);
    border: 1px solid var(--ali-border);
}

.ali-img-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    background: var(--ali-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ccc;
    font-size: 20px;
    border: 1px dashed #ddd;
}

.ali-img-wrap { position: relative; display: inline-block; }

.ali-img-btn {
    position: absolute;
    bottom: -4px;
    right: -4px;
    width: 22px;
    height: 22px;
    background: var(--ali-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 2px solid var(--ali-white);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.ali-img-btn input {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.ali-img-btn span {
    color: var(--ali-white);
    font-size: 12px;
    font-weight: 900;
}

/* Quick Actions */
.ali-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}

.ali-action-card {
    background: var(--ali-white);
    border-radius: 12px;
    padding: 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    text-decoration: none;
    color: var(--ali-dark);
    transition: all 0.2s;
    box-shadow: var(--ali-shadow);
    border: 1px solid rgba(0,0,0,0.03);
}

.ali-action-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--ali-shadow-lg);
}

.ali-action-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.ali-action-text { font-weight: 700; font-size: 14px; }

/* Empty State */
.ali-empty { text-align: center; padding: 50px 20px; color: var(--ali-gray-light); }
.ali-empty-icon { font-size: 48px; margin-bottom: 12px; opacity: 0.5; }
.ali-empty p { margin: 0; font-size: 14px; }

/* ========== Responsive ========== */
@media (max-width: 768px) {
    .ali-header { flex-direction: column; gap: 15px; text-align: center; }
    .ali-nav { padding: 0 10px; }
    .ali-nav-item { padding: 12px 14px; font-size: 12px; }
    .ali-main { padding: 15px; }
    .ali-card-header { flex-direction: column; align-items: flex-start; }
    .ali-search input { width: 160px; }
}
</style>