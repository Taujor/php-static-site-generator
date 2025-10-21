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
 * @method void _beforeExtract(array &$data, string &$path)
 * @method void _afterRender(array &$data, &$html)
 */
abstract class Renderable {
    /**
     * Renders a PHP view template with provided data.
     *
     * Extracts the associative `$data` array into variables that the template
     * can access, captures the output buffer, and returns the rendered HTML
     * as a string with a trailing newline.
     *
     * The `$view` path is relative to `src/views/` and should not include the
     * `.php` extension. Example: `'components/title'` or `'layouts/base'`.
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

    protected function _beforeExtract(array &$data, string &$path): void {}
    protected function _afterRender(array &$data, &$html): void {}

}
