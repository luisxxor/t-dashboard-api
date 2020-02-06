<?php

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table( 'permissions' )->insert( [
            [
                'name' => 'Search properties',
                'slug' => 'search.properties',
                'description' => null,
            ],
            [
                'name' => 'Order properties',
                'slug' => 'order.properties',
                'description' => null,
            ],
            [
                'name' => 'Pay own order',
                'slug' => 'pay.own.order',
                'description' => null,
            ],
            [
                'name' => 'See own orders list',
                'slug' => 'see.own.orders.list',
                'description' => null,
            ],
            [
                'name' => 'See own order',
                'slug' => 'see.own.order',
                'description' => null,
            ],
            [
                'name' => 'Download own order',
                'slug' => 'download.own.order',
                'description' => null,
            ],
            [
                'name' => 'Manage own profile',
                'slug' => 'manage.own.profile',
                'description' => null,
            ],


            [
                'name' => 'Manage users',
                'slug' => 'manage.users',
                'description' => null,
            ],
            [
                'name' => 'See foreign orders list',
                'slug' => 'see.foreign.orders.list',
                'description' => null,
            ],
            [
                'name' => 'See foreign order',
                'slug' => 'see.foreign.order',
                'description' => null,
            ],
            [
                'name' => 'Download foreign order',
                'slug' => 'download.foreign.order',
                'description' => null,
            ],


            [
                'name' => 'Release order without paying',
                'slug' => 'release.order.without.paying',
                'description' => null,
            ],
        ] );
    }
}
