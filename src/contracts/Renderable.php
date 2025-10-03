<?php

namespace Contracts;

/**
 * Represents a `Renderable` object that can be invoked to produce
 * a html output string. 
 *
 * This is typically used for component-level presenters with no sub-component or layout dependencies.
 */
interface Renderable {
     /**
     * Invoke the object as a callable.
     *
     * This allows the object to be composed with other `Renderable` or `Composable` objects.
     * Any arguments passed to `__invoke()` are intended to provide **data for the view**. 
     * 
     * For example, a Button component might accept:
     *   `$button("Click me", "/hello");`
     * The first argument could be rendered as `<?= $text ?>` in the view,
     * and the second as a part of a href attribute.
     * 
     *  Ensure that when passing arguments to `__invoke`
     * they have a default value `__invoke($text)` will throw a `PHP Fatal Error`
     * instead use `__invoke($text = "")`.
     *
     * @param mixed ...$any Arguments representing dynamic data to pass to the view.
     * @return string The rendered html output, typically returned from the `render()` method.
     */
    public function __invoke(): string;
    /**
     * Render a view template with optional data.
     *
     * The `$data` array provides variables to the template. Each key becomes a variable
     * available in the view. For example, if `$data = ['text' => 'Click me']`,
     * you can use `<?= $text ?>` in your view file.
     *
     * @param string $view The relative path of the template in the 'views' directory (without the '.php' extension) `components/button` for example.
     * @param array $data An associative array of key/value pairs to pass to the view.
     * @return string The rendered html output string.
     */
    public function render(string $view, array $data = []): string;
}
