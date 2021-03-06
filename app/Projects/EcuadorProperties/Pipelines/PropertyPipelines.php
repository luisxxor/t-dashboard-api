<?php

namespace App\Projects\EcuadorProperties\Pipelines;

use MongoDB\BSON\ObjectID;

/**
 * Trait PropertyPipelines
 * @package App\Projects\EcuadorProperties\Pipelines
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
     *
     * @return array
     */
    protected function pipelinePropertiesToSearch( string $searchId, array $metadata ): array
    {
        // pipeline
        $pipeline = [];

        // geo distance ($geoNear)
        $pipeline[] = [
            '$geoNear' => $metadata[ 'distance' ]
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

        // join con property_types ($lookup)
        $pipeline[] = [
            '$lookup' => [
                'from' => 'publication_types',
                'localField' => 'publication_type_id',
                'foreignField' => '_id',
                'as' => 'publication_types_docs'
            ]
        ];

        // geo within and filters ($match)
        $pipeline[] = [
            '$match' => $metadata[ 'propertiesWithin' ] + $metadata[ 'filters' ]
        ];

        // fields ($addFields)
        $pipeline[] = [
            '$addFields' => [
                'property_id' => '$_id',
                'search_id' => new ObjectID( $searchId ),
                'property_type' => [ '$ifNull' => [
                    [ '$arrayElemAt' => [ '$property_types_docs.name', 0 ] ],
                    null
                ] ],
                'publication_type' => [ '$ifNull' => [
                    [ '$arrayElemAt' => [ '$publication_types_docs.name', 0 ] ],
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

        // remove _id to avoid unique index error ($project)
        $pipeline[] = [
            '$project' => [
                '_id' => 0,
                'property_type_id' => 0,
                'property_types_docs' => 0,
                'publication_type_id' => 0,
                'publication_types_docs' => 0,
                'region_id' => 0,
                'regions_docs' => 0,
            ]
        ];

        // insert into select ($merge)
        $pipeline[] = [
            '$merge' => [
                'into' => 'searched_properties',
                'on' => [ 'property_id', 'search_id' ],
                'whenMatched' => 'merge',
                'whenNotMatched' => 'insert',
            ],
        ];

        return $pipeline;
    }
}
