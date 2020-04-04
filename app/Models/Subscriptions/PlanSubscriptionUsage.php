<?php

namespace App\Models\Subscriptions;

use App\Models\Subscriptions\PlanFeature;
use Illuminate\Database\Eloquent\Builder;
use Rinvex\Subscriptions\Models\PlanSubscriptionUsage as RinvexPlanSubscriptionUsage;

class PlanSubscriptionUsage extends RinvexPlanSubscriptionUsage
{
    /**
     * Scope subscription usage by feature slug.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $featureSlug
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByFeatureSlug( Builder $builder, string $featureSlug ): Builder
    {
        $feature = PlanFeature::where( 'slug', $featureSlug )->first();

        $id = $feature instanceof PlanFeature ? $feature->getKey() : null;

        return $builder->where( 'feature_id', $id );
    }
}
