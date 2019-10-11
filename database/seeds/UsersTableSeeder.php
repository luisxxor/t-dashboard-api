<?php

use App\Models\Dashboard\User;
use Caffeinated\Shinobi\Models\Permission;
use Caffeinated\Shinobi\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // permissions

        $permission1 = new Permission();
        $permission1->name = 'Search Properties';
        $permission1->slug = Str::slug( 'properties' );
        $permission1->description = null;
        $permission1->save();

        $permission2 = new Permission();
        $permission2->name = 'Export Properties';
        $permission2->slug = Str::slug( 'export' );
        $permission2->description = null;
        $permission2->save();

        // roles

        $role1 = new Role();
        $role1->name = 'Admin';
        $role1->slug = Str::slug( 'Admin' );
        $role1->description = null;
        $role1->special = 'all-access';
        $role1->save();

        $role2 = new Role();
        $role2->name = 'Regular User';
        $role2->slug = Str::slug( 'Regular User' );
        $role2->description = null;
        $role2->special = null;
        $role2->save();

        // permission role

        $role2->permissions()->attach( $permission1->id );
        $role2->permissions()->attach( $permission2->id );

        // users

        $user = new User();
        $user->name = 'Steven';
        $user->lastname = 'Sucre';
        $user->email = 'steven.g.s.p@gmail.com';
        $user->phone_number1 = null;
        $user->address_line1 = null;
        $user->address_line2 = null;
        $user->password = '12345678';
        $user->save();

        $user->roles()->attach( $role1->id );

        $user = new User();
        $user->name = 'Luis';
        $user->lastname = 'Bernales';
        $user->email = 'luisbernales85@gmail.com';
        $user->phone_number1 = null;
        $user->address_line1 = null;
        $user->address_line2 = null;
        $user->password = '12345678';
        $user->save();

        $user->roles()->attach( $role1->id );

        $user = new User();
        $user->name = 'Julio';
        $user->lastname = 'Rivas';
        $user->email = 'juliorafaelr@gmail.com';
        $user->phone_number1 = null;
        $user->address_line1 = null;
        $user->address_line2 = null;
        $user->password = '12345678';
        $user->save();

        $user->roles()->attach( $role1->id );

    }
}
