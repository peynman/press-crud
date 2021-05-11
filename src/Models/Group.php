<?php

namespace Larapress\CRUD\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Larapress\CRUD\ICRUDUser;

/**
 * Class Group.
 *
 * @property $id
 * @property string $name
 * @property string $title
 * @property int    $flags
 * @property int    $author_id
 * @property ICRUDUser[] $members
 * @property ICRUDUser[] $owners
 * @property ICRUDUser[] $admins
 * @property ICRUDUser $author
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class Group extends Model
{
    use SoftDeletes;

    const FLAGS_OWNER = 1;
    const FLAGS_ADMIN = 2;

    protected $table = 'groups';

    protected $fillable = [
        'author_id',
        'name',
        'data',
        'flags',
    ];

    public $casts = [
        'data' => 'array',
    ];

    public $appends = [
        'owner_ids',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function members()
    {
        return $this->belongsToMany(
            config('larapress.crud.user.class'),
            'user_group',
            'group_id',
            'user_id'
        )->withPivot(['flags']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function owners()
    {
        return $this->members()->where('flags', '&', Group::FLAGS_OWNER);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function admins()
    {
        return $this->members()->where('flags', '&', Group::FLAGS_ADMIN);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(
            config('larapress.crud.user.class'),
            'author_id',
        );
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getOwnerIdsAttribute()
    {
        $owners = $this->owners()->select('id')->pluck('id');
        $admins = $this->owners()->select('id')->pluck('id');
        return array_merge($owners, $admins);
    }
}
