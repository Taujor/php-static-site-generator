💖 PHPSSG is open source and free.  
If you find it useful, please consider [sponsoring me](https://github.com/sponsors/taujor) to support continued development.

# PHPSSG (Personal Home Page Static Site Generator)

A **lightweight, PHP-native static site generator** for building **composable templates**.  
PHPSSG uses **invokable component classes**, **output buffering**, and **plain PHP templates** to provide a clean developer experience — no third-party templating engine required, but you are welcome to use one.

---

## Features

- **Plain PHP templates** – no special syntax to learn.  
- **Invokable components** – use components like `$header()`.
- **Component based routing** – `Buildable` components get methods able to write the html they generate to a file.
- **Hooks** – Hooks are available in the build process to easily inject your own custom code to manipulate content before or just after it is generated.  
- **Centralized render helper** – avoids repeated `ob_start()` / `ob_get_clean()`.  
- **Nesting & composition** – layouts can include multiple components.  
- **PHAR Support** – easily ran as a phar if desired.
- **Slots & data passing** – inject content into templates easily.  
- **Incremental builds** – files are rewritten only if content changes (xxh3 hash comparison).  
- **Caching** – file paths and contents are hashed and cached to disk prioritizing thread safety.  
- **Native debugging** – works seamlessly with standard PHP tools.  
- **PSR-4 compliant** – fully autoloadable via Composer.  
- **Abstract Classes** – `Renderable`, `Composable`, `Buildable` help define component APIs, they are fundamental to PHPSSG.  
- **IDE-friendly** – docblocks provide autocomplete, type hints, and method signatures.  
- **Highly portable** – works in any PHP 8.1+ environment.  
- **Flexible structure** – There are no rules that force you to structure your project a certain way, I have made suggestions and set some reconfigurable defaults but beyond that I leave everything completely up to you.
- **Fast content comparison** – via [xxh3](https://php.watch/versions/8.1/xxHash) hashing.

---

## Requirements

- PHP 8.1+ (xxh3 hashing)  
- Composer (autoloading)
- PHP-DI (dependency injection)

---

## Installation

Install via Composer:

```bash
composer require taujor/phpssg
```

Run a local development server:

```bash
php -S localhost:8080 public/index.html
```

---

## Usage

### Layouts

Layouts are typically **Renderables**:

```php
// src/presenters/layouts/Base.php
use Taujor\PHPSSG\Contracts\Renderable;

class Base extends Renderable
{
    public function __invoke(string $content): string
    {
        // the directory specified here is relative to your views directory ("src/views" by default)
        return $this->render("layouts/base", ["content" => $content]);
    }
}
```

### Components

Components are typically **Renderables** (like layouts) or **Composables** :

```php
// src/presenters/components/Heading.php
use Taujor\PHPSSG\Contracts\Composable;

class Heading extends Composable {
    function __construct(private Title $title, private Subtitle $subtitle) {}
    function __invoke(): string {
        return ($this->title)() . ($this->subtitle)();
    }
}
```
`Title` and `Subtitle` are **Renderables** (in this example) they have their own respective template files, however our `Heading` class simply takes these two components and "composes" them into a new component (via string concatenation in this case). Hence it does not require its own template file but can still be invoked just the same as any other presenter.

```php
<!-- src/views/layouts/base.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?= $content ?>
</body>
</html>
```

The `render` method of `Renderable` extracts variables into the template and returns the output as a string.  

---

### Pages

Pages are typically **Buildables**, like **Composables** they are self-contained, and do not need separate view templates. They inherit the `compile` and `build` methods which output html files to the build directory (`public` by default):

```php
// src/presenters/pages/Post.php
use Taujor\PHPSSG\Contracts\Buildable;

class Post extends Buildable
{
    public function __construct(private Base $base, private Title $title, private Body $body) {}

    public function __invoke(object $data): string
    {
        return ($this->base)(
            ($this->title)($data->title) . ($this->body)($data->content)
        );
    }
}
```

Pages often combine components and layouts. The `$data` argument is passed to the page during the build process. `$data` is then passed to components via their respective `__invoke()` methods. Then finally the layout wraps the combined HTML content.

### Utilities

Utilities are typically helper classes that provide additional functionality to phpssg. You can use utility classes to implement things like alternative renderers (twig for example), provide custom methods to your components, etc.

```php
// src/utilities/Locate.php
<?php namespace Taujor\PHPSSG\Utilities;

use Phar;
use Composer\Factory;

class Locate {
    private static ?string $root = null;

    public static function root(): string {
        if(self::$root === null) Phar::running() ? self::$root = getcwd() : self::$root = dirname(Factory::getComposerFile());
        return self::$root;
    }
}
```

This utility class can be used to locate the root of a composer project, this example is derrived from the `Locate` utility class used throughout this project, see the "build scripts" section for details on how `Locate` is used for configuration.

You can even extend an abstract class such as `Renderable` to support entirely different templating systems such as twig or markdown and using your extended class in your components instead of the defaults. In a future release there will be separate offically maintained integrations you can install via composer that will extend PHPSSG's core features this way. Don't let that stop you from creating your own that are specfic to your individual project. 

```php
// EXAMPLE GOES HERE
```
---

### Views

Stored in **`src/views`** (by default). Only **Renderables** have view templates. **Composables** and **Buildables** don't require view templates. Views are plain php templates, I recommend their directory structure mirrors that of the `Renderables` in your `presenters/` directory for example:

```text
src/
└── presenters/
    ├── components/
    │   └── Title.php
    ├── layouts/
    │   └── Base.php
    ├── pages/
    │   └── Home.php
    views/
    ├── components/
    │   └── title.php
    └── layouts/
        └── base.php
```
---

### Hooks

There are currently four hooks available in the current version of PHPSSG. They include:
- `_beforeRender`
- `_afterRender`
- `_beforeWrite`
- `_afterWrite`

---

### Build scripts

This is the entry point of the application often placed in the `scripts` directory at the root of your project. They call on **Buildable** presenters to generate html using the `compile` or `build` static methods. The `compile` method takes a target path relative to your build directory, the generated html will be sent to this path. The second parameter supports either an `array` or `object` containing data you would like to `__invoke` the `Buildable` with. The `build` method simply iterates over your dataset and runs `compile` on each item. You can also use **placeholder** syntax to use any top level value in your dataset to generate unique filenames. For example:

```php

<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use Presenters\Pages\Post;

$data = [
    (object) [
        'id' => 1,
        'slug' => 'first-post',
        'title' => 'First Post',
        'content' => 'Hello'
    ],
    (object) [
        'id' => 2,
        'slug' => 'second-post',
        'title' => 'Second Post',
        'content' => 'World'
    ],
];

// {{}} are the default delimiters for placeholders you can set any delimiters you like separated by whitespace
// creates two files in the build directory "posts/post-1.html" and "posts/post-2.html"
Post::build("/posts/post-{{id}}.html", $data, "{{ }}"); 

// compile also supports placeholders
Post::compile("/posts/post-3.html", (object)[
    'id' => 3,
    'slug' => 'third-post',
    'title' => 'Third Post',
    'content' => 'Hello Again'
]);

```

---

## Project Structure

There are no mandated directory names or conventions, you can decide how you like to structure your project. However I have provided some sane default directories you can see in the next section that will work with zero configuration.

### Suggested Structure 

There are some directories PHPSSG needs to have around, those being a views, build, and cache directory (marked as **REQUIRED**). You are free to reconfigure them as needed but if they are missing the project will not work as expected. The cache directory is automatically created during runtime if it doesn't already exist.

- **`config/`** – bootstrap and environment setup.  
- **`public/`** – **REQUIRED** build directory, CSS, JS, and other web-facing assets. 
- **`scripts/`** – build scripts and/or other tooling.  
- **`presenters/`** – invokable PHP classes for logic (can be Buildable, Composable, or Renderable).  
- **`presenters/components/`** – reusable UI blocks typically Renderables or Composables.  
- **`presenters/layouts/`** – reusable page skeletons typically Renderables.  
- **`presenters/pages/`** – page-level composables or buildables.  
- **`views/`** – **REQUIRED** plain php templates.
- **`views/components/`** – templates for Renderable components.  
- **`views/layout/`** – templates for Renderable layouts.  
- **`utilities/`** – helpers like `Locate`, `TwigRenderer`, `PrettyPrint`. Make them do anything!

### Configuration

At the entry point of the PHPSSG application (commonly the build script) you can use the `Locate` class to tinker with your project layout.

```php

<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use Presenters\Pages\Post;

$rootDir = Locate::root(__DIR__);
$viewsDir = Locate::views("/src/custom_views"); // relative to root - returns __DIR__ . "/src/custom_views"
$cacheDir = Locate::cache("/custom_cache"); // relative to root - returns "/src/custom_views/custom_cache"
$hashDir = Locate::hashes("/custom_hashes"); // relative to cache - returns "/src/custom_views/custom_cache/custom_hashes"
$proxyDir = Locate::proxies("/custom_proxies"); // relative to cache - returns "/src/custom_views/custom_cache/custom_proxies"
$buildDir = Locate::build("/custom_build"); // relative to root - returns __DIR__ . "/src/custom_views"
$templateExt = Locate::engine(".twig"); // configures the file extenstion expected by the render method of Renderable

Post::compile("/posts/post-1.html", (object)[
    "id" => 1,
    "slug" => "first-post",
    "title" => "First Post",
    "content" => "Hello World"
]); 

```
As you can see above the `Locate` utility class provides static methods that can both override and return the configured location. Once an override is set further overrides will be ignored for the duration of the runtime, you can bypass this using `Locate::reset()` which will revert all changes to default. 

`Locate` is used througout PHPSSG to get configuration data, it works lazily, inteligently setting defaults the first time it is called and caching them in memory, subsequent calls always return the cached location rather than whatever is in the `$override` argument or expected by default. Calling `Locate::reset()` clears this cache setting each static variable to null. Then when a method of `Locate` is called again it will either set and return it's defaults or use a supplied override.

Here is a list of `Locate`'s default settings:

### Default Configuration

| Property | Method | Default Path | Relative To |
|-----------|---------|---------------|--------------|
| `$root` | `root()` | *(project root)* | — |
| `$views` | `views()` | `/src/views` | `root()` |
| `$cache` | `cache()` | `/cache` | `root()` |
| `$hashes` | `hashes()` | `/cache/hashes` | `cache()` |
| `$proxy` | `proxies()` | `/cache/proxies` | `cache()` |
| `$build` | `build()` | `/public` | `root()` |
| `$engine` | `engine()` | `.php` | — |

---

## Contributing

Contributions are welcome! Philosophy:

- **Minimalism First** – lightweight and simple, avoid heavy libraries.  
- **Developer-Friendly** – components should be easy to understand and compose.  
- **Consistency** – follow `presenters/`, `views/`, `utilities/` structure.  
- **Backward Compatibility** – avoid breaking APIs unless clearly beneficial.

---

## Planned Features
- [*] **Packagist Release** - use composer to install phpssg with ease.
- [*] **Hooks** - add extensibility to the build process.
- [*] **Caching** – reduce build times for large projects.  
- [ ] **Documentation Website** – phpssg.com for guides and community resources.  
- [ ] **Templates** – premade templates to start projects quickly.  
- [ ] **Tutorials** – step-by-step guides on using PHPSSG effectively.  