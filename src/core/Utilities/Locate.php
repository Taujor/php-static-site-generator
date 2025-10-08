<?php namespace Taujor\PHPSSG\Utilities;

use Phar;
use Composer\Factory;

class Locate {
    private static ?string $root = null;

    public static function root(): string {
        if(self::$root === null) Phar::running() ? self::$root = getcwd() : self::$root = dirname(Factory::getComposerFile());
        return self::$root;
    }
}