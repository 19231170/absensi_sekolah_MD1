<?php

namespace App\Patches;

/**
 * Patches for the PHPExcel classes to fix PHP 7.4+ compatibility issues
 * related to deprecated curly brace string access in PHP 7.4+ and removed in PHP 8.0+
 */
class PHPExcel_Shared_String_Patch
{
    /**
     * Apply all necessary patches for PHP 7.4+ compatibility
     * 
     * @return bool True if all patches were applied successfully
     */
    public static function apply()
    {
        $success = true;
        
        // Find all PHP files in the PHPExcel library and patch them
        $phpexcelPath = __DIR__ . '/../../vendor/phpoffice/phpexcel/Classes/PHPExcel';
        $success = self::patchAllPHPExcelFiles($phpexcelPath);
        
        return $success;
    }
    
    /**
     * Find and patch all PHP files in a directory and its subdirectories
     *
     * @param string $dir Directory to scan
     * @return bool True if all files were patched successfully
     */
    protected static function patchAllPHPExcelFiles($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $success = true;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
                $success = $success && self::patchFile($fileInfo->getPathname());
            }
        }
        
        return $success;
    }
    
    /**
     * Patch a single PHP file to replace curly brace string access with square brackets
     *
     * @param string $filePath Path to the file to patch
     * @return bool True if the file was patched successfully
     */
    protected static function patchFile($filePath)
    {
        // Read the file content
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }
        
        // Check if we need to patch this file
        if (strpos($content, '{$') === false && 
            strpos($content, '->$') === false && 
            !preg_match('/\$[a-zA-Z0-9_]+{/', $content)) {
            return true; // Skip files with no curly brace string access
        }
        
        // Apply string replacements for common patterns
        $replacements = [
            // Special replacements for known patterns
            'if( $bom_be ) { $val = ord($str{$i})   << 4; $val += ord($str{$i+1}); }' => 
                'if( $bom_be ) { $val = ord($str[$i])   << 4; $val += ord($str[$i+1]); }',
                
            'else {        $val = ord($str{$i+1}) << 4; $val += ord($str{$i}); }' =>
                'else {        $val = ord($str[$i+1]) << 4; $val += ord($str[$i]); }',
                
            'if ((isset($value{0})) && ($value{0} == \'"\')'  => 
                'if ((isset($value[0])) && ($value[0] == \'"\')',
                
            'if ((isset($value{0})) && ($value{0} == \'"\') && (substr($value,-1) == \'"\'))' =>
                'if ((isset($value[0])) && ($value[0] == \'"\') && (substr($value,-1) == \'"\'))',
                
            'if ((!isset($formula{0})) || ($formula{0} != \'=\'))' =>
                'if ((!isset($formula[0])) || ($formula[0] != \'=\'))',
                
            'if (!isset($formula{0}))' =>
                'if (!isset($formula[0]))',
                
            '$opCharacter = $formula{$index};' =>
                '$opCharacter = $formula[$index];',
                
            'isset(self::$_comparisonOperators[$formula{$index+1}])' =>
                'isset(self::$_comparisonOperators[$formula[$index+1]])',
                
            '$opCharacter .= $formula{++$index};' =>
                '$opCharacter .= $formula[++$index];',
                
            'if ($dynamicRuleType{0} == \'M\' || $dynamicRuleType{0} == \'Q\')' =>
                'if ($dynamicRuleType[0] == \'M\' || $dynamicRuleType[0] == \'Q\')',
        ];
        
        // Apply the specific replacements
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        
        // Use regex to catch other curly brace string access patterns
        $patterns = [
            // Match variable string access with curly braces: $var{0}, $var{$index}
            '/\$([a-zA-Z0-9_]+)\{(\$?[a-zA-Z0-9_\+\-]+)\}/' => '$$$1[$2]',
            
            // Match property string access with curly braces: $obj->prop{0}
            '/->([a-zA-Z0-9_]+)\{(\$?[a-zA-Z0-9_\+\-]+)\}/' => '->$1[$2]',
            
            // Match static property string access with curly braces: self::$prop{0}
            '/::(\$[a-zA-Z0-9_]+)\{(\$?[a-zA-Z0-9_\+\-]+)\}/' => '::$1[$2]',
            
            // Catch isset with curly braces: isset($var{0})
            '/isset\(\$([a-zA-Z0-9_]+)\{(\$?[a-zA-Z0-9_\+\-]+)\}\)/' => 'isset($$$1[$2])',
            
            // Catch array access with curly braces: $array[$key]{0}
            '/\$([a-zA-Z0-9_]+)\[([^\]]+)\]\{(\$?[a-zA-Z0-9_\+\-]+)\}/' => '$$$1[$2][$3]',
        ];
        
        // Apply regex replacements
        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        // Write the file back
        return file_put_contents($filePath, $content) !== false;
    }
}
