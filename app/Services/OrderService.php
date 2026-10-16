<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private Cart $cart) {}

    public function placeFromCart(array $customer): Order
    {
        $lines = $this->cart->lines();
        if ($lines->isEmpty()) {
            throw ValidationException::withMessages(['cart' => __('cart.empty')]);
        }

        foreach ($lines as $line) {
            if ($line->variant->stock < $line->qty) {
                throw ValidationException::withMessages([
                    'cart' => __('cart.not_enough', [
                        'item' => $line->variant->label(),
                        'stock' => $line->variant->stock,
                    ]),
                ]);
            }
        }

        $subtotal = (float) $lines->sum('line');
        $fee = Setting::deliveryFee();

        $order = DB::transaction(function () use ($customer, $lines, $subtotal, $fee) {
            $order = Order::create([
                ...$customer,
                'locale' => app()->getLocale(),
                'subtotal' => $subtotal,
                'delivery_fee' => $fee,
                'total' => $subtotal + $fee,
                'status' => 'new',
            ]);

            foreach ($lines as $line) {
                $v = $line->variant;
                $order->items()->create([
                    'product_id' => $v->product_id,
                    'product_variant_id' => $v->id,
                    // Item names are stored in Arabic + English so the admin always sees both.
                    'product_name' => $v->product->tr('name', 'en'),
                    'variant_name' => $v->tr('name', 'en').' / '.$v->tr('name', 'ar'),
                    'unit_price' => $v->product->price,
                    'qty' => $line->qty,
                    'line_total' => $line->line,
                ]);
            }

            return $order;
        });

        $this->cart->clear();

        return $order->fresh('items');
    }

    /** Ready-made WhatsApp message to the store, in the customer's language. */
    public function whatsappMessage(Order $order): string
    {
        $l = $order->locale;
        $jd = __('common.jd', [], $l);
        $rows = [
            __('wa.title', ['number' => $order->number], $l),
            '',
            __('wa.name', [], $l).': '.$order->customer_name,
            __('wa.phone', [], $l).': '.$order->phone,
            __('wa.city', [], $l).': '.$order->cityName($l),
            __('wa.address', [], $l).': '.$order->address,
        ];
        if ($order->notes) {
            $rows[] = __('wa.notes', [], $l).': '.$order->notes;
        }
        $rows[] = '';
        foreach ($order->items as $item) {
            $variant = $item->variant ? $item->variant->tr('name', $l) : $item->variant_name;
            $name = $item->variant?->product ? $item->variant->product->tr('name', $l) : $item->product_name;
            $rows[] = "• {$item->qty} × {$name} ({$variant}) = ".self::money($item->line_total)." {$jd}";
        }
        $rows[] = '';
        $rows[] = __('wa.subtotal', [], $l).': '.self::money($order->subtotal)." {$jd}";
        $rows[] = __('wa.delivery', [], $l).': '.self::money($order->delivery_fee)." {$jd}";
        $rows[] = __('wa.total', [], $l).': '.self::money($order->total)." {$jd}";
        $rows[] = __('wa.payment', [], $l);

        return implode("\n", $rows);
    }

    public function whatsappUrl(Order $order): string
    {
        $number = preg_replace('/\D/', '', Setting::get('whatsapp'));

        return 'https://wa.me/'.$number.'?text='.rawurlencode($this->whatsappMessage($order));
    }

    public static function money(float|string $value): string
    {
        $v = (float) $value;

        return fmod($v, 1.0) == 0.0 ? number_format($v, 0) : number_format($v, 2);
    }
}
