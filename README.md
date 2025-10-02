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

Generates a minified html "index.html" file in "/public" 
```bash
php scripts/build.php
```

Running a development server
```bash
php -S localhost:8080 public/index.html 
```

## Thinking Behind The Structure

- **`presenters/`**: Each PHP class is an **invokable component**.  
  - Example: `Header.php` holds the logic for rendering a `<header>`.  
  - These presenters pass data to the corresponding **view template**.  

- **`views/`**: Contains the **raw PHP templates** (markup only).  
  - Example: `header.php` defines the actual HTML structure for the header.  
  - Views don’t contain business logic they just display what presenters feed them.  

- **`utilities/`**: Small helpers like `Html` that handle repetitive tasks (e.g. output buffering and rendering).  

- **`layouts/`**: Reusable page skeletons. Presenters (like `Base.php`) map to layout views (like `base.php`).  

- **`pages/`**: Page-level presenters that compose components and layouts together to form a complete page.  

- **`public/`**: Web-facing assets and build directory. HTML entry points, CSS, JS. Keeps PHP source code out of the document root.  

- **`config/`**: Bootstrap and setup code (autoloaders, environment, etc).  

- **`scripts/`**: Development/build or other server-side tooling.  

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
- [] **Interfaces** - Add "Renderable" & "Composable" interfaces to assist in defining components and setting expectations as to their API 
- [] **Intelisense support** - Add docblocks to traits & interfaces
- [] **Tutorials** - helpful for implementing additional features outside the scope of this project, like localization, pagination, and markdown support.
