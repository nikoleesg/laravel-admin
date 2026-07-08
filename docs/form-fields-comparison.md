# Form types & field matrix — the single source of truth

laravel-admin ships three distinct form builders. They're easy to confuse because they share a lot of surface API (fluent `->text()`, `->select()`, etc.), but they differ in what's bound behind them, how submission is handled, and which fields are even available.

- **Model form** — `Encore\Admin\Form`, used from `AdminController::form()`. Bound to an Eloquent model; the full create/edit CRUD page.
- **Data form** — `Encore\Admin\Widgets\Form`. A standalone, reusable form shell, not bound to any model.
- **Action form** — `Encore\Admin\Actions\Interactor\Form`, built via `Action::form()`. The dialog shown when a Grid row-action / batch-action button is clicked, always rendered inside a Bootstrap modal.

The **Model form** and **Data form** draw from the *same* field registry (`Form::$availableFields`, in `src/Form/Concerns/HasFields.php`) — `Widgets\Form::__call()` looks up methods from that exact static array. The **Action form** does not use that registry at all; it has its own hardcoded whitelist (`Interactor::$elements`) plus one hand-written method per field.

## Behaviour & architecture

| | **Model form**<br>`src/Form.php` | **Data form**<br>`src/Widgets/Form.php` | **Action form**<br>`src/Actions/Interactor/Form.php` |
|---|---|---|---|
| **Purpose** | Full CRUD create/edit page for an Eloquent model | Standalone, reusable form shell — not bound to any model | The dialog shown when an Action button / row-action is clicked |
| **Bound to a model?** | Yes — `new Form($model)` | No — a bare form; you decide what it does | No — belongs to an `Action`, not a model |
| **Where it renders** | A full page, via `AdminController::form()` | Anywhere you can call `->render()` — a `Content` page, a dashboard, a custom view | Always inside a Bootstrap modal (`resources/views/actions/form/modal.blade.php`), triggered by clicking the action |
| **Submission → persistence** | **Automatic.** `store()`/`update()` map each field's column straight to model attributes/relations and call `$model->save()` — no manual request handling | **Manual.** Define `handle(Request $request)` yourself; the framework only routes the POST back to it (`_handle_form_` + hidden `_form_` marker) | **Manual and mandatory.** `Action::handle(Request $request)` is the only way values get used — same pattern as Data form's `handle()`, but every Action must implement it |
| **Response on submit** | HTTP redirect (or JSON if pjax) back to the resource list | Whatever your `handle()` returns | Always JSON (`Actions\Response`), consumed by JS — toastr message, modal close, page reload — never a full-page redirect by default |
| **Layout helpers** | `tab()`, `row()`, `column()`, `fieldset()` | `fieldset()` only (no `tab()`/`row()`/`column()`) | None |
| **Unknown field method** | `admin_error(...)` banner, returns a dummy `Field\Nullable` — degrades gracefully | **Silently no-ops** — `__call` just `return $this` if the method isn't registered | Hard `\BadMethodCallException` thrown immediately (`Action::__call`) |
| **Field Blade views** | `resources/views/form/*.blade.php` (`admin::form.<type>`, horizontal label/field grid, error bag) | Same `admin::form.<type>` views as Model form (wrapper is `admin::widgets.form`) | Separate, minimal `admin::actions.form.*` (just `form-group` + label + input, no grid classes) |
| **Custom field via `Form::extend()`** | ✅ affects it | ✅ affects it (same registry) | ❌ no effect — ignores `$availableFields` entirely |
| **Fields supported** | **56** | **56** | **25** |

## Field-by-field availability

**Legend:** ✅ Supported · ⚠️ Reachable but not functional · ❌ Not available

- `wrap` — an inputmask/format convenience wrapper over `Text`/`Date`, not a distinct field class.
- `no model` — resolvable via the shared registry, but nothing to bind to without an Eloquent model.

### Text & basic inputs

| Field | Model | Data | Action | Note |
|---|:--:|:--:|:--:|---|
| `text` | ✅ | ✅ | ✅ | |
| `textarea` | ✅ | ✅ | ✅ | |
| `email` | ✅ | ✅ | ✅ | |
| `password` | ✅ | ✅ | ✅ `wrap` | Action masks a Text field |
| `ip` | ✅ | ✅ | ✅ `wrap` | Action masks a Text field |
| `url` | ✅ | ✅ | ✅ `wrap` | Action masks a Text field |
| `mobile` | ✅ | ✅ | ✅ `wrap` | Action masks a Text field |
| `hidden` | ✅ | ✅ | ✅ | |
| `id` | ✅ | ✅ | ❌ | |
| `color` | ✅ | ✅ | ❌ | |
| `icon` | ✅ | ✅ | ❌ | |
| `captcha` | ✅ | ✅ | ❌ | |

### Numeric

| Field | Model | Data | Action | Note |
|---|:--:|:--:|:--:|---|
| `number` | ✅ | ✅ | ❌ | |
| `integer` | ❌ | ❌ | ✅ `wrap` | Action-only wrapper over Text |
| `numberRange` | ✅ | ✅ | ✅ | |
| `currency` | ✅ | ✅ | ❌ | |
| `decimal` | ✅ | ✅ | ❌ | |
| `rate` | ✅ | ✅ | ❌ | |
| `slider` | ✅ | ✅ | ❌ | |

### Choice & selection

| Field | Model | Data | Action | Note |
|---|:--:|:--:|:--:|---|
| `select` | ✅ | ✅ | ✅ | |
| `multipleSelect` | ✅ | ✅ | ✅ | |
| `checkbox` | ✅ | ✅ | ✅ | |
| `radio` | ✅ | ✅ | ✅ | |
| `checkboxButton` | ✅ | ✅ | ❌ | |
| `checkboxCard` | ✅ | ✅ | ❌ | |
| `radioButton` | ✅ | ✅ | ❌ | |
| `radioCard` | ✅ | ✅ | ❌ | |
| `switch` | ✅ | ✅ | ❌ | |
| `tags` | ✅ | ✅ | ❌ | |
| `listbox` | ✅ | ✅ | ❌ | |

### Date & time

| Field | Model | Data | Action | Note |
|---|:--:|:--:|:--:|---|
| `date` | ✅ | ✅ | ✅ | |
| `datetime` | ✅ | ✅ | ✅ `wrap` | Action = date + format |
| `time` | ✅ | ✅ | ✅ `wrap` | Action = date + format |
| `year` | ✅ | ✅ | ❌ | |
| `month` | ✅ | ✅ | ❌ | |
| `dateRange` | ✅ | ✅ | ❌ | |
| `dateTimeRange` | ✅ | ✅ | ❌ | |
| `timeRange` | ✅ | ✅ | ❌ | |
| `daterangepicker` | ✅ | ✅ | ❌ | |
| `DateMultiple` | ✅ | ✅ | ❌ | |

### Files & media

| Field | Model | Data | Action | Note |
|---|:--:|:--:|:--:|---|
| `file` | ✅ | ✅ | ✅ | |
| `multipleFile` | ✅ | ✅ | ✅ | |
| `image` | ✅ | ✅ | ✅ | |
| `multipleImage` | ✅ | ✅ | ✅ | |

### Layout & display

| Field | Model | Data | Action | Note |
|---|:--:|:--:|:--:|---|
| `html` | ✅ | ✅ | ✅ | |
| `display` | ✅ | ✅ | ❌ | |
| `divider` | ✅ | ✅ | ✅ | |
| `button` | ✅ | ✅ | ❌ | |
| `fieldset` | ✅ | ✅ | ❌ | Layout helper (Model also: `tab`, `row`) |

### Complex & composite

| Field | Model | Data | Action | Note |
|---|:--:|:--:|:--:|---|
| `table` | ✅ | ✅ | ✅ | |
| `keyValue` | ✅ | ✅ | ✅ | Action: value arrives as `{keys, values}` — combine in `handle()` (no auto `prepare()`) |
| `list` | ✅ | ✅ | ❌ | |
| `timezone` | ✅ | ✅ | ❌ | |
| `embeds` | ✅ | ⚠️ `no model` | ❌ | No model → nothing to bind |
| `latlong` | ✅ | ✅ | ❌ | Fork-specific field |

### Relations · need an Eloquent model

| Field | Model | Data | Action | Note |
|---|:--:|:--:|:--:|---|
| `hasMany` | ✅ | ⚠️ `no model` | ❌ | |
| `morphMany` | ✅ | ⚠️ `no model` | ❌ | |
| `belongsTo` | ✅ | ⚠️ `no model` | ❌ | |
| `belongsToMany` | ✅ | ⚠️ `no model` | ❌ | |

## What the matrix implies

- **Model form is the superset.** The only builder with functional relation fields (`hasMany`, `belongsTo`, `belongsToMany`, `morphMany`) and `embeds` — because it's bound to a model. It also adds `tab()` and `row()` layout helpers.
- **Data form = same registry, no model.** Anything callable on a Model form resolves here too (identical `__call` lookup). But with plain array data and no relations, relation/`embeds` fields have nothing to bind to — reachable, not functional.
- **Action form is a fixed subset.** ~25 hand-written methods listed in `Interactor::$elements`. `integer`, `ip`, `url`, `mobile`, `password`, `datetime`, `time` are inputmask/format wrappers over `Text`/`Date` — not distinct field classes.
- **Unknown methods behave differently.** Model form shows an `admin_error` banner and returns a `Field\Nullable`; Data form silently no-ops (`return $this`); Action form throws a hard `BadMethodCallException`.

## Adding a custom field type

For the Model & Data forms, one call is enough — `Form::extend('name', MyField::class)` registers it in the shared array. The Action form has **no user-space extension point**; you must edit the framework:

1. Add the method name to `Interactor::$elements` in `src/Actions/Interactor/Interactor.php`.
2. Write a matching method in `src/Actions/Interactor/Form.php` that builds the field and calls `$this->addField($field)`.
3. Add a Blade view under `resources/views/actions/form/{name}.blade.php` — it can't reuse the Model/Data form view (no error grid, no `viewClass`).

**Source of truth:** `src/Form/Concerns/HasFields.php` (`$availableFields`) · `src/Actions/Interactor/Form.php`
