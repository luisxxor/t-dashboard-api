<?php

namespace App\Http\Resources\Subscriptions;

use App\Http\Resources\Subscriptions\Feature as FeatureResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionUsage extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray( $request )
    {
        return [
            'id' => $this->id,
            'used' => $this->used,
            'remainings' => $this->when( is_numeric( $this->feature->value ), (int)$this->feature->value - $this->used ),
            'feature' => new FeatureResource( $this->feature ),
        ];
    }
}
