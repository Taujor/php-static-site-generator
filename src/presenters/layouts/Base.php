<?php namespace Presenters\Layouts;

use Utilities\Renderer;
use Contracts\Renderable;

class Base implements Renderable{
    use Renderer;
    function __invoke(string $content) :string {
        return $this->render("layouts/base", [
            "content" => $content
        ]);
    }
}