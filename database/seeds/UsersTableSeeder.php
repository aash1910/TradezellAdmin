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
                'settings' => json_encode(['account_role' => 'admin']),
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

        // Sample demo users (used by TradezellSampleDataSeeder).
        $sampleRoles = ['trader', 'seller', 'buyer'];
        for ($i = 1; $i <= 8; $i++) {
            $email = "sample{$i}@tradezell.com";
            $accountRole = $sampleRoles[($i - 1) % count($sampleRoles)];

            \App\User::updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => "Sample{$i}",
                    'last_name' => 'User',
                    'password' => $password,
                    'status' => 'active',
                    'is_verified' => true,
                    'settings' => json_encode(['account_role' => $accountRole]),
                ]
            );
        }
    }
}
