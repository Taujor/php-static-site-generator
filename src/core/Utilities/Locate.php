<?php namespace Taujor\PHPSSG\Utilities;

use Phar;
use Composer\Factory;

class Locate {
    private static ?string $root = null;
    private static ?string $views = null;
    private static ?string $cache = null;
    private static ?string $proxy = null;
    private static ?string $build = null;
    private static ?string $engine = null;

    public static function root(): string {
        if(self::$root === null) Phar::running() ? self::$root = getcwd() : self::$root = dirname(Factory::getComposerFile());
        return self::$root;
    }

    public static function views($override = ""): string {
        if(self::$views === null) self::$views = $override !== "" ? self::root() . $override : self::root() . "/src/views";
        return self::$views;
    }

    public static function cache($override = ""): string {
        if(self::$cache === null) self::$cache = $override !== "" ? self::root() . $override : self::root() . "/cache";
        return self::$cache;
    }

    public static function proxy($override = ""): string {
        if(self::$proxy === null) self::$proxy = $override !== "" ? self::root() . $override : self::root() . "/cache/proxy";
        return self::$proxy;
    }

    public static function build($override = ""): string {
        if(self::$build === null) self::$build = $override !== "" ? self::root() . $override : self::root() . "/public";
        return self::$build;
    }

    public static function engine($override = ""): string {
        if(self::$engine === null) self::$engine = $override !== "" ? $override : ".php";
        return self::$engine;
    }

    public static function reset(): void {
        self::$root = null;
        self::$views = null;
        self::$cache = null;
        self::$proxy = null;
        self::$build = null;
        self::$engine = null;
    }
}