<?php

namespace Encore\Admin\Auth\Database;

use Encore\Admin\Auth\GeneratedAvatar;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Parental\HasChildren;

/**
 * Class Administrator.
 *
 * @property Role[] $roles
 * @property string $type
 */
class Administrator extends Model implements AuthenticatableContract
{
    use Authenticatable;
    use DefaultDatetimeFormat;
    use HasChildren;
    use HasPermissions;
    use Notifiable;

    protected $fillable = [
        'username',
        'password',
        'name',
        'type',
        'avatar',
        'first_name',
        'last_name',
        'preferred_name',
        'gender',
        'birth_date',
        'nationality',
        'id_type',
        'id_number',
        'photo',
        'phone_number',
        'email',
        'blk',
        'street_name',
        'unit',
        'postal',
        'lat',
        'lng',
        'preferred_areas',
        'description',
    ];

    /**
     * Column that Parental reads to resolve a row into a child class.
     *
     * @var string
     */
    protected $childColumn = 'type';

    /**
     * Single-table-inheritance map for `admin.database.user_types`.
     *
     * Keys are the short aliases stored in the `type` column, values are the
     * child classes (each must extend this model and use `Parental\HasParent`).
     * When a value in `type` is not present in the map, Parental treats it as a
     * fully-qualified class name; `null` resolves to this base model.
     *
     * @return array<string, class-string<static>>
     */
    public function childTypes(): array
    {
        return config('admin.database.user_types', []);
    }

    /**
     * Create a new Eloquent model instance.
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.users_table'));

        parent::__construct($attributes);
    }

    /**
     * Get avatar attribute.
     *
     * @param  string|null  $avatar
     * @return string
     */
    public function getAvatarAttribute($avatar = null)
    {
        if ($avatar && url()->isValidUrl($avatar)) {
            return $avatar;
        }

        $disk = config('admin.upload.disk');

        if ($avatar && array_key_exists($disk, config('filesystems.disks'))) {
            return Storage::disk($disk)->url($avatar);
        }

        if (GeneratedAvatar::enabled()) {
            return GeneratedAvatar::url($this->name ?: $this->username ?: 'User');
        }

        $default = config('admin.default_avatar') ?: '/vendor/laravel-admin/AdminLTE/dist/img/user2-160x160.jpg';

        return admin_asset($default);
    }

    /**
     * A user has and belongs to many roles.
     */
    public function roles(): BelongsToMany
    {
        $pivotTable = config('admin.database.role_users_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id');
    }

    /**
     * A User has and belongs to many permissions.
     */
    public function permissions(): BelongsToMany
    {
        $pivotTable = config('admin.database.user_permissions_table');

        $relatedModel = config('admin.database.permissions_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'permission_id');
    }
}
