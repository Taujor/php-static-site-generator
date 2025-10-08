<?php namespace Taujor\PHPSSG\Utilities;

use Composer\Factory;

class Locate {
    public static function root(){
        if (\Phar::running()) {
            return dirname(\Phar::running(false));
        }
        return dirname(Factory::getComposerFile());
    }
}