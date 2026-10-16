<?php

namespace App\Models;

use App\Models\Concerns\HasLocalized;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasLocalized;

    protected $guarded = [];

    protected $casts = [
        'name' => 'json:unicode',
        'short' => 'json:unicode',
        'description' => 'json:unicode',
        'features' => 'json:unicode',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function totalStock(): int
    {
        return (int) $this->variants->sum('stock');
    }

    public function coverUrl(): ?string
    {
        $img = $this->images->firstWhere('product_variant_id', '!=', null) ?? $this->images->first();

        return $img?->url();
    }
}
