<?php

namespace App\Traits\Dashboard;

use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasReceipt
{
    /**
     * Define a polymorphic one-to-one relationship.
     *
     * @param  string  $related
     * @param  string  $name
     * @param  string|null  $type
     * @param  string|null  $id
     * @param  string|null  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    abstract public function morphOne( $related, $name, $type = null, $id = null, $localKey = null );

    /**
     * The model may have one receipt.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function receipt(): MorphOne
    {
        return $this->morphOne( \App\Models\Dashboard\Receipt::class, 'receiptable' );
    }

    /**
     * Set 'pending' status in model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract public function setPendingStatus();

    /**
     * Set 'released' status in model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract public function setReleasedStatus();
}
