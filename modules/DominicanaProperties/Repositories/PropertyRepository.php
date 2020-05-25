<?php

namespace Modules\DominicanaProperties\Repositories;

use Carbon\Carbon;
use Modules\Common\Pipelines\CommonFilterPipelines;
use Modules\Common\Repositories\CommonRepository;
use Modules\DominicanaProperties\Models\Property;
use Modules\DominicanaProperties\Models\PropertyType;
use Modules\DominicanaProperties\Models\PublicationType;
use Modules\DominicanaProperties\Models\Search;
use Modules\DominicanaProperties\Pipelines\PropertyPipelines;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDateTime;

/**
 * Class PropertyRepository
 * @package Modules\DominicanaProperties\Repositories
 * @version May 31, 2019, 5:17 am UTC
*/
class PropertyRepository extends CommonRepository
{
    use CommonFilterPipelines, PropertyPipelines;

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
        '_id' => 1,
    ];

    /**
     * Header for export files (with nested values, if any).
     *
     * @var array
     */
    protected $header = [
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
        'others_price'          => 'Precio (DOP)',
        'region'                => 'Región',
        'publication_type'      => 'Tipo de publicación',
        'urbanization'          => 'Urbanización',
        'location'              => 'Locación',
        'reference_place'       => 'Lugar de referencia',
        'comment_subtitle'      => 'Resumen',
        'comment_description'   => 'Descripción',
        'extra_fields'          => [
            'piscina' => 'Piscina',
            'ascensor' => 'Ascensor',
        ],
        'property_type'         => 'Tipo de propiedad',
        'is_new'                => 'Propiedad nueva',
        'longitude'             => 'Longitud',
        'latitude'              => 'Latitud',
        'distance'              => 'Distancia (m)',
    ];

    public function __construct()
    {
        $this->constants = config( 'multi-api.do-properties.constants' );
    }

    /**
     * Return filter fields (for $match aggregation pipeline operators).
     *
     * @return array
     */
    protected function filterFields(): array
    {
        return [
            'slidersFields' => [
                $this->constants[ 'FILTER_FIELD_BEDROOMS' ] => [
                    'name' => 'bedrooms',
                    'clousure' => function ( $field ) {
                        return $field === '5' ? (float)$field : (int)$field;
                    }
                ],
                $this->constants[ 'FILTER_FIELD_BATHROOMS' ] => [
                    'name' => 'bathrooms',
                    'clousure' => function ( $field ) {
                        return $field === '5' ? (float)$field : (int)$field;
                    }
                ],
                $this->constants[ 'FILTER_FIELD_PARKINGS' ] => [
                    'name' => 'parkings',
                    'clousure' => function ( $field ) {
                        return $field === '5' ? (float)$field : (int)$field;
                    }
                ],
            ],
            'numericFields' => [
                $this->constants[ 'FILTER_FIELD_ANTIQUITY_YEARS' ] => [
                    'name' => 'antiquity_years',
                    'clousure' => function ( $field ) {
                        return (int)$field;
                    }
                ],
                $this->constants[ 'FILTER_FIELD_TOTAL_AREA_M2' ] => [
                    'name' => 'total_area_m2',
                    'clousure' => function ( $field ) {
                        return (float)$field;
                    }
                ],
                $this->constants[ 'FILTER_FIELD_BUILD_AREA_M2' ] => [
                    'name' => 'build_area_m2',
                    'clousure' => function ( $field ) {
                        return (float)$field;
                    }
                ],
                $this->constants[ 'FILTER_FIELD_PUBLICATION_DATE' ] => [
                    'name' => 'publication_date',
                    'clousure' => function ( $field ) {
                        $carbonDate = Carbon::createFromFormat( 'd/m/Y', trim( $field ) );
                        return new UTCDateTime( $carbonDate );
                    },
                ],
            ],
            'dropdownFields' => [
                $this->constants[ 'FILTER_FIELD_PROPERTY_TYPE' ] => [
                    'name' => 'property_type_id',
                    'clousure' => function ( $field ) {
                        $results = PropertyType::where( 'name', $field )->get();
                        return array_column( $results->toArray(), '_id' );
                    },
                ],
                $this->constants[ 'FILTER_FIELD_PUBLICATION_TYPE' ] => [
                    'name' => 'publication_type_id',
                    'clousure' => function ( $field ) {
                        $results = PublicationType::where( 'name', $field )->get();
                        return array_column( $results->toArray(), '_id' );
                    },
                ],
                $this->constants[ 'FILTER_FIELD_IS_NEW' ] => [
                    'name' => 'is_new',
                    'clousure' => function ( $field ) {
                        return (bool)$field;
                    },
                ],
            ],
        ];
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

        return $collect->toArray();
    }

    /**
     * Returns only ids of matched properties
     * from given search, without pagination.
     *
     * @param Search $search The search model to match the properties.
     *
     * @return array
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
     * @param array $pagination {
     *     The values of the pagination
     *
     *     @type int $perpage [required] The number of rows per each
     *           page of the pagination.
     *     @type array $lastItem [optional] The last item to paginate from.
     * }
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
}
