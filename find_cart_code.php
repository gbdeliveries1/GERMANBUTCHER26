<?php
// Find cart JavaScript code - DELETE AFTER USE
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Finding Cart Code</h1>";
echo "<style>body{font-family:Arial;padding:20px;} pre{background:#f5f5f5;padding:10px;overflow:auto;max-height:300px;} .found{background:#d4edda;padding:10px;margin:10px 0;border-left:4px solid #28a745;}</style>";

// Search terms
$search_terms = ['add_to_cart', 'ADD_TO_CART', 'addToCart', 'addtocart', 'insert.php', 'cart'];

// Directories to search
$dirs = ['.', 'js', 'assets/js', 'includes', 'user/js', 'user'];

// Files to check directly
$direct_files = [
    'index.php',
    'js/main.js',
    'js/cart.js',
    'js/custom.js',
    'js/script.js',
    'assets/js/main.js',
    'assets/js/cart.js',
    'includes/footer.php',
    'includes/scripts.php',
    'includes/user-script.php',
    'user/js/main.js',
];

echo "<h2>Checking specific files:</h2>";

foreach ($direct_files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        foreach ($search_terms as $term) {
            if (stripos($content, $term) !== false) {
                echo "<div class='found'>";
                echo "<strong>✅ Found '$term' in: $file</strong>";
                
                // Show the relevant code
                $lines = explode("\n", $content);
                foreach ($lines as $num => $line) {
                    if (stripos($line, $term) !== false) {
                        $start = max(0, $num - 3);
                        $end = min(count($lines), $num + 10);
                        echo "<pre>Lines $start - $end:\n";
                        for ($i = $start; $i < $end; $i++) {
                            $lineNum = $i + 1;
                            $highlight = ($i == $num) ? '>>> ' : '    ';
                            echo htmlspecialchars("$highlight$lineNum: " . $lines[$i]) . "\n";
                        }
                        echo "</pre>";
                        break;
                    }
                }
                echo "</div>";
                break;
            }
        }
    }
}

// Search in all JS files
echo "<h2>All JS files found:</h2>";
function findJsFiles($dir) {
    $files = [];
    if (is_dir($dir)) {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path) && $item !== 'node_modules' && $item !== 'vendor') {
                $files = array_merge($files, findJsFiles($path));
            } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'js') {
                $files[] = $path;
            }
        }
    }
    return $files;
}

$jsFiles = findJsFiles('.');
foreach ($jsFiles as $file) {
    $size = filesize($file);
    echo "<p>$file ($size bytes)</p>";
}

// Check index.php for inline cart code
echo "<h2>Checking index.php for cart code:</h2>";
if (file_exists('index.php')) {
    $content = file_get_contents('index.php');
    
    // Find cart-related JavaScript
    preg_match_all('/function\s+\w*cart\w*\s*\([^)]*\)\s*\{[^}]+\}/i', $content, $matches);
    if (!empty($matches[0])) {
        echo "<div class='found'><strong>Found cart functions:</strong><pre>";
        foreach ($matches[0] as $match) {
            echo htmlspecialchars($match) . "\n\n";
        }
        echo "</pre></div>";
    }
    
    // Find AJAX calls to insert.php
    preg_match_all('/["\']action\/insert\.php["\'][^;]+;/i', $content, $matches);
    if (!empty($matches[0])) {
        echo "<div class='found'><strong>Found insert.php calls:</strong><pre>";
        foreach ($matches[0] as $match) {
            echo htmlspecialchars($match) . "\n\n";
        }
        echo "</pre></div>";
    }
}

echo "<h2>Check includes/user-script.php:</h2>";
if (file_exists('includes/user-script.php')) {
    echo "<pre>" . htmlspecialchars(file_get_contents('includes/user-script.php')) . "</pre>";
}
?>