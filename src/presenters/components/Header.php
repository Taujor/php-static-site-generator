<?php namespace Presenters\Components;

use Contracts\Renderable;
use Utilities\Renderer;

class Header implements Renderable {
    use Renderer;   
    function __construct(private Button $button){}

    function __invoke(): string {
        return $this->render("components/header", [
            "title" => "hi",
            "button" => $this->button
        ]);
    }
}