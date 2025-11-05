<?php namespace Taujor\PHPSSG\Contracts;

/**
 * Composable
 *
 * A Composable represents a self-contained unit of presentation logic that
 * produces a string of HTML when invoked. Unlike `Renderable` components,
 * composables do not require a separate template file. Instead, they are
 * designed to **combine other components** (Renderable or Composable) into
 * a larger structure.
 *
 * Subclasses must implement the `__invoke()` method, which allows them to
 * be used as callable objects in Renderables, Buildables, or other Composables.
 *
 * Example usage:
 * ```php
 * class Heading extends Composable {
 *     public function __construct(private Title $title, private Subtitle $subtitle) {}
 *
 *     public function __invoke(): string {
 *         return ($this->title)() . ($this->subtitle)();
 *     }
 * }
 *
 * $heading = new Heading(new Title("Hello"), new Subtitle("World"));
 * echo $heading(); // Renders the composed HTML
 * ```
 *
 * @package Taujor\PHPSSG\Contracts
 * @see Taujor\PHPSSG\Contracts\Renderable For components that render templates from files.
 * @see Taujor\PHPSSG\Contracts\Buildable For components that generate static HTML files.
 */

/**
 * Abstract class defining the structure for a Composable component.
 *
 * Composable components are designed to be self-contained units of presentation logic
 * that combine other components into larger structures. They implement the `__invoke()`
 * method to produce HTML output and can be used as callable objects.
 */
abstract class Composable
{
    /**
     * Constructor for Composable components.
     *
     * Initializes the component and calls the _beforeInvoke hook for any pre-processing.
     */
    function __construct()
    {
        static::_beforeInvoke();
    }

    /**
     * Invokes the component and returns its composed HTML output.
     *
     * Subclasses must implement this method to define how the component
     * composes other components or generates its output.
     *
     * @return string The composed HTML for this component.
     */
    abstract public function __invoke(): string;

    /**
     * Hook called before the component is invoked.
     *
     * Use this hook to initialize any resources or configurations required
     * before the component is invoked. This hook is called once when the component is instantiated.
     *
     * @internal Hooks are intended for use in subclasses only.
     */
    function _beforeInvoke(): void {}
}
