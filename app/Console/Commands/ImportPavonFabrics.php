<?php

namespace App\Console\Commands;

use App\Domain\Suppliers\Pavon\PavonCatalog;
use App\Domain\Suppliers\Pavon\PavonFabricImporter;
use App\Enums\ShadeFabricCollection;
use App\Models\ShadeVariant;
use Illuminate\Console\Command;

/**
 * Import katalogu textilií Pavon do Textilu pro rolety (bez výprodeje):
 *
 *   php artisan shades:import-pavon-fabrics
 *   php artisan shades:import-pavon-fabrics --no-images
 *
 * Jde spustit opakovaně – aktualizuje vlastnosti, skupiny a fotky.
 */
class ImportPavonFabrics extends Command
{
    protected $signature = 'shades:import-pavon-fabrics {--no-images : Nestahovat fotky}';

    protected $description = 'Načte látky na rolety Pavon (Standard, Screen, Magico, Roma) s cenovou skupinou A–D';

    public function handle(PavonFabricImporter $importer): int
    {
        $this->info('Načítám katalog textilií Pavon…');

        $count = $importer->import(
            ! $this->option('no-images'),
            fn (ShadeFabricCollection $collection, int $count) => $this->line("  {$collection->label()}: {$count} látek"),
        );

        // Provedením bez nastavených kolekcí doplnit výchozí (Magic → Magico, Roma → Roma, ostatní → Standard + Screen).
        ShadeVariant::query()->whereNull('fabric_collections')->with('product')->each(
            fn (ShadeVariant $variant) => $variant->update(['fabric_collections' => PavonCatalog::fabricCollections($variant->product->name)]),
        );

        $this->newLine();
        $this->info("Hotovo: {$count} látek, výprodej vynechán ({$importer->skippedSale}).");

        if ($importer->hidden > 0) {
            $this->line("Skryto {$importer->hidden} látek (výprodej nebo už nejsou v katalogu).");
        }

        foreach ($importer->warnings as $warning) {
            $this->warn($warning);
        }

        return self::SUCCESS;
    }
}
