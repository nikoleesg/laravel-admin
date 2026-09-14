# Changelog

All notable changes to `nikoleesg/laravel-admin` are recorded here and under
[GitHub releases](https://github.com/nikoleesg/laravel-admin/releases).
Versions follow [SemVer](https://semver.org/); tags are `vX.Y.Z` on `main`.

## v3.0.0 — 2026-09-14

First release cut from `main`. A major bump relative to the frozen 1.8.x line:
the platform floor, the `admin_users` schema and several defaults changed.

### Breaking

- Requires PHP `^8.3` and Laravel `^11.0 || ^12.0` (1.8.x allowed PHP >= 8.1 and Laravel 8–12).
- `Administrator` uses single-table inheritance (`tightenco/parental`) and gains profile columns
  (`first_name`, `last_name`, `gender`, `lat`, `lng`, …); run `php artisan migrate` after upgrading.
- Generated avatars are served from a cached route instead of inline GD renders on every access;
  `admin.default_avatar` is now only the fallback when avatar generation is disabled.
- `Form\Field\Latlong` (an unfinished stub) is removed.
- The `ShareErrors` middleware is removed (redundant with Laravel's `web` group).
- Package route names no longer depend on `admin.route.prefix`.

### Added

- Upstream extensions inlined into the package, each toggled under `config('admin.extensions')`:
  grid `->sortable()`, `->datatable()`, `->dateRange()`, `->lightbox()`; form `->daterangepicker()`;
  filter `->timestampBetween()`.
- Form fields: `numberRange`, `listbox`, `divider` and `keyValue` in Action forms.
- Grid help button with controller-provided content.
- Configurable STI child-type map for `Administrator`; `Notifiable` on the model.
- Configurable Intervention image driver.
- GitHub Actions CI (PHP 8.3/8.4 × Laravel 11/12 + `pint --test`).

### Fixed / security

- `_sort` input is validated before it reaches raw `ORDER BY` SQL.
- `GridSortableController` signs and validates the model class server-side.
- Titles in box/panel/tab/modal views and the Lightbox `src` are escaped.
- `HasPermissions::cannot()` is compatible with `Authorizable::cannot()`.
- `admin:make` / `ResourceGenerator` work on Laravel 11+ (native schema API).
- `Grid::with()` merges view variables instead of replacing them.
- Lang files publish to the application's `langPath()` on Laravel 12.
- Implicitly-nullable parameters declared explicitly (PHP 8.4 clean).

## 1.8.x (frozen)

`v1.8.20`, `v1.8.21` and `v1.8.22` are the fork's releases on the upstream 1.8
codebase, kept on the `release/1.8` branch. No further 1.8 releases are planned.

For the history of the original `z-song/laravel-admin` see
<https://github.com/z-song/laravel-admin> and <https://laravel-admin.org/docs/>.
