<?php namespace Taujor\PHPSSG\Contracts;

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
    /**
     * Compiles a template pattern with provided data and writes it to a file.
     *
     * Resolves placeholders in the pattern using the data, renders the HTML 
     * by invoking the component, and writes it to the resolved file path 
     * under the build directory. Uses SHA256 hash comparison to avoid 
     * overwriting unchanged files.
     *
     * Hooks are available for customization:
     * @method static void _beforeRender(array|object &$data)
     * @method static void _afterRender(string &$html)
     * @method static void _beforeWrite(string &$file)
     * @method static void _afterWrite()
     *
     * @param string $pattern Placeholder pattern containing placeholders like `{{slug}}` or just plain text either resolves to a path relative to the build directory.
     * @param array|object $data Data to inject into the template.
     * @return int|false Number of bytes written, 0 if unchanged, or false on failure.
     *
     * @throws \InvalidArgumentException If the class is not callable or does not extend Buildable.
     * @throws \RuntimeException If the output directory cannot be created.
     */
    public static function compile(string $pattern, array|object $data = []): int|false
    {
        $container = Container::instance();
        $buildable = $container->get(static::class);

        if (!($buildable instanceof Buildable) || !is_callable($buildable)) {
            throw new \InvalidArgumentException(sprintf(
                "Class '%s' must implement the 'Buildable' interface and be callable.", static::class)
            );
        }

        static::_beforeRender($data);
        $html = $buildable($data);
        static::_afterRender($html);

        $file = self::resolve(Locate::root() . "/public" . $pattern, $data);
        static::_beforeWrite($file);

        $isUnchanged = is_file($file) && hash("sha256", $file) === hash("sha256", $html);
        if ($isUnchanged) return 0;

        $dir = dirname($file);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException("Failed to create directory: $dir");
        }

        $bytes = file_put_contents($file, $html);
        static::_afterWrite();

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
     * @return int|false Total bytes written, or false on failure.
     */
    public static function build(string $pattern, iterable $dataset): int|false
    {
        $bytes = 0;

        foreach ($dataset as $data) {
            $result = self::compile($pattern, $data);
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
     * @return string Pattern with placeholders replaced by actual data.
     */
    protected static function resolve(string $pattern, array|object|null $data, string $delimiters = "{{ }}"): string
    {
        $delimiters = preg_split("/\s+/", trim($delimiters));
        return preg_replace_callback("/$delimiters[0]\s*(.*?)\s*$delimiters[1]/", function ($matches) use ($data) {
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

    protected static function _beforeRender(array|object &$data): void {}
    protected static function _afterRender(string &$html): void {}
    protected static function _beforeWrite(string &$file): void {}
    protected static function _afterWrite(): void {}
}
