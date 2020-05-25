<?php

namespace Modules\Common\Repositories;

use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Collection;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * Class Repository
 * @package Modules\Common\Repositories
 * @version May 24, 2020, 18:48 UTC
*/
abstract class Repository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app
     *
     * @throws \Exception
     */
    public function __construct( Application $app )
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Configure the Model
     *
     * @return string
     */
    abstract public function model();

    /**
     * Make Model instance
     *
     * @throws \Exception
     *
     * @return Model
     */
    public function makeModel()
    {
        $model = $this->app->make( $this->model() );

        if ( $model instanceof Model !== true ) {
            throw new \Exception( "Class {$this->model()} must be an instance of Jenssegers\\Mongodb\\Eloquent\\Model" );
        }

        return $this->model = $model;
    }

    /**
     * Save a new document in repository
     *
     * @param array $attributes
     *
     * @return \Jenssegers\Mongodb\Eloquent\Model
     */
    public function create( array $attributes )
    {
        return $this->model->create( $attributes );
    }

    /**
     * Find a document by id in repository
     *
     * @param  mixed  $id
     *
     * @return \Jenssegers\Mongodb\Eloquent\Model
     */
    public function find( $id )
    {
        return $this->model->find( $id );
    }

    /**
     * Find a document by its primary key or throw an exception.
     *
     * @param  mixed  $id
     *
     * @return \Jenssegers\Mongodb\Eloquent\Model
     * @throws \Exception
     */
    public function findOrFail( $id )
    {
        $model = $this->find( $id );

        if ( empty( $model ) === true ) {
            throw new \Exception( 'Search not found.' );
        }

        return $model;
    }

    /**
     * Update a document in repository by id
     *
     * @param  array $attributes
     * @param  mixed $id
     *
     * @return \Jenssegers\Mongodb\Eloquent\Model
     */
    public function update( array $attributes, $id )
    {
        $model = $this->model->find( $id );

        $model->fill( $attributes );

        $model->save();

        return $model;
    }

    /**
     * Save a new document in collection
     *
     * @param array $attributes
     *
     * @return string
     */
    public function distinct( string $field ): Collection
    {
        return $this->model->distinct( $field )->get();
    }
}
