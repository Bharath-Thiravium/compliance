<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'demo@test.com'],
            [
                'name'       => 'Demo User',
                'email'      => 'demo@test.com',
                'password'   => Hash::make('123456'),
                'tenant_id'  => 1,
                'branch_id'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
