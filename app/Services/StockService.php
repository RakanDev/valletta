<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Stock leaves the shelf when an order is confirmed (or moves straight to
 * shipped/delivered) and comes back when it is cancelled or returned.
 */
class StockService
{
    public function sync(Order $order): void
    {
        $shouldHold = in_array($order->status, Order::STOCK_HOLDING, true);

        if ($shouldHold && ! $order->stock_deducted) {
            $this->deduct($order);
        } elseif (! $shouldHold && $order->stock_deducted) {
            $this->restore($order);
        }
    }

    private function deduct(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if (! $item->product_variant_id) {
                    continue;
                }
                $variant = ProductVariant::query()->lockForUpdate()->find($item->product_variant_id);
                if (! $variant || $variant->stock < $item->qty) {
                    throw ValidationException::withMessages([
                        'data.status' => __('admin.not_enough_stock', [
                            'item' => trim($item->product_name.' '.$item->variant_name),
                            'stock' => $variant?->stock ?? 0,
                        ]),
                    ]);
                }
                $variant->decrement('stock', $item->qty);
            }
            $order->stock_deducted = true;
        });
    }

    private function restore(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    ProductVariant::query()->whereKey($item->product_variant_id)->increment('stock', $item->qty);
                }
            }
            $order->stock_deducted = false;
        });
    }
}
