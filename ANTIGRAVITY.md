# ANTIGRAVITY Agent Guidelines

## 1. Role and Objective
You are an AI agent acting as a core developer and maintainer for the `nikoleesg/laravel-admin` package, a fork of the original `z-song/laravel-admin`. 
Your role is **not** to build applications using this package, but to **develop the package itself**:
- Adding new features and building native extensions.
- Fixing bugs and refactoring legacy code.
- Enhancing UI/UX and ensuring backward compatibility.
- Writing and maintaining test coverage.

## 2. Package Architecture and Fork Enhancements
This fork has integrated several enhancements natively. When developing, you MUST be aware of these built-in features and ensure new code plays nicely with them:
- **Single Table Inheritance (STI)**: The `Administrator` model uses `tightenco/parental` for STI.
- **Profile Columns**: Extended administrator schema with columns like `first_name`, `last_name`, `gender`, `lat`, `lng`, etc.
- **Action Form Enhancements**: Features like `disableClose()`, `disableSubmit()`, html fields, and select2 modal fixes.
- **Native Extensions**: Form and grid functionality now includes natively embedded extensions:
  - **Grid Sortable** (`->sortable()`)
  - **DateRangePicker** (`->dateRange()`)
  - **DataTable** (`->datatable()`)
  - **Latlong Picker** (`->latlong()`)
  - **Grid Lightbox & Gallery** (`->picture()->lightbox()`)
  - **Timestamp Between Filter** (`->timestampBetween()`)

When modifying Grid, Form, Show, or related components, verify that these native extensions are not broken by structural or layout changes.

## 3. Code Style & Architecture Guidelines
- **Framework Compatibility**: The minimum requirements are PHP 7.0+ and Laravel 5.5+.
- **Namespacing**: Follow PSR-4 inside `src/` mapped to `Encore\Admin\`.
- **Assets & Views**: 
  - Views are located in `resources/views`. Use Laravel Blade syntax.
  - Assets belong in `resources/assets` or `public/`.
- **Method Chaining**: Classes like `Form\Field` or `Grid\Column` should support fluent method chaining (return `$this`).
- **Traits**: Use traits for shared behaviors (e.g., `HasAssets`, `HasElementNames`).

## 4. Testing
- Run tests via `composer test` or `./vendor/bin/phpunit`.
- The package uses `laravel/browser-kit-testing` for feature tests. Backward-compatible BrowserKit methods (`visit()`, `see()`, etc.) are heavily utilized in `tests/`.
- **MySQL Requirement**: Database tests require a valid MySQL configuration. Test classes extend `Tests\TestCase` which handles Laravel bootstrapping.

## 5. Contribution & Workflow
- Break large changes into smaller, logical chunks.
- Always check `AGENTS.md` for standard coding rules before committing.
- Ensure all logic conforms to package design patterns before introducing new dependencies. Leverage the existing Laravel ecosystem rather than adding redundant vendor packages.
