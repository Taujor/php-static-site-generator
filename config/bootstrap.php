<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use DI\ContainerBuilder;

$builder = new ContainerBuilder();
$builder->enableCompilation(dirname(__DIR__) . '/tmp');
$builder->writeProxiesToFile(true, "../" . __DIR__ . '/tmp/proxies');

$container = $builder->build();

$minify = function (string $html): string {
    // Remove whitespace between tags
    $html = preg_replace('/>\s+</', '><', $html);
    // Collapse multiple spaces
    $html = preg_replace('/\s+/', ' ', $html);
    // Remove comments (except IE conditionals)
    $html = preg_replace('/<!--(?!\[if).*?-->/', '', $html);

    return trim($html);
};

return $build = function($class, $route) use ($container, $minify): void {
    $page = $container->get($class);
    $file = dirname(__DIR__) . "/public$route";
    $directory = dirname($file);

    if(!is_dir($directory)){
        mkdir($directory, 0755, true);
    }

    file_put_contents($file, $minify($page()));
};