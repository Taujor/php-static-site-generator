<?php namespace Taujor\PHPSSG\Contracts;

use Taujor\PHPSSG\Utilities\Locate;

/**
 * Class Renderable
 *
 * Abstract base class for components that can render plain php view templates.
 * Provides a helper method to include view templates
 * with optional data.
 *
 * @package Taujor\PHPSSG\Contracts
 * 
 * @method string render(string $view, array $data = []) Render a plain php view template with optional data and return its HTML.
 */
abstract class Renderable {
    /**
     * Renders a PHP view template with provided data.
     *
     * This method extracts the data array into variables available
     * in the view, captures the output buffer, and returns the
     * rendered HTML as a string with a trailing newline.
     *
     * @param string $view Name of the plain php view template file (without .php extension) for example `component/filename` or 'layout/filename'.
     * @param array $data Optional associative array of variables to pass to the view.
     * @return string Rendered html content as a string.
     */
    protected function render(string $view, array $data = []): string {
        extract($data, EXTR_SKIP);
        ob_start();
        include Locate::root() . "/src/views/" . $view . ".php";
        return ob_get_clean() . "\n";
    }
}