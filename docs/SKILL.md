# Skills: laravel-admin

## Extend Administrator Model (STI Child)

### Create Child Model

Create a model that extends `Administrator`:

```php
// app/Models/SuperAdmin.php
namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;

class SuperAdmin extends Administrator
{
    //
}
```

### Type Value

By default, Parental stores the **fully-qualified class name** in the type column (e.g., `App\Models\SuperAdmin`).

To use custom aliases, define `$childTypes` in the parent model:

```php
// In Administrator model
protected $childTypes = [
    'super_admin' => SuperAdmin::class,
    'admin' => Administrator::class,
];
```

### Usage

```php
// Create user - type is automatically set
$user = SuperAdmin::create([
    'username' => 'super',
    'password' => bcrypt('password'),
    'name' => 'Super Admin',
]);

// Query specific type
$superAdmins = SuperAdmin::all();

// Query all users (including child types)
$allAdmins = Administrator::all();

// Check instance type
if ($user instanceof SuperAdmin) {
    // Handle super admin
}
```

---

## Form Input Fields Side-by-Side

Use Laravel Admin's `row()` method with `width()`:

```php
$form->row(function ($row) {
    $row->width(6)->text('first_name', 'First Name');
    $row->width(6)->text('last_name', 'Last Name');
});
```

### Width Guidelines

| Columns | Width per Field | Example |
|---------|-----------------|---------|
| 2 columns | 6 + 6 = 12 | first_name + last_name |
| 3 columns | 4 + 4 + 4 = 12 | |
| 1 column | 12 | Full width |

Note: Sum of widths must equal 12.

---

## Action Form Enhancements

### Disable Buttons

To disable close or submit buttons in action form modal:

```php
public function form()
{
    $this->disableClose();   // Hide close button and X button
    $this->disableSubmit();  // Hide submit button
    
    $this->text('name', 'Name');
}
```

### Add Custom HTML

To inject custom HTML content in action form:

```php
public function form()
{
    $this->html('<div class="alert alert-info">Warning message</div>', 'Notice');
    
    $this->text('name', 'Name');
}
```

Note: HTML field is for display only - it won't be submitted with the form.
