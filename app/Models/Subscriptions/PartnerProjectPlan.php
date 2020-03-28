<?php

namespace App\Models\Subscriptions;

use Illuminate\Database\Eloquent\Model;

class PartnerProjectPlan extends Model
{
    public $table = 'partner_project_plan';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

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
    public function partnerProject()
    {
        return $this->belongsTo( \App\Models\Dashboard\PartnerProject::class );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function plan()
    {
        return $this->belongsTo( \App\Models\Subscriptions\Plan::class );
    }
}
