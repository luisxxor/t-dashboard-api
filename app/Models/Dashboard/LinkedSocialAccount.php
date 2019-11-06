<?php

namespace App\Models\Dashboard;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LinkedSocialAccount extends Model
{
    use SoftDeletes;

    public $table = 'linked_social_accounts';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

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