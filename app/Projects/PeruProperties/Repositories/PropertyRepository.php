<?php

namespace App\Projects\PeruProperties\Repositories;

use App\Projects\PeruProperties\Models\Property;
use App\Projects\PeruProperties\Models\PropertyType;
use App\Projects\PeruProperties\Models\Search;
use Carbon\Carbon;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;
use MongoDB\BSON\UTCDateTime;

/**
 * Class PropertyRepository
 * @package App\Projects\PeruProperties\Repositories
 * @version May 31, 2019, 5:17 am UTC
*/
class PropertyRepository
{
    /**
     * @var array
     */
    protected $constants;

    /**
     * @var array
     */
    protected $outputFields = [
        'id',
        'dollars_price',
        'others_price',
        'bedrooms',
        'bathrooms',
        'parkings',
        'property_type',
        'publication_date_formated',
        'image_list',
    ];

    /**
     * Header for export files.
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
        'property_new'          => 'Propiedad nueva',
        'longitude'             => 'Longitud',
        'latitude'              => 'Latitud',
        'distance'              => 'Distancia (m)',
    ];

    /**
     * Fields and its order to sort the properties.
     *
     * @var string
     */
    protected $sortFields = [
        'publication_date' => -1,
        'distance' => -1,
        '_id' => -1,
    ];

    public function __construct() {
        $this->constants = config( 'multi-api.pe-properties.constants' );
    }

    /**
     * Return the filters to the query.
     *
     * @param array $filters
     * @return array
     */
    protected function getFiltersToQuery( $filters ): array
    {
        $filterFields = [
            'slidersFields' => [
                'bedrooms' => [
                    'name' => $this->constants[ 'FILTER_FIELD_BEDROOMS' ],
                    'clousure' => function ( $field ) {
                        return ( $field === '5' ) ? 5.1 : (float)$field;
                    }
                ],
                'bathrooms' => [
                    'name' => $this->constants[ 'FILTER_FIELD_BATHROOMS' ],
                    'clousure' => function ( $field ) {
                        return ( $field === '5' ) ? 5.1 : (float)$field;
                    }
                ],
                'parkings' => [
                    'name' => $this->constants[ 'FILTER_FIELD_PARKINGS' ],
                    'clousure' => function ( $field ) {
                        return ( $field === '5' ) ? 5.1 : (float)$field;
                    }
                ]
            ],
            'numericFields' => [
                'antiquity_years' => [
                    'name' => $this->constants[ 'FILTER_FIELD_ANTIQUITY_YEARS' ],
                    'clousure' => function ( $field ) {
                        return (int)$field;
                    }
                ],
                'total_area_m2' => [
                    'name' => $this->constants[ 'FILTER_FIELD_TOTAL_AREA_M2' ],
                    'clousure' => function ( $field ) {
                        return (float)$field;
                    }
                ],
                'build_area_m2' => [
                    'name' => $this->constants[ 'FILTER_FIELD_BUILD_AREA_M2' ],
                    'clousure' => function ( $field ) {
                        return (float)$field;
                    }
                ],
                'publication_date' => [
                    'name' => $this->constants[ 'FILTER_FIELD_PUBLICATION_DATE' ],
                    'clousure' => function ( $field ) {
                        $carbonDate = Carbon::createFromFormat( 'd/m/Y', trim( $field ) );

                        # evaluar si es necesario convertir a UTCDateTime
                        return new UTCDateTime( $carbonDate );
                    },
                ]
            ],
            'combosFields' => [
                'property_type_id' => [
                    'name' => $this->constants[ 'FILTER_FIELD_PROPERTY_TYPE' ],
                    'clousure' => function ( $field ) {
                        // select
                        $results = PropertyType::where( 'owner_name', $field )->get();

                        return array_column( $results->toArray(), '_id' );
                    },
                ],
                'publication_type' => [
                    'name' => $this->constants[ 'FILTER_FIELD_PUBLICATION_TYPE' ],
                ],
                'property_new' => [
                    'name' => $this->constants[ 'FILTER_FIELD_PROPERTY_NEW' ],
                    'clousure' => function ( $field ) {
                        return (bool)$field;
                    },
                ]
            ]
        ];

        $output = [];

        // para slidersFields
        foreach ( $filterFields[ 'slidersFields' ] as $key => $field ) {

            // si viene el campo
            if ( isset( $filters[ $field[ 'name' ] ] ) ) {

                // si no viene vacio el campo
                if ( empty( $filters[ $field[ 'name' ] ] ) === false ) { // si el campo viene vacio viene un string vacio

                    // obtenemos ambos valores del rango
                    $field_array = explode( '--', $filters[ $field[ 'name' ] ] );
                    $min_field = (string)(int)$field_array[ 0 ];
                    $max_field = (string)(int)$field_array[ 1 ];

                    //ejecutamos un callback, en caso de ser necesario
                    if ( isset( $field[ 'clousure' ] ) ) {
                        $min_field = $field[ 'clousure' ]( $min_field );
                        $max_field = $field[ 'clousure' ]( $max_field );
                    }

                    //se realiza where
                    if ( $min_field === $max_field ) {
                        $output[ $key ] = [ '$eq' => $min_field ];
                    }
                    else {
                        if ( is_decimal( $max_field ) === true ) {
                            $output[ $key ] = [ '$gte' => $min_field, ];
                        }
                        else {
                            $output[ $key ] = [ '$gte' => $min_field, '$lte' => $max_field ];
                        }
                    }
                }
            }
        }

        // para numericFields
        foreach ( $filterFields[ 'numericFields' ] as $key => $field ) {

            // si viene el campo
            if ( isset( $filters[ $field[ 'name'] ] ) ) {

                // si no viene vacio el campo
                if ( $filters[ $field[ 'name' ] ] !== '--' ) { // si el campo viene vacio viene solo '=='

                    // obtenemos ambos valores del rango
                    $field_array = explode( '--', $filters[ $field[ 'name' ] ] );
                    $min_field = (string)$field_array[ 0 ];
                    $max_field = (string)$field_array[ 1 ];

                    //ejecutamos un callback, en caso de ser necesario
                    if ( isset( $field[ 'clousure' ] ) ) {
                        $min_field = $field[ 'clousure' ]( $min_field );
                        $max_field = $field[ 'clousure' ]( $max_field );
                    }

                    //se realiza where
                    if ( $min_field === $max_field ) {
                        $output[ $key ] = [ '$eq' => $min_field ];
                    }
                    else {
                        $output[ $key ] = [ '$gte' => $min_field, '$lte' => $max_field ];
                    }
                }
            }
        }

        // para combosFields
        foreach ( $filterFields[ 'combosFields' ] as $key => $field ) {

            // si viene el campo
            if ( isset( $filters[ $field[ 'name' ] ] ) ) {

                // si no viene vacio el campo
                if ( $filters[ $field[ 'name' ] ] !== null || $filters[ $field[ 'name' ] ] !== '' ) {

                    //obtenemos el campo del filters que viene del buscador
                    $finalField = $filters[ $field[ 'name' ] ];

                    //ejecutamos un callback para la busqueda, en caso de ser necesario
                    if ( isset( $field[ 'clousure' ] ) ) {
                        $finalField = $field[ 'clousure' ]( $finalField );
                    }

                    //ejecutamos un callback para la mascara (para operadores ILIKE o LIKE), en caso de ser necesario
                    if ( isset( $field[ 'mask' ] ) ) {
                        $finalField = $field[ 'mask' ]( $finalField );
                    }

                    //se realiza where considerando si incluye operador espedifico
                    if ( is_array( $finalField ) === true ) {
                        $output[ $key ] = [ '$in' => $finalField ];
                    }
                    else {
                        $output[ $key ] = [ '$eq' => $finalField ];
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Return the array of vertices of the polygon.
     *
     * @param  array $arrayShape
     *
     * @return array
     */
    protected function getPropertiesWithinToQuery( $arrayShape ): array
    {
        $polygon = [];

        $index = 0;
        foreach ( $arrayShape as $value ) {
            $polygon[ $index ][ 0 ] = (float)$value[ 'lng' ];
            $polygon[ $index ][ 1 ] = (float)$value[ 'lat' ];

            $index++;
        }

        // close polygon
        $polygon[] = $polygon[ 0 ];

        // match
        $match = [
            'geo_location' => [
                '$geoWithin' => [
                    '$geometry' => [
                        'type' => 'Polygon' ,
                        'coordinates' => [ $polygon ]
                    ]
                ]
            ]
        ];

        return $match;
    }

    /**
     * Get the distance between the base marker and each property.
     *
     * @param float $lat
     * @param float $lng
     *
     * @return array
     */
    protected function getDistanceToQuery( float $lat, float $lng ): array
    {
        $geoNear = [
            'near' => [
                'type' => 'Point',
                'coordinates' => [ $lng, $lat ]
            ],
            'spherical' => true,
            'distanceField' => 'distance',
            '$limit' => 100000 # pendiente definir este limite
        ];

        return $geoNear;
    }

    /**
     * Store matched properties in a temp collection.
     *
     * @param Search $search The search model to store the matched properties.
     *
     * @return array
     */
    public function storeTempProperties( Search $search ): array
    {
        // get properties within (parameters)
        $propertiesWithin = $this->getPropertiesWithinToQuery( $search[ 'metadata' ][ 'vertices' ] );

        // get filters (parameters)
        $filters = $this->getFiltersToQuery( (array)$search[ 'metadata' ][ 'filters' ] );

        // get distance (parameters)
        $distance = $this->getDistanceToQuery( $search[ 'metadata' ][ 'initPoint' ][ 'lat' ], $search[ 'metadata' ][ 'initPoint' ][ 'lng' ] );

        // pipeline
        $pipeline = $this->pipelinePropertiesToTemp( $propertiesWithin, $filters, $distance );

        // insert into select ($out)
        $pipeline[] = [
            '$out' => $search->_id
        ];

        // exec query
        $toTemp = Property::raw( ( function( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );

        return $toTemp->toArray(); // empty if ok
    }

    /**
     * Return (paginated) 'properties' in the temp collection
     * specified by $searchId.
     *
     * @param string $searchId The collection name to get the properties.
     * @param array $pagination {
     *     The values of the pagination
     *
     *     @type int $page [required] The page needed to return.
     *     @type int $perpage [required] The number of rows per each
     *           page of the pagination.
     *     @type string $field [optional] The field needed to be sorted.
     *     @type string $sort [optional] The 'asc' or 'desc' to be sorted.
     * }
     * @return array
     */
    public function getTempProperties( string $searchId, array $pagination ): array
    {
        // select count of temp collection
        $total = DB::connection( 'peru_properties' )->collection( $searchId )->count();

        // calculo la cantidad de paginas del resultado a partir de la cantidad
        // de registros '$total' y la cantidad de registros por pagina '$pagination[ 'perpage' ]'
        $pages = ceil( $total / $pagination[ 'perpage' ] );

        // valido que la ultima pagina no este fuera de rango
        $page = $pagination[ 'page' ] > $pages ? $pages : $pagination[ 'page' ];

        // validacion cero
        $page = $page === 0.0 ? 1 : $page;

        // limit y offset para paginar, define el número 0 para empezar
        // a paginar multiplicado por la cantidad de registros por pagina 'perpage'
        $offset = ( $page - 1 ) * $pagination[ 'perpage' ];

        // pipeline
        $pipeline = $this->pipelinePropertiesFromTemp( $pagination[ 'perpage' ], $offset, $pagination[ 'field' ], $pagination[ 'sort' ] );

        // select paginated
        $pagitatedItems = DB::connection( 'peru_properties' )
            ->collection( $searchId )
            ->raw( ( function ( $collection ) use ( $pipeline ) {
                return $collection->aggregate( $pipeline );
            } ) )->toArray();

        // new instance of LengthAwarePaginator
        $paginator = new LengthAwarePaginator( $pagitatedItems, $total, $pagination[ 'perpage' ], $page );

        // cast to array
        $paginator = $paginator->toArray();

        // search id
        $paginator[ 'searchId' ] = $searchId;

        return $paginator;
    }

    /**
     * Return 'properties' in the temp collection
     * named as $searchId, that were selected by user.
     *
     * @param string $searchId The collection name to get the properties.
     *
     * @return array
     */
    public function getSelectedTempProperties( string $searchId ): array
    {
        // get the search document
        $search = Search::find( $searchId );

        // pipeline
        $pipeline = $this->pipelineSelectedPropertiesFromTemp( $search[ 'selected_properties' ] );

        // get selected data in final format
        $results = DB::connection( 'peru_properties' )
            ->collection( $searchId )
            ->raw( ( function ( $collection ) use ( $pipeline ) {
                return $collection->aggregate( $pipeline );
            } ) )->toArray();

        return [
            'data' => [
                'header'    => array_values( (array)array_column( $results, 'header' )[ 0 ]->jsonSerialize() ),
                'body'      => array_column( $results, 'body' ),
            ],
            'metadata' => [
                'vertices'      => $search[ 'metadata' ][ 'vertices' ] ?? null,
                'filters'       => $search[ 'metadata' ][ 'filters' ] ?? null,
                'lat'           => $search[ 'metadata' ][ 'initPoint' ][ 'lat' ] ?? null,
                'lng'           => $search[ 'metadata' ][ 'initPoint' ][ 'lng' ] ?? null,
                'address'       => $search[ 'metadata' ][ 'initPoint' ][ 'address' ] ?? null,
                'image_list'    => array_column( $results, 'image_list' ) ?? null,
                'rowQuantity'   => count( $results ),
            ]
        ];
    }

    /**
     * Return pipeline to retrive properties
     * that match with the specified input.
     *
     * @param array $propertiesWithin
     * @param array $filters
     * @param array $distance
     * @param bool|null $allFields
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    protected function pipelinePropertiesToTemp( array $propertiesWithin, array $filters, array $distance ): array
    {
        // pipeline
        $pipeline = [];

        // geo distance ($geoNear)
        $pipeline[] = [
            '$geoNear' => $distance
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

        // filters ($match)
        if ( empty( $filters ) === false ) {
            $pipeline[] = [
                '$match' => $filters
            ];
        }

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

        // geo within ($match)
        $pipeline[] = [
            '$match' => $propertiesWithin
        ];

        return $pipeline;
    }

    /**
     * Return pipeline to retrive properties paginated
     * from temp collection.
     *
     * @param int|null $limit
     * @param int|null $offset
     * @param string $offset
     * @param int $offset
     *
     * @return array
     */
    protected function pipelinePropertiesFromTemp( $limit = null, $offset = null, string $field, int $sort ): array
    {
        // pipeline
        $pipeline = [];

        // sort array
        if ( array_key_exists( $field, $this->sortFields ) ) {
            $sortFields = array_merge( $this->sortFields, [ $field => $sort ] );
        }
        else {
            $sortFields = array_merge( [ $field => $sort ], $this->sortFields );
        }

        // order by ($sort)
        $pipeline[] = [
            '$sort' => $sortFields
        ];

        // geo fields ($project)
        $pipeline[] = [
            '$project' => [
                'type' => 'Feature',
                'properties' => [
                    'id' => '$id',
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
                    'geo_location' => '$geo_location'
                ],
                'geo_location' => '$geo_location',
                'geometry' => '$geo_location'
            ]
        ];

        // offset ($skip)
        if ( $offset !== null ) {
            $pipeline[] = [
                '$skip' => $offset,
            ];
        }

        // limit ($limit)
        if ( $limit !== null ) {
            $pipeline[] = [
                '$limit' => $limit,
            ];
        }

        return $pipeline;
    }

    /**
     * Return pipeline to retrive selected properties
     * from temp collection.
     *
     * @param array $ids
     *
     * @return array
     */
    protected function pipelineSelectedPropertiesFromTemp( array $ids ): array
    {
        // pipeline
        $pipeline = [];

        // where in ($match)
        if ( $ids !== [ '*' ] ) {
            $pipeline[] = [
                '$match' => [
                    '_id' => [ '$in' => $ids ]
                ]
            ];
        }

        // order by ($sort)
        $pipeline[] = [
            '$sort' => $this->sortFields
        ];

        // fields ($project)
        $pipeline[] = [
            '$project' => [
                // body
                'body' => [
                    '_id'                   => '$_id',
                    'link'                  => [ '$ifNull' => [ '$link', null ] ],
                    'antiquity_years'       => [ '$ifNull' => [ '$antiquity_years', null ] ],
                    'bedrooms'              => [ '$convert' => [ 'input' => '$bedrooms', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'bathrooms'             => [ '$convert' => [ 'input' => '$bathrooms', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'parkings'              => [ '$convert' => [ 'input' => '$parkings', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'total_area_m2'         => [ '$convert' => [ 'input' => '$total_area_m2', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'build_area_m2'         => [ '$convert' => [ 'input' => '$build_area_m2', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'address'               => [ '$ifNull' => [ '$address', null ] ],
                    'publication_date'      => [ '$convert' => [ 'input' => '$publication_date', 'to' => 'string', 'onError' => 'Error', 'onNull' => 0.0 ] ],
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
                    'publication_type'      => [ '$ifNull' => [ '$publication_type', null ] ],
                    'urbanization'          => [ '$ifNull' => [ '$urbanization', null ] ],
                    'location'              => [ '$ifNull' => [ '$location', null ] ],
                    'reference_place'       => [ '$ifNull' => [ '$reference_place', null ] ],
                    'comment_subtitle'      => [ '$ifNull' => [ '$comment_subtitle', null ] ],
                    'comment_description'   => [ '$ifNull' => [ '$comment_description', null ] ],
                    'pool'                  => [ '$convert' => [ 'input' => '$pool', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'elevator'              => [ '$convert' => [ 'input' => '$elevator', 'to' => 'double', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'property_type'         => [ '$ifNull' => [
                        [ '$arrayElemAt' => [ '$property_types_docs.name', 0 ] ],
                        null
                    ] ],
                    'property_new'          => [ '$ifNull' => [ '$property_new', null ] ],
                    'longitude'             => [ '$convert' => [ 'input' => '$longitude', 'to' => 'string', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'latitude'              => [ '$convert' => [ 'input' => '$latitude', 'to' => 'string', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                    'distance'              => [ '$convert' => [ 'input' => '$distance', 'to' => 'int', 'onError' => 'Error', 'onNull' => 0.0 ] ],
                ],

                'header' => $this->header,

                // 'image_list' => [
                //     '_id' => '$_id',
                //     'image_list' => [ '$ifNull' => [ '$image_list', null ] ]
                // ],

                'image_list' => [ '$ifNull' => [ '$image_list', null ] ],

                'geometry' => '$geo_location'
            ]
        ];

        return $pipeline;
    }

    //

    /**
     * Return 'features' value of the geoJSON.
     *
     * @param  array $vertices
     * @param  array $filters
     * @return array
     */
    /*public function selectGeoJSON( array $vertices, array $filters = [] ): array
    {
        // get properties within (parameters)
        $propertiesWithin = $this->getPropertiesWithinToQuery( $vertices );

        // get filters (parameters)
        $filters = $this->getFiltersToQuery( $filters );

        // pipeline
        $pipeline = $this->pipelineGeoJSON( $propertiesWithin, $filters );

        // select
        $results = Property::raw( ( function( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );

        // construct geoJSON
        $geoJSON = [
            'type' => 'FeatureCollection',
            'features' => $results
        ];

        return $geoJSON;
    }*/

    /**
     * Return pipeline to retrive properties in geojson format
     * that match with the specified input.
     *
     * @param array $propertiesWithin
     * @param array $filters
     * @return array
     */
    /*protected function pipelineGeoJSON( array $propertiesWithin, array $filters ): array
    {
        // pipeline
        $pipeline = [];

        // join con property_types ($lookup)
        $pipeline[] = [
            '$lookup' => [
                'from' => 'property_types',
                'localField' => 'property_type_id',
                'foreignField' => '_id',
                'as' => 'property_types_docs'
            ]
        ];

        // filters ($match)
        if ( empty( $filters ) === false ) {
            $pipeline[] = [
                '$match' => $filters
            ];
        }

        // geo fields ($project)
        $pipeline[] = [
            '$project' => [
                'type' => 'Feature',
                'properties' => [
                    'comment_subtitle' => '$comment_subtitle',
                    'bedrooms' => '$bedrooms'
                ],
                'geo_location' => '$geo_location',
                'geometry' => '$geo_location'
            ]
        ];

        // fields ($addFields)
        $pipeline[] = [
            '$addFields' => [
                'property_type' => [ '$ifNull' => [
                    [ '$arrayElemAt' => [ '$property_types_docs.name', 0 ] ],
                    null
                ] ],
            ]
        ];

        // geo within ($match)
        $pipeline[] = [
            '$match' => $propertiesWithin
        ];

        return $pipeline;
    }*/
}
