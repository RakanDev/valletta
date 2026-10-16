<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public const DEFAULTS = [
        'whatsapp' => '962797620010',
        'instagram' => 'https://www.instagram.com/valletta.jo/',
        'facebook' => 'https://www.facebook.com/sweet.mini.things/',
        'delivery_fee' => '2',
        'slogan_ar' => 'Valletta - احملي عالمك بأناقة',
        'slogan_en' => 'Carry elegance, carry Valletta',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = Cache::rememberForever('settings', fn () => static::query()->pluck('value', 'key')->all());

        return $all[$key] ?? $default ?? (self::DEFAULTS[$key] ?? null);
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('settings');
    }

    public static function deliveryFee(): float
    {
        return (float) static::get('delivery_fee');
    }
}
