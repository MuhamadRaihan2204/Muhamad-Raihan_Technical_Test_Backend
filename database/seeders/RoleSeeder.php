<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'id'    => 1,
            'name'    => 'Super Admin',
        ]);

        Role::create([
            'id'    => 2,
            'name'    => 'Customer Service',
        ]);

        Role::create([
            'id'    => 3,
            'name'    => 'Sales Person',
        ]);

        Role::create([
            'id'    => 4,
            'name'    => 'Operational',
        ]);

        Role::create([
            'id'    => 5,
            'name'    => 'Client',
        ]);
    }
}
