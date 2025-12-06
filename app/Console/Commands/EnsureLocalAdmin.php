<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class EnsureLocalAdmin extends Command
{
    protected $signature = 'user:ensure-admin
        {--email=admin@example.com : OluÅŸturulacak/gÃ¼ncellenecek admin e-posta adresi}
        {--password=admin123 : Admin kullanÄ±cÄ±sÄ±nÄ±n parolasÄ±}
        {--name=Yerel Admin : Admin kullanÄ±cÄ±sÄ±nÄ±n adÄ±}';

    protected $description = 'GeliÅŸtirme ortamÄ± iÃ§in admin hesabÄ± oluÅŸturur veya gÃ¼nceller.';

    public function handle(): int
    {
        $email = (string) $this->option('email');
        $password = (string) $this->option('password');
        $name = (string) $this->option('name');

        if (strlen($password) < 6) {
            $this->error('Parola en az 6 karakter olmalÄ±.');

            return SymfonyCommand::FAILURE;
        }

        $user = User::firstOrNew(['email' => $email]);

        $user->name = $name;
        $user->role = 'admin';
        $user->is_active = true;
        $user->email_verified_at = now();
        $user->password = Hash::make($password);

        $user->save();

        $this->info("Admin hesabÄ± hazÄ±r: {$email}");
        $this->line("Parola: {$password}");

        return SymfonyCommand::SUCCESS;
    }
}
