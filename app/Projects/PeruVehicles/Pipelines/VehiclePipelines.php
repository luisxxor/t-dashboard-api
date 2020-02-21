<?php

namespace App\Projects\PeruVehicles\Pipelines;

use MongoDB\BSON\ObjectID;

/**
 * Trait PropertyPipelines
 * @package App\Projects\PeruVehicles\Pipelines
 * @version Dec 24, 2019, 15:31 UTC
*/
trait VehiclePipelines
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
    protected function pipelineVehiclesToSearch( string $searchId, array $metadata ): array
    {
        // pipeline
        $pipeline = [];

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
                'from' => 'publication_types',
                'localField' => 'publication_type_id',
                'foreignField' => '_id',
                'as' => 'publication_types_docs'
            ]
        ];
        // geo within and filters ($match)  
        if (!empty($metadata)) {
            $pipeline[] = [
                '$match' => $metadata
            ];
        }

        // fields ($addFields)
        $pipeline[] = [
            '$addFields' => [
                'vehicle_id' => '$_id',
                'search_id' => new ObjectID( $searchId ),
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
                'region_id' => 0,
                'regions_docs' => 0,
                'publication_type_id' => 0,
                'publication_types_docs' => 0,
            ]
        ];

        // insert into select ($merge)
        $pipeline[] = [
            '$merge' => [
                'into' => 'searched_vehicles',
                'on' => [ 'vehicle_id', 'search_id' ],
                'whenMatched' => 'merge',
                'whenNotMatched' => 'insert',
            ],
        ];

        return $pipeline;
    }
}
