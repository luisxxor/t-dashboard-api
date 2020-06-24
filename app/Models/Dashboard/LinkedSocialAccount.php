<?php

namespace App\Models\Dashboard;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class LinkedSocialAccount extends BaseModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'linked_social_accounts';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [ 'deleted_at' ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider_name',
        'provider_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo( \App\Models\Dashboard\User::class );
    }
}