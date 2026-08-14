<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Roles
        DB::table('roles')->updateOrInsert(
            ['name' => 'super_admin'],
            ['created_at' => now(), 'updated_at' => now()]
        );

        // 2. Seed Default Plans
        DB::table('plans')->updateOrInsert(
            ['name' => 'Free'],
            [
                'price' => 0,
                'duration' => 0, // permanent
                'contract_limit' => 2,
                'elite_skill_access' => false,
                'personal_domain_access' => false,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        DB::table('plans')->updateOrInsert(
            ['name' => 'Premium'],
            [
                'price' => 1, // Rs 1 for testing
                'duration' => 30, // 30 days
                'contract_limit' => 5,
                'elite_skill_access' => true,
                'personal_domain_access' => true,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        DB::table('plans')->updateOrInsert(
            ['name' => 'Elite'],
            [
                'price' => 299,
                'duration' => 30, // 30 days
                'contract_limit' => 100,
                'elite_skill_access' => true,
                'personal_domain_access' => true,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 3. Seed Global Feature Flags
        $flags = ['registration', 'contracts', 'skills', 'domains'];
        foreach ($flags as $flag) {
            DB::table('feature_flags')->updateOrInsert(
                ['feature_key' => $flag],
                [
                    'enabled' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}
