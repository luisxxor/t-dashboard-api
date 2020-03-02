<?php

namespace App\Projects\PeruProperties\Repositories;

use App\Projects\PeruProperties\Models\Property;
use App\Projects\PeruProperties\Models\Search;
use App\Projects\PeruProperties\Models\SearchedProperty;
use App\Projects\PeruProperties\Pipelines\FilterPipelines;
use App\Projects\PeruProperties\Pipelines\PropertyPipelines;
use App\Projects\PeruProperties\Pipelines\SearchedPropertyPipelines;
use Illuminate\Pagination\LengthAwarePaginator;
use MongoDB\BSON\ObjectID;

/**
 * Class PropertyRepository
 * @package App\Projects\PeruProperties\Repositories
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
        // 'distance' => 1,
        '_id' => 1,
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

    public function __construct() {
        $this->constants = config( 'multi-api.pe-properties.constants' );
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
     * Return matched properties from given search.
     *
     * @param Search $search The search model to match the properties.
     * @param array $pagination {
     *     The values of the pagination
     *
     *     @type int $perpage [required] The number of rows per each
     *           page of the pagination.
     *     @type string $field [optional] The field needed to be sorted.
     *     @type int $sort [optional] The 'asc' (1) or 'desc' (-1) to be sorted.
     *     @type array $lastItem [optional] The last item to paginate from.
     * }
     *
     * @return array
     */
    public function searchProperties( Search $search, array $pagination ): array
    {
        // pipeline to get distance (parameters)
        $distance = $this->pipelineDistanceToQuery( $search->metadata[ 'initPoint' ][ 'lat' ], $search->metadata[ 'initPoint' ][ 'lng' ] );

        // pipeline to get properties within (parameters)
        $propertiesWithin = $this->pipelinePropertiesWithinToQuery( $search->metadata[ 'vertices' ] );

        // pipeline to get filters (parameters)
        $filters = $this->pipelineFiltersToQuery( (array)$search->metadata[ 'filters' ] );

        // metadata
        $metadata = compact( 'distance', 'propertiesWithin', 'filters' );

        // pipeline
        $pipeline = $this->pipelineSearchProperties( $search->_id, $metadata, $pagination );

        // exec query
        $collect = Property::raw( ( function ( $collection ) use ( $pipeline ) {
            return $collection->aggregate( $pipeline );
        } ) );

        // new instance of LengthAwarePaginator
        $paginator = new LengthAwarePaginator( $collect, 0, $pagination[ 'perpage' ], 1 );

        // cast to array
        $paginator = $paginator->toArray();

        // search id
        $paginator[ 'searchId' ] = $search->id;

        return $paginator;
    }


    /**
     * Return (paginated) searched properties from the given search.
     *
     * @param string $searchId The id of the current search.
     *
     * @return array
     */
    public function getSearchedProperties( string $searchId, array $pagination ): array
    {

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
