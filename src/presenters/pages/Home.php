<?php namespace Pages;

use Layouts\Base;
use Components\Header;

class Home {
    function __construct(private Header $header, private Base $layout){}

    function __invoke() :string {
        return ($this->layout)(
            ($this->header)()
        );
    }
}