<?php

namespace App\Models\Subscriptions;

use Rinvex\Subscriptions\Models\Plan as RinvexPlan;

class Plan extends RinvexPlan
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function planProjects()
    {
        return $this->hasMany( \App\Models\Subscriptions\PlanProject::class );
    }
}
