<?php namespace Taujor\PHPSSG\Contracts;

// ADD HOOKS NEXT
// THEN DOCS
// THEN MD5 HASH CACHE

use Taujor\PHPSSG\Utilities\Container;
use Taujor\PHPSSG\Utilities\Locate;

abstract class Buildable {
    /**
     * @method int|false compile(string $pattern, array|object|null $data)
     * Compile a single page into static HTML.
     *
     * @param array $data Data for the page
     * @param string $pattern filename or filename pattern, e.g. "post-{{id}}.html" relative to "./public"
     */
    public static function compile(string $pattern, array|object $data = []): int|false {
        $container = Container::instance();
        
        $buildable = $container->get(static::class);

        if (!($buildable instanceof Buildable) || !is_callable($buildable)) {
            throw new \InvalidArgumentException(sprintf(
                "Class '%s' must implement the 'Buildable' interface and be callable.", $buildable)
            );
        }

        $html = $buildable($data);

        $file = self::resolve(Locate::root(). "/public" . $pattern, $data);

        $isUnchanged = is_file($file) && md5_file($file) === md5($html);
        if($isUnchanged) return 0;

        $dir = dirname($file);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        return file_put_contents($file, $html);
    }

    /**
     * * @method void build(string $pattern, array|object|null $data)
     * Compile multiple pages from a dataset.
     *
     * @param array $dataset Array of associative arrays (each a post for example)
     * @param string|null $pattern Optional file name pattern, e.g. "post-{{id}}.html"
     */
    public static function build(string $pattern, iterable $dataset): int|false {
        $bytesWritten = 0;

        foreach ($dataset as $data) {
            $result = self::compile($pattern, $data);

            if ($result === false) {
                return false;
            }

            $bytesWritten += $result;
        }

        return $bytesWritten;
    }

    // add support for custom delimiters default to '{{ }}'
    protected static function resolve(string $pattern, array|object|null $data): string {
        return preg_replace_callback('/{{\s*(.*?)\s*}}/', function($matches) use ($data) {
            $key = $matches[1];
        
            if (is_array($data)) {
                return !array_key_exists($key, $data) ? "" : $data[$key];
            }

            if (is_object($data)) {
                return !property_exists($data, $key) ? "" : $data->$key;
            }

            return "";
        
        }, $pattern);
    }
}