<?php

namespace App\Models\Dashboard;

use App\Models\Dashboard\Partner;
use App\Models\Dashboard\Project;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use App\Notifications\VerifyEmail as VerifyEmailNotification;
use Caffeinated\Shinobi\Concerns\HasRolesAndPermissions;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasRolesAndPermissions, SoftDeletes, HasApiTokens;

    protected $dates = [ 'deleted_at' ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'lastname', 'email',

        'phone_number1', 'address_line1', 'address_line2',

        'password',

        'email_verified_at',

        'accessible_projects',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'lastname' => 'string',
        'email' => 'string',

        'phone_number1' => 'integer',
        'address_line1' => 'string',
        'address_line2' => 'string',

        'password' => 'string',

        'accessible_projects' => 'array',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'updated_at', 'created_at', 'deleted_at'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        //
    ];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification( $token )
    {
        $this->notify( new ResetPasswordNotification( $token ) );
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify( new VerifyEmailNotification );
    }

    /**
     * Encrypt the user's password.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute( $value )
    {
        $this->attributes[ 'password' ] = Hash::make( $value );
    }

    /**
     * Get the user's roles.
     *
     * @return array
     */
    public function getRoleListAttribute()
    {
        return array_column( $this->roles()->get( [ 'slug' ] )->toArray(), 'slug' );
    }

    /**
     * Get user scopes.
     *
     * @return array
     */
    public function getScopes()
    {
        $accessibleProjects = array_column( $this->accessible_projects, 'project' );

        foreach ( $accessibleProjects as $key => $accessibleProject ) {
            $accessibleProjects[ $key ] = 'access-' . $accessibleProject;
        };

        return $accessibleProjects;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany( \App\Models\Dashboard\Order::class );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function linkedSocialAccounts()
    {
        return $this->hasMany( \App\Models\Dashboard\LinkedSocialAccount::class );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function projectAccessRequests()
    {
        return $this->hasMany( \App\Models\Dashboard\ProjectAccessRequest::class );
    }

    /**
     * @return array
     **/
    public function getProjectAccessRequestList()
    {
        $projectAccessRequests = $this->projectAccessRequests()->with( 'partnerProject' )->get();

        $list = [];
        foreach ( $projectAccessRequests as $projectAccessRequest ) {
            $partnerProject = $projectAccessRequest->partnerProject;

            $list[] = [
                'partner' => $partnerProject->partner,
                'project' => $partnerProject->project,
                'status' => $projectAccessRequest->status,
            ];
        }

        return $list;
    }

    /**
     * Checks if the user has the given partner-project associated.
     *
     * @param  Partner|string  $partner
     * @param  Project|string  $project
     *
     * @return boolean
     */
    public function hasPartnerProjectAccess( $partner, $project )
    {
        if ( $partner instanceof Partner ) {
            $partner = $partner->code;
        }

        if ( $project instanceof Project ) {
            $project = $project->code;
        }

        $needle = [
            'partner' => $partner,
            'project' => $project,
        ];

        return array_search( $needle, $this->accessible_projects ) !== false;
    }

    /**
     * Checks if the user has a created requser for the given partner-project.
     *
     * @param  Partner|string  $partner
     * @param  Project|string  $project
     *
     * @return boolean
     */
    public function hasPartnerProjectRequest( $partner, $project )
    {
        if ( $partner instanceof Partner ) {
            $partner = $partner->code;
        }

        if ( $project instanceof Project ) {
            $project = $project->code;
        }

        $projectAccessRequests = $this->projectAccessRequests()->with( 'partnerProject' )->get();

        foreach ( $projectAccessRequests as $projectAccessRequest ) {
            $partnerProject = $projectAccessRequest->partnerProject;

            if ( $partnerProject->partner_code === $partner && $partnerProject->project_code === $project ) {
                return true;
            }
        }

        return false;
    }
}
