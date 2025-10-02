<?php namespace Presenters\Layouts;

use Utilities\Renderer;

class Base {
    use Renderer;
    function __invoke(string $content) :string {
        return $this->render("layouts/base", [
            "content" => $content
        ]);
    }
}