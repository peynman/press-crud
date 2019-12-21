<?php

namespace Larapress\CRUD\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Permission.
 *
 * @property $id
 * @property string $name
 * @property string $verb
 * @property Role[] $roles
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'verb',
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
