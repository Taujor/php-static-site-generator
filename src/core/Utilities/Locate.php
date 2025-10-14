<?php namespace Taujor\PHPSSG\Utilities;

use Phar;
use Composer\Factory;

/**
 * Class Locate
 *
 * Provides static utility methods for determining and caching
 * key filesystem paths used by the PHPSSG application.
 *
 * The class resolves paths relative to the Composer project root,
 * or to the current working directory when running from a PHAR archive.
 * Each path can be overridden once via the `$override` parameter on first call.
 * After the first resolution, the value is cached for subsequent calls.
 *
 * To reconfigure all paths, use {@see Locate::reset()}.
 *
 * @package Taujor\PHPSSG\Utilities
 */
class Locate {
    /** @var string|null Cached absolute path to the project root directory */
    private static ?string $root = null;

    /** @var string|null Cached absolute path to the views directory */
    private static ?string $views = null;

    /** @var string|null Cached absolute path to the cache directory */
    private static ?string $cache = null;

    /** @var string|null Cached absolute path to the proxy cache directory */
    private static ?string $proxy = null;

    /** @var string|null Cached absolute path to the build directory */
    private static ?string $build = null;

    /** @var string|null Cached engine file extension (e.g. `.php`, `.twig`) */
    private static ?string $engine = null;

    /**
     * Resolve and return the project root directory.
     *
     * If running inside a PHAR, this will return the current working directory.
     * Otherwise, it uses Composer’s `Factory::getComposerFile()` to locate the root and go one directory up.
     *
     * @return string Absolute path to the project root.
     */
    public static function root(): string {
        if (self::$root === null) {
            self::$root = Phar::running() ? getcwd() : dirname(Factory::getComposerFile());
        }
        return self::$root;
    }

    /**
     * Get or set the views directory.
     *
     * @param string $override Optional path to override the default (applied only on first call).
     * @return string Absolute path to the views directory. Defaults to `/src/views`.
     */
    public static function views($override = ""): string {
        if (self::$views === null) {
            self::$views = $override !== "" ? self::root() . $override : self::root() . "/src/views";
        }
        return self::$views;
    }

    /**
     * Get or set the cache directory.
     *
     * @param string $override Optional path to override the default (applied only on first call).
     * @return string Absolute path to the cache directory. Defaults to `/cache`.
     */
    public static function cache($override = ""): string {
        if (self::$cache === null) {
            self::$cache = $override !== "" ? self::root() . $override : self::root() . "/cache";
        }
        return self::$cache;
    }

    /**
     * Get or set the proxy cache directory.
     *
     * @param string $override Optional path to override the default (applied only on first call).
     * @return string Absolute path to the proxy directory. Defaults to `/cache/proxy`.
     */
    public static function proxy($override = ""): string {
        if (self::$proxy === null) {
            self::$proxy = $override !== "" ? self::root() . $override : self::root() . "/cache/proxy";
        }
        return self::$proxy;
    }

    /**
     * Get or set the build directory.
     *
     * @param string $override Optional path to override the default (applied only on first call).
     * @return string Absolute path to the build directory. Defaults to `/public`.
     */
    public static function build($override = ""): string {
        if (self::$build === null) {
            self::$build = $override !== "" ? self::root() . $override : self::root() . "/public";
        }
        return self::$build;
    }

    /**
     * Get or set the template engine extension used by the renderer.
     *
     * Determines what file extension should be used when resolving templates.
     *
     * @param string $override Optional override for the file extension (applied only on first call).
     * @return string File extension for the template engine (e.g. `.php`, `.twig`). Defaults to `.php`.
     */
    public static function engine($override = ""): string {
        if (self::$engine === null) {
            self::$engine = $override !== "" ? $override : ".php";
        }
        return self::$engine;
    }

    /**
     * Reset all cached paths and settings.
     *
     * This clears all previously stored directory paths and the engine setting,
     * allowing new overrides to be applied on subsequent calls.
     *
     * @return void
     */
    public static function reset(): void {
        self::$root = null;
        self::$views = null;
        self::$cache = null;
        self::$proxy = null;
        self::$build = null;
        self::$engine = null;
    }
}
