<?php

namespace Modules\PeruProperties\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\PeruProperties\Models\Property;
use Modules\PeruProperties\Models\Search;
use Modules\PeruProperties\Pipelines\FilterPipelines;
use Modules\PeruProperties\Pipelines\PropertyPipelines;
use MongoDB\BSON\ObjectID;

/**
 * Class PropertyRepository
 * @package Modules\PeruProperties\Repositories
 * @version May 31, 2019, 5:17 am UTC
*/
class PropertyRepository
{
    use FilterPipelines, PropertyPipelines;

    /**
     * @var array
     */
    protected $constants;

    /**
     * Fields and its order to sort the properties.
     *
     * @var string
     */
    protected $sortFields = [
        'publication_date' => -1,
        // 'distance' => 1,
        '_id' => 1,
    ];

    /**
     * Header for export files.
     *
     * @var array
     */
    public $header = [
        '_id'                   => 'Código',
        'link'                  => 'Enlace',
        'antiquity_years'       => 'Antigüedad',
        'bedrooms'              => 'Habitaciones',
        'bathrooms'             => 'Baños',
        'parkings'              => 'Cocheras',
        'total_area_m2'         => 'Área total',
        'build_area_m2'         => 'Área construida',
        'address'               => 'Dirección',
        'publication_date'      => 'Fecha de publicación',
        'dollars_price'         => 'Precio (USD)',
        'others_price'          => 'Precio (Soles)',
        'region'                => 'Región',
        'publication_type'      => 'Tipo de publicación',
        'urbanization'          => 'Urbanización',
        'location'              => 'Locación',
        'reference_place'       => 'Lugar de referencia',
        'comment_subtitle'      => 'Resumen',
        'comment_description'   => 'Descripción',
        'pool'                  => 'Piscina',
        'elevator'              => 'Ascensor',
        'property_type'         => 'Tipo de propiedad',
        'is_new'                => 'Propiedad nueva',
        'longitude'             => 'Longitud',
        'latitude'              => 'Latitud',
        'distance'              => 'Distancia (m)',
    ];

    public function __construct() {
        $this->constants = config( 'multi-api.pe-properties.constants' );
    }

    /**
     * Returns output fields of matched properties
     * from given search, with pagination.
     *
     * @param Search $search The search model to match the properties.
     * @param array $pagination {
     *     The values of the pagination
     *
     *     @type int $perpage [required] The number of rows per each
     *           page of the pagination.
     *     @type string $field [optional] The field needed to be sorted.
     *     @type int $sort [optional] The 'asc' (1) or 'desc' (-1) to be sorted.
     *     @type array $lastItem [optional] The last item to paginate from.
     * }
     *
     * @return Collection
     */
    public function searchPropertiesReturnOutputFields( Search $search, array $pagination ): Collection
    {
        // pipeline
        $pipeline = $this->pipelineSearchProperties( $search, $pagination );

        // join con property_types ($lookup)
        $pipeline[] = [
            '$lookup' => [
                'from' => 'property_types',
                'localField' => 'property_type_id',
                'foreignField' => '_id',
                'as' => 'property_types_docs'
            ]
        ];

        // output fields ($project)
        $pipeline[] = [
            '$project' => [
                '_id' => '$_id',
                'address' => [ '$ifNull' => [ '$address', null ] ],
                'dollars_price' => [ '$ifNull' => [ '$dollars_price', null ] ],
                'others_price' => [ '$ifNull' => [ '$others_price', null ] ],
                'bedrooms' => [ '$ifNull' => [ '$bedrooms', null ] ],
                'bathrooms' => [ '$ifNull' => [ '$bathrooms', null ] ],
                'parkings' => [ '$ifNull' => [ '$parkings', null ] ],
                'property_type' => [ '$ifNull' => [
                    [ '$arrayElemAt' => [ '$property_types_docs.name', 0 ] ],
                    null
                ] ],
                'publication_date' => [ '$ifNull' => [ '$publication_date', null ] ],
                'image_list' => [ '$ifNull' => [ '$image_list', null ] ],
                'distance' => [ '$convert' => [ 'input' => '$distance', 'to' => 'int', 'onError' => 'Error', 'onNull' => null ] ],
                'geometry' => '$geo_location',
            ]
        ];

        // exec query
        $collect = Property::raw( ( function ( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );

        return $collect;
    }

    /**
     * Returns only ids of matched properties
     * from given search, without pagination.
     *
     * @param Search $search The search model to match the properties.
     *
     * @return Collection
     */
    public function searchPropertiesReturnIds( Search $search ): Collection
    {
        // pipeline
        $pipeline = $this->pipelineSearchProperties( $search );

        // only _id ($project)
        $pipeline[] = [
            '$project' => [
                '_id' => '$_id',
            ]
        ];

        // exec query
        $collect = Property::raw( ( function ( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );

        return $collect;
    }

    /**
     * Update selected properties in given search with given array.
     * In case [ '*' ], then do the search to get the ids.
     *
     * @param Search $search The search model to match the properties.
     * @param array $ids Array of property's ids selected by user.
     *        [ '*' ] in case all were selected.
     *
     * @return void
     */
    public function updateSelectedPropertiesInSearch( Search $search, array $ids ): void
    {
        // empty selected properties
        $this->setSelectedPropertiesInSearch( $search->id, [] );

        if ( $ids !== [ '*' ] ) {
            // set selected properties
            $this->setSelectedPropertiesInSearch( $search->id, $ids );
        }
        else {
            // pipeline
            $pipeline = $this->pipelineSearchProperties( $search );

            // $group
            $pipeline[] = [
                '$group' => [
                    '_id' => new ObjectID( $search->id ),
                    'selected_properties' => [ '$push' => '$$ROOT._id' ]
                ]
            ];

            // insert into select ($merge)
            $pipeline[] = [
                '$merge' => [
                    'into' => 'searches',
                    'on' => '_id',
                    'whenMatched' => 'merge',
                    'whenNotMatched' => 'discard',
                ],
            ];

            // exec query
            Property::raw( ( function ( $collection ) use ( $pipeline ) {
                return $collection->aggregate( $pipeline );
            } ) );
        }
    }

    /**
     * Update selected properties in given search with given array.
     *
     * @param string $searchId The id of the current search.
     * @param array $selectedProperties Array to update.
     *
     * @return void
     */
    protected function setSelectedPropertiesInSearch( string $searchId, array $selectedProperties ): void
    {
        // query by which to filter documents
        $filter = [
            '_id' => [ '$eq' => new ObjectID( $searchId ) ],
        ];

        // update
        $update = [
            '$set' => [ 'selected_properties' => $selectedProperties ]
        ];

        // unselect all properties
        Search::raw( ( function ( $collection ) use ( $filter, $update ) {
            return $collection->updateMany( $filter, $update );
        } ) );
    }

    /**
     * Returns count of searched properties for given search.
     *
     * @param Search $search The search model to match the properties.
     *
     * @return int
     */
    public function countSearchedProperties( Search $search ): int
    {
        // pipeline
        $pipeline = $this->pipelineSearchProperties( $search );

        // output fields ($project)
        $pipeline[] = [
            '$count' => 'total'
        ];

        // exec query
        $collect = Property::raw( ( function ( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );

        $total = $collect->toArray()[ 0 ][ 'total' ];

        return $total;
    }

    /**
     * Returns count of selected properties in given search.
     *
     * @param string $searchId The id of the current search.
     *
     * @return int
     * @throws \ErrorException
     */
    public function countSelectedPropertiesInSearch( string $searchId ): int
    {
        $query = Search::raw( ( function ( $collection ) use ( $searchId ) {
            return $collection->aggregate( [
                [
                    '$match' => [
                        '_id' => [ '$eq' => new ObjectID( $searchId ) ],
                    ]
                ],
                [
                    '$project' => [
                        'total' => [
                            '$cond' => [
                                'if' => [
                                    '$isArray' => '$selected_properties'
                                ],
                                'then' => [
                                    '$size' => '$selected_properties'
                                ],
                                'else' => 0
                            ]
                        ]
                    ]
                ]
            ] );
        } ) );

        try {
            return $query->toArray()[ 0 ][ 'total' ];
        } catch ( \ErrorException $e ) {
            return 0;
        }
    }

    /**
     * Return properties (from properties collection) that were selected
     *  by user in given search.
     *
     * @param Search $search The search model to match the properties.
     * @param array $pagination {
     *     The values of the pagination
     *
     *     @type int $perpage [required] The number of rows per each
     *           page of the pagination.
     *     @type array $lastItem [optional] The last item to paginate from.
     * }
     *
     * @return Collection
     */
    public function getSelectedPropertiesFromProperties( Search $search, array $pagination ): Collection
    {
        // pipeline
        $pipeline = $this->pipelineSelectedPropertiesFromProperties( $search, $pagination );

        // get selected data in final format
        $collect = Property::raw( ( function ( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );

        return $collect;
    }
}
