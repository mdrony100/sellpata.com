<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@sellpata.com');
        $password = env('ADMIN_PASSWORD', 'sellpata-admin');

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'SellPata Admin',
                'password' => Hash::make($password),
                'is_admin' => true,
            ],
        );

        $this->command?->info("Admin login: {$email}");
    }
}
