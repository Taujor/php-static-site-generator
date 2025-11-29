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
 * @see \Taujor\PHPSSG\Contracts\Renderable For template-based rendering.
 * @see \Taujor\PHPSSG\Contracts\Composable For combining components without templates.
 * @see \Taujor\PHPSSG\Utilities\Container For dependency injection.
 * @see \Taujor\PHPSSG\Utilities\Locate For file and directory location utilities.
 *
 * @method string|false __invoke(mixed $data) Render the buildable component with provided data and return HTML.
 * @method static int|false compile(string $pattern, mixed $data) Compile a single placeholder pattern with data and write to file.
 * @method static int|false build(string $pattern, iterable $dataset) Build multiple files from an iterable dataset.
 *
 * Available hooks for customization:
 * @method void _beforeInvoke() Called before the component is invoked, useful for initialization.
 * @method void _beforeRender(mixed &$data) Called before data is rendered, useful for data preprocessing.
 * @method void _afterRender(mixed &$data, string &$html) Called after HTML is rendered, useful for HTML postprocessing.
 * @method void _beforeWrite(mixed &$data, string &$file) Called before writing to file, useful for file path adjustments.
 * @method void _afterWrite(string &$file, int|false &$bytes) Called after writing to file, useful for logging or notifications.
 */
abstract class Buildable {
    /**
     * Constructor for Buildable components.
     *
     * Initializes the component and calls the _beforeInvoke hook for any pre-processing.
     */
    function __construct() {
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
     * The process includes these steps:
     * 1. Resolve the component from the container
     * 2. Validate that the component is callable
     * 3. Call _beforeRender hook for data preprocessing
     * 4. Render HTML by invoking the component
     * 5. Call _afterRender hook for HTML postprocessing
     * 6. Resolve the output file path with placeholders
     * 7. Call _beforeWrite hook for file adjustments
     * 8. Create directory if needed
     * 9. Compare hashes to avoid unnecessary writes
     * 10. Write the file
     * 11. Cache the content
     * 12. Call _afterWrite hook for completion actions
     *
     * @param string $pattern Placeholder pattern containing placeholders like `{{slug}}` or just plain text either resolves to a path relative to the build directory.
     * @param mixed $data Data to inject into the template.
     * @param string $delimiters Placeholder delimiters separated by whitespace (default: '{{ }}').
     * @return int|false Number of bytes written, 0 if unchanged, or false on failure.
     * @throws \InvalidArgumentException If the class is not callable or does not extend Buildable.
     * @throws \RuntimeException If the output directory cannot be created or file cannot be written.
     */
    public static function compile(string $pattern, mixed $data, string $delimiters = "{{ }}") : int|false {
        $container = Container::instance();
        $buildable = $container->get(static::class);
        if (!($buildable instanceof Buildable) || !is_callable($buildable)) {
            throw new \InvalidArgumentException(sprintf(
                "Class '%s' must extend the 'Buildable' abstract class and be callable.", static::class)
            );
        }
        $buildable->_beforeRender($data);
        $html = $buildable($data);
        $buildable->_afterRender($data, $html);
        $file = self::resolve(Locate::build() . $pattern, $data, $delimiters);
        $buildable->_beforeWrite($data, $file);
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
        $buildable->_afterWrite($file, $bytes);
        return $bytes;
    }

    /**
     * Builds multiple files from an iterable dataset.
     *
     * Iterates over the dataset and calls `compile()` for each item.
     * Returns the total number of bytes written, or false if any compile fails.
     *
     * This is useful for generating multiple pages from a dataset, such as
     * blog posts from an array of post objects.
     *
     * @param string $pattern Template pattern with placeholders.
     * @param iterable $dataset Array or object iterable containing data items.
     * @param string $delimiters Placeholder delimiters separated by whitespace (default: '{{ }}').
     * @return int|false Total bytes written, or false on failure.
     */
    public static function build(string $pattern, iterable $dataset, string $delimiters = "{{ }}") : int|false {
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
     * Example:
     * ```php
     * $pattern = '/posts/{{slug}}.html';
     * $data = ['slug' => 'my-post'];
     * $resolved = self::resolve($pattern, $data);
     * // Returns '/posts/my-post.html'
     * ```
     *
     * @param string $pattern Template pattern containing placeholders.
     * @param array|object|null $data Data to replace placeholders.
     * @param string $delimiters Placeholder delimiters separated by whitespace (default: '{{ }}').
     * @return string Pattern with placeholders replaced by actual data, if the data key did not exist an empty string will be returned.
     */
    protected static function resolve(string $pattern, mixed $data, string $delimiters = "{{ }}") : string {
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

    /**
     * Hook called before the component is invoked.
     *
     * Use this hook to initialize any resources or configurations required
     * before the component is invoked. This hook is called once when the component is instantiated.
     *
     * @internal Hooks are intended for use in subclasses only.
     */
    protected function _beforeInvoke() : void {}

    /**
     * Hook called before data is rendered.
     *
     * Use this hook to modify or validate the data before it is passed to the template.
     * This is useful for setting default values, sanitizing input, or transforming data.
     *
     * @param array|object $data The data that will be used for rendering.
     * @internal Hooks are intended for use in subclasses only.
     */
    protected function _beforeRender(mixed &$data) : void {}

    /**
     * Hook called after HTML is rendered.
     *
     * Use this hook to modify the rendered HTML before it is written to file.
     * This is useful for post-processing the HTML, such as adding global scripts,
     * minifying the output, or injecting metadata.
     *
     * @param mixed $data The data that was used for rendering.
     * @param string $html The rendered HTML content.
     * @internal Hooks are intended for use in subclasses only.
     */
    protected function _afterRender(mixed &$data, string &$html) : void {}

    /**
     * Hook called before writing to file.
     *
     * Use this hook to modify the file path or validate the output location
     * before the file is written. This is useful for organizing files into
     * different directories based on data properties.
     *
     * @param array|object $data The data that was used for rendering.
     * @param string $file The resolved file path where the content will be written.
     * @internal Hooks are intended for use in subclasses only.
     */
    protected function _beforeWrite(mixed &$data, string &$file) : void {}

    /**
     * Hook called after writing to file.
     *
     * Use this hook to log success, update indexes, or trigger notifications
     * after the file has been written. This is useful for integrating with
     * external systems or tracking build progress.
     *
     * @param string $file The file path where content was written.
     * @param int|false $bytes The number of bytes written, or false on failure.
     * @internal Hooks are intended for use in subclasses only.
     */
    protected function _afterWrite(string &$file, int|false &$bytes) : void {}
}
