<?php

namespace App\Http\Resources\Subscriptions;

use App\Http\Resources\Subscriptions\Plan as PlanResource;
use App\Http\Resources\Subscriptions\SubscriptionUsage as SubscriptionUsageResource;
use Illuminate\Http\Resources\Json\JsonResource;

class Subscription extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray( $request )
    {
        return $this->mergeWhen( true, [
            [
                'project' => $this->planProject->project_code,
                'id' => $this->id,
                'starts_at' => $this->starts_at,
                'ends_at' => $this->ends_at,
                'calcels_at' => $this->calcels_at,
                'canceled_at' => $this->canceled_at,
                'plan' => new PlanResource( $this->whenLoaded( 'realPlan' ) ),
                'usage' => SubscriptionUsageResource::collection( $this->whenLoaded( 'usage' ) ),
                'created_at' => $this->created_at,
            ]
        ] );
    }
}
