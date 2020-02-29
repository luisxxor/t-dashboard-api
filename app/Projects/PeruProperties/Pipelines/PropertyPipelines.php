<?php

namespace App\Projects\PeruProperties\Pipelines;

use MongoDB\BSON\ObjectID;

/**
 * Trait PropertyPipelines
 * @package App\Projects\PeruProperties\Pipelines
 * @version Dec 24, 2019, 15:31 UTC
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
     * Return pipeline to retrive properties
     * that match with the specified input.
     *
     * @param string $searchId The id of the current search.
     * @param array $metadata {
     *     The metadata to search the properties.
     *
     *     @type array $distance [required] Pipeline $geoNear.
     *     @type array $propertiesWithin [required] Pipeline $match.
     *     @type array $filters [required] Pipeline $match.
     * }
     * @param array $pagination {
     *     The values of the pagination
     *
     *     @type int $perpage [required] The number of rows per each
     *           page of the pagination.
     *     @type string $field [required] The field needed to be sorted.
     *     @type string $sort [required] The 'asc' or 'desc' to be sorted.
     * }
     *
     * @return array
     */
    protected function pipelineSearchProperties( string $searchId, array $metadata, array $pagination ): array
    {
        // pipeline
        $pipeline = [];

        // geo distance ($geoNear)
        $pipeline[] = [
            '$geoNear' => $metadata[ 'distance' ]
        ];

        // geo within and filters ($match)
        $pipeline[] = [
            '$match' => $metadata[ 'propertiesWithin' ] + $metadata[ 'filters' ]
        ];

        // order by ($sort)
        $pipeline[] = [
            '$sort' => $this->mergeSortFields( $pagination[ 'field' ], $pagination[ 'sort' ] )
        ];

        // pagination
        if ( isset( $pagination[ 'lastId' ] ) === true ) {
            $pipeline[] = [
                '$match' => [
                    'publication_date' => [ '$gte' => new \MongoDB\BSON\UTCDateTime( strtotime( $pagination[ 'lastDate' ] ) ) ],
                    'distance' => [ '$gte' => $pagination[ 'lastDistance' ] ],
                    '_id' => [ '$gt' => $pagination[ 'lastId' ] ]
                ]
            ];
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
            ]
        ];

        // remove unnecessary fields ($project)
        $pipeline[] = [
            '$project' => [
                'property_type_id' => 0,
                'property_types_docs' => 0,
                'region_id' => 0,
                'regions_docs' => 0,
            ]
        ];

        // insert into select ($merge)
        // $pipeline[] = [
        //     '$merge' => [
        //         'into' => 'searched_properties',
        //         'on' => [ 'property_id', 'search_id' ],
        //         'whenMatched' => 'merge',
        //         'whenNotMatched' => 'insert',
        //     ],
        // ];
// dd( json_encode( $pipeline ) );
        return $pipeline;
    }

    protected function mergeSortFields( string $field, int $sort )
    {
        if ( array_key_exists( $field, $this->sortFields ) === true ) {
            $sortFields = array_merge( $this->sortFields, [ $field => $sort ] );
        }
        else {
            $sortFields = array_merge( [ $field => $sort ], $this->sortFields );
        }

        return $sortFields;
    }
}
