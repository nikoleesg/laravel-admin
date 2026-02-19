# Agent Guidelines for laravel-admin

This document provides guidance for agentic coding agents working in this repository.

## Project Overview

laravel-admin is a Laravel package that provides a graphical interface to administer Laravel applications. It follows PSR-4 autoloading with namespace `Encore\Admin`.

## Build, Lint, and Test Commands

### Running Tests

```bash
# Run all tests
composer test
./vendor/bin/phpunit

# Run a single test file
./vendor/bin/phpunit tests/UsersTest.php

# Run a single test method
./vendor/bin/phpunit --filter=testMethodName

# Run tests with a specific configuration
./vendor/bin/phpunit --configuration phpunit.xml.dist

# Run tests matching a pattern
./vendor/bin/phpunit --filter="testCreate|testUpdate"
```

### Dependencies

```bash
# Install dependencies
composer install

# Update dependencies
composer update
```

### Code Style

This project does not have a configured linter. Code should follow PSR standards and match the existing codebase style.

## Code Style Guidelines

### PHP Version and Requirements

- Minimum PHP 7.0.0
- Laravel >= 5.5

### Namespace and Autoloading

- PSR-4 autoloading: `Encore\Admin\` maps to `src/`
- Test classes in `tests/` with `Tests\` namespace
- Helper functions in `src/helpers.php`

### Naming Conventions

```php
// Classes: PascalCase
class AdminServiceProvider {}
class UserGridTest {}

// Methods and properties: camelCase
public function getLongVersion() {}
protected $navbar;

// Constants: SCREAMING_SNAKE_CASE
const VERSION = '1.8.17';

// Trait names: PascalCase with "Trait" suffix (optional convention)
use HasAssets;
use HasValuePicker;
```

### Class Structure

```php
<?php

namespace Encore\Admin;

use Closure;
use Illuminate\Support\Facades\Auth;
// ... imports

/**
 * Class Admin.
 */
class Admin
{
    use HasAssets;

    /**
     * The Laravel admin version.
     *
     * @var string
     */
    const VERSION = '1.8.17';

    /**
     * @var Navbar
     */
    protected $navbar;

    /**
     * @var array
     */
    protected $menu = [];

    /**
     * Returns the long version of Laravel-admin.
     *
     * @return string The long application version
     */
    public static function getLongVersion()
    {
        return sprintf('Laravel-admin <comment>version</comment> <info>%s</info>', self::VERSION);
    }
}
```

### Imports

- Use explicit `use` statements for all classes
- Group Laravel framework imports together
- Sort imports alphabetically within groups
- Avoid fully qualified class names in code

```php
use Closure;
use Encore\Admin\Auth\Database\Menu;
use Encore\Admin\Controllers\AuthController;
use Encore\Admin\Layout\Content;
use Encore\Admin\Traits\HasAssets;
use Encore\Admin\Widgets\Navbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
```

### Documentation (PHPDoc)

All classes should have a PHPDoc block:

```php
/**
 * Class Admin.
 */
class Admin
{
}
```

Method PHPDoc should include:
- Description
- `@param` types (when applicable)
- `@return` type
- `@deprecated` tags for deprecated methods

```php
/**
 * @param $model
 * @param Closure $callable
 *
 * @return \Encore\Admin\Grid
 *
 * @deprecated since v1.6.1
 */
public function grid($model, Closure $callable)
{
    return new Grid($this->getModel($model), $callable);
}
```

### Type Declarations

- Use return type hints when possible (PHP 7.0+)
- Use nullable types with `?` for optional parameters
- Use docblocks for complex type hints

### Traits

Use traits for code reuse. Multiple traits can be combined with commas:

```php
use Concerns\HasElementNames,
    Concerns\HasHeader,
    Concerns\HasFooter,
    Concerns\HasFilter,
    Concerns\HasTools;
```

### Method Chaining

Methods that return `$this` should be chainable:

```php
$this->defaultAttribute('type', 'text')
    ->defaultAttribute('id', $this->id)
    ->defaultAttribute('name', $this->elementName ?: $this->formatName($this->column))
    ->defaultAttribute('value', old($this->elementName ?: $this->column, $this->value()));
```

### Error Handling

- Use Laravel's exception handling
- Throw specific exceptions: `InvalidArgumentException`, etc.
- Use `try-catch` for operations that may fail gracefully

### String Concatenation

Use single quotes for simple strings, double quotes when interpolation is needed:

```php
$icon = 'fa-pencil';
$html = '<i class="fa '.$this->icon.' fa-fw"></i>';
```

### Array Syntax

Use short array syntax `[]` for new arrays:

```php
protected $menu = [];
public static $extensions = [];
```

### Database and Eloquent

- Follow Laravel conventions for Eloquent models
- Use dependency injection for model instances
- Use the Query Builder for complex queries

### Views and Templates

- Blade templates in `resources/views`
- Use Laravel's Blade syntax
- Follow existing template patterns

### Testing Conventions

- Extend the package's `Tests\TestCase`
- Use `createApplication()` to bootstrap Laravel
- Use `setUp()` and `tearDown()` methods
- Test files in `tests/` directory
- Use descriptive test method names: `testMethodName`
- Legacy BrowserKit methods are bridged for backward compatibility (`visit()`, `see()`, `seeInDatabase()`, etc.)

```php
class UsersTest extends TestCase
{
    public function testUsers()
    {
        $this->visit('/admin/users')
            ->see('Users');
    }
}
```

### Configuration

- Package configuration in `config/admin.php`
- Merge with application config using `$app['config']->set()`

### Contributing

- All code changes should be submitted as pull requests
- Include tests with new features
- Break large PRs into smaller chunks
- Follow existing code style

## File Organization

```
src/
    Actions/         # Action classes
    Auth/             # Authentication
    Console/          # Artisan commands
    Controllers/      # Controllers
    Exception/        # Exception handlers
    Facades/          # Facade classes
    Form/             # Form builder
    Grid/             # Grid builder
    Layout/           # Layout components
    Middleware/       # HTTP middleware
    Show/             # Detail show
    Traits/           # Shared traits
    Tree/             # Tree structures
    Widgets/          # UI widgets
    Admin.php         # Main class
    Grid.php          # Grid class
    Form.php          # Form class
    Show.php          # Show class
    Tree.php          # Tree class
    helpers.php       # Helper functions
tests/
    *.php             # Test files
    controllers/      # Test controllers
    models/           # Test models
    migrations/       # Test migrations
    seeds/            # Test seeders
    config/           # Test configuration
```

## Important Notes

- This package requires a Laravel application to run tests
- Database tests require MySQL configuration (see `tests/TestCase.php`)
- Uses `laravel/browser-kit-testing` for feature tests
- Version: 1.8.17

## Known Issues

- Tests use backward-compatible BrowserKit-style methods (see `tests/TestCase.php`)
- Database tests require MySQL configuration (see `tests/TestCase.php`)
