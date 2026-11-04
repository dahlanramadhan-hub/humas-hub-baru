<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('hub.admin_password')
            ?: throw new \RuntimeException('Isi HUB_ADMIN_PASSWORD di .env dulu.');

        User::updateOrCreate(
            ['username' => 'admin'],
            ['name' => 'Super Admin', 'email' => 'admin@humas.local', 'password' => $password, 'role' => 'super_admin', 'is_active' => true]
        );
    }
}