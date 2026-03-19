<?php
echo "<h2>Deep Search for Database Connection</h2>";

// Check index.php for database connection
echo "<h3>1. Checking index.php content:</h3>";
$indexContent = file_get_contents('index.php');

// Look for common database patterns
if (preg_match('/\$conn\s*=/', $indexContent)) {
    echo "✅ Found \$conn in index.php<br>";
}
if (preg_match('/mysqli_connect|new mysqli/i', $indexContent)) {
    echo "✅ Found mysqli connection in index.php<br>";
}
if (preg_match('/require|include/i', $indexContent, $matches)) {
    echo "✅ Found require/include statements<br>";
}

// Show first 200 lines of index.php to find includes
echo "<h3>2. First part of index.php:</h3>";
echo "<pre style='background:#f5f5f5;padding:10px;overflow:auto;max-height:400px;'>";
$lines = explode("\n", $indexContent);
$count = 0;
foreach ($lines as $line) {
    if ($count < 100) {
        echo htmlspecialchars($line) . "\n";
    }
    $count++;
}
echo "</pre>";

// Check parent directories more thoroughly
echo "<h3>3. All PHP files in parent directory (public_html/user):</h3>";
$parentFiles = glob('../*.php');
foreach ($parentFiles as $file) {
    echo $file . "<br>";
}

echo "<h3>4. All PHP files in public_html:</h3>";
$rootFiles = glob('../../*.php');
foreach ($rootFiles as $file) {
    echo $file . "<br>";
}

echo "<h3>5. Looking for config/includes folders:</h3>";
$searchDirs = array(
    '../../config',
    '../../includes', 
    '../../inc',
    '../../lib',
    '../../core',
    '../config',
    '../includes'
);

foreach ($searchDirs as $dir) {
    if (is_dir($dir)) {
        echo "✅ Found directory: $dir<br>";
        $files = glob($dir . '/*.php');
        foreach ($files as $file) {
            echo " - $file<br>";
        }
    }
}

echo "<h3>6. Search for 'mysqli' in all PHP files in current dir:</h3>";
$phpFiles = glob('*.php');
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    if (stripos($content, 'mysqli') !== false || stripos($content, '$conn') !== false) {
        echo "✅ $file contains database code<br>";
    }
}
?>