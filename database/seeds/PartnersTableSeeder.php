<?php

use Illuminate\Database\Seeder;

class PartnersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table( 'partners' )->insert( [
            [
                'code' => 't',
                'name' => 'Tasing',
                'data' => '{"url":"https://tasing.pe/"}',
            ]
        ] );
    }
}
