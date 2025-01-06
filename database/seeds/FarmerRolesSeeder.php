<?php

use Illuminate\Database\Seeder;

class FarmerRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('farmer_roles')->insert([
            'farmer_role_name' => 'Farm Manager',
            'farmer_role_description' => 'Farm Manager',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);

        DB::table('farmer_roles')->insert([
            'farmer_role_name' => 'Actual Tiller',
            'farmer_role_description' => 'Actual Tiller',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);

        DB::table('farmer_roles')->insert([
            'farmer_role_name' => 'Percentage Laborer',
            'farmer_role_description' => 'Percentage Laborer',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);

        DB::table('farmer_roles')->insert([
            'farmer_role_name' => 'Shared Laborer',
            'farmer_role_description' => 'Percentage Laborer',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);

        DB::table('farmer_roles')->insert([
            'farmer_role_name' => 'Farm Worker',
            'farmer_role_description' => 'Farm Worker',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);
    }
}
