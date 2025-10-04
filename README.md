# PHPSSG (Personal Home Page Static Site Generator)

A lightweight static site generator, and easy way to build **composable templates** in pure PHP.  
This project uses **invokable component classes** and **output buffering** to provide a clean, composable developer experience without a third-party templating engine.

## Features

- Plain PHP templates (no special syntax to learn)  
- Invokable components (`$header()`)  
- Centralized render helper (no repeated `ob_start()` / `ob_get_clean()`)  
- Nesting & composition (e.g. `Layout` contains `Header` + `content`)  
- Supports “slots” by passing data/HTML into templates  
- Highly portable, works well in any php environment
- Easy native debugging

## Requirements

- PHP 8.1+ (typed properties & short closures recommended)
- Composer (for autoloading)

## Usage

Install dependencies
```bash
composer install
```

Generates a minified html "index.html" file in "/public" 
```bash
php scripts/build.php
```

Running a development server
```bash
php -S localhost:8080 public/index.html 
```

## Build scripts

The `Builder` class compiles your `Renderable` or `Composable` pages into static HTML files. Your build scripts are plain PHP files you can add to the "scripts" directory. You'll find the default "build.php" there already.

```php
use Utilities\Builder;
use Presenters\Pages\Home;

Builder::compile(Home::class, "/index.html");
```

`Builder::compile()` works like a simple router, first you specify the page class, then the target output path relative to the `public` directory.

```php
Builder::build(
    [Home::class, "/hello/index.html"],
    [Home::class, "/world/index.html"]
);
```

With `Builder::build` you are able to run `Builder::compile` against multiple target paths. This is the recommended approach for generating your entire site in one call.

```php
require dirname(__DIR__) . "/config/bootstrap.php";
```

Before building your script make sure you require the "bootstrap.php" file since each script is an entry point to the application. If your script depends on another script that has already required bootstrap.php, you don’t need to require it again.

## Thinking Behind The Structure

- **`config/`**: Bootstrap and setup code (autoloaders, environment, etc).  

- **`public/`**: Web-facing assets and build directory. HTML entry points, CSS, JS. Keeps PHP source code out of the document root.  

- **`scripts/`**: Development/build or other server-side tooling.  

- **`contracts/`**: Holds interfaces that define shared behaviors across the application (e.g. `Renderable`, `Composable`).
These provide clear contracts for how different classes interact, making components (for example) interchangeable and easier to document as well as integrating with unit testing frameworks.

- **`presenters/`**: Each PHP class is an **invokable component**.  
  - Example: `Header.php` holds the logic for rendering a `<header>`.  
  - These presenters pass data to the corresponding **view template**.  

- **`components/`**: Reusable building blocks of the UI (buttons, headers, footers, etc.). Each component has a presenter `Header.php` and a view `header.php`. Components are composed into larger structures such as layouts and pages.  

- **`layouts/`**: Reusable page skeletons. Presenters (like `Base.php`) map to layout views (like `base.php`).  

- **`pages/`**: Page-level presenters that compose components and layouts together to form a complete page.  

- **`utilities/`**: Small helpers like `Renderer` that handle repetitive tasks (e.g. output buffering and rendering).  

- **`views/`**: Contains the **raw PHP templates** (markup only). They are organized according to their presenter counterparts.  
  - Example: `views/components/header.php` defines the actual HTML structure for the header component `presenters/components/Header.php`.  
  - Views don’t contain business logic they just display what presenters feed them.  

---

This structure gives you:
- A **clean separation of logic vs. markup**  
- **Composable components** (presenter + view pairings)  
- Easy maintainability (everything has a single place and responsibility)  
- A safe `public/` directory that only exposes what's necessary

## Contributing

Contributions are welcome! Whether it’s bug fixes, optimizations, or new ideas, we appreciate your help. Before submitting a pull request, please keep in mind the philosophy of this project:

- **Minimalism First** – The project is intentionally lightweight. Avoid adding heavy libraries or complex abstractions unless absolutely necessary for developer experience.  
- **Developer-Friendly Workflow** – Components should remain easy to understand and compose. Prefer clarity and simplicity over cleverness.  
- **Consistency** – Follow the existing structure: `presenters/` for logic, `views/` for templates, `utilities/` for helpers.  
- **Backward Compatibility** – Try not to break existing APIs unless there’s a clear improvement or refactor that benefits overall simplicity or developer experience.

---

## Planned Features
- [x] **Full PSR-4 compliance** - Fully adhere to the PSR-4 autoloading standard
- [x] **Interfaces** - Add "Renderable" & "Composable" interfaces to assist in defining components and setting expectations as to their API 
- [x] **Intelisense support** - Add docblocks to ~~traits~~ & interfaces
- [ ] **Templates** - Create opensource templates ready to use for multiple types of static website (brochure, blog, landing, etc).
- [ ] **Tutorials** - helpful for implementing additional features outside the scope of this project, like localization, pagination, and markdown support.
