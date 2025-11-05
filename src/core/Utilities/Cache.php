<?php namespace Taujor\PHPSSG\Utilities;
use Taujor\PHPSSG\Utilities\Locate;

/**
 * Cache
 *
 * A utility class for caching file hashes to avoid unnecessary file writes
 * during the build process. This class uses xxh3 hashing algorithm to
 * generate unique identifiers for files and their contents.
 *
 * The cache stores hash values of file contents in a separate directory,
 * allowing comparison between new and existing content to determine if
 * a file needs to be rewritten.
 *
 * @package Taujor\PHPSSG\Utilities
 * @see \Taujor\PHPSSG\Utilities\Locate For file and directory location utilities.
 */
class Cache
{
    /**
     * Retrieves the cached hash for a given file path.
     *
     * This method reads the hash from the cache directory and returns it if available.
     * If the hash file doesn't exist or cannot be read, it returns false.
     *
     * @param string $path The file path to retrieve the hash for.
     * @return string|false The cached hash value, or false if not found or an error occurs.
     * @throws \RuntimeException If the hash file exists but cannot be read.
     */
    public static function get(string $path): string|false
    {
        $filename = hash("xxh3", $path);
        $hashfile = Locate::hashes() . "/$filename.hash";
        if (!file_exists($hashfile) || !is_file($hashfile)) return false;

        $hash = file_get_contents($hashfile);
        if($hash === false) throw new \RuntimeException("Failed to read file: '$hashfile'");
        return $hash;
    }

    /**
     * Stores the hash of content for a given file path.
     *
     * This method calculates the hash of the content, ensures the cache directory exists,
     * and writes the hash to a file in the cache directory.
     *
     * @param string $path The file path to cache the hash for.
     * @param string $content The content to calculate the hash from.
     * @throws \RuntimeException If the cache directory cannot be created or the hash cannot be written.
     */
    public static function set(string $path, string $content): void
    {
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
