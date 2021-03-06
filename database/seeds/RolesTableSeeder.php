<?php

use Caffeinated\Shinobi\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // role 1

        $role1 = Role::where( 'slug', 'admin' )->first();
        if ( empty( $role1 ) === true ) {
            $role1 = new Role();
            $role1->name = 'Admin';
            $role1->slug = 'admin';
            $role1->description = null;
            $role1->special = null;
            $role1->save();
        }

        // permissions role 1

        $role1->givePermissionTo(
            'manage.users',
            'manage.projects.access',
            'see.foreign.orders.list',
            'see.foreign.order',
            'download.foreign.order'
        );

        // role 2

        $role2 = Role::where( 'slug', 'regular-user' )->first();
        if ( empty( $role2 ) === true ) {
            $role2 = new Role();
            $role2->name = 'Regular User';
            $role2->slug = 'regular-user';
            $role2->description = null;
            $role2->special = null;
            $role2->save();
        }

        // permissions role 2

        $role2->givePermissionTo(
            'search.properties',
            'order.properties',
            'pay.own.order',
            'see.own.orders.list',
            'see.own.order',
            'download.own.order',
            'manage.own.profile',
            'manage.own.projects.access',
            'manage.own.subscriptions'
        );

        // role 3

        $role3 = Role::where( 'slug', 'vip' )->first();
        if ( empty( $role3 ) === true ) {
            $role3 = new Role();
            $role3->name = 'VIP';
            $role3->slug = 'vip';
            $role3->description = null;
            $role3->special = null;
            $role3->save();
        }

        // permissions role 3

        $role3->givePermissionTo(
            'release.order.without.paying'
        );

        // role 4

        $role4 = Role::where( 'slug', 'super-admin' )->first();
        if ( empty( $role4 ) === true ) {
            $role4 = new Role();
            $role4->name = 'Super admin';
            $role4->slug = 'super-admin';
            $role4->description = null;
            $role4->special = 'all-access';
            $role4->save();
        }
    }
}
