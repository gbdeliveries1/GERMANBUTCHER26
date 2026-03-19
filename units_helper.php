<?php
/**
 * Product Units Helper Functions
 * Include this file where unit functions are needed
 * 
 * @version 1.0.0
 * @author Senior E-commerce Engineer
 */

/**
 * Get all active product units
 * 
 * @param mysqli $conn Database connection
 * @param string|null $type Filter by unit type (quantity, weight, volume, packaging)
 * @return array Array of unit records
 */
function getProductUnits($conn, $type = null) {
    $sql = "SELECT unit_id, unit_code, unit_name, unit_name_plural, unit_symbol, unit_type 
            FROM product_units 
            WHERE is_active = 1";
    
    if ($type !== null) {
        $sql .= " AND unit_type = '" . $conn->real_escape_string($type) . "'";
    }
    
    $sql .= " ORDER BY sort_order ASC, unit_name ASC";
    
    $result = $conn->query($sql);
    $units = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $units[] = $row;
        }
    }
    
    return $units;
}

/**
 * Get all units grouped by type
 * 
 * @param mysqli $conn Database connection
 * @return array Units grouped by type
 */
function getProductUnitsGrouped($conn) {
    $units = getProductUnits($conn);
    $grouped = [
        'quantity' => [],
        'weight' => [],
        'volume' => [],
        'packaging' => []
    ];
    
    foreach ($units as $unit) {
        $grouped[$unit['unit_type']][] = $unit;
    }
    
    return $grouped;
}

/**
 * Get a single unit by ID
 * 
 * @param mysqli $conn Database connection
 * @param int $unitId Unit ID
 * @return array|null Unit record or null
 */
function getUnitById($conn, $unitId) {
    $stmt = $conn->prepare("SELECT * FROM product_units WHERE unit_id = ?");
    $stmt->bind_param("i", $unitId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Get unit by code
 * 
 * @param mysqli $conn Database connection
 * @param string $code Unit code
 * @return array|null Unit record or null
 */
function getUnitByCode($conn, $code) {
    $stmt = $conn->prepare("SELECT * FROM product_units WHERE unit_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Get default unit ID (piece)
 * 
 * @param mysqli $conn Database connection
 * @return int Default unit ID
 */
function getDefaultUnitId($conn) {
    $unit = getUnitByCode($conn, 'piece');
    return $unit ? $unit['unit_id'] : 1;
}

/**
 * Format unit name based on quantity
 * 
 * @param array $unit Unit record
 * @param int|float $quantity Quantity
 * @return string Formatted unit name (singular or plural)
 */
function formatUnitName($unit, $quantity = 1) {
    if (!$unit) {
        return 'Piece';
    }
    
    return ($quantity == 1) ? $unit['unit_name'] : $unit['unit_name_plural'];
}

/**
 * Format price with unit
 * Example: "8,500 RWF / Plate"
 * 
 * @param float $price Price value
 * @param array $unit Unit record
 * @param string $currency Currency code (default: RWF)
 * @return string Formatted price with unit
 */
function formatPriceWithUnit($price, $unit, $currency = 'RWF') {
    $formattedPrice = number_format($price, 0, '.', ',');
    $unitName = $unit ? $unit['unit_name'] : 'Piece';
    
    return "{$formattedPrice} {$currency} / {$unitName}";
}

/**
 * Format quantity with unit
 * Example: "3 Bottles" or "1 Kg"
 * 
 * @param int|float $quantity Quantity
 * @param array $unit Unit record
 * @return string Formatted quantity with unit
 */
function formatQuantityWithUnit($quantity, $unit) {
    $unitName = formatUnitName($unit, $quantity);
    return "{$quantity} {$unitName}";
}

/**
 * Generate HTML select options for units
 * 
 * @param mysqli $conn Database connection
 * @param int|null $selectedId Currently selected unit ID
 * @param bool $grouped Group by unit type
 * @return string HTML options string
 */
function generateUnitOptions($conn, $selectedId = null, $grouped = true) {
    $html = '<option value="">-- Select Unit --</option>';
    
    if ($grouped) {
        $grouped = getProductUnitsGrouped($conn);
        $typeLabels = [
            'quantity' => 'Quantity Units',
            'weight' => 'Weight Units',
            'volume' => 'Volume Units',
            'packaging' => 'Packaging Units'
        ];
        
        foreach ($grouped as $type => $units) {
            if (!empty($units)) {
                $html .= '<optgroup label="' . htmlspecialchars($typeLabels[$type]) . '">';
                foreach ($units as $unit) {
                    $selected = ($selectedId == $unit['unit_id']) ? 'selected' : '';
                    $html .= sprintf(
                        '<option value="%d" %s>%s (%s)</option>',
                        $unit['unit_id'],
                        $selected,
                        htmlspecialchars($unit['unit_name']),
                        htmlspecialchars($unit['unit_symbol'])
                    );
                }
                $html .= '</optgroup>';
            }
        }
    } else {
        $units = getProductUnits($conn);
        foreach ($units as $unit) {
            $selected = ($selectedId == $unit['unit_id']) ? 'selected' : '';
            $html .= sprintf(
                '<option value="%d" %s>%s</option>',
                $unit['unit_id'],
                $selected,
                htmlspecialchars($unit['unit_name'])
            );
        }
    }
    
    return $html;
}

/**
 * Get unit data as JSON for JavaScript
 * 
 * @param mysqli $conn Database connection
 * @return string JSON encoded units array
 */
function getUnitsAsJson($conn) {
    $units = getProductUnits($conn);
    $unitsMap = [];
    
    foreach ($units as $unit) {
        $unitsMap[$unit['unit_id']] = $unit;
    }
    
    return json_encode($unitsMap);
}

/**
 * Validate unit ID
 * 
 * @param mysqli $conn Database connection
 * @param int $unitId Unit ID to validate
 * @return bool True if valid, false otherwise
 */
function isValidUnitId($conn, $unitId) {
    $stmt = $conn->prepare("SELECT unit_id FROM product_units WHERE unit_id = ? AND is_active = 1");
    $stmt->bind_param("i", $unitId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

/**
 * Save unit to order item (preserves unit info in order history)
 * 
 * @param mysqli $conn Database connection
 * @param int $orderItemId Order item ID
 * @param int $unitId Unit ID
 * @return bool Success status
 */
function saveOrderItemUnit($conn, $orderItemId, $unitId) {
    $unit = getUnitById($conn, $unitId);
    
    if (!$unit) {
        return false;
    }
    
    $stmt = $conn->prepare("UPDATE order_items SET unit_id = ?, unit_name = ? WHERE order_item_id = ?");
    $stmt->bind_param("isi", $unitId, $unit['unit_name'], $orderItemId);
    
    return $stmt->execute();
}