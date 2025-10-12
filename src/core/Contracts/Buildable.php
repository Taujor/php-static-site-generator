<?php namespace Taujor\PHPSSG\Contracts;

use Taujor\PHPSSG\Utilities\Container;
use Taujor\PHPSSG\Utilities\Locate;
use Taujor\PHPSSG\Utilities\Minify;

/**
 * Buildable
 *
 * Abstract base class for objects that can generate static HTML files
 * from templates and datasets. Provides methods for compiling a single
 * template and building multiple files from an iterable dataset.
 *
 * Child classes must implement the __invoke() method and be callable.
 * 
 * @package Taujor\PHPSSG\Contracts
 * 
 * @method string|false __invoke(array|object $data = []) Render the buildable component with provided data and return HTML.
 * @method static int|false compile(string $pattern, array|object $data = []) Compile a single template pattern with data and write to file.
 * @method static int|false build(string $pattern, iterable $dataset) Build multiple files from an iterable dataset.
 *
 */
abstract class Buildable
{
    /**
     * Compiles a template pattern with provided data and writes it to a file.
     *
     * The method resolves placeholders in the pattern using the data provided,
     * writes the generated HTML to a resolved file path under the public directory,
     * and avoids overwriting unchanged files using an MD5 hash comparison.
     *
     * @param string $pattern Template pattern with placeholders.
     * @param array|object $data Data to replace placeholders in the template.
     * @return int|false Number of bytes written, 0 if file unchanged, or false on failure.
     *
     * @throws \InvalidArgumentException If the class is not callable or does not extend Buildable.
     */
    public static function compile(string $pattern, array|object $data = []): int|false
    {
        $container = Container::instance();
        
        $buildable = $container->get(static::class);

        if (!($buildable instanceof Buildable) || !is_callable($buildable)) {
            throw new \InvalidArgumentException(sprintf(
                "Class '%s' must implement the 'Buildable' interface and be callable.", $buildable)
            );
        }

        $html = $buildable($data);

        $file = self::resolve(Locate::root() . "/public" . $pattern, $data);

        $isUnchanged = is_file($file) && md5_file($file) === md5($html);
        if ($isUnchanged) return 0;

        $dir = dirname($file);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        return file_put_contents($file, $html);
    }

    /**
     * Builds multiple files from an iterable dataset.
     *
     * Iterates over the dataset and calls compile() for each item.
     * Returns the total number of bytes written or false if any compile fails.
     *
     * @param string $pattern Template pattern with placeholders.
     * @param iterable $dataset Array or object iterable containing data items.
     * @return int|false Total bytes written, or false on failure.
     */
    public static function build(string $pattern, iterable $dataset): int|false
    {
        $bytes = 0;

        foreach ($dataset as $data) {
            $result = self::compile($pattern, $data);

            if ($result === false) {
                return false;
            }

            $bytes += $result;
        }

        return $bytes;
    }

    /**
     * Resolves placeholders in a template pattern using provided data.
     *
     * Placeholders in the template should use the default delimiters '{{ }}'.
     * Supports arrays and objects for replacement.
     *
     * @param string $pattern Template pattern containing placeholders.
     * @param array|object|null $data Data to replace placeholders.
     * @return string Pattern with placeholders replaced by actual data.
     */
    protected static function resolve(string $pattern, array|object|null $data): string
    {
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