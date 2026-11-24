<?php

namespace App\Console\Commands;

use App\Domain\Suppliers\Laguna\LagunaCatalog;
use App\Domain\Suppliers\Laguna\LagunaClient;
use App\Domain\Suppliers\Laguna\LagunaImporter;
use Illuminate\Console\Command;
use Throwable;

/**
 * Import katalogu dodavatele Laguna Textil do Ceníku textilu:
 *
 *   php artisan textile:import-laguna                 # záclony včetně fotek (několik minut)
 *   php artisan textile:import-laguna zavesy          # dekorační látky jako závěsy
 *   php artisan textile:import-laguna vse             # obojí
 *   php artisan textile:import-laguna zavesy --pages=2 --no-images
 *
 * Jde spustit opakovaně – doplní nové látky a barvy, ceny a viditelnost nemění.
 */
class ImportLagunaCurtains extends Command
{
    protected $signature = 'textile:import-laguna
        {katalog=zaclony : zaclony, zavesy (dekorační látky) nebo vse}
        {--pages= : Načíst jen prvních N stránek katalogu (zkouška)}
        {--no-images : Nestahovat fotky barev}';

    protected $description = 'Načte záclony nebo dekorační látky Laguna Textil (název/výška/barva) do Ceníku textilu včetně fotek barev';

    public function handle(LagunaClient $client, LagunaImporter $importer): int
    {
        // Dlouhý běh: stovky detailů a fotek. Výchozích 128 MB nestačí.
        ini_set('memory_limit', '512M');

        $choice = (string) $this->argument('katalog');
        $catalogs = $choice === 'vse' ? LagunaCatalog::cases() : array_values(array_filter([LagunaCatalog::tryFrom($choice)]));

        if ($catalogs === []) {
            $this->error("Neznámý katalog „{$choice}“. Použijte zaclony, zavesy nebo vse.");

            return self::INVALID;
        }

        foreach ($catalogs as $catalog) {
            $this->importCatalog($catalog, $client, $importer);
        }

        foreach ($importer->warnings as $warning) {
            $this->warn($warning);
        }

        return self::SUCCESS;
    }

    private function importCatalog(LagunaCatalog $catalog, LagunaClient $client, LagunaImporter $importer): void
    {
        $this->info("Načítám {$catalog->label()} Laguna Textil…");
        $items = $client->items(
            $this->option('pages') ? (int) $this->option('pages') : null,
            fn (int $page, int $last) => $this->output->write("\r  stránka {$page} / {$last}"),
            $catalog,
        );
        $this->newLine();
        $this->info(count($items).' položek (barev) v katalogu.');

        $this->info('Seskupuji podle názvu a výšky a čtu parametry z detailů…');
        $products = $importer->group($items, fn (int $done, int $total) => $this->output->write("\r  detail {$done} / {$total}"), $catalog);
        $this->newLine();

        $this->info('Ukládám '.count($products).' produktů'.($this->option('no-images') ? '' : ' a stahuji fotky').'…');
        $bar = $this->output->createProgressBar(count($products));
        $created = 0;

        foreach ($products as $data) {
            // Chyba u jedné látky nesmí zastavit import ostatních; vypíše se na konci.
            try {
                $product = $importer->save($data, ! $this->option('no-images'));
                $created += $product->wasRecentlyCreated ? 1 : 0;
            } catch (Throwable $e) {
                $importer->warnings[] = "{$data->code}: produkt se nepodařilo uložit ({$e->getMessage()}).";
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info(sprintf('Hotovo (%s): %d produktů (%d nových, %d aktualizovaných), %d barev.',
            $catalog->label(), count($products), $created, count($products) - $created, array_sum(array_map(fn ($p) => count($p->colors), $products))));
        $this->line('Nové produkty jsou skryté a bez ceny – doplňte cenu a „Zobrazit na webu“ v Ceníku textilu.');
        $this->newLine();
    }
}
