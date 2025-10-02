<?php

namespace Contracts;

/**
 * Represents a `Composable` object that can be invoked to produce
 * a html output string. 
 *
 * This is typically used for page-level presenters or high-level component presenters
 * that have sub-component and/or layout dependencies.
 */
interface Composable
{
    /**
     * Composable classes should combine any internal components, layouts,
     * or content into a single string. It is recommended to use `Renderable`
     * objects by concatenating their output into a final string.
     *
     * Example implementation:
     * ```php
     * class Home implements Composable {
     *     // dependencies injected into the constructor in this example are implementing the "Renderable" interface 
     *     public function __construct(private Button $button, private Header $header, private Base $layout) {}
     *
     *     public function __invoke(): string {
     *         return ($this->layout)(
     *             ($this->header)() .
     *             ($this->button)("Click me") 
     *         );
     *     }
     * }
     * ```
     *
     * Example usage:
     * ```php
     * $home = new Home($header, $layout);
     * echo $home(); // returns the html string
     * ```
     *
     * Any arguments passed to `__invoke()` should be interpreted by the
     * `Composable` as data to pass to its internal sub-components or layout.
     *
     * @param mixed ...$args Dynamic data to pass to the sub-component or layout.
     * @return string The composed html output string.
     */
    public function __invoke(): string;
}
