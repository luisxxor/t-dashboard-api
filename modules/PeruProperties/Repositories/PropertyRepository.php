<?php

namespace Modules\PeruProperties\Repositories;

use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Modules\Common\Repositories\PropertyRepository as CommonPropertyRepository;
use Modules\PeruProperties\Models\Property;
use Modules\PeruProperties\Models\PropertyType;
use Modules\PeruProperties\Models\PublicationType;
use Modules\PeruProperties\Models\Search;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDateTime;

/**
 * Class PropertyRepository
 * @package Modules\PeruProperties\Repositories
 * @version May 31, 2019, 09:17 UTC
*/
class PropertyRepository extends CommonPropertyRepository
{
    /**
     * @var string The project in app
     */
    protected $projectCode = 'pe-properties';

    /**
     * Fields and its order to sort the results.
     *
     * @var string
     */
    protected $sortFields = [
        'publication_date' => -1,
        '_id' => 1,
    ];

    /**
     * Header for export files (with nested values, if any).
     *
     * @var array
     */
    protected $header = [
        '_id'                   => 'Código',
        'link'                  => 'Enlace',
        'antiquity_years'       => 'Antigüedad',
        'bedrooms'              => 'Habitaciones',
        'bathrooms'             => 'Baños',
        'parkings'              => 'Cocheras',
        'total_area_m2'         => 'Área total',
        'build_area_m2'         => 'Área construida',
        'address'               => 'Dirección',
        'publication_date'      => 'Fecha de publicación',
        'dollars_price'         => 'Precio (USD)',
        'others_price'          => 'Precio (Soles)',
        'region'                => 'Región',
        'publication_type'      => 'Tipo de publicación',
        'urbanization'          => 'Urbanización',
        'location'              => 'Locación',
        'reference_place'       => 'Lugar de referencia',
        'comment_subtitle'      => 'Resumen',
        'comment_description'   => 'Descripción',
        'pool'                  => 'Piscina',
        'elevator'              => 'Ascensor',
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

        $this->constants = config( 'multi-api.pe-properties.constants' );
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
            $this->constants[ 'FILTER_FIELD_ANTIQUITY_YEARS' ] => [
                'name' => 'antiquity_years',
                'clousure' => function ( $field ) {
                    return (int)$field;
                }
            ],
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
                    $results = PropertyType::where( 'owner_name', $field )->get();
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
