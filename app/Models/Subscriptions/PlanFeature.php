<?php

namespace App\Models\Subscriptions;

use Illuminate\Database\Eloquent\Builder;
use Rinvex\Subscriptions\Models\PlanFeature as RinvexPlanFeature;

class PlanFeature extends RinvexPlanFeature
{
    /**
     * Scope models by slug.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $slug
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySlug( Builder $builder, string $slug ): Builder
    {
        return $builder->where( 'slug', $slug );
    }
}
