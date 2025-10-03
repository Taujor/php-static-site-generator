<?php

require dirname(__DIR__) . "/config/bootstrap.php";

use Utilities\Builder;
use Presenters\Pages\Home;

// build single Renderable|Composable class
Builder::compile(Home::class, "/index.html");

// build multiple Renderable|Composable classes
Builder::build(
    [Home::class, "/hello/index.html"],  
    [Home::class, "/world/index.html"]
);