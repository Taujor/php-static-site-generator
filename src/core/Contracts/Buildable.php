<?php namespace Taujor\PHPSSG\Contracts;

interface Buildable {
    public static function compile(): int|false;
    public static function build(): void;
}
