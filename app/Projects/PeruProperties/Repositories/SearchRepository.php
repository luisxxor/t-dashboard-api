<?php

namespace App\Projects\PeruProperties\Repositories;

use App\Projects\PeruProperties\Models\Search;

/**
 * Class SearchRepository
 * @package App\Projects\PeruProperties\Repositories
 * @version Jun 28, 2019, 2:34 pm UTC
*/
class SearchRepository
{
    /**
     * Save a new document in repository
     *
     * @param array $attributes
     *
     * @return \App\Projects\PeruProperties\Models\Search
     */
    public function create( array $attributes )
    {
        return Search::create( $attributes );
    }
    /**
     * Find a document by id in repository
     *
     * @param  mixed  $id
     *
     * @return \App\Projects\PeruProperties\Models\Search
     */
    public function find( $id )
    {
        return Search::find( $id );
    }

    /**
     * Find a document by its primary key or throw an exception.
     *
     * @param  mixed  $id
     *
     * @return \App\Projects\PeruProperties\Models\Search
     * @throws \Exception
     */
    public function findOrFail( $id )
    {
        $search = $this->find( $id );

        if ( empty( $search ) === true ) {
            throw new \Exception( 'Search not found.' );
        }

        return $search;
    }

    /**
     * Update a document in repository by id
     *
     * @param  array $attributes
     * @param  mixed $id
     *
     * @return \App\Projects\PeruProperties\Models\Search
     */
    public function update( array $attributes, $id )
    {
        $search = Search::find( $id );

        $search->fill( $attributes );

        $search->save();

        return $search;
    }
}
