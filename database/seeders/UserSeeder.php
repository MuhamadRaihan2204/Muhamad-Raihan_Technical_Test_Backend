<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'id'    => 1,
            'role_id'    => 1,
            'name'    => 'Admin SolarKita',
            'email'    => 'superadmin@solarkita.com',
            'phone'    => '+6212345678',
            'password'    => bcrypt('superadmin123'),
        ]);

        User::create([
            'id'    => 2,
            'role_id'    => 2,
            'name'    => 'CS SolarKita',
            'email'    => 'cs@solarkita.com',
            'phone'    => '+62123456789',
            'password'    => bcrypt('cs1234'),
        ]);

        User::create([
            'id'    => 3,
            'role_id'    => 3,
            'name'    => 'Sales SolarKita',
            'email'    => 'sales@solarkita.com',
            'phone'    => '+621234567810',
            'password'    => bcrypt('sales123'),
        ]);

        User::create([
            'id'    => 4,
            'role_id'    => 4,
            'name'    => 'Operational SolarKita',
            'email'    => 'operational@solarkita.com',
            'phone'    => '+621234567811',
            'password'    => bcrypt('operational123'),
        ]);

        User::create([
            'id'    => 5,
            'role_id'    => 5,
            'name'    => 'Client SolarKita',
            'email'    => 'client@solarkita.com',
            'phone'    => '+621234567812',
            'password'    => bcrypt('client123'),
        ]);

        User::create([
            'role_id'    => 3,
            'name'    => 'Sales pertama',
            'email'    => 'sales1@solarkita.com',
            'phone'    => '+621234567813',
            'password'    => bcrypt('sales123'),
        ]);

        User::create([
            'role_id'    => 3,
            'name'    => 'Sales kedua',
            'email'    => 'sales2@solarkita.com',
            'phone'    => '+621234567814',
            'password'    => bcrypt('sales123'),
        ]);

        User::create([
            'role_id'    => 3,
            'name'    => 'Sales ketiga',
            'email'    => 'sales3@solarkita.com',
            'phone'    => '+621234567815',
            'password'    => bcrypt('sales123'),
        ]);

        User::create([
            'role_id'    => 3,
            'name'    => 'Sales keempat',
            'email'    => 'sales4@solarkita.com',
            'phone'    => '+621234567816',
            'password'    => bcrypt('sales123'),
        ]);
        
    }
}
