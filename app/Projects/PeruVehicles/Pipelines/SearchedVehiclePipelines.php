<?php

namespace App\Projects\PeruVehicles\Pipelines;

use MongoDB\BSON\ObjectID;

/**
 * Trait SearchedPropertyPipelines
 * @package App\Projects\PeruVehicles\Pipelines
 * @version Dec 24, 2019, 15:44 UTC
*/
trait SearchedVehiclePipelines
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
                '_id' => '$vehicle_id',
                'publication_type' => '$publication_type',
                'properties' => [
                    'make' => [ '$ifNull' => [ '$make', null ] ],
                    'model' => [ '$ifNull' => [ '$model', null ] ],
                    'category' => [ '$ifNull' => [ '$category', null ] ],
                    'year' => [ '$ifNull' => [ '$year', null ] ],
                    'mileage' => [ '$ifNull' => [ '$mileage', null ] ],
                    'fuel_type' => [ '$ifNull' => [ '$fuel_type', null ] ],
                    'transmission' => [ '$ifNull' => [ '$transmission', null ] ],
                    'engine_displacement' => [ '$ifNull' => [ '$engine_displacement', null ] ],
                    'dollars_price' => [ '$ifNull' => [ '$dollars_price', null ] ],
                    'publication_date' => [ '$toString' => [ '$publication_date' ] ],
                    'image_list' => [ '$ifNull' => [ '$image_list', null ] ],
                    'temp_images' => [ '$ifNull' => [ '$temp_images', null ] ],
                ],
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
    protected function pipelineSelectedVehiclessFromSearch( string $searchId ): array
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
                '_id' => '$vehicle_id',
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
    protected function pipelineSelectedVehiclesFromSearchExcelFormat( string $searchId, string $publication_type ): array
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
        switch ($publication_type) {
            case 'auto':
                $pipeline[] = [
                    '$project' => [
                        '_id'                       => '$vehicle_id',
                        'link'                      => [ '$ifNull' => [ '$link', '' ] ],
                        'make'                      => [ '$ifNull' => [ '$make', '' ] ],
                        'model'                     => [ '$ifNull' => [ '$model', '' ] ],
                        'category'                  => [ '$ifNull' => [ '$category', '' ] ],
                        'year'                      => [ '$ifNull' => [ '$year', '' ] ],
                        'mileage'                   => [ '$ifNull' => [ '$mileage', 0.0 ] ],
                        'fuel_type'                 => [ '$ifNull' => [ '$fuel_type', '' ] ],
                        'transmission'              => [ '$ifNull' => [ '$transmission', '' ] ],
                        'engine_displacement'       => [ '$ifNull' => [ '$engine_displacement', '' ] ],
                        'steering_wheel'            => [ '$ifNull' => [ '$steering_wheel', '' ] ],
                        'drive_type'                => [ '$ifNull' => [ '$drive_type', '' ] ],
                        'color'                     => [ '$ifNull' => [ '$color', '' ] ],
                        'number_of_cylinders'       => [ '$ifNull' => [ '$number_of_cylinders', '' ] ],
                        'number_of_doors'           => [ '$ifNull' => [ '$number_of_doors', 0.0 ] ],
                        'dollars_price'             => [ '$ifNull' => [ '$dollars_price', 0.0 ] ],
                        'location'                  => [ '$ifNull' => [ '$location', '' ] ],
                        'publication_date_custom'   => [ '$dateToString' => [ 'date' => '$publication_date', 'format' => '%d-%m-%Y', 'onNull' => '' ] ],
                    ]
                ];
                break;
            case 'moto':
                $pipeline[] = [
                    '$project' => [
                        '_id'                       => '$vehicle_id',
                        'link'                      => [ '$ifNull' => [ '$link', '' ] ],
                        'make'                      => [ '$ifNull' => [ '$make', '' ] ],
                        'model'                     => [ '$ifNull' => [ '$model', '' ] ],
                        'category'                  => [ '$ifNull' => [ '$category', '' ] ],
                        'year'                      => [ '$ifNull' => [ '$year', '' ] ],
                        'mileage'                   => [ '$ifNull' => [ '$mileage', 0.0 ] ],
                        'fuel_type'                 => [ '$ifNull' => [ '$fuel_type', '' ] ],
                        'transmission'              => [ '$ifNull' => [ '$transmission', '' ] ],
                        'engine_displacement'       => [ '$ifNull' => [ '$engine_displacement', '' ] ],
                        'brakes'                    => [ '$ifNull' => [ '$brakes', '' ] ],
                        'starter_type'              => [ '$ifNull' => [ '$starter_type', '' ] ],
                        'drive_type'                => [ '$ifNull' => [ '$drive_type', '' ] ],
                        'color'                     => [ '$ifNull' => [ '$color', '' ] ],
                        'number_of_cylinders'       => [ '$ifNull' => [ '$number_of_cylinders', '' ] ],
                        'dollars_price'             => [ '$ifNull' => [ '$dollars_price', 0.0 ] ],
                        'location'                  => [ '$ifNull' => [ '$location', '' ] ],
                        'publication_date_custom'   => [ '$dateToString' => [ 'date' => '$publication_date', 'format' => '%d-%m-%Y', 'onNull' => '' ] ],
                    ]
                ];
                break;
            case 'bus-camion':
                $pipeline[] = [
                    '$project' => [
                        '_id'                       => '$vehicle_id',
                        'link'                      => [ '$ifNull' => [ '$link', '' ] ],
                        'make'                      => [ '$ifNull' => [ '$make', '' ] ],
                        'model'                     => [ '$ifNull' => [ '$model', '' ] ],
                        'category'                  => [ '$ifNull' => [ '$category', '' ] ],
                        'year'                      => [ '$ifNull' => [ '$year', '' ] ],
                        'mileage'                   => [ '$ifNull' => [ '$mileage', 0.0 ] ],
                        'fuel_type'                 => [ '$ifNull' => [ '$fuel_type', '' ] ],
                        'transmission'              => [ '$ifNull' => [ '$transmission', '' ] ],
                        'engine_displacement'       => [ '$ifNull' => [ '$engine_displacement', '' ] ],
                        'steering_wheel'            => [ '$ifNull' => [ '$steering_wheel', '' ] ],
                        'number_of_doors'           => [ '$ifNull' => [ '$number_of_doors', 0.0 ] ],
                        'drive_type'                => [ '$ifNull' => [ '$drive_type', '' ] ],
                        'color'                     => [ '$ifNull' => [ '$color', '' ] ],
                        'number_of_cylinders'       => [ '$ifNull' => [ '$number_of_cylinders', '' ] ],
                        'max_passengers'            => [ '$ifNull' => [ '$max_passengers', 0.0 ] ],
                        'max_cargo'                 => [ '$ifNull' => [ '$max_cargo', 0.0 ] ],
                        'tire_size'                 => [ '$ifNull' => [ '$tire_size', 0.0 ] ],
                        'brakes'                    => [ '$ifNull' => [ '$brakes', '' ] ],
                        'max_power'                 => [ '$ifNull' => [ '$max_power', 0.0 ] ],
                        'dollars_price'             => [ '$ifNull' => [ '$dollars_price', 0.0 ] ],
                        'location'                  => [ '$ifNull' => [ '$location', '' ] ],
                        'publication_date_custom'   => [ '$dateToString' => [ 'date' => '$publication_date', 'format' => '%d-%m-%Y', 'onNull' => '' ] ],
                    ]
                ];
                break;
            
            default:
                # code...
                break;
        }


        return $pipeline;
    }
}
