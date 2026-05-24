<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@compliance.com'],
            [
                'name'           => 'Super Admin',
                'email'          => 'superadmin@compliance.com',
                'password'       => Hash::make('SuperAdmin@2026'),
                'is_super_admin' => true,
                'tenant_id'      => null,
            ]
        );

        $this->command->info('Super Admin created: superadmin@compliance.com / SuperAdmin@2026');
    }
}
