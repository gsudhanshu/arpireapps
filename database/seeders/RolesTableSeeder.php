<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Role::truncate();
        $data = array(
            array("role_name"=>"admin","status"=>1),
            array("role_name"=>"customer","status"=>1),
        );
        Role::insert($data); // Eloquent
    }
}
