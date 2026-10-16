<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('valletta.admin_email');
        if (! User::where('email', $email)->exists()) {
            $password = config('valletta.admin_password') ?: Str::password(16, symbols: false);
            User::create(['name' => 'Valletta Admin', 'email' => $email, 'password' => $password, 'is_admin' => true]);
            $this->command?->warn("Admin: {$email} / ".(config('valletta.admin_password') ? '(ADMIN_PASSWORD)' : $password));
        }

        $this->call(CatalogSeeder::class);
    }
}
