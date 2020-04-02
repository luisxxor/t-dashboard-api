<?php

namespace App\Models\Subscriptions;

use App\Models\Subscriptions\PlanSubscriptionUsage;
use App\Traits\Dashboard\HasReceipt;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Rinvex\Subscriptions\Models\PlanSubscription as RinvexPlanSubscription;
use Rinvex\Subscriptions\Models\PlanSubscriptionUsage as RinvexPlanSubscriptionUsage;

class PlanSubscription extends RinvexPlanSubscription
{
    use HasReceipt;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'user_id',
        'user_type',
        'partner_project_plan_id',
        'slug',
        'name',
        'description',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'cancels_at',
        'canceled_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'user_id' => 'integer',
        'user_type' => 'string',
        'partner_project_plan_id' => 'integer',
        'slug' => 'string',
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancels_at' => 'datetime',
        'canceled_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct( array $attributes = [] )
    {
        parent::__construct( $attributes );

        $this->setTable( config( 'rinvex.subscriptions.tables.plan_subscriptions' ) );
        $this->setRules( [
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:10000',
            'slug' => 'required|alpha_dash|max:150|unique:' . config( 'rinvex.subscriptions.tables.plan_subscriptions' ) . ',slug',
            'partner_project_plan_id' => 'required|integer|exists:partner_project_plan,id',
            'user_id' => 'required|integer',
            'user_type' => 'required|string',
            'trial_ends_at' => 'nullable|date',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date',
            'cancels_at' => 'nullable|date',
            'canceled_at' => 'nullable|date',
        ] );
    }

    /**
     * The model always belongs to a plan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partnerProjectPlan(): BelongsTo
    {
        return $this->belongsTo( \App\Models\Subscriptions\PartnerProjectPlan::class );
    }

    /**
     * Record feature usage.
     *
     * @param string $featureSlug
     * @param int    $uses
     *
     * @return \App\Models\Subscriptions\PlanSubscriptionUsage
     */
    public function recordFeatureUsage( string $featureSlug, int $uses = 1, bool $incremental = true ): RinvexPlanSubscriptionUsage
    {
        $feature = $this->partnerProjectPlan->plan->features()->where( 'slug', $featureSlug )->first();

        $usage = $this->usage()->firstOrNew( [
            'subscription_id' => $this->getKey(),
            'feature_id' => $feature->getKey(),
        ] );

        if ( $feature->resettable_period ) {
            // Set expiration date when the usage record is new or doesn't have one.
            if ( is_null( $usage->valid_until ) ) {
                // Set date from subscription creation date so the reset
                // period match the period specified by the subscription's plan.
                $usage->valid_until = $feature->getResetDate( $this->created_at );
            } elseif ( $usage->expired() ) {
                // If the usage record has been expired, let's assign
                // a new expiration date and reset the uses to zero.
                $usage->valid_until = $feature->getResetDate( $usage->valid_until );
                $usage->used = 0;
            }
        }

        $usage->used = ( $incremental ? $usage->used + $uses : $uses );

        $usage->save();

        return $usage;
    }

    /**
     * Determine if the feature can be used.
     *
     * @param string $featureSlug
     *
     * @return bool
     */
    public function canUseFeature( string $featureSlug ): bool
    {
        $featureValue = $this->getFeatureValue( $featureSlug );
        $usage = $this->usage()->byFeatureSlug( $featureSlug )->first();

        if ( $featureValue === 'true' ) {
            return true;
        }

        $isUsageExpired = $usage instanceof PlanSubscriptionUsage ? $usage->expired() : false;

        // If the feature value is zero, let's return false since
        // there's no uses available. (useful to disable countable features)
        if ( $isUsageExpired || is_null( $featureValue ) || $featureValue === '0' || $featureValue === 'false' ) {
            return false;
        }

        // Check for available uses
        return $this->getFeatureRemainings( $featureSlug ) > 0;
    }

    /**
     * Get how many times the feature has been used.
     *
     * @param string $featureSlug
     *
     * @return int
     */
    public function getFeatureUsage( string $featureSlug ): int
    {
        $usage = $this->usage()->byFeatureSlug( $featureSlug )->first();

        $isUsageExpired = $usage instanceof PlanSubscriptionUsage ? $usage->expired() : false;

        $used = $usage instanceof PlanSubscriptionUsage ? $usage->used : 0;

        return ! $isUsageExpired ? $used : 0;
    }

    /**
     * Get feature value.
     *
     * @param string $featureSlug
     *
     * @return mixed
     */
    public function getFeatureValue( string $featureSlug )
    {
        $feature = $this->partnerProjectPlan->plan->features()->where( 'slug', $featureSlug )->first();

        return $feature->value ?? null;
    }

    /**
     * Set 'pending' status in PlanSubscription.
     *
     * @return \App\Models\Subscriptions\PlanSubscription
     */
    public function setPendingStatus()
    {
        # TODO

        return $this;
    }

    /**
     * Set 'released' status in PlanSubscription.
     *
     * @return \App\Models\Subscriptions\PlanSubscription
     */
    public function setReleasedStatus()
    {
        # TODO

        return $this;
    }
}
