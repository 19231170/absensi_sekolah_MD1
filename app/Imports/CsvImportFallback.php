<?php

namespace App\Imports;

/**
 * This class provides a fallback implementation for when the ZipArchive extension is not available.
 * It only supports basic CSV files, not Excel files.
 */
class CsvImportFallback
{
    /**
     * Import CSV data from a file
     * 
     * @param string $filePath Path to the CSV file
     * @return \Illuminate\Support\Collection
     */
    public static function import($filePath)
    {
        // Make sure file exists
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }
        
        // Try to read the file
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \Exception("Failed to read file: {$filePath}");
        }
        
        // Detect and remove BOM if present
        $bom = pack('H*', 'EFBBBF');
        $content = preg_replace("/^{$bom}/", '', $content);
        
        // Split by lines
        $lines = explode("\n", $content);
        if (empty($lines)) {
            throw new \Exception("No data found in file");
        }
        
        // Get headers from first line
        $headers = str_getcsv(array_shift($lines));
        $headers = array_map('trim', $headers);
        
        // Clean up headers
        $cleanHeaders = [];
        foreach ($headers as $header) {
            // Remove BOM if present
            $header = str_replace("\xEF\xBB\xBF", '', $header);
            $header = trim($header);
            $cleanHeaders[] = $header;
        }
        $headers = $cleanHeaders;
        
        // Parse data
        $data = [];
        foreach ($lines as $i => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $row = str_getcsv($line);
            if (count($row) !== count($headers)) {
                // Skip mismatched rows
                \Log::warning("Skipping mismatched row in CSV import", [
                    'row_number' => $i + 2, // +2 because 0-indexed and we already removed the header
                    'headers_count' => count($headers),
                    'values_count' => count($row),
                    'row' => $row
                ]);
                continue;
            }
            
            $data[] = array_combine($headers, $row);
        }
        
        return collect($data);
    }
}
