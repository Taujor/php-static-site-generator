<?php namespace Utilities;

use Utilities\Container;
use Contracts\{Renderable, Composable};

class Builder {

    private static function minify (string $html): string {
        // Remove whitespace between tags
        $html = preg_replace('/>\s+</', '><', $html);
        // Collapse multiple spaces
        $html = preg_replace('/\s+/', ' ', $html);
        // Remove comments (except IE conditionals)
        $html = preg_replace('/<!--(?!\[if).*?-->/', '', $html);

        return trim($html);
    }

    public static function compile(string $class, string $route): int|false {
        $container = Container::instance();
        $page = $container->get($class);

        if (!($page instanceof Renderable) && !($page instanceof Composable)) {
            throw new \InvalidArgumentException(sprintf(
                "Class '%s' must implement 'Renderable' or 'Composable'", $class)
            );
        }

        $file = dirname(dirname(__DIR__)) . "/public$route";
        $directory = dirname($file);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return file_put_contents($file, self::minify($page()));
    }

    public static function build(array ...$pages): void {
        foreach ($pages as [$class, $route]) {
            self::compile($class, $route);
        }
    }
}