<?php

namespace App\Models\Tokens;

use Illuminate\Database\Eloquent\Model;

class DataToken extends Model
{
    public $table = 'data_tokens';

    const CREATED_AT = 'created_at';

    /**
     * The primary key for the model.
     * @var string
     */
    protected $primaryKey = 'token';

    /**
     * The primary key type.
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    public $fillable = [
        'data',
        'token',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'token' => 'string',
    ];
}
