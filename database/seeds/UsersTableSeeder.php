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
        // TradezellAdmin uses a customized users table:
        // - no `name` column anymore (split into first_name/last_name)
        // - has `status`, `is_verified`, and JSON `settings`
        // Seed in an idempotent way so re-running doesn't create duplicates.

        $password = \Hash::make('123456');

        \App\User::updateOrCreate(
            ['email' => 'admin@tradezell.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Tradezell',
                'password' => $password,
                'status' => 'active',
                'is_verified' => true,
                'settings' => json_encode(['account_role' => 'trader']),
            ]
        );

        \App\User::updateOrCreate(
            ['email' => 'ashraful@tradezell.com'],
            [
                'first_name' => 'Ashraful',
                'last_name' => 'Islam',
                'password' => $password,
                'status' => 'active',
                'is_verified' => true,
                'settings' => json_encode(['account_role' => 'trader']),
            ]
        );
    }
}
