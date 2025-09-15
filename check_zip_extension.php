<?php
echo "PHP Version: " . phpversion() . "\n";
echo "PHP Loaded Extensions:\n";

$loadedExtensions = get_loaded_extensions();
echo "ZIP Extension: " . (in_array('zip', $loadedExtensions) ? "INSTALLED" : "NOT INSTALLED") . "\n";

// Find php.ini file location
$phpIniPath = php_ini_loaded_file();
echo "PHP INI Path: " . $phpIniPath . "\n";

// Check if extension_dir is set
$extensionDir = ini_get('extension_dir');
echo "Extension Directory: " . $extensionDir . "\n";

if (!in_array('zip', $loadedExtensions)) {
    echo "\n";
    echo "=== How to Enable the ZIP Extension ===\n";
    echo "1. Open your php.ini file at: {$phpIniPath}\n";
    echo "2. Find the line with 'extension=zip' or ';extension=zip' (it might be commented out with a semicolon)\n";
    echo "3. Remove the semicolon if it exists (uncomment it)\n";
    echo "4. If the line doesn't exist, add 'extension=zip' or 'extension=php_zip.dll' (on Windows)\n";
    echo "5. Save the file and restart your web server\n";
    echo "6. To check if it worked, run this script again\n";
}
