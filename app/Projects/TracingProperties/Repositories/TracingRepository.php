<?php

namespace App\Projects\TracingProperties\Repositories;

use App\Projects\TracingProperties\Models\Tracing;

/**
 * Class TracingRepository
 * @package App\Projects\TracingProperties\Repositories
 * @version Jun 28, 2019, 2:34 pm UTC
*/
class TracingRepository
{
    /**
     * Save a new document in repository
     *
     * @param array $attributes
     *
     * @return \App\Projects\TracingProperties\Models\Tracing
     */
    public function create( array $attributes )
    {
        return Tracing::create( $attributes );
    }
    /**
     * Find a document by id in repository
     *
     * @param  mixed  $id
     *
     * @return \App\Projects\TracingProperties\Models\Tracing
     */
    public function find( $id )
    {
        return Tracing::find( $id );
    }

    /**
     * Find a document by its primary key or throw an exception.
     *
     * @param  mixed  $id
     *
     * @return \App\Projects\TracingProperties\Models\Tracing
     * @throws \Exception
     */
    public function findOrFail( $id )
    {
        $tracing = $this->find( $id );

        if ( empty( $tracing ) === true ) {
            throw new \Exception( 'Tracing not found.' );
        }

        return $tracing;
    }

    /**
     * Update a document in repository by id
     *
     * @param  array $attributes
     * @param  mixed $id
     *
     * @return \App\Projects\TracingProperties\Models\Tracing
     */
    public function update( array $attributes, $id )
    {
        $tracing = Tracing::find( $id );

        $tracing->fill( $attributes );

        $tracing->save();

        return $tracing;
    }
}
