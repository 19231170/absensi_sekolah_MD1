<?php
// Test ZIP extension availability
echo "Testing ZIP extension availability...\n";

if (extension_loaded('zip')) {
    echo "✓ ZIP extension is loaded\n";
    
    if (class_exists('ZipArchive')) {
        echo "✓ ZipArchive class is available\n";
        
        $zip = new ZipArchive();
        echo "✓ ZipArchive can be instantiated\n";
    } else {
        echo "✗ ZipArchive class is not available\n";
    }
} else {
    echo "✗ ZIP extension is not loaded\n";
    echo "Available extensions: " . implode(', ', get_loaded_extensions()) . "\n";
}

// Test QR Code generation
echo "\nTesting QR Code generation...\n";
try {
    $qrCode = new \Endroid\QrCode\QrCode('test');
    echo "✓ QR Code can be created\n";
    
    $writer = new \Endroid\QrCode\Writer\PngWriter();
    $result = $writer->write($qrCode);
    echo "✓ QR Code can be written to PNG\n";
} catch (Exception $e) {
    echo "✗ QR Code error: " . $e->getMessage() . "\n";
}
