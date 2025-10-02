<?php namespace Presenters\Pages;

use Presenters\Layouts\Base;
use Presenters\Components\Header;

class Home {
    function __construct(private Header $header, private Base $layout){}

    function __invoke() :string {
        return ($this->layout)(
            ($this->header)()
        );
    }
}