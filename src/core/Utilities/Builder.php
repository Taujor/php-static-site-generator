<?php namespace Taujor\PHPSSG\Traits;

use Taujor\PHPSSG\Utilities\Container;
use Taujor\PHPSSG\Utilities\Locate;
use Taujor\PHPSSG\Contracts\Buildable;

trait Builder
{
    /**
     * Compile a single page into static HTML.
     *
     * @param array $data Data for the page
     * @param string|null $pattern Optional file name pattern, e.g. "post-{{id}}.html"
     */
    public static function compile(?string $pattern = null, array|object $data): int|false {
        $container = Container::instance();
        
        $buildable = $container->get(static::class);

        if (!($buildable instanceof Buildable)) {
            throw new \InvalidArgumentException(sprintf(
                "Class '%s' must implement the 'Buildable' interface.", $buildable)
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
     * Compile multiple pages from a dataset.
     *
     * @param array $dataset Array of associative arrays (each a post for example)
     * @param string|null $pattern Optional file name pattern, e.g. "post-{{id}}.html"
     */
    public static function build(?string $pattern = null, array $dataset): void
    {
        foreach ($dataset as $data) {
            self::compile($pattern, $data);
        }
    }

    // add support for custom delimiters default to '{{ }}'
    protected static function resolve(string $pattern, array|object $data): string {
        return preg_replace_callback('/{{\s*(.*?)\s*}}/', function($matches) use ($data) {
            $key = $matches[1];
        
            if (is_array($data)) {
                return !array_key_exists($key, $data) ? "" : $data[$key];
            }

             if (is_object($data)) {
                return property_exists($data, $key) ? $data->$key : "";
            }
        
        }, $pattern);
    }
}
