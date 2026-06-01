# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this repo is

`nikoleesg/laravel-admin` is a **fork of `z-song/laravel-admin`** — a Laravel package that generates a CRUD admin UI (grids, forms, detail views, trees) from a few lines of code. You are developing **the package itself**, not an app that uses it.

Despite the vendor name, the PHP namespace is still `Encore\Admin\` (mapped to `src/` via PSR-4). The Composer package is `nikoleesg/laravel-admin`; the published Laravel provider is `Encore\Admin\AdminServiceProvider` and the facade alias is `Admin`.

## Branching & development workflow

Remotes: `origin` = this fork (`github.com/nikoleesg/laravel-admin`, push here); `upstream` = `github.com/z-song/laravel-admin` (pull updates here only).

Branches and their roles:
- `master` — pristine mirror of z-song's `master` (tracks `upstream/master`); never developed on directly.
- `main` — the release branch; what apps/Packagist install via `dev-main`. Default branch on the fork.
- `dev` — where all development happens.

**All new work goes on `dev`.** When a change is done and tested:

```bash
git checkout main && git merge --ff-only dev   # main must stay a fast-forward of dev
git push origin main
git push origin dev
```

**Absorbing upstream (z-song) changes:**

```bash
git checkout master && git pull upstream master   # refresh the mirror
git checkout dev && git merge master              # bring upstream changes into dev
```

Do not push `master` to `origin` — it carries z-song's full history (including long-public dummy test credentials in old commits) and would be rejected by GitHub push protection; it exists only as a local upstream mirror.

## Requirements & tooling

`composer.json` is the source of truth (the README, `AGENTS.md`, and `ANTIGRAVITY.md` still cite the upstream PHP 7.0 / Laravel 5.5 era — that is stale):

- PHP `^8.3`, Laravel `^11.0 || ^12.0`
- Notable deps: `tightenco/parental` (STI), `laravolt/avatar`, `spatie/eloquent-sortable`, `doctrine/dbal`, `symfony/dom-crawler`

## Commands

```bash
composer install                          # install deps
composer test                             # run the full suite (./vendor/bin/phpunit)
./vendor/bin/phpunit tests/UsersTest.php  # single test file
./vendor/bin/phpunit --filter=testMethodName
./vendor/bin/pint                         # format code (laravel/pint is the linter; no pint.json, uses defaults)
./vendor/bin/pint --test                  # check formatting without writing
```

Note: `phpunit.xml.dist` sets `stopOnFailure="true"`, so the run halts at the first failure.

## Testing model (important — differs from older docs)

`tests/TestCase.php` is the real reference. It boots a Laravel app via `CreatesApplicationTrait`, runs against a **SQLite `:memory:` database** (NOT MySQL, despite what `AGENTS.md`/`ANTIGRAVITY.md` say), and on each `setUp()`:

1. publishes the package assets/config (`vendor:publish --force`),
2. runs `admin:install` (creates the admin tables + default `admin/admin` user),
3. migrates `tests/migrations/`, then loads `tests/routes.php` and `tests/seeds/factory.php`.

Feature tests extend `Tests\TestCase` and use **BrowserKit** style (`laravel/browser-kit-testing`): `$this->visit('/admin/users')->see('...')`, `seeInDatabase()`, etc. Test fixtures live in `tests/models`, `tests/controllers`, `tests/migrations`, `tests/seeds`, `tests/config`.

## Architecture

The package centers on four builders, each a top-level class in `src/` backed by a subdirectory of components:

- **`Grid` / `src/Grid/`** — listing tables. Columns (`Grid\Column`), filters (`Grid\Filter/`), tools, exporters, row actions. Built up via `Concerns/` traits (`HasFilter`, `HasTools`, `HasHeader`, etc.).
- **`Form` / `src/Form/`** — create/edit forms. Each input is a `Form\Field` subclass; fields are fluent (return `$this`) and chainable.
- **`Show` / `src/Show/`** — read-only detail pages (`Show\Field`, `Show\Panel`).
- **`Tree` / `src/Tree/`** — nested/sortable hierarchical models.

Supporting pieces: `src/Admin.php` (the central registry/facade target — assets, menu, extensions, boot callbacks), `src/Layout/` (page scaffolding, `Content`), `src/Widgets/` (Box, Navbar, Table, charts…), `src/Auth/` (auth models incl. `Administrator`, permissions, roles), `src/Controllers/` (base CRUD controllers users extend), `src/Middleware/`, `src/Actions/`, `src/Console/` (Artisan generators), `src/Traits/` (`HasAssets`, etc.), `src/helpers.php` (global helpers like `admin_path()`, autoloaded).

### Service provider boot flow

`AdminServiceProvider` registers all Artisan commands, route middleware (`admin.auth`, `admin.pjax`, `admin.permission`, `admin.bootstrap`, `admin.log`, …) and the `admin` middleware group, loads views/translations, loads the published `admin_path('routes.php')`, and enables the natively-integrated fork extensions (see below). It also defines `@box`/`@endbox` Blade directives.

## Fork-specific native enhancements

This fork inlines several upstream "extensions" directly into the package. When changing `Grid`, `Form`, `Show`, or layout/asset code, verify these still work:

- **STI** on the `Administrator` model via `tightenco/parental`, plus extra profile columns (`first_name`, `last_name`, `gender`, `lat`, `lng`, …).
- Grid: `->sortable()`, `->datatable()`, `->dateRange()`, picture `->lightbox()` (grid-lightbox; assets registered in the provider).
- Form/Show: `->latlong()` picker (`src/Latlong/`), DateRangePicker.
- Filter: `->timestampBetween()` — registered via `Grid\Filter::extend('timestampBetween', ...)` and gated by `config('admin.extensions.timestamp-between.enable')`. Grid-lightbox is likewise gated by `config('admin.extensions.grid-lightbox.enable')`.

These toggles live under the `extensions` key of `config/admin.php`. New self-contained features should follow the same pattern: register conditionally in the provider, default-on, with a config gate.

## Conventions

- PSR-4 in `src/` → `Encore\Admin\`. Explicit `use` imports (no inline FQCNs), grouped/alphabetized; short array syntax.
- Builder/field/column classes are fluent — methods that configure state return `$this`.
- Compose behavior with `Concerns`/`Traits` rather than inheritance where the codebase already does so.
- Blade views in `resources/views`; front-end assets in `resources/assets`. Lang files in `resources/lang`.
- See `AGENTS.md` for the full upstream style guide (accurate on style; **ignore its version/MySQL claims**).
