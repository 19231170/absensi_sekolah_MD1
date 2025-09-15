<?php
if (class_exists('ZipArchive')) {
    echo "ZipArchive is available\n";
} else {
    echo "ZipArchive is NOT available\n";
}

echo "PHP Version: " . phpversion() . "\n";
echo "Loaded extensions:\n";
$extensions = get_loaded_extensions();
foreach($extensions as $ext) {
    if (stripos($ext, 'zip') !== false) {
        echo "- $ext\n";
    }
}
