<?php

namespace App\Models\Dashboard;

use App\Models\BaseModel;
use App\Models\Dashboard\Project;

class Partner extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'partners';

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
        'updated_at',
        'created_at',
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
