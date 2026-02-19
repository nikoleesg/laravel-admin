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

---

## Integrated Extensions

### 1. Grid Sortable

Sort grid rows by drag and drop.

```php
$grid->sortable();

// Or with custom column
$grid->sortable('order');
```

---

### 2. DateRangePicker

Enhanced date range picker for filters.

```php
$filter->dateRange('created_at', 'Created At');
```

---

### 3. DataTable

Advanced table display with server-side processing.

```php
$grid->datatable();
```

---

### 4. Latlong Picker

Select latitude and longitude on a map (Google Map & Amap only).

**Form Usage:**
```php
$form->latlong('latitude', 'longitude', 'Position');

// With options
$form->latlong('latitude', 'longitude', 'Position')->height(500);
$form->latlong('latitude', 'longitude', 'Position')->zoom(16);
```

**Show Page Usage:**
```php
$show->field('Position')->latlong('lat_column', 'long_column', $height = 400, $zoom = 16);
```

**Configuration (.env):**
```env
GOOGLE_API_KEY=your_google_api_key
AMAP_API_KEY=your_amap_api_key
```

---

### 5. Grid Lightbox & Gallery

Display images in a lightbox or gallery view.

```php
// Simple lightbox
$grid->picture()->lightbox();

// Gallery (multiple images)
$grid->picture()->gallery();

// With options
$grid->picture()->lightbox(['width' => 50, 'height' => 50]);
$grid->picture()->gallery(['zooming' => true]);
$grid->picture()->lightbox(['class' => 'rounded']);
```

---

### 6. Timestamp Between Filter

Filter by date range with timestamp conversion.

```php
$filter->timestampBetween('created_at', 'Created At')->datetime();
```

This filter converts date input to UNIX timestamps before querying the database, useful for columns that store timestamps (integers).

---

## Configuration

All extensions can be configured in `config/admin.php`:

```php
'extensions' => [

    'latlong' => [
        'enable' => true,
        'default' => 'google',
        'providers' => [
            'google' => [
                'api_key' => env('GOOGLE_API_KEY', ''),
            ],
            'amap' => [
                'api_key' => env('AMAP_API_KEY', ''),
            ],
        ],
    ],

    'grid-lightbox' => [
        'enable' => true,
    ],

    'timestamp-between' => [
        'enable' => true,
    ],
],
```
