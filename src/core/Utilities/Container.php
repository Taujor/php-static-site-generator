<?php namespace Taujor\PHPSSG\Utilities;

use Taujor\PHPSSG\Utilities\Locate;

/**
 * Container
 *
 * Provides a singleton wrapper around a PHP-DI container instance.
 *
 * The container is lazily initialized and configured with compiled
 * definitions and generated proxies for performance.
 *
 * Directories for cache and proxy generation are automatically created
 * based on paths resolved by {@see Locate::cache()} and {@see Locate::proxy()}.
 *
 * @package Taujor\PHPSSG\Utilities
 */
class Container {
    /** @var \DI\Container|null Cached PHP-DI container instance */
    private static ?\DI\Container $instance = null;

    /**
     * Create and configure a new PHP-DI container.
     *
     * - Ensures cache and proxy directories exist.
     * - Enables container compilation for faster performance.
     * - Configures PHP-DI to write proxies to the proxy directory.
     *
     * @return \DI\Container A fully built dependency injection container.
     */
    public static function create(): \DI\Container {
        $cache = Locate::cache();
        $proxy = Locate::proxies();

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

    /**
     * Get the singleton container instance.
     *
     * Lazily initializes the container on first call using {@see Container::create()}.
     *
     * @return \DI\Container The shared dependency injection container instance.
     */
    public static function instance(): \DI\Container {
        if (self::$instance === null) {
            self::$instance = self::create();
        }
        return self::$instance;
    }
}
