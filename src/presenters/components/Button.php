<?php namespace Components;

use Utilities\Renderer;

class Button {
    use Renderer;   
    function __invoke(string $text): string {
        return $this->render("components/button", [
            "content" => $text,
        ]);
    }
}