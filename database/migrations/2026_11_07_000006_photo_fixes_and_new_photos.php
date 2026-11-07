<?php

use App\Models\Product;
use App\Models\ProductImage;
use App\Support\Media;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Data-only fixes from testing on 2026-11-07:
 *  - Buckle has no black in stock: remove that color;
 *  - Urban grey showed the pockets chart (files were swapped), Urban black and
 *    Essential black had cropped photos: re-convert from the corrected originals;
 *  - add the new photos shared for existing models.
 */
return new class extends Migration
{
    /** slug => [file => color (English name) or null for every color] */
    private array $add = [
        'valletta-cork-backpack' => [
            'lifestyle-mosaic-mall' => 'Mediterranean Mosaic',
            'lifestyle-butterfly' => 'Butterfly Garden',
            'lifestyle-honeycomb' => 'Honeycomb Mandala',
            'lifestyle-paisley' => 'Baroque Paisley',
        ],
        'valletta-classic-backpack' => [
            'lifestyle-black' => 'Black',
            'lifestyle-beige' => 'Beige',
            'lifestyle-grey' => 'Grey',
        ],
        'valletta-urban-backpack' => ['black-views' => 'Black'],
        'valletta-bella-backpack' => ['inside' => null, 'size' => null],
        'valletta-mini-backpack' => ['size' => null],
        'valletta-city-backpack' => ['size' => null],
    ];

    public function up(): void
    {
        // 1) Buckle: remove the black color (no stock).
        $buckle = Product::where('slug', 'valletta-buckle-backpack')->first();
        if ($buckle) {
            foreach ($buckle->variants as $v) {
                if (($v->name['en'] ?? '') === 'Black') {
                    $v->images()->get()->each->delete();
                    if (! DB::table('order_items')->where('product_variant_id', $v->id)->exists()) {
                        $v->delete();
                    } else {
                        $v->update(['stock' => 0]);
                    }
                }
            }
        }

        // 2) Re-convert Urban + Essential photos from the corrected originals.
        $ids = Product::whereIn('slug', ['valletta-urban-backpack', 'valletta-essential-backpack'])->pluck('id');
        ProductImage::whereIn('product_id', $ids)->update(['normalized' => false]);

        // 3) New photos for existing models (converted in the background job).
        foreach ($this->add as $slug => $files) {
            $product = Product::with('variants')->where('slug', $slug)->first();
            if (! $product) {
                continue;
            }
            $dir = ['valletta-cork-backpack' => 'cork', 'valletta-classic-backpack' => 'classic', 'valletta-urban-backpack' => 'urban',
                'valletta-bella-backpack' => 'bella', 'valletta-mini-backpack' => 'mini', 'valletta-city-backpack' => 'city'][$slug];

            foreach ($files as $file => $color) {
                $source = database_path("seeders/images/{$dir}/{$file}.webp");
                $path = "products/{$dir}/{$file}.webp";
                if (! is_file($source) || ProductImage::where('product_id', $product->id)->where('path', 'like', "products/{$dir}/{$file}%")->exists()) {
                    continue;
                }
                $variant = $color ? $product->variants->first(fn ($v) => ($v->name['en'] ?? '') === $color) : null;
                if ($color && ! $variant) {
                    continue;
                }

                Storage::disk(Media::disk())->put($path, file_get_contents($source), 'public');
                ProductImage::withoutEvents(fn () => ProductImage::create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'path' => $path,
                    'sort' => $variant ? 50 : 95,
                    'normalized' => false,
                ]));
            }
        }
    }

    public function down(): void
    {
        // Data fix: nothing to undo.
    }
};
