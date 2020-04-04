<?php

use Illuminate\Database\Seeder;

class ProjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table( 'projects' )->insert( [
            [
                'code' => 'pe-properties',
                'name' => 'Perú Propiedades',
                'data' => null,
            ],
            [
                'code' => 'pe-vehicles',
                'name' => 'Perú Vehiculos',
                'data' => null,
            ],
            [
                'code' => 'cl-properties',
                'name' => 'Chile Propiedades',
                'data' => null,
            ],
            [
                'code' => 'ec-properties',
                'name' => 'Ecuador Propiedades',
                'data' => null,
            ]
        ] );
    }
}
