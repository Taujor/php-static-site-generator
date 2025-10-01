<?php

$build = require dirname(__DIR__) . "/config/bootstrap.php";

use Pages\Home;

$build(Home::class, "/index.html");