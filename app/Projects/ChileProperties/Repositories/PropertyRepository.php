<?php

namespace App\Projects\ChileProperties\Repositories;

use App\Projects\ChileProperties\Models\Property;
use App\Projects\ChileProperties\Models\Search;
use App\Projects\ChileProperties\Models\SearchedProperty;
use App\Projects\ChileProperties\Pipelines\FilterPipelines;
use App\Projects\ChileProperties\Pipelines\PropertyPipelines;
use App\Projects\ChileProperties\Pipelines\SearchedPropertyPipelines;
use Illuminate\Pagination\LengthAwarePaginator;
use MongoDB\BSON\ObjectID;

/**
 * Class PropertyRepository
 * @package App\Projects\ChileProperties\Repositories
 * @version May 31, 2019, 5:17 am UTC
*/
class PropertyRepository
{
    use FilterPipelines, PropertyPipelines, SearchedPropertyPipelines;

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
     * Fields and its order to sort the properties.
     *
     * @var string
     */
    protected $sortFields = [
        'publication_date' => -1,
        'distance' => -1,
        '_id' => -1,
    ];

    /**
     * Header for export files.
     *
     * @var array
     */
    public $header = [
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
        'others_price'          => 'Precio (Soles)',
        'uf_price'              => 'Unidad de Fomento',
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

    public function __construct() {
        $this->constants = config( 'multi-api.cl-properties.constants' );
    }

    /**
     * Make a search of properties but not specting response.
     *
     * @param float $lat
     * @param float $lng
     * @param int $maxDistance The maximum distance from the center
     *        point that the documents can be (in meters).
     *
     * @return void
     */
    public function searchPropertiesOnlyByGeonear( float $lat, float $lng, int $maxDistance ): void
    {
        // pipeline to get distance (parameters)
        $distance = $this->pipelineDistanceToQuery( $lat, $lng, $maxDistance );

        // pipeline
        $pipeline = $this->pipelinePropertiesOnlyByGeonear( $distance );

        // exec query
        Property::raw( ( function ( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );
    }

    /**
     * Store matched properties as searched properties from given search.
     *
     * @param Search $search The search model to store the matched properties.
     *
     * @return array
     */
    public function storeSearchedProperties( Search $search ): array
    {
        // pipeline to get properties within (parameters)
        $propertiesWithin = $this->pipelinePropertiesWithinToQuery( $search[ 'metadata' ][ 'vertices' ] );

        // pipeline to get filters (parameters)
        $filters = $this->pipelineFiltersToQuery( (array)$search[ 'metadata' ][ 'filters' ] );

        // pipeline to get distance (parameters)
        $distance = $this->pipelineDistanceToQuery( $search[ 'metadata' ][ 'initPoint' ][ 'lat' ], $search[ 'metadata' ][ 'initPoint' ][ 'lng' ] );

        // metadata
        $metadata = compact( 'propertiesWithin', 'filters', 'distance' );

        // pipeline
        $pipeline = $this->pipelinePropertiesToSearch( $search->_id, $metadata );

        // exec query
        $toTemp = Property::raw( ( function ( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );

        return $toTemp->toArray(); // empty if ok
    }

    /**
     * Return (paginated) searched properties from the given search.
     *
     * @param string $searchId The id of the current search.
     * @param array $pagination {
     *     The values of the pagination
     *
     *     @type int $page [required] The page needed to return.
     *     @type int $perpage [required] The number of rows per each
     *           page of the pagination.
     *     @type string $field [optional] The field needed to be sorted.
     *     @type string $sort [optional] The 'asc' or 'desc' to be sorted.
     * }
     *
     * @return array
     */
    public function getSearchedProperties( string $searchId, array $pagination ): array
    {
        // get total searched properties
        $total = $this->countSearchedProperties( $searchId );

        if ( empty( $total ) === true ) {
            return [];
        }

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

        // agrego offset al pagination
        $pagination[ 'offset' ] = $offset;

        // pipeline
        $pipeline = $this->pipelinePropertiesFromSearch( $searchId, $pagination );

        // select paginated
        $pagitatedItems = SearchedProperty::raw( ( function ( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );

        // new instance of LengthAwarePaginator
        $paginator = new LengthAwarePaginator( $pagitatedItems, $total, $pagination[ 'perpage' ], $page );

        // cast to array
        $paginator = $paginator->toArray();

        // search id
        $paginator[ 'searchId' ] = $searchId;

        return $paginator;
    }

    /**
     * Update selected searched properties of given search.
     *
     * @param string $searchId The id of the current search.
     * @param array $ids Array of property's ids selected by user.
     *        [ '*' ] in case all were selected.
     *
     * @return void
     */
    public function updateSelectedSearchedProperties( string $searchId, array $ids ): void
    {
        // query by which to filter documents
        $filter = [
            'search_id' => [ '$eq' => new ObjectID( $searchId ) ],
        ];

        // update
        $update = [
            '$set' => [ 'selected' => false ]
        ];

        // unselect all properties
        SearchedProperty::raw( ( function ( $collection ) use ( $filter, $update ) {
            return $collection->updateMany( $filter, $update );
        } ) );

        // in case all where not selected, query only they who are ($in) '$ids'
        if ( $ids !== [ '*' ] ) {
            $filter[ 'property_id' ] = [ '$in' => $ids ];
        }

        // update to apply to the matched document
        $update = [
            '$set' => [ 'selected' => true ]
        ];

        // exec query
        SearchedProperty::raw( ( function ( $collection ) use ( $filter, $update ) {
            return $collection->updateMany( $filter, $update );
        } ) );
    }

    /**
     * Return properties that were selected by user in given search.
     *
     * @param string $searchId The id of the current search.
     *
     * @return array
     * @throws \Exception
     */
    public function getSelectedSearchedProperties( string $searchId ): array
    {
        // pipeline
        $pipeline = $this->pipelineSelectedPropertiesFromSearch( $searchId );

        // get selected data in final format
        $results = SearchedProperty::raw( ( function ( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );

        if ( $results->isEmpty() === true ) {
            throw new \Exception( 'No properties selected in given search.' );

        }

        return $results->toArray();
    }


    /**
     * Return properties that were selected by user in given search
     * in excel format.
     *
     * @param string $searchId The id of the current search.
     *
     * @return array
     * @throws \Exception
     */
    public function getSelectedSearchedPropertiesExcelFormat( string $searchId ): array
    {
        // pipeline
        $pipeline = $this->pipelineSelectedPropertiesFromSearchExcelFormat( $searchId );

        // get selected data in final format
        $results = SearchedProperty::raw( ( function ( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );

        if ( $results->isEmpty() === true ) {
            throw new \Exception( 'No properties selected in given search.' );

        }

        return $results->toArray();
    }

    /**
     * Return count of searched properties for given search.
     *
     * @param string $searchId The id of the current search.
     *
     * @return int
     */
    public function countSearchedProperties( string $searchId ): int
    {
        $query = SearchedProperty::raw( ( function ( $collection ) use ( $searchId ) {
            return $collection->aggregate( [
                [
                    '$match' => [
                        'search_id' => [ '$eq' => new ObjectID( $searchId ) ]
                    ]
                ],
                [
                    '$count' => "total"
                ]
            ] );
        } ) )->toArray();

        try {
            return $query[ 0 ][ 'total' ];
        } catch ( \ErrorException $e ) {
            return 0;
        }
    }

    /**
     * Return count of selected searched properties for given search.
     *
     * @param string $searchId The id of the current search.
     *
     * @return int
     */
    public function countSelectedSearchedProperties( string $searchId ): int
    {
        $query = SearchedProperty::raw( ( function ( $collection ) use ( $searchId ) {
            return $collection->aggregate( [
                [
                    '$match' => [
                        'search_id' => [ '$eq' => new ObjectID( $searchId ) ],
                        'selected' => [ '$eq' => true ]
                    ]
                ],
                [
                    '$count' => "total"
                ]
            ] );
        } ) )->toArray();

        try {
            return $query[ 0 ][ 'total' ];
        } catch ( \ErrorException $e ) {
            return 0;
        }
    }
}
