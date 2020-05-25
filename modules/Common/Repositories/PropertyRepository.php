<?php

namespace Modules\Common\Repositories;

use Illuminate\Container\Container as Application;
use Modules\Common\Pipelines\FilterPipelines;
use Modules\Common\Pipelines\PropertyPipelines;
use Modules\Common\Repositories\Repository;
use Modules\Common\Models\Property;
use Modules\Common\Models\Search;
use MongoDB\BSON\ObjectID;

/**
 * Class PropertyRepository
 * @package Modules\Common\Repositories
 * @version May 25, 2020, 20:40 am UTC
*/
class PropertyRepository extends Repository
{
    use FilterPipelines, PropertyPipelines;

    /**
     * @var array
     */
    protected $constants;

    /**
     * @var Search
     */
    protected $searchModel;

    /**
     * Header for export files (with nested values, if any).
     *
     * @var array
     */
    protected $header;

    /**
     * Flattened header for export files
     * (will be fill it when flattenedHeader() method is called).
     *
     * @var array
     */
    protected $flattenedHeader = [];

    /**
     * Fields and its order to sort the properties.
     *
     * @var string
     */
    protected $sortFields = [
        'publication_date' => -1,
        '_id' => 1,
    ];

    public function __construct( Application $app, Search $searchMod )
    {
        parent::__construct( $app );

        $this->searchModel = $searchMod;
    }

    /**
     * Configure the Model
     *
     * @return Property
     */
    public function model()
    {
        return Property::class;
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
        $collect = $this->model->raw( ( function ( $collection ) use ( $pipeline ) {
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
        $collect = $this->model->raw( ( function ( $collection ) use ( $pipeline ) {
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
            $this->model->raw( ( function ( $collection ) use ( $pipeline ) {
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
        $this->searchModel->raw( ( function ( $collection ) use ( $filter, $update ) {
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
        $collect = $this->model->raw( ( function ( $collection ) use ( $pipeline ) {
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
        $query = $this->searchModel->raw( ( function ( $collection ) use ( $searchId ) {
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
        $collect = $this->model->raw( ( function ( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );

        return $collect->toArray();
    }

    /**
     * Returns the flattened header.
     *
     * @return array
     */
    public function flattenedHeader(): array
    {
        if ( empty( $this->flattenedHeader ) !== true ) {
            return $this->flattenedHeader;
        }

        $counter = 0;
        $this->flattenedHeader = $this->header;
        foreach ( $this->flattenedHeader as $key => $value ) {
            if ( is_array( $value ) === true ) {
                $this->flattenedHeader = array_slice( $this->flattenedHeader, 0, $counter ) + $value + array_slice( $this->flattenedHeader, $counter + 1 );
            }

            $counter++;
        }

        return $this->flattenedHeader;
    }
}
