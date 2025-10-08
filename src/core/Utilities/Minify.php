<?php namespace Taujor\PHPSSG\Utilities;

class Minify {
    public static function string (string $html): string {
        // Remove whitespace between tags
        $html = preg_replace('/>\s+</', '><', $html);
        // Collapse multiple spaces
        $html = preg_replace('/\s+/', ' ', $html);
        // Remove comments (except IE conditionals)
        $html = preg_replace('/<!--(?!\[if).*?-->/', '', $html);

        return trim($html);
    }
}