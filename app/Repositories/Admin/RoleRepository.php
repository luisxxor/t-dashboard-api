<?php

namespace App\Repositories\Admin;

use App\Repositories\BaseRepository;
use Caffeinated\Shinobi\Models\Role;

/**
 * Class RoleRepository
 * @package App\Repositories\Admin
 * @version February 5, 2020, 00:14 -4 UTC
*/
class RoleRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'code',
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
        return Role::class;
    }

    /**
     * Retrieve all records
     *
     * @param array $columns
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function get($columns = ['*'])
    {

        return $this->model->get($columns);
    }

    /**
     * Begin querying a model with eager loading.
     *
     * @param  array|string  $relations
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function with( $relations )
    {
        return $this->model->with( $relations );
    }

}
