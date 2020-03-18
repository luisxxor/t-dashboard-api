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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function projects()
    {
        return $this->belongsToMany( Project::class, 'partner_project', 'partner_code', 'project_code' );
    }

    /**
     * Checks if the partner has the given project associated.
     *
     * @param  Project|string  $project
     * @return boolean
     */
    // public function hasProject( $project ): bool
    // {
    //     if ( $project instanceof Project ) {
    //         $project = $project->code;
    //     }
    //     return (bool)$this->projects->where( 'code', $project )->count();
    // }
}
