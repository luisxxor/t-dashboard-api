<?php

namespace App\Models\Dashboard;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class PartnerProject extends BaseModel
{
    public $table = 'partner_project';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at', 'created_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function partner()
    {
        return $this->belongsTo( \App\Models\Dashboard\Partner::class );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function project()
    {
        return $this->belongsTo( \App\Models\Dashboard\Project::class );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function requests()
    {
        return $this->hasMany( \App\Models\Dashboard\ProjectAccessRequest::class );
    }

    /**
     * Scope models by partner.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $partnerCode
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPartner( Builder $builder, string $partnerCode ): Builder
    {
        return $builder->where( 'partner_code', $partnerCode );
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
