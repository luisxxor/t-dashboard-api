<?php

namespace App\Models\Dashboard;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectAccessRequest extends BaseModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'project_access_requests';

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
    public $fillable = [
        'user_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at', 'created_at', 'deleted_at', 'id', 'user_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function partnerProject()
    {
        return $this->belongsTo( \App\Models\Dashboard\PartnerProject::class );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo( \App\Models\Dashboard\User::class );
    }
}
