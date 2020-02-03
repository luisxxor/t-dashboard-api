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
            ],
            [
                'code' => 'pe-vehicles',
                'name' => 'Perú Vehiculos',
            ],
            [
                'code' => 'cl-properties',
                'name' => 'Chile Propiedades',
            ],
            [
                'code' => 'tracing-properties',
                'name' => 'Tracing Properties',
            ]
        ] );
    }
}
