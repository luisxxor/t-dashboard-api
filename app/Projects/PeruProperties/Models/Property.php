<?php

namespace App\Projects\PeruProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;
use Jenssegers\Mongodb\Relations\BelongsTo;
use Jenssegers\Mongodb\Relations\HasOne;

class Property extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'peru_properties';

    /**
     * @var string
     */
    protected $collection = 'properties';

    /**
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * @var array
     */
    protected $appends = [
        /*'publication_custom_date', 'address_custom',*/
    ];

    // public function getPublicationCustomDateAttribute()
    // {
    //     return date( 'd/m/Y',strtotime( "{$this->publication_date}" ) );
    // }

    // public function getAddressCustomAttribute()
    // {
    //     return substr( "{$this->address}", 0, 3 );
    // }

    /**
     * @return BelongsTo
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo( 'App\Models\PeruProperties\Region' );
    }

    /**
     * @return HasOne
     */
    public function propertyLink(): HasOne
    {
        return $this->hasOne( 'PropertyLink', '_id', '_id' );
    }
}
