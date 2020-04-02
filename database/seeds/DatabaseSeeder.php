<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call( ProjectsTableSeeder::class );
        $this->call( PartnersTableSeeder::class );
        $this->call( PartnerProjectTableSeeder::class );
        $this->call( PlansTableSeader::class );
        $this->call( PermissionsTableSeeder::class );
        $this->call( RolesTableSeeder::class );
    }
}
