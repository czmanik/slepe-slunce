<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Author;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Author::firstOrCreate(['name' => 'Mirek Mužík'], ['bio' => 'Spoluautor projektu Slepé Slunce.', 'is_expedition_member' => true, 'sort_order' => 1]);

        if (env('ADMIN_EMAIL') && env('ADMIN_PASSWORD')) {
            User::updateOrCreate(['email' => env('ADMIN_EMAIL')], ['name' => env('ADMIN_NAME', 'Správce'), 'password' => env('ADMIN_PASSWORD'), 'role' => UserRole::Admin, 'is_active' => true]);
        }
    }
}
