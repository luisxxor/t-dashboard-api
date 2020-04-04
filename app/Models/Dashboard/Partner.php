<?php

namespace App\Models\Dashboard;

use App\Models\Dashboard\Project;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    public $table = 'partners';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function projects()
    {
        return $this->belongsToMany( Project::class, 'partner_project', 'partner_code', 'project_code' );
    }
}
