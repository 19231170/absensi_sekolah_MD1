# ZIP Extension Fix for Data Import

## Problem
The data import feature was encountering the error "Class 'ZipArchive' not found" when trying to import Excel files. This happens because the PHP zip extension is not installed or enabled on the server.

## Solutions

### Option 1: Install the PHP ZIP Extension (Recommended)
The best solution is to enable the PHP ZIP extension on your server:

1. Locate your `php.ini` file (run `php -i | findstr "php.ini"` to find it)
2. Open the file and find the line `;extension=zip` or `;extension=php_zip.dll`
3. Remove the semicolon to uncomment the line
4. Save the file and restart your web server

### Option 2: Use CSV Files Only (Fallback Solution)
If you cannot install the ZIP extension, the system now includes a fallback that supports CSV files:

1. Export your data as CSV instead of Excel
2. Make sure the CSV has the correct column headers:
   - `nis`
   - `nama_siswa`
   - `jenis_kelamin`
   - `jurusan/kelas` (format: "JURUSAN/TINGKAT KELAS", e.g. "TKJ/10 A")
3. Upload the CSV file through the import form

## How the Fallback Works
The system now detects if the ZipArchive class is available:
- If available: Uses the normal import process (supports Excel and CSV)
- If not available: Falls back to a native PHP CSV parser (CSV only)

## Troubleshooting
If you still encounter issues:
1. Make sure your CSV is properly formatted with the correct headers
2. Check for any BOM (Byte Order Mark) issues in your CSV
3. Try saving your CSV with UTF-8 encoding without BOM
4. Check the server logs for specific error messages

## Technical Details
The fallback implementation uses PHP's native `str_getcsv()` function to parse CSV files without requiring the ZIP extension. It handles basic CSV parsing needs but doesn't support Excel files as those require ZipArchive to read the XLSX format.
