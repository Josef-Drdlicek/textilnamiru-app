<?php

namespace App\Console\Commands;

use App\Domain\Suppliers\Detede\DetedeImporter;
use Illuminate\Console\Command;

/**
 * Import kolejnic a dekoračních garnýží DeTeDe do Ceníku kolejnic:
 *
 *   php artisan rails:import-detede
 *   php artisan rails:import-detede --no-images
 *
 * Jde spustit opakovaně – texty a fotky aktualizuje, ceny ani viditelnost nemění.
 */
class ImportDetedeRails extends Command
{
    protected $signature = 'rails:import-detede {--no-images : Nestahovat fotky}';

    protected $description = 'Načte kolejnice DeTeDe (každý typ zvlášť, se sestavami) a dekorační garnýže do Ceníku kolejnic';

    public function handle(DetedeImporter $importer): int
    {
        $this->info('Načítám kolejnice a garnýže DeTeDe…');

        $count = $importer->import(
            ! $this->option('no-images'),
            fn (string $product) => $this->line("  {$product}"),
        );

        $this->newLine();
        $this->info("Hotovo: {$count} produktů, staženo {$importer->images} fotek.");

        if ($importer->removed > 0) {
            $this->line("Odstraněno {$importer->removed} starých produktů (řady kolejnic jsou teď rozdělené na jednotlivé kolejnice).");
        }
        $this->line('Nové produkty jsou skryté a bez cen – vyplňte ceny podle délky a zobrazení v Ceníku kolejnic.');

        foreach ($importer->warnings as $warning) {
            $this->warn($warning);
        }

        return self::SUCCESS;
    }
}
