<?php namespace Utilities;
trait Renderer {
    public function render(string $filename, array $data): string {
        extract($data, EXTR_SKIP);
        ob_start();
        include dirname(__DIR__) . "/views/" . $filename . ".php";
        return ob_get_clean();
    }
}