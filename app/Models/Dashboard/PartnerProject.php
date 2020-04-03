<?php

namespace App\Models\Dashboard;

use Illuminate\Database\Eloquent\Model;

class PartnerProject extends Model
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
}
