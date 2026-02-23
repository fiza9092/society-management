<?php
echo "<h2>Files in document root:</h2>";
$dir = $_SERVER['DOCUMENT_ROOT'];
$files = scandir($dir);
echo "<ul>";
foreach($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "<li>$file</li>";
    }
}
echo "</ul>";
?>