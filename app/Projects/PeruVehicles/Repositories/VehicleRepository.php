<?php

namespace App\Projects\PeruVehicles\Repositories;

use App\Projects\PeruVehicles\Models\Car;
use App\Projects\PeruVehicles\Models\MotorCycle;
use App\Projects\PeruVehicles\Models\BusesTruck;
use App\Projects\PeruVehicles\Models\Search;
use App\Projects\PeruVehicles\Models\SearchedProperty;
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
     * Fields and its order to sort the vehicles.
     *
     * @var string
     */
    protected $sortFields = [ ];

    /**
     * Header for export files.
     *
     * @var array
     */
    public $header = [ ];

    public function __construct() {
        $this->constants = config( 'multi-api.pe-vehicles.constants' );
    }


    public function storeSearchedVehicle( Search $search ): array
    {
        // pipeline to get filters (parameters)
        $filters = $this->pipelineFiltersToQuery( (array)$search[ 'metadata' ][ 'filters' ], $search->publication_type);

        // pipeline
        $pipeline = $this->pipelineVehiclesToSearch( $search->_id, $filters );

        // exec query
        if ($this->constants['CAR'] == $search->publication_type) {
           $toTemp = Car::raw( ( function ( $collection ) use ( $pipeline ) {
                return $collection->aggregate( $pipeline );
            } ) );
        }

        if ($this->constants['MOTORCYCLES'] == $search->publication_type) {
            $toTemp = MotorCycle::raw( ( function ( $collection ) use ( $pipeline ) {
                return $collection->aggregate( $pipeline );
            } ) );
        }        

        if ($this->constants['BUSESTRUCK'] == $search->publication_type) {
            $toTemp = BusesTruck::raw( ( function ( $collection ) use ( $pipeline ) {
                return $collection->aggregate( $pipeline );
            } ) );
        }

        return $toTemp->toArray(); // empty if ok
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
