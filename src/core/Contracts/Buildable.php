<?php namespace Taujor\PHPSSG\Contracts;

interface Buildable {
    public static function compile(string $pattern, object|array|null $data): int|false;
    public static function build(): void;
    public function __invoke(object|array|null $data): string;
}
