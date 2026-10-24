<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductTidier;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

#[Signature('catalog:tidy
    {--dry-run : List the changes without saving them}')]
#[Description('Clean product data from the old sites: ALL-CAPS names, truncated codes ("DRS-") and "download the attachment" text on products without one')]
class TidyCatalog extends Command
{
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $counts = ['name' => 0, 'code' => 0, 'description' => 0, 'specifications' => 0];
        $examples = [];

        Product::query()->with('brand')->orderBy('id')->each(function (Product $product) use ($dryRun, &$counts, &$examples): void {
            $changes = ProductTidier::changes($product);

            foreach (array_keys($changes) as $field) {
                $counts[$field]++;

                if (count($examples) < 12 && in_array($field, ['name', 'code'], true)) {
                    $examples[] = [$field, Str::limit((string) $product->{$field}, 55), Str::limit((string) $changes[$field], 55)];
                }
            }

            if ($changes !== [] && ! $dryRun) {
                // Slugs stay unchanged, so product URLs don't move; saving refreshes the search text.
                $product->fill($changes)->save();
            }
        });

        if (! $dryRun) {
            Cache::flush();
        }

        $this->table(['Field', 'Before', 'After'], $examples);
        $this->table(['Field', 'Products changed'], collect($counts)->map(fn (int $count, string $field) => [$field, $count])->values()->all());
        $this->info($dryRun ? 'Dry run: nothing was saved.' : 'Product data tidied.');

        return self::SUCCESS;
    }
}
