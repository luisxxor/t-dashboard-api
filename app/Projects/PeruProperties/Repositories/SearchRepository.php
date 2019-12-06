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
     * @return mixed
     */
    public function create( array $attributes ): Search
    {
        return Search::create( $attributes );
    }
    /**
     * Find a document by id in repository
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function find( $id ): Search
    {
        return Search::find( $id );
    }

    /**
     * Update a document in repository by id
     *
     * @param array $attributes
     * @param       $id
     *
     * @return mixed
     */
    public function update( array $attributes, $id ): Search
    {
        $search = Search::find( $id );

        $search->fill( $attributes );

        $search->save();

        return $search;
    }
}
