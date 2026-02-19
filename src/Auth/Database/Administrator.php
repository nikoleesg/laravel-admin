<?php

namespace Encore\Admin\Auth\Database;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravolt\Avatar\Avatar;
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

    protected string $typeColumn = 'type';

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
     * @param  string  $avatar
     * @return string
     */
    public function getAvatarAttribute($avatar = null)
    {
        try {
            $avatar = new Avatar;

            $name = $this->name ?: $this->username ?: 'User';

            return $avatar
                ->create($name)
                ->setTheme('colorful')
                ->setDimension(160, 160)
                ->setFontSize(72)
                ->toBase64();
        } catch (\Exception $e) {
            return '';
        }
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
