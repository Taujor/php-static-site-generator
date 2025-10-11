<?php namespace Taujor\PHPSSG\Contracts;

interface Buildable {
    public static function compile(): string;
    public static function build(): string;
}
