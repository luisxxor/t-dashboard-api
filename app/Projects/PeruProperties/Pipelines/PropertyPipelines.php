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
     * Returns pipeline to retrive properties
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
     *     @type int $sort [required] The 'asc' (1) or 'desc' (-1) to be sorted.
     *     @type array $lastItem [optional] The last item to paginate from.
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
        if ( isset( $pagination[ 'lastItem' ] ) === true ) {
            $lastPublicationDate = new \MongoDB\BSON\UTCDateTime( strtotime( $pagination[ 'lastItem' ][ 'publication_date' ] ) * 1000 );

            $pipeline[] = [
                '$match' => [
                    '$or' => [
                        [
                            'publication_date' => [ '$lt' => $lastPublicationDate ],
                        ],
                        [
                            '_id' => [ '$gt' => $pagination[ 'lastItem' ][ '_id' ] ],
                            'publication_date' => $lastPublicationDate,
                        ],
                    ],
                ]
            ];
        }

        // limit ($limit)
        $pipeline[] = [
            '$limit' => $pagination[ 'perpage' ],
        ];

        // join con regions ($lookup)
        // $pipeline[] = [
        //     '$lookup' => [
        //         'from' => 'regions',
        //         'localField' => 'region_id',
        //         'foreignField' => '_id',
        //         'as' => 'regions_docs'
        //     ]
        // ];

        // join con property_types ($lookup)
        // $pipeline[] = [
        //     '$lookup' => [
        //         'from' => 'property_types',
        //         'localField' => 'property_type_id',
        //         'foreignField' => '_id',
        //         'as' => 'property_types_docs'
        //     ]
        // ];

        // fields ($addFields)
        // $pipeline[] = [
        //     '$addFields' => [
        //         'property_type' => [ '$ifNull' => [
        //             [ '$arrayElemAt' => [ '$property_types_docs.name', 0 ] ],
        //             null
        //         ] ],
        //         'region' => [
        //             '$concat' => [
        //                 [ '$arrayElemAt' => [ '$regions_docs.sub_reg1', 0 ] ],
        //                 ', ',
        //                 [ '$arrayElemAt' => [ '$regions_docs.sub_reg2', 0 ] ],
        //                 ', ',
        //                 [ '$arrayElemAt' => [ '$regions_docs.sub_reg3', 0 ] ]
        //             ]
        //         ],
        //     ]
        // ];

        // remove unnecessary fields ($project)
        // $pipeline[] = [
        //     '$project' => [
        //         'property_type_id' => 0,
        //         'property_types_docs' => 0,
        //         'region_id' => 0,
        //         'regions_docs' => 0,
        //     ]
        // ];

        // fields ($project)
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

        return $pipeline;
    }

    /**
     * Returns the fields with which the response will be sorted.
     *
     * @param string $field The field needed to be sorted.
     * @param int $sort The 'asc' (1) or 'desc' (-1) to be sorted.
     *
     * @return array
     */
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
