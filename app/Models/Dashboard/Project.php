<?php

namespace App\Models\Dashboard;

use App\Models\BaseModel;

class Project extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'projects';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'code';

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
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function orders()
    {
        return $this->hasMany( \App\Models\Dashboard\Order::class, 'project', 'code' );
    }

    /**
     * Return is_free value.
     *
     * @return bool
     */
    public function isFree(): bool
    {
        return (bool)$this->is_free;
    }
}
