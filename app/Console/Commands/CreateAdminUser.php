<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Založení účtu dílny (admina) — např. první účet po nasazení:
 *
 *   php artisan app:create-admin dilna --name="Textil na míru"
 */
class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin
        {username : Přihlašovací jméno}
        {--name= : Zobrazované jméno (výchozí = username)}
        {--password= : Heslo; bez zadání se příkaz zeptá interaktivně}';

    protected $description = 'Založí aktivní účet s administrátorskými právy';

    public function handle(): int
    {
        $username = $this->argument('username');

        if (User::query()->where('username', $username)->exists()) {
            $this->error("Účet „{$username}“ už existuje.");

            return self::FAILURE;
        }

        $password = $this->option('password') ?? $this->secret('Heslo pro nový účet');

        if (blank($password) || strlen($password) < 8) {
            $this->error('Heslo musí mít alespoň 8 znaků.');

            return self::FAILURE;
        }

        User::create([
            'name' => $this->option('name') ?? $username,
            'username' => $username,
            'password' => $password,
            'is_admin' => true,
            'is_active' => true,
        ]);

        $this->info("Administrátorský účet „{$username}“ byl založen.");

        return self::SUCCESS;
    }
}
