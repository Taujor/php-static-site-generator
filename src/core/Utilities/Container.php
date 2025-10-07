<?php namespace Taujor\PHPSSG\Utilities;

class Container {
    private static ?\DI\Container $instance = null;

    public static function create(): \DI\Container {
        $root = dirname(__DIR__, 2);
        $cache = $root . "/cache";
        $proxy = $root . "/cache/proxies";

        foreach ([$cache, $proxy] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $containerBuilder = new \DI\ContainerBuilder();
        $containerBuilder->enableCompilation($cache);
        $containerBuilder->writeProxiesToFile(true, $proxy);
        return $containerBuilder->build();
    }

    public static function instance(): \DI\Container {
        if (self::$instance === null) {
            self::$instance = self::create();
        }
        return self::$instance;
    }
}