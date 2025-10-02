<?php

$build = require dirname(__DIR__) . "/config/bootstrap.php";

use Presenters\Pages\Home;

$build(Home::class, "/index.html");