<?php

namespace App\Models\Subscriptions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PlanProject extends Model
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    public $table = 'plan_project';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [ 'deleted_at' ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at', 'created_at', 'deleted_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function project()
    {
        return $this->belongsTo( \App\Models\Dashboard\Project::class );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function plan()
    {
        return $this->belongsTo( \App\Models\Subscriptions\Plan::class );
    }

    /**
     * Scope models by project.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $projectCode
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByProject( Builder $builder, string $projectCode ): Builder
    {
        return $builder->where( 'project_code', $projectCode );
    }
}
