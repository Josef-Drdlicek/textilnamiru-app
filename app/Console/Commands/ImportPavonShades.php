<?php

namespace App\Console\Commands;

use App\Domain\Suppliers\Pavon\PavonImporter;
use Illuminate\Console\Command;

/**
 * Import rolet Pavon do Ceníku rolet (texty, technické informace, rozměry, fotky):
 *
 *   php artisan shades:import-pavon
 *   php artisan shades:import-pavon --no-images
 *
 * Jde spustit opakovaně – texty a fotky aktualizuje, ceny, rozměry ani viditelnost nemění.
 */
class ImportPavonShades extends Command
{
    protected $signature = 'shades:import-pavon {--no-images : Nestahovat fotky}';

    protected $description = 'Načte rolety Pavon (Leontina, Maestra, Bruno, Espiral, Rolsun, Roma, Maestra Magic) do Ceníku rolet';

    public function handle(PavonImporter $importer): int
    {
        $this->info('Načítám rolety Pavon…');

        $count = $importer->import(
            ! $this->option('no-images'),
            fn (string $variant) => $this->line("  {$variant}"),
        );

        $this->newLine();
        $this->info("Hotovo: {$count} variant rolet.");
        $this->line('Nové rolety jsou skryté a bez cen – vyplňte cenovou tabulku a zobrazení v Ceníku rolet.');

        foreach ($importer->warnings as $warning) {
            $this->warn($warning);
        }

        return self::SUCCESS;
    }
}
