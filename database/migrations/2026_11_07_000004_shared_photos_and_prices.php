<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data-only fix (no schema change):
 *  - size / inside / back / detail photos that were attached to one color
 *    are made shared, so they show for every color;
 *  - prices agreed on 2026-11-07.
 */
return new class extends Migration
{
    private array $shared = [
        'valletta-cork-backpack' => ['size'],
        'valletta-essential-backpack' => ['size', 'side'],
        'valletta-mini-backpack' => ['views'],
        'valletta-classic-backpack' => ['size'],
        'valletta-office-backpack' => ['inside-pink', 'back-beige'],
        'valletta-metro-backpack' => ['waterproof'],
    ];

    private array $prices = [
        'valletta-cork-backpack' => 16,
        'valletta-essential-backpack' => 18,
        'valletta-city-backpack' => 14,
        'valletta-vintage-backpack' => 16,
        'valletta-heritage-backpack' => 16,
        'valletta-urban-backpack' => 18,
        'valletta-metro-backpack' => 28,
    ];

    public function up(): void
    {
        foreach ($this->shared as $slug => $files) {
            $productId = DB::table('products')->where('slug', $slug)->value('id');
            if (! $productId) {
                continue;
            }
            foreach ($files as $i => $file) {
                DB::table('product_images')
                    ->where('product_id', $productId)
                    ->whereNotNull('product_variant_id')
                    ->where('path', 'like', "%/{$file}.webp")
                    ->update(['product_variant_id' => null, 'sort' => 90 + $i]);
            }
        }

        foreach ($this->prices as $slug => $price) {
            DB::table('products')->where('slug', $slug)->update(['price' => $price]);
        }
    }

    public function down(): void
    {
        // Data fix: nothing to undo.
    }
};
