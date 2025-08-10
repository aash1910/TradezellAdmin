<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::insert([
            [
                'name' => 'Admin',
                'email' => 'admin@piqdrop.com',
                'password' => \Hash::make('123456'),
            ],
            [
                'name' => 'Ashraful',
                'email' => 'ashraful@piqdrop.com',
                'password' => \Hash::make('123456'), 
            ],
        ]);
    }
}
