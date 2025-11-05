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
 * @see \Taujor\PHPSSG\Utilities\Locate For resolving cache and proxy directories.
 * @see \DI\Container PHP-DI container documentation.
 */
class Container
{
    /** @var \DI\Container|null Cached PHP-DI container instance */
    private static ?\DI\Container $instance = null;

    /**
     * Create and configure a new PHP-DI container.
     *
     * This method sets up a new container with the following configuration:
     *
     * - Ensures cache and proxy directories exist by creating them if necessary
     * - Enables container compilation for faster performance
     * - Configures PHP-DI to write proxies to the proxy directory
     *
     * The container is configured to use the directories provided by:
     * - @see Locate::cache() for compiled container definitions
     * - @see Locate::proxies() for generated proxy classes
     *
     * @return \DI\Container A fully configured dependency injection container.
     * @throws \RuntimeException If directory creation fails.
     */
    public static function create(): \DI\Container
    {
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
     * This method implements the singleton pattern by:
     * - Returning the existing container instance if already created
     * - Lazily initializing the container on first call using {@see Container::create()}
     *
     * This ensures that only one container instance exists throughout the application lifecycle.
     *
     * @return \DI\Container The shared dependency injection container instance.
     * @see Container::create() For container initialization details.
     */
    public static function instance(): \DI\Container
    {
        if (self::$instance === null) {
            self::$instance = self::create();
        }
        return self::$instance;
    }
}
