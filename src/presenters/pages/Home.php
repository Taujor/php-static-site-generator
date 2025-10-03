<?php namespace Presenters\Pages;

use Contracts\Composable;
use Presenters\Layouts\Base;

class Home implements Composable {
    function __construct(private Base $layout){}

    function __invoke() :string {
        return ($this->layout)(
        
        );
    }
}