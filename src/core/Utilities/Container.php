<?php namespace Taujor\PHPSSG\Utilities;

use Taujor\PHPSSG\Utilities\Locate;

class Container {
    private static ?\DI\Container $instance = null;

    public static function create(): \DI\Container {
        $cache = Locate::cache();
        $proxy = Locate::proxy();

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