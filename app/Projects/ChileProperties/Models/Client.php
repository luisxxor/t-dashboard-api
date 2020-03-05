<?php

namespace App\Projects\ChileProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class Client extends Moloquent
{
    use SoftDeletes;
    /**
     * @var string
     */
    protected $connection = 'chile_properties';

    /**
     * @var string
     */
    protected $collection = 'client';

    /**
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'personal_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'executive',
        'email_executive',
        'user_id',
    ];








}
