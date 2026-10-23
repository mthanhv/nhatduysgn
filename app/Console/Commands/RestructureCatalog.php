<?php

namespace App\Console\Commands;

use App\Services\CatalogRestructurer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('catalog:restructure
    {--dry-run : Show what would change without saving anything}')]
#[Description('Apply the product group structure in config/catalog.php: merge old categories into the standard groups and move their products')]
class RestructureCatalog extends Command
{
    public function handle(CatalogRestructurer $restructurer): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $report = $restructurer->run($dryRun);

        if (! $dryRun) {
            Cache::flush();
        }

        $this->table(['', ''], [
            ['Product groups before', $report['before']],
            ['Product groups after', $report['after']],
            ['Groups created', $report['created']],
            ['Products moved', $report['moved']],
            ['Old categories deleted', count($report['deleted'])],
        ]);

        if ($report['kept'] !== []) {
            $this->warn('Kept (not part of config/catalog.php, or still holding products):');

            foreach ($report['kept'] as $name) {
                $this->line("  - {$name}");
            }
        }

        $this->info($dryRun ? 'Dry run: nothing was saved.' : 'Product groups restructured.');

        return self::SUCCESS;
    }
}
