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

            $webp = ImageNormalizer::toWebp($this->originalBytes() ?? $disk->get($this->path));
            $newPath = $this->basePath().'_'.Str::lower(Str::random(8)).'.webp';

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

    /**
     * Catalog photos ship with the app (database/seeders/images). Converting
     * from that original gives a cleaner result than re-converting a
     * converted file. Photos uploaded in the admin have no original here.
     */
    private function originalBytes(): ?string
    {
        if (! preg_match('#^products/([^/]+)/(.+)$#', $this->basePath(), $m)) {
            return null;
        }
        // Older converted files carry a "-xxxxxx" suffix; try with and without it.
        foreach ([$m[2], preg_replace('/-[a-z0-9]{6}$/', '', $m[2])] as $name) {
            $file = database_path("seeders/images/{$m[1]}/{$name}.webp");
            if (is_file($file)) {
                return file_get_contents($file);
            }
        }

        return null;
    }

    /** Path without extension and without the "_xxxxxxxx" version suffix. */
    private function basePath(): string
    {
        return preg_replace('/(_[a-z0-9]{8})?\.[A-Za-z0-9]+$/', '', $this->path);
    }

    public function url(): string
    {
        return Media::url($this->path);
    }
}
