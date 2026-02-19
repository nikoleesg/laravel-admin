# Enhancement: Administrator Model

## Profile Columns Added

The `Administrator` model now includes the following profile columns:

| Column | Type | Description |
|--------|------|-------------|
| `type` | string | STI type column (default: 'admin') |
| `first_name` | string | User's first name |
| `last_name` | string | User's last name |
| `preferred_name` | string | User's preferred name |
| `gender` | unsignedTinyInteger | Gender (1=Male, 2=Female, 3=Other) |
| `birth_date` | date | Date of birth |
| `nationality` | string | Nationality |
| `id_type` | unsignedTinyInteger | ID type (1=NRIC, 2=Passport, 3=FIN, 4=Other) |
| `id_number` | string | ID number |
| `photo` | string | Photo file path |
| `phone_number` | string | Phone number |
| `email` | string | Email address |
| `blk` | string | Block number |
| `street_name` | string | Street name |
| `unit` | string | Unit number |
| `postal` | string | Postal code |
| `lat` | decimal(10,7) | Latitude |
| `lng` | decimal(10,7) | Longitude |
| `preferred_areas` | text | Preferred areas |
| `description` | text | Description |

## Single Table Inheritance (STI) Support

This package uses [tightenco/parental](https://github.com/tighten/parental) for STI support.

### How STI Works

The `Administrator` model uses a `type` column to store the child class name. When you create a child model:

- Creating a `SuperAdmin` instance saves with `type = 'super_admin'`
- Querying `Administrator::all()` returns all users
- Querying `SuperAdmin::all()` returns only super admins

### Creating a Child Model

```php
// app/Models/SuperAdmin.php
namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;

class SuperAdmin extends Administrator
{
    // Automatically uses 'super_admin' as type value
}
```

### Using Child Models

```php
// Create a super admin
$superAdmin = SuperAdmin::create([
    'username' => 'super',
    'password' => bcrypt('password'),
    'name' => 'Super Admin',
    'email' => 'super@example.com',
]);

// Query specific type
$superAdmins = SuperAdmin::all();

// Query all admins (including child types)
$allAdmins = Administrator::all();

// Check instance type
if ($user instanceof SuperAdmin) {
    // Do something specific for super admins
}
```

### Configuration

The STI type column is configured via:

```php
// In Administrator model
protected $typeColumn = 'type';
```

The `type` column is also added to `$fillable` to allow mass assignment when creating users.

---

## Action Form Enhancements

### Disable Close/Submit Buttons

Added ability to disable close and submit buttons in action forms:

```php
public function form()
{
    $this->disableClose();
    $this->disableSubmit();
    // or both
    $this->disableClose()->disableSubmit();
    
    $this->text('name', 'Name');
}
```

### Select2 in Modal Fix

Added JavaScript fix to enable select2 search functionality within modals:

```javascript
$.fn.modal.Constructor.prototype.enforceFocus = function () {};
$("div[id^='grid-modal-']").removeAttr('tabindex');
$("div[id='modal']").removeAttr('tabindex');
```

### HTML Field Support

Added `Html` field support to inject custom HTML content in action forms:

```php
public function form()
{
    $this->html('<div class="alert alert-info">Custom HTML content</div>', 'Info');
    
    // or with closure
    $this->html(function () {
        return '<div>Dynamic content</div>';
    }, 'Label');
    
    $this->text('name', 'Name');
}
```

Note: The HTML field doesn't submit data - it's for display purposes only (warnings, instructions, etc.).
