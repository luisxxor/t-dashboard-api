<?php

namespace App\Projects\PeruProperties\Repositories;

use App\Projects\PeruProperties\Models\Property;
use App\Projects\PeruProperties\Models\Search;
use App\Projects\PeruProperties\Models\SearchedProperty;
use App\Projects\PeruProperties\Pipelines\FilterPipelines;
use App\Projects\PeruProperties\Pipelines\PropertyPipelines;
use App\Projects\PeruProperties\Pipelines\SearchedPropertyPipelines;
use Illuminate\Pagination\LengthAwarePaginator;
use MongoDB\BSON\ObjectID;

/**
 * Class PropertyRepository
 * @package App\Projects\PeruProperties\Repositories
 * @version May 31, 2019, 5:17 am UTC
*/
class PropertyRepository
{
    use FilterPipelines, PropertyPipelines, SearchedPropertyPipelines;

    /**
     * @var array
     */
    protected $constants;

    /**
     * @var array
     */
    protected $outputFields = [
        'id',
        'dollars_price',
        'others_price',
        'bedrooms',
        'bathrooms',
        'parkings',
        'property_type',
        'publication_date_formated',
        'image_list',
    ];

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
        'property_new'          => 'Propiedad nueva',
        'longitude'             => 'Longitud',
        'latitude'              => 'Latitud',
        'distance'              => 'Distancia (m)',
    ];

    public function __construct() {
        $this->constants = config( 'multi-api.pe-properties.constants' );
    }

    /**
     * Make a search of properties but not specting response.
     *
     * @param float $lat
     * @param float $lng
     * @param int $maxDistance The maximum distance from the center
     *        point that the documents can be (in meters).
     *
     * @return void
     */
    public function searchPropertiesOnlyByGeonear( float $lat, float $lng, int $maxDistance ): void
    {
        // pipeline to get distance (parameters)
        $distance = $this->pipelineDistanceToQuery( $lat, $lng, $maxDistance );

        // pipeline
        $pipeline = $this->pipelinePropertiesOnlyByGeonear( $distance );

        // exec query
        Property::raw( ( function ( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );
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
     * @return array
     */
    public function searchPropertiesReturnOutputFields( Search $search, array $pagination ): array
    {
        // pipeline
        $pipeline = $this->pipelineSearchProperties( $search, $pagination );

        // output fields ($project)
        $pipeline[] = [
            '$project' => [
                '_id' => '$_id',
                'type' => 'Feature',
                'properties' => [
                    'address' => [ '$ifNull' => [ '$address', null ] ],
                    'dollars_price' => [ '$ifNull' => [ '$dollars_price', null ] ],
                    'others_price' => [ '$ifNull' => [ '$others_price', null ] ],
                    'bedrooms' => [ '$ifNull' => [ '$bedrooms', null ] ],
                    'bathrooms' => [ '$ifNull' => [ '$bathrooms', null ] ],
                    'parkings' => [ '$ifNull' => [ '$parkings', null ] ],
                    'property_type' => [ '$ifNull' => [ '$property_type', null ] ],
                    'publication_date' => [ '$toString' => [ '$publication_date' ] ],
                    'image_list' => [ '$ifNull' => [ '$image_list', null ] ],
                    'distance' => [ '$convert' => [ 'input' => '$distance', 'to' => 'int', 'onError' => 'Error', 'onNull' => null ] ],
                ],
                'geometry' => '$geo_location'
            ]
        ];

        // exec query
        $collect = Property::raw( ( function ( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );

        // new instance of LengthAwarePaginator
        $paginator = new LengthAwarePaginator( $collect, 0, $pagination[ 'perpage' ], 1 );

        // cast to array
        $paginator = $paginator->toArray();

        // search id
        $paginator[ 'searchId' ] = $search->id;

        return $paginator;
    }

    /**
     * Returns only ids of matched properties
     * from given search, without pagination.
     *
     * @param Search $search The search model to match the properties.
     *
     * @return void
     */
    public function searchPropertiesReturnIds( Search $search ): array
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

        return $collect->toArray();
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
     *
     * @return array
     */
    public function getSelectedPropertiesFromProperties( Search $search, array $pagination ): array
    {
        // pipeline
        $pipeline = $this->pipelineSelectedPropertiesFromProperties( $search, $pagination );

        // get selected data in final format
        $collect = Property::raw( ( function ( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );

        return $collect->toArray();
    }




    /**
     * Return properties that were selected by user in given search
     * in excel format.
     *
     * @param string $searchId The id of the current search.
     *
     * @return array
     * @throws \Exception
     */
    public function getSelectedSearchedPropertiesExcelFormat( string $searchId ): array
    {
        // pipeline
        $pipeline = $this->pipelineSelectedPropertiesFromSearchExcelFormat( $searchId );

        // get selected data in final format
        $results = SearchedProperty::raw( ( function ( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );

        if ( $results->isEmpty() === true ) {
            throw new \Exception( 'No properties selected in given search.' );

        }

        return $results->toArray();
    }
}
