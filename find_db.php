<?php
echo "<h2>Finding Database Config</h2>";

$possiblePaths = array(
    'database.php',
    'config/database.php',
    '../database.php',
    '../config/database.php',
    '../../database.php',
    '../../config/database.php',
    '../../../config/database.php',
    '../../includes/database.php',
    '../../inc/database.php',
    '../../db.php',
    '../../connection.php',
    '../../config.php'
);

echo "<p>Current directory: " . getcwd() . "</p>";
echo "<p>Script location: " . __DIR__ . "</p>";

foreach ($possiblePaths as $path) {
    $fullPath = __DIR__ . '/' . $path;
    if (file_exists($path)) {
        echo "✅ FOUND: <strong>$path</strong><br>";
    } else {
        echo "❌ Not found: $path<br>";
    }
}

echo "<h3>Files in current directory:</h3>";
$files = scandir(__DIR__);
foreach ($files as $file) {
    echo $file . "<br>";
}

echo "<h3>Files in parent directory:</h3>";
$files = scandir(__DIR__ . '/..');
foreach ($files as $file) {
    echo $file . "<br>";
}