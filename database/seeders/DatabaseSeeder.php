<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Admin accounts are created with `php artisan make:filament-user`, never seeded.
     */
    public function run(): void
    {
        $this->call(ServiceSeeder::class);
    }
}
