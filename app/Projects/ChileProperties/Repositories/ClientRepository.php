<?php

namespace App\Projects\ChileProperties\Repositories;

use App\Projects\ChileProperties\Models\Client;

/**
 * Class ClientRepository
 * @package App\Projects\ChileProperties\Repositories
 * @version Jun 28, 2019, 2:34 pm UTC
*/
class ClientRepository
{
    /**
     * Save a new document in repository
     *
     * @param array $attributes
     *
     * @return \App\Projects\ChileProperties\Models\Client
     */
    public function create( array $attributes )
    {
        return Client::create( $attributes );
    }
    /**
     * Find a document by id in repository
     *
     * @param  mixed  $id
     *
     * @return \App\Projects\ChileProperties\Models\Client
     */
    public function find( $id )
    {
        return Client::find( $id );
    }

    /**
     * Find a document by its primary key or throw an exception.
     *
     * @param  mixed  $id
     *
     * @return \App\Projects\ChileProperties\Models\Client
     * @throws \Exception
     */
    public function findOrFail( $id )
    {
        $client = $this->find( $id );

        if ( empty( $client ) === true ) {
            throw new \Exception( 'Client not found.' );
        }

        return $client;
    }

    /**
     * Update a document in repository by id
     *
     * @param  array $attributes
     * @param  mixed $id
     *
     * @return \App\Projects\ChileProperties\Models\Client
     */
    public function update( array $attributes, $id )
    {
        $client = Client::find( $id );

        $client->fill( $attributes );

        $client->save();

        return $client;
    }

    public function delete( $id )
    {
        $client = Client::find( $id );

        $client->delete();

        return $client;
    }
}
