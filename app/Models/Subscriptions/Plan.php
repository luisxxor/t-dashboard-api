<?php

namespace App\Models\Subscriptions;

use Rinvex\Subscriptions\Models\Plan as RinvexPlan;

class Plan extends RinvexPlan
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function partnerProjects()
    {
        return $this->belongsToMany( \App\Models\Dashboard\PartnerProject::class );
    }

    /**
     * @return \App\Models\Dashboard\PartnerProject
     **/
    public function partnerProject()
    {
        return $this->partnerProjects->first();
    }
}
