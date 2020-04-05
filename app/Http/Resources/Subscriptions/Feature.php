<?php

namespace App\Http\Resources\Subscriptions;

use App\Http\Resources\Subscriptions\SubscriptionUsage as SubscriptionUsageResource;
use Illuminate\Http\Resources\Json\JsonResource;

class Feature extends JsonResource
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
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}
