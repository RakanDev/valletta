<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        // Images added under a color inherit the color's product.
        static::saving(function (ProductImage $image) {
            if (! $image->product_id && $image->product_variant_id) {
                $image->product_id = ProductVariant::whereKey($image->product_variant_id)->value('product_id');
            }
        });

        // Remove the file when the image row is deleted.
        static::deleted(fn (ProductImage $image) => \Illuminate\Support\Facades\Storage::disk(\App\Support\Media::disk())->delete($image->path));
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function url(): string
    {
        return Media::url($this->path);
    }
}
