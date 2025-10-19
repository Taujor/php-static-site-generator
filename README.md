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
- **Slots & data passing** – inject content into templates easily.  
- **Incremental builds** – files are rewritten only if content changes (xxh3 hash comparison).  
- **Caching** – file paths and contents are hashed and atomically cached to disk.  
- **Native debugging** – works seamlessly with standard PHP tools.  
- **PSR-4 compliant** – fully autoloadable via Composer.  
- **Abstract Classes** – `Renderable`, `Composable`, `Buildable` help define component APIs, they are fundamental to PHPSSG.  
- **IDE-friendly** – docblocks provide autocomplete, type hints, and method signatures.  
- **Highly portable** – works in any PHP 8.1+ environment.  
- **Flexible structure** – pages, components, and presenters can each be **Buildable**, **Composable**, or **Renderable** depending on your project.
- **Fast content comparison** – via xxh3 hashing.


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
<!-- src/presenters/layouts/Base.php -->
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
<!-- src/presenters/components/Heading.php -->
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

Utilities are typically helper classes that provide additional functionality to phpssg. You can use utility classes to implement different renderers, 

```php
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

This utility class can be used to locate the root of a composer project, it's currently already in use throughout the project.

---

### Views

- Stored in **`src/views/`** (mandatory directory).  
- Only **Renderables** have view templates. **Composables** and **Buildables** don't require view templates.  
- Views are plain php templates I recommend their directory structure mirrors that of `presenters/`:

```text
src/
└── views/
├── components/
│ └── title.php
└── layouts/
  └── base.php
```

- The `render()` method in **Renderable** extracts variables into the template and captures the output.  
- **No escaping is necessary** — all data comes from trusted sources.  

---

### Build scripts

This is the entry point of the application often placed in the `scripts` directory at the root of your project. They call on **Buildable** presenters to generate html. For example:

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

Post::build("/posts/post-{{id}}.html", $data);

Post::compile("/posts/post-3.html", (object)[
    'id' => 3,
    'slug' => 'third-post',
    'title' => 'Third Post',
    'content' => 'Hello Again'
]);

```

## Compile and Bulid methods

```php
// Compile a single page
// 1. target output path relative to the public directory (with optional placeholder syntax)
// 2. data to pass to the page, can be an object or associative array based on what your component expects to handle2
Post::compile("/posts/post1.html", (object)[
    "id" => 1,
    "slug" => "first-post",
    "title" => "First Post",
    "content" => "Hello"
]); 

$dataset = [
    (object) [
        'id' => 2,
        'slug' => 'second-post',
        'title' => 'Second Post',
        'content' => 'World'
    ],
    (object) [
        'id' => 3,
        'slug' => 'third-post',
        'title' => 'Third Post',
        'content' => 'Hello Again'
    ],
];

// Build multiple pages using placeholder syntax (on the first iteration the path would be `public/posts/first-post.html`)
Post::build("/posts/{{slug}}.html", $dataset);
```

- `compile(string $pattern, array|object $data = [])` writes a single file.  
- `build(string $pattern, iterable $dataset)` writes multiple files iteratively.  
**Placeholders** use the syntax `{{key}}` in the file path or filename:  
```php
"/posts/{{slug}}.html"
```
Each `{{key}}` is replaced with the corresponding key from the dataset item (array or object). Incremental builds ensure that files are only rewritten if content changes. Full caching support is in development.

---

### Required Directories

There are no required directories, you can decide how you like to structure your project. I have provided some sane default directories you can see in the next section that will work with zero configuration.

---

### Suggested Project Structure

- **`config/`** – bootstrap and environment setup.  
- **`public/`** – compiled HTML, CSS, JS; web-facing assets.  
- **`scripts/`** – development/build scripts.  
- **`presenters/`** – invokable PHP classes for logic (can be Buildable, Composable, or Renderable).  
- **`presenters/components/`** – reusable UI blocks typically Renderables or Composables.  
- **`presenters/layouts/`** – reusable page skeletons typically Renderables.  
- **`presenters/pages/`** – page-level composables or buildables.  
- **`views/components/`** – templates for Renderable components.  
- **`views/layout/`** – templates for Renderable layouts.  
- **`utilities/`** – helpers like `Locate`, `TwigRenderer`, `PrettyPrint`. Make them do anything!

This structure ensures:

- **Separation of logic vs markup**  
- **Composable, testable components**  
- **Safe public directory exposure**  
- **Maintainability and clarity**  

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
- [ ] **Documentation** – phpssg.com for guides and community resources.  
- [ ] **Templates** – premade templates to start projects quickly.  
- [ ] **Tutorials** – step-by-step guides on using PHPSSG effectively.  