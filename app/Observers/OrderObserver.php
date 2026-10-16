<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\StockService;

class OrderObserver
{
    public function created(Order $order): void
    {
        if (! $order->number) {
            $order->number = config('valletta.order_prefix').(1000 + $order->id);
            $order->saveQuietly();
        }
    }

    public function updating(Order $order): void
    {
        if ($order->isDirty('status')) {
            app(StockService::class)->sync($order);
        }
    }

    public function deleting(Order $order): void
    {
        // Deleting a confirmed order puts its items back on the shelf.
        if ($order->stock_deducted) {
            $order->status = 'cancelled';
            app(StockService::class)->sync($order);
        }
    }
}
