<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateStorageLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:create-link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a symbolic link from public/storage to storage/app/public';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Create public/storage directory if it doesn't exist
        $publicPath = public_path('storage');
        if (!File::exists($publicPath)) {
            // Create the directory
            if (File::makeDirectory($publicPath, 0755, true)) {
                $this->info('Directory created: ' . $publicPath);
            } else {
                $this->error('Failed to create directory: ' . $publicPath);
                return 1;
            }
        }
        
        // Create symbolic link
        $target = storage_path('app/public');
        $link = public_path('storage');
        
        // On Windows, we need to use a different approach since symlinks might require admin privileges
        if (PHP_OS_FAMILY === 'Windows') {
            $this->info('Windows detected, creating a junction instead of symlink');
            
            // Remove existing directory if it's not a junction
            if (File::exists($link) && !$this->isJunction($link)) {
                File::deleteDirectory($link);
            }
            
            // Create the junction
            if (!File::exists($link)) {
                $command = 'mklink /J "' . str_replace('/', '\\', $link) . '" "' . str_replace('/', '\\', $target) . '"';
                $this->info('Running: ' . $command);
                exec($command, $output, $resultCode);
                
                if ($resultCode === 0) {
                    $this->info('Junction created successfully.');
                } else {
                    $this->error('Failed to create junction. Error code: ' . $resultCode);
                    $this->error('Output: ' . implode("\n", $output));
                    return 1;
                }
            } else {
                $this->info('Junction already exists.');
            }
        } else {
            // On Unix-like systems, use symlink directly
            if (file_exists($link)) {
                if (is_link($link)) {
                    $this->info('Removing existing symlink...');
                    unlink($link);
                } else {
                    $this->error('A non-symlink file or directory exists at '.$link);
                    return 1;
                }
            }

            if (symlink($target, $link)) {
                $this->info('Symbolic link created: '.$link.' -> '.$target);
            } else {
                $this->error('Failed to create symbolic link: '.$link.' -> '.$target);
                return 1;
            }
        }

        return 0;
    }
    
    /**
     * Check if the given path is a Windows junction.
     *
     * @param string $path
     * @return bool
     */
    private function isJunction($path)
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return false;
        }
        
        if (!file_exists($path)) {
            return false;
        }
        
        // Use fsutil to check if it's a junction
        exec('fsutil reparsepoint query "' . $path . '" 2>nul', $output, $resultCode);
        return $resultCode === 0;
    }
}
