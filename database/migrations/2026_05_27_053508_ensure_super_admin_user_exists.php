<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $email = 'superadmin@compliance.com';
        $now = now();

        $existing = DB::table('users')->where('email', $email)->first();

        if ($existing) {
            DB::table('users')
                ->where('id', $existing->id)
                ->update([
                    'name' => $existing->name ?: 'Super Admin',
                    'tenant_id' => null,
                    'is_super_admin' => 1,
                    'is_active' => 1,
                    'updated_at' => $now,
                ]);

            return;
        }

        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => $email,
            'password' => Hash::make('SuperAdmin@2026'),
            'tenant_id' => null,
            'is_super_admin' => 1,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('email', 'superadmin@compliance.com')
            ->update(['is_super_admin' => 0, 'updated_at' => now()]);
    }
};
