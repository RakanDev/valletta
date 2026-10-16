<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = ['new', 'confirmed', 'shipped', 'delivered', 'cancelled', 'returned'];

    /** Statuses in which the order's items are taken out of stock. */
    public const STOCK_HOLDING = ['confirmed', 'shipped', 'delivered'];

    public const STATUS_COLORS = [
        'new' => 'warning',
        'confirmed' => 'info',
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'gray',
        'returned' => 'danger',
    ];

    protected $guarded = [];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'stock_deducted' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function statusOptions(): array
    {
        return collect(self::STATUSES)->mapWithKeys(fn ($s) => [$s => __("status.$s")])->all();
    }

    public function cityName(?string $locale = null): string
    {
        $city = config("valletta.cities.{$this->city}");

        return $city ? $city[($locale ?? app()->getLocale()) === 'ar' ? 0 : 1] : $this->city;
    }

    /** Customer's phone in international format for wa.me links. */
    public function phoneIntl(): string
    {
        $digits = preg_replace('/\D/', '', $this->phone);

        return str_starts_with($digits, '0') ? '962'.substr($digits, 1) : $digits;
    }
}
