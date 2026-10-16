<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;

/** Session cart: [variant_id => qty]. */
class Cart
{
    private const KEY = 'cart';

    public const MAX_QTY = 10;

    public function raw(): array
    {
        return session(self::KEY, []);
    }

    public function add(ProductVariant $variant, int $qty = 1): void
    {
        $cart = $this->raw();
        $cart[$variant->id] = min(self::MAX_QTY, ($cart[$variant->id] ?? 0) + max(1, $qty));
        session([self::KEY => $cart]);
    }

    public function set(int $variantId, int $qty): void
    {
        $cart = $this->raw();
        if ($qty <= 0) {
            unset($cart[$variantId]);
        } else {
            $cart[$variantId] = min(self::MAX_QTY, $qty);
        }
        session([self::KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    /** @return Collection<int, object{variant: ProductVariant, qty: int, line: float}> */
    public function lines(): Collection
    {
        $raw = $this->raw();
        $variants = ProductVariant::with(['product.images', 'images'])
            ->whereIn('id', array_keys($raw))
            ->get()
            ->filter(fn ($v) => $v->product?->is_active);

        return $variants->map(fn (ProductVariant $v) => (object) [
            'variant' => $v,
            'qty' => (int) $raw[$v->id],
            'line' => (float) $v->product->price * (int) $raw[$v->id],
            'image' => ($v->images->first() ?? $v->product->images->first())?->url(),
        ])->values();
    }

    public function subtotal(): float
    {
        return (float) $this->lines()->sum('line');
    }
}
