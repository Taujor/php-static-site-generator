<?php namespace Components;

use Utilities\Html;

class Header {
    use Html;   
    function __construct(private Button $button){}

    function __invoke(): string {
        return $this->render("components/header", [
            "title" => "hi",
            "button" => $this->button
        ]);
    }
}