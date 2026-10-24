<?php

namespace App\Console\Commands;

use App\Services\LegacyImporter;
use App\Services\ProductSiteImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('legacy:import
    {--step=* : Only run these steps (files, categories, brands, products, catalog, posts, clients, contacts, tags)}
    {--skip-existing : Leave records that were already imported untouched (keeps edits made in the admin)}
    {--site= : Import only the products of another old site from config legacy.product_sites (e.g. tbn = thietbinongong.com); steps: files, brands, products}')]
#[Description('Import products, categories, brands, news, clients, contact messages and uploads from the old nhatduysgn.com database')]
class ImportLegacySite extends Command
{
    public function handle(LegacyImporter $importer): int
    {
        if ($site = $this->option('site')) {
            if (! config("legacy.product_sites.{$site}")) {
                $this->error("Unknown site [{$site}]. Valid: ".implode(', ', array_keys(config('legacy.product_sites', []))));

                return self::FAILURE;
            }

            $importer = ProductSiteImporter::forSite($site);
        }

        $valid = $importer::STEPS;
        $steps = $this->option('step') ?: $valid;
        $unknown = array_diff($steps, $valid);

        if ($unknown !== []) {
            $this->error('Unknown step(s): '.implode(', ', $unknown).'. Valid: '.implode(', ', $valid));

            return self::FAILURE;
        }

        try {
            DB::connection($importer->connectionName())->getPdo();
        } catch (Throwable $exception) {
            $this->error("Cannot connect to the legacy database [{$importer->connectionName()}] (LEGACY_* in .env): ".$exception->getMessage());

            return self::FAILURE;
        }

        $results = $importer
            ->skipExisting((bool) $this->option('skip-existing'))
            ->onProgress(fn (string $message) => $this->line($message))
            ->run($steps);

        Cache::flush();

        $this->table(['Step', 'Handled'], collect($results)->map(fn (int $count, string $step) => [$step, $count])->values()->all());

        if ($importer instanceof ProductSiteImporter && $importer->duplicates() !== []) {
            $this->warn(count($importer->duplicates()).' products already exist on the new site (same code or name) and were linked instead of duplicated:');
            $this->table(['Old ID', 'Existing product #', 'Old name'], $importer->duplicates());
        }
        $this->info('Legacy import finished.');

        return self::SUCCESS;
    }
}
