<?php namespace Components;

use Utilities\Html;

class Button {
    use Html;   
    function __invoke(string $text): string {
        return $this->render("components/button", [
            "content" => $text,
        ]);
    }
}