<?php

namespace Modules\DominicanaProperties\Repositories;

use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Modules\Common\Repositories\PropertyRepository as CommonPropertyRepository;
use Modules\DominicanaProperties\Models\Property;
use Modules\DominicanaProperties\Models\PropertyType;
use Modules\DominicanaProperties\Models\PublicationType;
use Modules\DominicanaProperties\Models\Search;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDateTime;

/**
 * Class PropertyRepository
 * @package Modules\DominicanaProperties\Repositories
 * @version May 27, 2020, 17:41 UTC
*/
class PropertyRepository extends CommonPropertyRepository
{
    /**
     * @var string The project in app
     */
    protected $projectCode = 'do-properties';

    /**
     * Header for export files (with nested values, if any).
     *
     * @var array
     */
    protected $header = [
        'customId'              => 'Índice',
        'webpage'               => 'Fuente',
        '_id'                   => 'Código',
        'link'                  => 'Enlace',
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

    /**
     * Create a new repository instance.
     *
     * @param Application $app
     * @param Search $searchMod
     *
     * @return void
     */
    public function __construct( Application $app, Search $searchMod )
    {
        parent::__construct( $app, $searchMod );

        $this->constants = config( 'multi-api.do-properties.constants' );
    }

    /**
     * Configure the Model
     *
     * @return string
     */
    public function model()
    {
        return Property::class;
    }

    /**
     * Return filter slider fields.
     *
     * @return array
     */
    protected function filterSliderFields(): array
    {
        return [
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
        ];
    }

    /**
     * Return filter numeric fields.
     *
     * @return array
     */
    protected function filterNumericFields(): array
    {
        return [
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
        ];
    }

    /**
     * Return filter dropdown fields.
     *
     * @return array
     */
    protected function filterDropdownFields(): array
    {
        return [
            $this->constants[ 'FILTER_FIELD_PROPERTY_TYPE' ] => [
                'name' => 'property_type_id',
                'clousure' => function ( $field ) {
                    $results = PropertyType::where( 'name', $field )->get();
                    $results = array_column( $results->toArray(), '_id' );
                    foreach ( $results as $key => $value ) {
                        $results[ $key ] = new ObjectID( $value );
                    }
                    return $results;
                },
            ],
            $this->constants[ 'FILTER_FIELD_PUBLICATION_TYPE' ] => [
                'name' => 'publication_type_id',
                'clousure' => function ( $field ) {
                    $results = PublicationType::where( 'name', $field )->get();
                    $results = array_column( $results->toArray(), '_id' );
                    foreach ( $results as $key => $value ) {
                        $results[ $key ] = new ObjectID( $value );
                    }
                    return $results;
                },
            ],
            $this->constants[ 'FILTER_FIELD_IS_NEW' ] => [
                'name' => 'is_new',
                'clousure' => function ( $field ) {
                    return (bool)$field;
                },
            ],
        ];
    }
}
