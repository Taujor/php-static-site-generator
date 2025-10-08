<?php namespace Taujor\PHPSSG\Utilities;

use Taujor\PHPSSG\Utilities\Locate;

trait Renderer {
    public function render(string $view, array $data = []): string {
        extract($data, EXTR_SKIP);
        ob_start();
        include Locate::root() . "/src/views/" . $view . ".php";
        return ob_get_clean();
    }
}