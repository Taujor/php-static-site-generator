<?php namespace Taujor\PHPSSG\Utilities;

use Taujor\PHPSSG\Utilities\Locate;

class Cache {

    public static function get(string $path): string|false {
        $filename = hash("xxh3", $path);
        $hashfile = Locate::hashes() . "/$filename.hash";

        if (!file_exists($hashfile) || !is_file($hashfile)) return false;
        
        $hash = file_get_contents($hashfile);

        if($hash === false) throw new \RuntimeException("Failed to read file: '$hashfile'");

        return $hash; 
    }

    public static function set(string $path, string $content): void {
        $dir = Locate::hashes();
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException("Failed to create directory: $dir");
        }

        $filename = hash('xxh3', $path);
        $target = "$dir/$filename.hash";
        $hash = hash('xxh3', $content);


        if (file_put_contents($target, $hash, LOCK_EX) === false) {
            throw new \RuntimeException("Failed to write hash to file: $target");
        }
    }
}