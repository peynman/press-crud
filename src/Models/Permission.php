<?php

namespace Larapress\CRUD\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Permission.
 *
 * @property $id
 * @property $name
 * @property $title
 * @property $group_name
 * @property $group_title
 * @property Role[] $roles
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'title',
        'group_name',
        'group_title',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_permission',
            'permission_id',
            'role_id'
        );
    }
}
