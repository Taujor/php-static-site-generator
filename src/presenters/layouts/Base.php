<?php namespace Layouts;

use Utilities\Html;

class Base {
    use Html;
    function __invoke(string $content) :string {
        return $this->render("layouts/base", [
            "content" => $content
        ]);
    }
}