<?php namespace Taujor\PHPSSG\Contracts;

use Taujor\PHPSSG\Utilities\Cache;
use Taujor\PHPSSG\Utilities\Container;
use Taujor\PHPSSG\Utilities\Locate;

/**
 * Buildable
 *
 * A component that can **generate static HTML files** 
 * from templates and datasets. `Buildable` components combine invokable 
 * rendering with file compilation to the build directory, supporting 
 * **placeholder replacement**, incremental builds, and pre/post hooks.
 *
 * Child classes must implement the `__invoke()` method, allowing instances
 * to be called as functions and return HTML strings.
 *
 * `Buildable` is intended for generating pages from datasets and compiling components into html documents.
 *
 * Example usage:
 * ```php
 * $dataset = [
 *     (object) ['id' => 1, 'slug' => 'first-post', 'title' => 'Hello', 'content' => 'World'],
 *     (object) ['id' => 2, 'slug' => 'second-post', 'title' => 'Foo', 'content' => 'Bar'],
 * ];
 * // build a post per item in the dataset
 * Post::build('/posts/{{slug}}.html', $dataset);
 * // build only one post in the dataset
 * Post::compile('/posts/{{slug}}.html', $dataset[0]);
 * ```
 *
 * @package Taujor\PHPSSG\Contracts
 *
 * @method string|false __invoke(array|object $data = []) Render the buildable component with provided data and return HTML.
 * @method static int|false compile(string $pattern, array|object $data = []) Compile a single placeholder pattern with data and write to file.
 * @method static int|false build(string $pattern, iterable $dataset) Build multiple files from an iterable dataset.
 */
abstract class Buildable {
    function __construct(){
        static::_beforeInvoke();
    }

    /**
     * Compiles a template pattern with provided data and writes it to a file.
     *
     * Resolves placeholders in the pattern using the data, renders the HTML 
     * by invoking the component, and writes it to the resolved file path 
     * under the build directory. Uses SHA256 hash comparison to avoid 
     * overwriting unchanged files.
     *
     * Hooks are available for customization:
     * @method void _beforeInvoke()
     * @method void _beforeRender(array|object &$data)
     * @method void _afterRender(string &$html, array|object &$data)
     * @method void _beforeWrite(string &$file)
     * @method void _afterWrite(int|false &$bytes, string &$file)
     *
     * @param string $pattern Placeholder pattern containing placeholders like `{{slug}}` or just plain text either resolves to a path relative to the build directory.
     * @param array|object $data Data to inject into the template.
     * @param string $delimiters Placeholder delimiters separated by whitespace (default: '{{ }}').
     * 
     * @return int|false Number of bytes written, 0 if unchanged, or false on failure.
     *
     * @throws \InvalidArgumentException If the class is not callable or does not extend Buildable.
     * @throws \RuntimeException If the output directory cannot be created.
     */
    public static function compile(string $pattern, array|object $data = [], string $delimiters = "{{ }}"): int|false {
        $container = Container::instance();
        $buildable = $container->get(static::class);

        if (!($buildable instanceof Buildable) || !is_callable($buildable)) {
            throw new \InvalidArgumentException(sprintf(
                "Class '%s' must extend the 'Buildable' abstract class and be callable.", static::class)
            );
        }

        $buildable->_beforeRender($data);
        $html = $buildable($data);
        $buildable->_afterRender($html, $data);

        $file = self::resolve(Locate::build() . $pattern, $data, $delimiters);
        $buildable->_beforeWrite($file);

        $dir = dirname($file);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException("Failed to create directory: $dir");
        }

        $storedHash = Cache::get($file);
        $newHash = hash('xxh3', $html);

        if ($storedHash === $newHash) {
            return 0;
        }

        $bytes = file_put_contents($file, $html);
        if ($bytes === false) {
            throw new \RuntimeException("Failed to write file: $file");
        }

        Cache::set($file, $html);

        $buildable->_afterWrite($bytes, $file);
        return $bytes;
    }


    /**
     * Builds multiple files from an iterable dataset.
     *
     * Iterates over the dataset and calls `compile()` for each item.
     * Returns the total number of bytes written, or false if any compile fails.
     *
     * @param string $pattern Template pattern with placeholders.
     * @param iterable $dataset Array or object iterable containing data items.
     * @param string $delimiters Placeholder delimiters separated by whitespace (default: '{{ }}').
     * @return int|false Total bytes written, or false on failure.
     */
    public static function build(string $pattern, iterable $dataset, string $delimiters = "{{ }}"): int|false {
        $bytes = 0;

        foreach ($dataset as $data) {
            $result = self::compile($pattern, $data, $delimiters);
            if ($result === false) return false;
            $bytes += $result;
        }

        return $bytes;
    }

    /**
     * Resolves placeholders in a template pattern using provided data.
     *
     * Default delimiters are `{{ }}`. Supports top-level array keys or object
     * properties for replacement.
     *
     * @param string $pattern Template pattern containing placeholders.
     * @param array|object|null $data Data to replace placeholders.
     * @param string $delimiters Placeholder delimiters separated by whitespace (default: '{{ }}').
     * @return string Pattern with placeholders replaced by actual data, if the data key did not exist an empty string will be returned.
     */
    protected static function resolve(string $pattern, array|object|null $data, string $delimiters = "{{ }}"): string {
        $delimiters = preg_split("/\s+/", trim($delimiters));
        $delimiters = count($delimiters) < 2 ? ["{{", "}}"] : $delimiters;

        $open = preg_quote($delimiters[0], '/');
        $close = preg_quote($delimiters[1], '/');

        return preg_replace_callback("/$open\s*(.*?)\s*$close/", function ($matches) use ($data) {
            $key = $matches[1];

            if (is_array($data)) {
                return $data[$key] ?? "";
            }

            if (is_object($data)) {
                return property_exists($data, $key) ? $data->$key : "";
            }

            return "";
        }, $pattern);
    }

    protected function _beforeInvoke(): void {}
    protected function _beforeRender(array|object &$data): void {}
    protected function _afterRender(string &$html, array|object &$data): void {}
    protected function _beforeWrite(string &$file): void {}
    protected function _afterWrite(int|false &$bytes, string &$file): void {}
}
