<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use Illuminate\Console\Command;

class NormalizeImages extends Command
{
    protected $signature = 'valletta:normalize-images';

    protected $description = 'Convert every product photo not yet converted to the 1080x1350 (4:5) WebP standard';

    public function handle(): int
    {
        $ok = 0;
        $failed = 0;

        ProductImage::where('normalized', false)->orderBy('id')->each(function (ProductImage $image) use (&$ok, &$failed) {
            $image->normalize() ? $ok++ : $failed++;
        });

        $this->info("Normalized: {$ok}, failed: {$failed}");

        return self::SUCCESS;
    }
}
