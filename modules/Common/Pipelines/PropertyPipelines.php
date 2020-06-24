<?php

namespace Modules\Common\Pipelines;

use DateTime;
use Modules\Common\Models\Search;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDateTime;

/**
 * Trait PropertyPipelines
 * @package Modules\Common\Pipelines
 * @version May 25, 2020, 18:42 UTC
*/
trait PropertyPipelines
{
    /**
     * Return pipeline to search properties
     * only by geonear.
     *
     * @param array $distance Pipeline $geoNear.
     *
     * @return array
     */
    protected function pipelinePropertiesOnlyByGeonear( array $distance ): array
    {
        // pipeline
        $pipeline = [];

        // geo distance ($geoNear)
        $pipeline[] = [
            '$geoNear' => $distance
        ];

        return $pipeline;
    }

    /**
     * Returns pipeline to retrive properties
     * that match with the specified input.
     *
     * @param Search $search The search model to match the properties.
     * @param array $pagination {
     *     The values of the pagination
     *
     *     @type int $perpage [required] The number of rows per each
     *           page of the pagination.
     *     @type string $field [required] The field needed to be sorted.
     *     @type int $sort [required] The 'asc' (1) or 'desc' (-1) to be sorted.
     *     @type array $lastItem [optional] The last item to paginate from.
     * }
     *
     * @return array
     */
    protected function pipelineSearchProperties( Search $search, array $pagination = [] ): array
    {
        // pipeline
        $pipeline = [];

        // geo distance ($geoNear)
        $pipeline[] = [
            '$geoNear' => $this->pipelineDistanceToQuery( $search->metadata[ 'initPoint' ][ 'lat' ], $search->metadata[ 'initPoint' ][ 'lng' ] )
        ];

        // geo within and filters ($match)
        $pipeline[] = [
            '$match' => $this->pipelinePropertiesWithinToQuery( $search->metadata[ 'vertices' ] ) + $this->pipelineFiltersToQuery( (array)$search->metadata[ 'filters' ] )
        ];

        // order by ($sort)
        $pipeline[] = [
            '$sort' => $this->sortFields
        ];

        // pagination
        if ( empty( $pagination ) === false ) {
            if ( isset( $pagination[ 'lastItem' ] ) === true && empty( $pagination[ 'lastItem' ] ) === false ) {
                $pipeline[] = $this->customPagination( $pagination[ 'lastItem' ] );
            }

            // limit ($limit)
            $pipeline[] = [
                '$limit' => $pagination[ 'perpage' ],
            ];
        }

        return $pipeline;
    }

    /**
     * Return pipeline to retrive selected properties
     * for given search.
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
    protected function pipelineSelectedPropertiesFromProperties( Search $search, array $pagination ): array
    {
        // pipeline
        $pipeline = [];

        // geo distance ($geoNear)
        $pipeline[] = [
            '$geoNear' => $this->pipelineDistanceToQuery( $search->metadata[ 'initPoint' ][ 'lat' ], $search->metadata[ 'initPoint' ][ 'lng' ] )
        ];

        // select the current search ($match)
        $pipeline[] = [
            '$match' => [
                '_id' => [ '$in' => $search->selected_properties ],
            ]
        ];

        // order by ($sort)
        $pipeline[] = [
            '$sort' => $this->sortFields
        ];

        // pagination
        if ( isset( $pagination[ 'lastItem' ] ) === true && empty( $pagination[ 'lastItem' ] ) === false ) {
            $pipeline[] = $this->customPagination( $pagination[ 'lastItem' ] );
        }

        // limit ($limit)
        $pipeline[] = [
            '$limit' => $pagination[ 'perpage' ],
        ];

        // join con regions ($lookup)
        $pipeline[] = [
            '$lookup' => [
                'from' => 'regions',
                'localField' => 'region_id',
                'foreignField' => '_id',
                'as' => 'regions_docs'
            ]
        ];

        // join con property_types ($lookup)
        $pipeline[] = [
            '$lookup' => [
                'from' => 'property_types',
                'localField' => 'property_type_id',
                'foreignField' => '_id',
                'as' => 'property_types_docs'
            ]
        ];

        // join con publication_types ($lookup)
        $pipeline[] = [
            '$lookup' => [
                'from' => 'publication_types',
                'localField' => 'publication_type_id',
                'foreignField' => '_id',
                'as' => 'publication_types_docs'
            ]
        ];

        // fields ($addFields)
        $pipeline[] = [
            '$addFields' => [
                'property_type' => [ '$ifNull' => [
                    [ '$arrayElemAt' => [ '$property_types_docs.name', 0 ] ],
                    null
                ] ],
                'region' => [
                    '$concat' => [
                        [ '$arrayElemAt' => [ '$regions_docs.sub_reg1', 0 ] ],
                        ', ',
                        [ '$arrayElemAt' => [ '$regions_docs.sub_reg2', 0 ] ],
                        ', ',
                        [ '$arrayElemAt' => [ '$regions_docs.sub_reg3', 0 ] ]
                    ]
                ],
                'publication_type' => [ '$ifNull' => [
                    [ '$arrayElemAt' => [ '$publication_types_docs.name', 0 ] ],
                    null
                ] ],
            ]
        ];

        // remove unnecessary fields ($project)
        $pipeline[] = [
            '$project' => [
                'property_type_id' => 0,
                'property_types_docs' => 0,
                'region_id' => 0,
                'regions_docs' => 0,
                'publication_type_id' => 0,
                'publication_types_docs' => 0,
            ]
        ];

        return $pipeline;
    }

    /**
     * Custom pagination through last paginated value.
     *
     * @param array $lastItem {
     *     Last paginated value.
     *
     *     @type string $publication_date [required]
     *     @type int $_id [required]
     * }
     *
     * @return array
     */
    protected function customPagination( array $lastItem )
    {
        $lastPublicationDate = new UTCDateTime( DateTime::createFromFormat( config( 'app.datetime_format' ), $lastItem[ 'publication_date' ] ) );
        $lastId = $lastItem[ '_id' ];

        return [
            '$match' => [
                '$or' => [
                    [
                        'publication_date' => [ '$lt' => $lastPublicationDate ],
                    ],
                    [
                        '_id' => [ '$gt' => $lastId ],
                        'publication_date' => [ '$eq' => $lastPublicationDate ],
                    ],
                ],
            ]
        ];
    }
}