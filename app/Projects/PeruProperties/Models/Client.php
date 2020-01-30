<?php

namespace App\Projects\PeruProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class Search extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'peru_properties';

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
    ];








}
