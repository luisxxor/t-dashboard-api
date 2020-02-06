<?php

namespace App\Repositories\Dashboard;

use App\Models\Dashboard\User;
use Illuminate\Support\Facades\Auth;
use App\Repositories\BaseRepository;

/**
 * Class UserRepository
 * @package App\Repositories\Dashboard
*/
class UserRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name', 'lastname', 'email',
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return User::class;
    }

    /**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param       $id
     *
     * @return mixed
     */
    public function update( array $attributes, $id )
    {
        // not update password if is empty
        if ( array_key_exists( 'password', $attributes ) === true && empty( $attributes[ 'password' ] ) === true ) {
            unset( $attributes[ 'password' ] );
        }

        return parent::update( $attributes, $id );
    }
}
