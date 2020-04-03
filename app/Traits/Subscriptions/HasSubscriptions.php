<?php

namespace App\Traits\Subscriptions;

use App\Models\Subscriptions\PlanProject;
use App\Models\Subscriptions\PlanSubscription;
use Illuminate\Database\Eloquent\Collection;
use Rinvex\Subscriptions\Services\Period;
use Rinvex\Subscriptions\Traits\HasSubscriptions as RinvexHasSubscriptions;

trait HasSubscriptions
{
    use RinvexHasSubscriptions;

    /**
     * Get a subscription by slug.
     *
     * @param string $subscriptionSlug
     *
     * @return \App\Models\Subscriptions\PlanSubscription|null
     */
    public function subscription( string $subscriptionSlug ): ?PlanSubscription
    {
        return $this->subscriptions()->where( 'slug', $subscriptionSlug )->first();
    }

    /**
     * Subscribe user to a new plan.
     *
     * @param string $subscription
     * @param \App\Models\Subscriptions\PlanProject $planProject
     *
     * @return \App\Models\Subscriptions\PlanSubscription
     */
    public function newSubscription( $subscription, PlanProject $planProject ): PlanSubscription
    {
        $plan = $planProject->plan;

        $trial = new Period( $plan->trial_interval, $plan->trial_period, now() );
        $period = new Period( $plan->invoice_interval, $plan->invoice_period, $trial->getEndDate() );

        return $this->subscriptions()->create( [
            'name' => $subscription,
            'plan_project_id' => $planProject->getKey(),
            'trial_ends_at' => $trial->getEndDate(),
            'starts_at' => $period->getStartDate(),
            'ends_at' => $period->getEndDate(),
        ] );
    }
}
