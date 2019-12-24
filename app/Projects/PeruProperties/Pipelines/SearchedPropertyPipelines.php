<?php

namespace App\Projects\PeruProperties\Pipelines;

use MongoDB\BSON\ObjectID;

/**
 * Trait SearchedPropertyPipelines
 * @package App\Projects\PeruProperties\Pipelines
 * @version Dec 24, 2019, 15:44 UTC
*/
trait SearchedPropertyPipelines
{
    /**
     * Return pipeline to retrive paginated searched properties
     * from given search.
     *
     * @param string $searchId The id of the current search.
     * @param array $pagination {
     *     The values of the pagination
     *
     *     @type int $perpage [required] The number of rows per each
     *           page of the pagination.
     *     @type int $offset [required] The offset to paginate.
     *     @type string $field [required] The field needed to be sorted.
     *     @type string $sort [required] The 'asc' or 'desc' to be sorted.
     * }
     *
     * @return array
     */
    protected function pipelinePropertiesFromSearch( string $searchId, array $pagination ): array
    {
        // pipeline
        $pipeline = [];

        // sort array
        if ( array_key_exists( $pagination[ 'field' ], $this->sortFields ) === true ) {
            $sortFields = array_merge( $this->sortFields, [ $pagination[ 'field' ] => $pagination[ 'sort' ] ] );
        }
        else {
            $sortFields = array_merge( [ $pagination[ 'field' ] => $pagination[ 'sort' ] ], $this->sortFields );
        }

        // select the current search ($match)
        $pipeline[] = [
            '$match' => [
                'search_id' => [ '$eq' => new ObjectID( $searchId ) ]
            ]
        ];

        // order by ($sort)
        $pipeline[] = [
            '$sort' => $sortFields
        ];

        // geo fields ($project)
        $pipeline[] = [
            '$project' => [
                '_id' => '$property_id',
                'type' => 'Feature',
                'properties' => [
                    'address' => '$address',
                    'dollars_price' => [ '$convert' => [ 'input' => '$dollars_price', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'others_price' => [ '$convert' => [ 'input' => '$others_price', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'bedrooms' => [ '$convert' => [ 'input' => '$bedrooms', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'bathrooms' => [ '$convert' => [ 'input' => '$bathrooms', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'parkings' => [ '$convert' => [ 'input' => '$parkings', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'property_type' => '$property_type',
                    'publication_date' => [ '$toString' => [ '$publication_date' ] ],
                    'image_list' => [ '$ifNull' => [ '$image_list', null ] ],
                    'distance' => [ '$convert' => [ 'input' => '$distance', 'to' => 'int', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                ],
                'geometry' => '$geo_location'
            ]
        ];

        // offset ($skip)
        if ( $pagination[ 'offset' ] !== null ) {
            $pipeline[] = [
                '$skip' => $pagination[ 'offset' ],
            ];
        }

        // limit ($limit)
        if ( $pagination[ 'perpage' ] !== null ) {
            $pipeline[] = [
                '$limit' => $pagination[ 'perpage' ],
            ];
        }

        return $pipeline;
    }

    /**
     * Return pipeline to retrive selected searched properties
     * from given search.
     *
     * @param string $searchId The id of the current search.
     *
     * @return array
     */
    protected function pipelineSelectedPropertiesFromSearch( string $searchId ): array
    {
        // pipeline
        $pipeline = [];

        // select the current search ($match)
        $pipeline[] = [
            '$match' => [
                'search_id' => [ '$eq' => new ObjectID( $searchId ) ],
                'selected' => [ '$eq' => true ]
            ]
        ];

        // order by ($sort)
        $pipeline[] = [
            '$sort' => $this->sortFields
        ];

        // fields ($project)
        $pipeline[] = [
            '$project' => [
                // body
                'body' => [
                    '_id'                   => '$property_id',
                    'link'                  => [ '$ifNull' => [ '$link', '' ] ],
                    'antiquity_years'       => [ '$ifNull' => [ '$antiquity_years', '' ] ],
                    'bedrooms'              => [ '$convert' => [ 'input' => '$bedrooms', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'bathrooms'             => [ '$convert' => [ 'input' => '$bathrooms', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'parkings'              => [ '$convert' => [ 'input' => '$parkings', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'total_area_m2'         => [ '$convert' => [ 'input' => '$total_area_m2', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'build_area_m2'         => [ '$convert' => [ 'input' => '$build_area_m2', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'address'               => [ '$ifNull' => [ '$address', '' ] ],
                    'publication_date'      => [ '$dateToString' => [ 'date' => '$publication_date', 'format' => '%d-%m-%Y', 'onNull' => 0.0 ] ],
                    'dollars_price'         => [ '$convert' => [ 'input' => '$dollars_price', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'others_price'          => [ '$convert' => [ 'input' => '$others_price', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'region'                => [
                        '$concat' => [
                            [ '$arrayElemAt' => [ '$regions_docs.sub_reg1', 0 ] ],
                            ', ',
                            [ '$arrayElemAt' => [ '$regions_docs.sub_reg2', 0 ] ],
                            ', ',
                            [ '$arrayElemAt' => [ '$regions_docs.sub_reg3', 0 ] ]
                        ]
                    ],
                    'publication_type'      => [ '$ifNull' => [ '$publication_type', '' ] ],
                    'urbanization'          => [ '$ifNull' => [ '$urbanization', '' ] ],
                    'location'              => [ '$ifNull' => [ '$location', '' ] ],
                    'reference_place'       => [ '$ifNull' => [ '$reference_place', '' ] ],
                    'comment_subtitle'      => [ '$ifNull' => [ '$comment_subtitle', '' ] ],
                    'comment_description'   => [ '$ifNull' => [ '$comment_description', '' ] ],
                    'pool'                  => [ '$convert' => [ 'input' => '$pool', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'elevator'              => [ '$convert' => [ 'input' => '$elevator', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'property_type'         => [ '$ifNull' => [
                        [ '$arrayElemAt' => [ '$property_types_docs.name', 0 ] ],
                        ''
                    ] ],
                    'property_new'          => [ '$ifNull' => [ '$property_new', '' ] ],
                    'longitude'             => [ '$convert' => [ 'input' => '$longitude', 'to' => 'string', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'latitude'              => [ '$convert' => [ 'input' => '$latitude', 'to' => 'string', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'distance'              => [ '$convert' => [ 'input' => '$distance', 'to' => 'int', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                ],

                'header' => $this->header,

                'image_list' => [ '$ifNull' => [ '$image_list', '' ] ],

                'geometry' => '$geo_location'
            ]
        ];

        return $pipeline;
    }
}
