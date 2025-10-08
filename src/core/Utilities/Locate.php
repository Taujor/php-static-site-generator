<?php namespace Taujor\PHPSSG\Utilities;

class Locate {
    public static function root(){
        $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        return dirname($reflection->getFileName(), 3);
    }
}