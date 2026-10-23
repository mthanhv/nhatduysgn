<?php

namespace App\Console\Commands;

use App\Services\LegacyImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('legacy:import
    {--step=* : Only run these steps (files, categories, brands, products, posts, clients, contacts, tags)}
    {--skip-existing : Leave records that were already imported untouched (keeps edits made in the admin)}')]
#[Description('Import products, categories, brands, news, clients, contact messages and uploads from the old nhatduysgn.com database')]
class ImportLegacySite extends Command
{
    public function handle(LegacyImporter $importer): int
    {
        $steps = $this->option('step') ?: LegacyImporter::STEPS;
        $unknown = array_diff($steps, LegacyImporter::STEPS);

        if ($unknown !== []) {
            $this->error('Unknown step(s): '.implode(', ', $unknown).'. Valid: '.implode(', ', LegacyImporter::STEPS));

            return self::FAILURE;
        }

        try {
            DB::connection('legacy')->getPdo();
        } catch (Throwable $exception) {
            $this->error('Cannot connect to the legacy database (LEGACY_DB_* in .env): '.$exception->getMessage());

            return self::FAILURE;
        }

        $results = $importer
            ->skipExisting((bool) $this->option('skip-existing'))
            ->onProgress(fn (string $message) => $this->line($message))
            ->run($steps);

        Cache::flush();

        $this->table(['Step', 'Handled'], collect($results)->map(fn (int $count, string $step) => [$step, $count])->values()->all());
        $this->info('Legacy import finished.');

        return self::SUCCESS;
    }
}
