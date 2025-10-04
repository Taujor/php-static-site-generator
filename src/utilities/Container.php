<?php namespace Utilities;

use DI\ContainerBuilder;

class Container {
    private static ?\DI\Container $instance = null;

    public static function create(): \DI\Container {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->enableCompilation(dirname(dirname(__DIR__)) . "/cache");
        $containerBuilder->writeProxiesToFile(true, dirname(dirname(__DIR__)) . '/cache/proxies');
        return $containerBuilder->build();
    }

    public static function instance(): \DI\Container {
        if (self::$instance === null) {
            self::$instance = self::create();
        }
        return self::$instance;
    }
}
