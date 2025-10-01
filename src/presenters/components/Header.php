<?php namespace Components;

use Utilities\Renderer;

class Header {
    use Renderer;   
    function __construct(private Button $button){}

    function __invoke(): string {
        return $this->render("components/header", [
            "title" => "hi",
            "button" => $this->button
        ]);
    }
}