<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin {email?}';
    protected $description = 'Vytvoří nebo aktualizuje účet správce.';

    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('E-mail');
        $name = $this->ask('Jméno', 'Správce');
        $password = $this->secret('Heslo (alespoň 12 znaků)');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen((string) $password) < 12) {
            $this->error('Zadejte platný e-mail a heslo o alespoň 12 znacích.');
            return self::FAILURE;
        }

        User::updateOrCreate(['email' => $email], ['name' => $name, 'password' => Hash::make($password), 'role' => UserRole::Admin, 'is_active' => true]);
        $this->info('Účet správce je připraven.');

        return self::SUCCESS;
    }
}
