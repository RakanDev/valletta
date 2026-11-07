<?php

namespace App\Models;

use App\Support\ImageNormalizer;
use App\Support\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    protected $guarded = [];

    protected $casts = ['normalized' => 'boolean'];

    protected static function booted(): void
    {
        // Images added under a color inherit the color's product.
        static::saving(function (ProductImage $image) {
            if (! $image->product_id && $image->product_variant_id) {
                $image->product_id = ProductVariant::whereKey($image->product_variant_id)->value('product_id');
            }
        });

        // Every new photo is converted to the 4:5 store standard.
        static::created(fn (ProductImage $image) => $image->normalize());

        // Remove the file when the image row is deleted.
        static::deleted(fn (ProductImage $image) => Storage::disk(Media::disk())->delete($image->path));
    }

    /**
     * Convert the stored file to a 1080x1350 WebP. A new file name is used so
     * browsers and the CDN never serve the old version. Never throws: a failed
     * conversion leaves the original photo in place.
     */
    public function normalize(): bool
    {
        if ($this->normalized) {
            return true;
        }

        $disk = Storage::disk(Media::disk());

        try {
            if (! $this->path || ! $disk->exists($this->path)) {
                return false;
            }

            $webp = ImageNormalizer::toWebp($disk->get($this->path));
            $newPath = preg_replace('/\.[A-Za-z0-9]+$/', '', $this->path).'-'.Str::lower(Str::random(6)).'.webp';

            $disk->put($newPath, $webp, ['visibility' => 'public', 'ContentType' => 'image/webp']);
            $disk->delete($this->path);

            $this->forceFill(['path' => $newPath, 'normalized' => true])->saveQuietly();

            return true;
        } catch (\Throwable $e) {
            Log::warning('Image normalize failed', ['id' => $this->id, 'path' => $this->path, 'error' => $e->getMessage()]);

            return false;
        }
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
