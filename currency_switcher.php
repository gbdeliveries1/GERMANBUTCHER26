<?php
/**
 * Currency Switcher Dropdown Component
 * Include this where you want the currency selector to appear
 */

if (!function_exists('getCurrentCurrency')) {
    include_once __DIR__ . '/currency.php';
}

$currencies = getCurrencies($conn);
$currentCurrency = getCurrentCurrency($conn);
?>

<style>
.currency-switcher {
    position: relative;
    display: inline-block;
}

.currency-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #333;
    transition: all 0.2s;
}

.currency-btn:hover {
    border-color: #ff6000;
    background: #fff8f5;
}

.currency-btn .flag {
    font-size: 18px;
}

.currency-btn .code {
    font-weight: 600;
}

.currency-btn .arrow {
    font-size: 10px;
    color: #999;
    transition: transform 0.2s;
}

.currency-switcher.open .currency-btn .arrow {
    transform: rotate(180deg);
}

.currency-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 5px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    min-width: 220px;
    z-index: 1000;
    display: none;
    overflow: hidden;
}

.currency-switcher.open .currency-dropdown {
    display: block;
    animation: dropdownSlide 0.2s ease;
}

@keyframes dropdownSlide {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.currency-dropdown-header {
    padding: 12px 15px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
    font-size: 12px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
}

.currency-option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    cursor: pointer;
    transition: background 0.2s;
    border-bottom: 1px solid #f5f5f5;
}

.currency-option:last-child {
    border-bottom: none;
}

.currency-option:hover {
    background: #fff8f5;
}

.currency-option.active {
    background: #fff0e6;
}

.currency-option .flag {
    font-size: 24px;
}

.currency-option .info {
    flex: 1;
}

.currency-option .name {
    font-size: 14px;
    font-weight: 500;
    color: #333;
}

.currency-option .rate {
    font-size: 11px;
    color: #888;
    margin-top: 2px;
}

.currency-option .symbol {
    font-size: 14px;
    font-weight: 600;
    color: #ff6000;
}

.currency-option.active .symbol::after {
    content: ' ✓';
    color: #27ae60;
}

/* Mobile */
@media (max-width: 768px) {
    .currency-dropdown {
        position: fixed;
        top: auto;
        bottom: 0;
        left: 0;
        right: 0;
        margin: 0;
        border-radius: 20px 20px 0 0;
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .currency-dropdown-header {
        padding: 20px 15px;
        text-align: center;
    }
}
</style>

<div class="currency-switcher" id="currencySwitcher">
    <button type="button" class="currency-btn" onclick="toggleCurrencyDropdown()">
        <span class="flag"><?php echo getCurrencyFlag($currentCurrency['currency_code']); ?></span>
        <span class="code"><?php echo $currentCurrency['currency_code']; ?></span>
        <span class="arrow">▼</span>
    </button>
    
    <div class="currency-dropdown">
        <div class="currency-dropdown-header">Select Currency</div>
        <?php foreach ($currencies as $cur): ?>
        <div class="currency-option <?php echo $cur['currency_code'] === $currentCurrency['currency_code'] ? 'active' : ''; ?>" 
             onclick="changeCurrency('<?php echo $cur['currency_code']; ?>')">
            <span class="flag"><?php echo getCurrencyFlag($cur['currency_code']); ?></span>
            <div class="info">
                <div class="name"><?php echo htmlspecialchars($cur['currency_name']); ?></div>
                <div class="rate">
                    <?php if ($cur['is_default']): ?>
                        Base currency
                    <?php else: ?>
                        1 <?php echo $cur['currency_code']; ?> = <?php echo number_format(1 / $cur['exchange_rate'], 0); ?> RWF
                    <?php endif; ?>
                </div>
            </div>
            <span class="symbol"><?php echo $cur['currency_symbol']; ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function toggleCurrencyDropdown() {
    document.getElementById('currencySwitcher').classList.toggle('open');
}

function changeCurrency(code) {
    // Show loading
    document.getElementById('currencySwitcher').classList.remove('open');
    
    // AJAX request
    fetch('includes/set_currency.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'currency_code=' + encodeURIComponent(code)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to update prices
            location.reload();
        } else {
            alert(data.message || 'Failed to change currency');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        location.reload();
    });
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    var switcher = document.getElementById('currencySwitcher');
    if (switcher && !switcher.contains(e.target)) {
        switcher.classList.remove('open');
    }
});
</script>

<?php
// Helper function to get flag emoji
function getCurrencyFlag($code) {
    $flags = [
        'RWF' => '🇷🇼',
        'USD' => '🇺🇸',
        'EUR' => '🇪🇺',
        'GBP' => '🇬🇧',
        'KES' => '🇰🇪',
        'UGX' => '🇺🇬',
        'TZS' => '🇹🇿',
        'ZAR' => '🇿🇦',
        'NGN' => '🇳🇬',
        'GHS' => '🇬🇭',
        'XAF' => '🇨🇲',
        'XOF' => '🇸🇳',
        'INR' => '🇮🇳',
        'CNY' => '🇨🇳',
        'JPY' => '🇯🇵',
        'AED' => '🇦🇪',
        'CAD' => '🇨🇦',
        'AUD' => '🇦🇺',
    ];
    return $flags[$code] ?? '💱';
}
?>