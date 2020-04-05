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
     * A model may have many active subscriptions.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function activeSubscriptions(): Collection
    {
        return $this->subscriptions->reject->inactive()->filter->released()->values();
    }

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
            'ends_at' => $plan->invoice_period === 0 ? null : $period->getEndDate(),
            'status' => $plan->isFree() === true ? config( 'constants.PLAN_SUBSCRIPTIONS.STATUS.RELEASED' ) : config( 'constants.PLAN_SUBSCRIPTIONS.STATUS.TO_PAY' ),
        ] );
    }

    /**
     * Get subscribed planProjects Ids.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function subscribedPlanProjectIds()
    {
        return $this->subscriptions->reject->inactive()->pluck( 'plan_project_id' )->unique();
    }

    /**
     * Get subscribed planProjects.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function subscribedPlanProjects()
    {
        return PlanProject::whereIn( 'id', $this->subscribedPlanProjectIds() )->get();
    }
}
