<?php

namespace App\Models\Dashboard;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    public $table = 'projects';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at', 'created_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function purchases()
    {
        return $this->hasMany( \App\Models\Dashboard\Purchase::class, 'project', 'code' );
    }
}
