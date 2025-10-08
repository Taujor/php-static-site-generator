<?php namespace Taujor\PHPSSG\Utilities;

class Locate {
    public static function root(){
        if (\Phar::running()) {
            return dirname(\Phar::running(false));
        }
        $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        return dirname($reflection->getFileName(), 3);
    }
}