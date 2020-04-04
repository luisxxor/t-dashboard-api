<?php

use Illuminate\Database\Seeder;

class PartnerProjectTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table( 'partner_project' )->insert( [
            [
                'partner_code' => 't',
                'project_code' => 'pe-properties',
                'default' => 'default',
            ],
            [
                'partner_code' => 't',
                'project_code' => 'pe-vehicles',
                'default' => null,
            ],
            [
                'partner_code' => 't',
                'project_code' => 'cl-properties',
                'default' => null,
            ],
            [
                'partner_code' => 't',
                'project_code' => 'ec-properties',
                'default' => null,
            ]
        ] );
    }
}
