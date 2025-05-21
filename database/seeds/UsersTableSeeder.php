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
                'email' => 'admin@amarhaor.com',
                'password' => \Hash::make('123456'),
            ],
            [
                'name' => 'Ashraful',
                'email' => 'ashraful@hnsautomobiles.com',
                'password' => \Hash::make('123456'), 
            ],
            [
                'name' => 'Mahbub',
                'email' => 'mahbub@hnsautomobiles.com',
                'password' => \Hash::make('123456'), 
            ],
            [
                'name' => 'Mezbah',
                'email' => 'mezbah@hnsautomobiles.com',
                'password' => \Hash::make('123456'), 
            ],
        ]);
    }
}
