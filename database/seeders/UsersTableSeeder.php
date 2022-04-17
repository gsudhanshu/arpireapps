<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //User::truncate();
        $temp = Hash::make("password");
        $r = Role::where("role_name", "admin")->firstOrFail();
        $data = array(
                array("name"=>"Sudhanshu Garg", 
                    "email"=>"garg.sudhanshu@gmail.com", 
                    "password"=> $temp,
                    "status"=>1,
                    "role_id"=>$r->id,
                    ),
                );
        User::insert($data); // Eloquent
    }
}
