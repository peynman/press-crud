<?php

namespace Larapress\CRUD\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class Roles.
 *
 * @property int $id
 * @property string $name
 * @property string $title
 * @property int $priority
 * @property int $author_id
 * @property Permission[] $permissions
 * @property ICRUDUser $author
 * @property ICRUDUser[] $users
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class Role extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'roles';

    public $fillable = [
        'name',
        'title',
        'priority',
        'author_id',
    ];

    /**
     * Undocumented function
     *
     * @return Factory
     */
    protected static function newFactory()
    {
        return RoleFactory::new();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permission',
            'role_id',
            'permission_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(
            config('larapress.crud.user.model'),
            'user_role',
            'role_id',
            'user_id'
        );
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(config('larapress.crud.user.model'), 'author_id');
    }
}
