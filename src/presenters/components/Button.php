<?php namespace Presenters\Components;

use Contracts\Renderable;
use Utilities\Renderer;

class Button implements Renderable{
    use Renderer;   
    function __invoke(string $text): string {
        return $this->render("components/button", [
            "content" => $text,
        ]);
    }
}