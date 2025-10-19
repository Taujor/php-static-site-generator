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

    public static function set($path, $content): void {
        $hash = hash("xxh3", $content);

        if (!is_dir(Locate::hashes()) && !mkdir(Locate::hashes(), 0755, true) && !is_dir(Locate::hashes())) {
            throw new \RuntimeException("Failed to create directory: " . Locate::hashes());
        }

        $filename = hash("xxh3", $path);
        if (file_put_contents(Locate::hashes() . "/$filename.hash", $hash) === false) {
            throw new \RuntimeException("Failed to write file: " . Locate::hashes() . "/$filename.hash");
        }
    }


}