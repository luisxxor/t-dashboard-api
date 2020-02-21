<?php

namespace App\Projects\PeruVehicles\Repositories;

use App\Projects\PeruVehicles\Models\Car;
use App\Projects\PeruVehicles\Models\MotorCycle;
use App\Projects\PeruVehicles\Models\BusesTruck;
use App\Projects\PeruVehicles\Models\Search;
use App\Projects\PeruVehicles\Models\SearchedVehicles;
use App\Projects\PeruVehicles\Pipelines\FilterPipelines;
use App\Projects\PeruVehicles\Pipelines\VehiclePipelines;
use App\Projects\PeruVehicles\Pipelines\SearchedVehiclePipelines;
use Illuminate\Pagination\LengthAwarePaginator;
use MongoDB\BSON\ObjectID;
use Illuminate\Database\Eloquent\Collection;
/**
 * Class VehicleRepository
 * @package App\Projects\PeruVehicles\Repositories
 * @version May 31, 2019, 5:17 am UTC
*/
class VehicleRepository
{
    use FilterPipelines, VehiclePipelines, SearchedVehiclePipelines;

    /**
     * @var array
     */
    protected $constants;

    /**
     * @var array
     */
    protected $outputFields = [ ];

       /**
     * Fields and its order to sort the properties.
     *
     * @var string
     */
    protected $sortFields = [
        'publication_date' => -1,
        '_id' => -1,
    ];

    /**
     * Header for export files.
     *
     * @var array
     */
    public $header = [
        'auto' => [
            '_id'                   => 'Código',
            'link'                  => 'Enlace',
            'make'                  => 'Marca',
            'model'                 => 'Modelo',
            'category'              => 'Categoria',
            'year'                  => 'Año',
            'mileage'               => 'Millas Recorrida',
            'fuel_type'             => 'Tipo de gasolina',
            'transmission'          => 'Transmisión',
            'engine_displacement'   => 'Desplazamiento del motor',
            'volante'               => 'Volante',
            'drive_type'            => 'Tipo de Manejo',
            'color'                 => 'Color',
            'number_of_cylinders'   => 'Número de cilindros',
            'number_of_doors'       => 'Número de puertas',
            'dollars_price'         => 'Precio (USD)',
            'location'              => 'Locación',
            'publication_date'      => 'Fecha de publicación'
         ],
        'moto'=>[
            '_id'                   => 'Código',
            'link'                  => 'Enlace',
            'make'                  => 'Marca',
            'model'                 => 'Modelo',
            'category'              => 'Categoria',
            'year'                  => 'Año',
            'mileage'               => 'Millas Recorrida',
            'fuel_type'             => 'Tipo de gasolina',
            'transmission'          => 'Transmisión',
            'engine_displacement'   => 'Desplazamiento del motor',
            'brakes'                => 'Frenos',
            'starter_type'          => 'Tipo de Encendido',
            'drive_type'            => 'Tipo de Manejo',
            'color'                 => 'Color',
            'number_of_cylinders'   => 'Número de cilindros',
            'dollars_price'         => 'Precio (USD)',
            'location'              => 'Locación',
            'publication_date'      => 'Fecha de publicación'
        ],
        'bus-camion'=>[
            '_id'                   => 'Código',
            'link'                  => 'Enlace',
            'make'                  => 'Marca',
            'model'                 => 'Modelo',
            'category'              => 'Categoria',
            'year'                  => 'Año',
            'mileage'               => 'Millas Recorrida',
            'fuel_type'             => 'Tipo de gasolina',
            'transmission'          => 'Transmisión',
            'engine_displacement'   => 'Desplazamiento del motor',
            'volante'               => 'Volante',
            'number_of_doors'       => 'Número de puertas',
            'drive_type'            => 'Tipo de Manejo',
            'color'                 => 'Color',
            'number_of_cylinders'   => 'Número de cilindros',
            'max_passengers'        => 'Max pasajeros',
            'max_cargo'             => 'Max peso',
            'tire_size'             => 'Tamaño de llanta',
            'brakes'                => 'Frenos',
            'max_power'             => 'Maximo Poder',
            'dollars_price'         => 'Precio (USD)',
            'location'              => 'Locación',
            'publication_date'      => 'Fecha de publicación'
        ]
    ];

    public function __construct() {
        $this->constants = config( 'multi-api.pe-vehicles.constants' );
    }
    /**
     * Return (paginated) searched vehicles from the given search.
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
    public function getSearchedVehicles( string $searchId, array $pagination ): array
    {
        // get total searched vehicles
        $total = $this->countSearchedVehicles( $searchId );

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
        $pagitatedItems = SearchedVehicles::raw( ( function ( $collection ) use ( $pipeline ) {
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
    public function updateSelectedSearchedVehicles( string $searchId, array $ids ): void
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
        SearchedVehicles::raw( ( function ( $collection ) use ( $filter, $update ) {
            return $collection->updateMany( $filter, $update );
        } ) );

        // in case all where not selected, query only they who are ($in) '$ids'
        if ( $ids !== [ '*' ] ) {
            $filter[ 'vehicle_id' ] = [ '$in' => $ids ];
        }

        // update to apply to the matched document
        $update = [
            '$set' => [ 'selected' => true ]
        ];

        // exec query
        SearchedVehicles::raw( ( function ( $collection ) use ( $filter, $update ) {
            return $collection->updateMany( $filter, $update );
        } ) );
    }


    /**
     * Store matched vehicles as searched vehicles from given search.
     *
     * @param Search $search The search model to store the matched vehicles.
     *
     * @return array
     */
    public function storeSearchedVehicle( Search $search ): array
    {
        // pipeline to get filters (parameters)
        $filters = $this->pipelineFiltersToQuery( (array)$search[ 'metadata' ][ 'filters' ], $search->publication_type);

        // pipeline
        $pipeline = $this->pipelineVehiclesToSearch( $search->_id, $filters );

        switch ($search->publication_type) {
            case $this->constants['CAR']:
                $toTemp = Car::raw( ( function ( $collection ) use ( $pipeline ) {
                    return $collection->aggregate( $pipeline );
                } ) );
                break;
            case $this->constants['MOTORCYCLES']:
                $toTemp = MotorCycle::raw( ( function ( $collection ) use ( $pipeline ) {
                    return $collection->aggregate( $pipeline );
                } ) );
                break;
            case $this->constants['BUSESTRUCK']:
                $toTemp = BusesTruck::raw( ( function ( $collection ) use ( $pipeline ) {
                    return $collection->aggregate( $pipeline );
                } ) );
                break;
            default:
                break;
        }
        return $toTemp->toArray(); // empty if ok
    }

    /**
     * Return properties that were selected by user in given search.
     *
     * @param string $searchId The id of the current search.
     *
     * @return array
     * @throws \Exception
     */
    public function getSelectedSearchedVehicles( string $searchId ): array
    {
        // pipeline
        $pipeline = $this->pipelineSelectedVehiclessFromSearch( $searchId );

        // get selected data in final format
        $results = SearchedVehicles::raw( ( function ( $collection ) use ( $pipeline ) {
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
    public function countSearchedVehicles( string $searchId ): int
    {
        $query = SearchedVehicles::raw( ( function ( $collection ) use ( $searchId ) {
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
     * Return properties that were selected by user in given search
     * in excel format.
     *
     * @param string $searchId The id of the current search.
     *
     * @return array
     * @throws \Exception
     */
    public function getSelectedSearchedVehiclesExcelFormat( string $searchId, string $publication_type ): array
    {
        // pipeline
        $pipeline = $this->pipelineSelectedVehiclesFromSearchExcelFormat( $searchId , $publication_type);

        // get selected data in final format
        $results = SearchedVehicles::raw( ( function ( $collection ) use ( $pipeline ) {
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
    public function countSelectedSearchedVehicles( string $searchId ): int
    {
        $query = SearchedVehicles::raw( ( function ( $collection ) use ( $searchId ) {
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

    public function distinct( string $field ,$publication_type): Collection
    {
        $vehicles = new Collection;

        if ($this->constants['CAR'] == $publication_type) {
           $vehicles = Car::distinct( $field )->get();
        }

        if ($this->constants['MOTORCYCLES'] == $publication_type) {
            $vehicles = MotorCycle::distinct( $field )->get();
        }        

        if ($this->constants['BUSESTRUCK'] == $publication_type) {
            $vehicles = BusesTruck::distinct( $field )->get();
        }

        return $vehicles;
    }
}
