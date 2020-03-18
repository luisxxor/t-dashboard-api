<?php

namespace App\Models\Dashboard;

use Illuminate\Database\Eloquent\Model;

class PartnerProject extends Model
{
    public $table = 'partner_project';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at', 'created_at',
    ];
}
