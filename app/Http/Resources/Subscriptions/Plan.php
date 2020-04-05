<?php

namespace App\Http\Resources\Subscriptions;

use App\Http\Resources\Subscriptions\Feature as FeatureResource;
use Illuminate\Http\Resources\Json\JsonResource;

class Plan extends JsonResource
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
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency,
            'features' => FeatureResource::collection( $this->features ),
        ];
    }
}
