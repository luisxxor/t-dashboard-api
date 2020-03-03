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

        // remove _id (ObjectId)
        $pipeline[] = [
            '$project' => [
                '_id' => 0,
            ]
        ];

        // set property id as _id
        $pipeline[] = [
            '$addFields' => [
                '_id' => '$property_id',
            ]
        ];

        // remove property_id (old _id)
        $pipeline[] = [
            '$project' => [
                'property_id' => 0,
            ]
        ];

        return $pipeline;
    }

    /**
     * Return pipeline to retrive selected searched properties
     * from given search in excel format.
     *
     * @param string $searchId The id of the current search.
     *
     * @return array
     */
    protected function pipelineSelectedPropertiesFromSearchExcelFormat( string $searchId ): array
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
                '_id'                       => '$property_id',
                'link'                      => [ '$ifNull' => [ '$link', '' ] ],
                'antiquity_years'           => [ '$ifNull' => [ '$antiquity_years', '' ] ],
                'bedrooms'                  => [ '$ifNull' => [ '$bedrooms', 0.0 ] ],
                'bathrooms'                 => [ '$ifNull' => [ '$bathrooms', 0.0 ] ],
                'parkings'                  => [ '$ifNull' => [ '$parkings', 0.0 ] ],
                'total_area_m2'             => [ '$ifNull' => [ '$total_area_m2', 0.0 ] ],
                'build_area_m2'             => [ '$ifNull' => [ '$build_area_m2', 0.0 ] ],
                'address'                   => [ '$ifNull' => [ '$address', '' ] ],
                'publication_date_custom'   => [ '$dateToString' => [ 'date' => '$publication_date', 'format' => '%d-%m-%Y', 'onNull' => '' ] ],
                'dollars_price'             => [ '$ifNull' => [ '$dollars_price', 0.0 ] ],
                'others_price'              => [ '$ifNull' => [ '$others_price', 0.0 ] ],
                'region'                    => [ '$ifNull' => [ '$region', '' ] ],
                'publication_type'          => [ '$ifNull' => [ '$publication_type', '' ] ],
                'urbanization'              => [ '$ifNull' => [ '$urbanization', '' ] ],
                'location'                  => [ '$ifNull' => [ '$location', '' ] ],
                'reference_place'           => [ '$ifNull' => [ '$reference_place', '' ] ],
                'comment_subtitle'          => [ '$ifNull' => [ '$comment_subtitle', '' ] ],
                'comment_description'       => [ '$ifNull' => [ '$comment_description', '' ] ],
                'pool'                      => [ '$ifNull' => [ '$pool', 0.0 ] ],
                'elevator'                  => [ '$ifNull' => [ '$elevator', 0.0 ] ],
                'property_type'             => [ '$ifNull' => [ '$property_type', '' ] ],
                'property_new'              => [ '$ifNull' => [ '$property_new', '' ] ],
                'longitude'                 => [ '$ifNull' => [ '$longitude', 0.0 ] ],
                'latitude'                  => [ '$ifNull' => [ '$latitude', 0.0 ] ],
                'distance'                  => [ '$convert' => [ 'input' => '$distance', 'to' => 'int', 'onError' => 'Error', 'onNull' => null ] ],
            ]
        ];

        return $pipeline;
    }
}
