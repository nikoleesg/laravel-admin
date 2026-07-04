# Comparison: Model form vs Data form vs Action form

laravel-admin has three distinct form-building classes. They're easy to confuse because they share a lot of surface API (fluent `->text()`, `->select()`, etc.), but they differ significantly in what's bound behind them, how submission is handled, and which fields are even available.

- **Model form** — `Encore\Admin\Form`, used from `AdminController::form()`. Bound to an Eloquent model; the full create/edit CRUD page.
- **Data form** — `Encore\Admin\Widgets\Form`. A standalone, reusable form shell, not bound to any model.
- **Action form** — `Encore\Admin\Actions\Interactor\Form`, built via `Action::form()`. The dialog shown when a Grid row-action/toolbar action button is clicked, always rendered inside a Bootstrap modal.

## Design

| | **Model form**<br>`src/Form.php` | **Data form**<br>`src/Widgets/Form.php` | **Action form**<br>`src/Actions/Interactor/Form.php` |
|---|---|---|---|
| Purpose | Full CRUD create/edit page for an Eloquent model | Standalone, reusable form shell — not bound to any model | The dialog shown when an Action button/row-action is clicked |
| Bound to a model? | Yes — constructed with `new Form($model)` | No — a bare form; you decide what it does | No — belongs to an `Action`, not a model |
| Where it renders | A full page, via `AdminController::form()` | Anywhere you can call `->render()` — a `Content` page, a dashboard, a custom view | Always inside a Bootstrap modal (`resources/views/actions/form/modal.blade.php`), triggered by clicking the action |
| Submission → persistence | **Automatic.** `store()`/`update()` map each field's column straight to model attributes/relations and call `$model->save()` — no manual request handling | **Manual.** Define `handle(Request $request)` yourself; the framework only routes the POST back to it (`_handle_form_` + hidden `_form_` marker) | **Manual and mandatory.** `Action::handle(Request $request)` is the only way values get used — same pattern as Data form's `handle()`, but every Action must implement it |
| Response on submit | HTTP redirect (or JSON if pjax) back to the resource list | Whatever your `handle()` returns | Always JSON, consumed by JS (toastr message, modal close, page reload) — never a full-page redirect by default |
| Layout helpers | `tab()`, `fieldset()`, `row()` | `fieldset()` only (no `tab()`/`row()`) | None |
| Unknown field method called | `admin_error(...)` banner shown, returns a dummy `Field\Nullable` — degrades gracefully | **Silently no-ops** — `__call` just `return $this` if the method isn't registered, no error at all | Hard `\BadMethodCallException` thrown immediately (`Action::__call`) |
| Blade view namespace | `resources/views/form/*.blade.php` (full horizontal label/field grid, error bag) | Same `resources/views/form/*.blade.php` as Model form | Separate, minimal `resources/views/actions/form/*.blade.php` (just `form-group` + label + input, no grid classes) |

## Field availability

Model form and Data form **share the same registry** (`Encore\Admin\Form::$availableFields`, in `src/Form/Concerns/HasFields.php`) — `Widgets\Form::__call()` looks up methods from that exact static array. Action form does **not** use that registry at all; it has its own hardcoded whitelist (`Interactor::$elements`, in `src/Actions/Interactor/Interactor.php`) plus one hand-written method per field in `Actions\Interactor\Form`.

| Category | Model form | Data form | Action form |
|---|---|---|---|
| Basic inputs (text/email/password/textarea/hidden/url/ip/mobile) | ✅ | ✅ | ✅ |
| select/multipleSelect/checkbox/radio | ✅ | ✅ | ✅ (only the plain variants — `radioButton`/`radioCard`/`checkboxButton`/`checkboxCard` not whitelisted) |
| date/datetime/time | ✅ | ✅ | ✅ |
| dateRange/datetimeRange/timeRange | ✅ | ✅ | ❌ not in `Interactor::$elements` |
| numberRange | ✅ | ✅ | ✅ |
| number/currency/decimal/rate | ✅ | ✅ | ❌ none whitelisted |
| file/image/multipleFile/multipleImage | ✅ | ✅ | ✅ |
| Relationships: hasMany/belongsTo/belongsToMany/morphMany | ✅ | ⚠️ technically reachable via the shared registry, but meaningless/unsupported without a bound model+relation — will likely error at render/save time | ❌ |
| tags/icon/map/latlong/keyValue/listbox/captcha/timezone/table/embeds | ✅ | ✅ (shared registry) | ❌ |
| html (static display, not submitted) | ✅ | ✅ | ✅ |
| Custom fields via `Form::extend()` | ✅ affects it | ✅ affects it (same registry) | ❌ **no effect** — Action form ignores `$availableFields` entirely |

### Practical gotcha

To add a new field type to the Model form and Data form, `Form::extend('name', MyField::class)` is enough. To add one to Action forms, you must edit the framework itself:

1. Add the method name to `Interactor::$elements` in `src/Actions/Interactor/Interactor.php`.
2. Write a corresponding method in `src/Actions/Interactor/Form.php` that constructs the field and calls `$this->addField($field)`.
3. Add a matching Blade view under `resources/views/actions/form/{fieldname}.blade.php` — it cannot reuse the Model/Data form's view, since the variable/markup conventions differ (no `viewClass`, no error grid).

There's no user-space extension point for Action forms today.
