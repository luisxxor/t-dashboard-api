<?php

namespace App\Traits\Dashboard;

use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasReceipt
{
    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $name
     * @param  string|null  $type
     * @param  string|null  $id
     * @param  string|null  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    abstract public function morphMany( $related, $name, $type = null, $id = null, $localKey = null );

    /**
     * The model may have one receipt.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function receipt(): MorphOne
    {
        return $this->morphOne( \App\Models\Dashboard\Receipt::class, 'receiptable' );
    }
}
