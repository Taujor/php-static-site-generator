<?php namespace Taujor\PHPSSG\Contracts;

abstract class Composable {
    abstract public function __invoke(): string;
}
