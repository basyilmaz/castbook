<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Varsayılan admin kullanıcı
        User::query()->updateOrCreate(
            ['email' => 'muhasebe@example.com'],
            [
                'name' => 'Muhasebe Yonetici',
                'password' => Hash::make('Parola123!'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'ofis@muhasebe.com'],
            [
                'name' => 'Ofis Personeli',
                'password' => Hash::make('Parola123!'),
                'role' => 'user',
                'is_active' => true,
            ]
        );

        Setting::setValue('invoice_day', '1');
        Setting::setValue('invoice_due_days', '10');

        $this->call([
            TaxFormSeeder::class,
        ]);

        // Demo verileri için: php artisan db:seed --class=DemoSeeder
        $this->command->info('');
        $this->command->info('💡 Demo verileri için: php artisan db:seed --class=DemoSeeder');
    }
}
