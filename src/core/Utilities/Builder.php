<?php namespace Taujor\PHPSSG\Utilities;

use Taujor\PHPSSG\Utilities\Container;
use Taujor\PHPSSG\Contracts\{Renderable, Composable};
use Taujor\PHPSSG\Utilities\Locate;

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

        $content = self::minify($page());

        $file = Locate::root() . "/public$route";
 
        $isUnchanged = is_file($file) && md5_file($file) === md5($content);

        if ($isUnchanged) {
            return 0;
        }
 
        $directory = dirname($file);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return file_put_contents($file, $content);
    }

    public static function build(array ...$pages): void {
        foreach ($pages as [$class, $route]) {
            self::compile($class, $route);
        }
    }
}