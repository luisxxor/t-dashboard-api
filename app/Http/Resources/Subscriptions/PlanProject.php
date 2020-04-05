<?php

namespace App\Http\Resources\Subscriptions;

use App\Http\Resources\Subscriptions\Plan as PlanResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanProject extends JsonResource
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
            'project_code' => $this->project_code,
            'plan' => new PlanResource( $this->whenLoaded( 'plan' ) ),
        ];
    }
}
