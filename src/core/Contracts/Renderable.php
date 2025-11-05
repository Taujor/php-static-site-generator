<?php namespace Taujor\PHPSSG\Contracts;
use Taujor\PHPSSG\Utilities\Container;
use Taujor\PHPSSG\Utilities\Locate;

/**
 * Renderable
 *
 * A `Renderable` component is responsible for producing HTML by including
 * PHP template files located in the `views` directory. It provides
 * a helper method (`render`) that extracts an array of variables into
 * the template's scope and captures the output buffer, returning the
 * rendered HTML as a string.
 *
 * `Renderable` components are typically **invokable**, meaning subclasses
 * implement the `__invoke()` method to return their HTML output. This
 * allows them to be used seamlessly in Composables, and Buildables.
 *
 * Example usage:
 * ```php
 * class Title extends Renderable {
 *     public function __invoke(string $text): string {
 *         return $this->render('components/title', ['text' => $text]);
 *     }
 * }
 * ```
 *
 * @package Taujor\PHPSSG\Contracts
 * @see \Taujor\PHPSSG\Contracts\Composable
 * @see \Taujor\PHPSSG\Contracts\Buildable
 *
 * @method string render(string $view, array $data = []) Render a plain PHP view template with optional data and return its HTML.
 *
 * Hooks are available for customization:
 * @method void _beforeInvoke() Called before the component is invoked, useful for initialization.
 * @method void _beforeExtract(array &$data, string &$path) Called before extracting data into the template, useful for modifying data or path.
 * @method void _afterRender(array &$data, string &$html) Called after rendering the template, useful for modifying the HTML output.
 */
abstract class Renderable {
    function __construct(){
        static::_beforeInvoke();
    }
    /**
     * Renders a PHP view template with provided data.
     *
     * Extracts the associative `$data` array into variables that the template
     * can access, captures the output buffer, and returns the rendered HTML
     * as a string with a trailing newline.
     *
     * The `$view` path is relative to your `views` directory (`src/views/` by default) and should not include the
     * `.php` extension which is handled by `Locate::engine()` instead. @see \Taujor\PHPSSG\Utilities\Locate for more details on configuration. Example: `'components/title'` or `'layouts/base'`.
     *
     * @param string $view Relative path to the PHP view template (without .php).
     * @param array $data Optional associative array of variables for the template.
     * @return string Rendered HTML content.
     */
    protected function render(string $view, array $data = []): string {
        $path = Locate::views() . "/$view" . Locate::engine();
        $container = Container::instance();
        $renderable = $container->get(static::class);
        $renderable->_beforeExtract($data, $path);
        extract($data, EXTR_SKIP);
        ob_start();
        include $path;
        $html = ob_get_clean();
        $renderable->_afterRender($data, $html);
        return $html;
    }
    /**
     * Hook called before the component is invoked.
     *
     * Use this hook to initialize any resources or configurations required
     * before rendering begins. This hook is called once when the component is instantiated.
     *
     * @internal Hooks are intended for use in subclasses only.
     */
    protected function _beforeInvoke(): void {}
    /**
     * Hook called before extracting data into the template.
     *
     * Use this hook to modify the data or template path before the template is rendered.
     * This is useful for preprocessing data or dynamically changing the template path.
     *
     * @param array $data Associative array of variables to be extracted into the template.
     * @param string $path The resolved path to the template file.
     * @internal Hooks are intended for use in subclasses only.
     */
    protected function _beforeExtract(array &$data, string &$path): void {}
    /**
     * Hook called after rendering the template.
     *
     * Use this hook to modify the rendered HTML before it is returned. This is useful
     * for post-processing the HTML, such as DOM manipulation, minifying the output,
     * or injecting metadata, etc.
     *
     * @param array $data Associative array of variables used in the template.
     * @param string $html The rendered HTML content.
     * @internal Hooks are intended for use in subclasses only.
     */
    protected function _afterRender(array &$data, string &$html): void {}
}
