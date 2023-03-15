<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Mahmoud',
            'email' => 'mahmoud@gmail.com',
            'password' => Hash::make('mahmoudPassword'),
        ]);

        DB::table('users')->insert([
            'name' => 'Eslam',
            'email' => 'eslam@gmail.com',
            'password' => Hash::make('EslamPassword'),
        ]);
    }
}
