<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\ProductVariant;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $sales = Order::where('status', 'delivered')->where('created_at', '>=', now()->startOfMonth())->sum('subtotal');

        return [
            Stat::make(__('admin.new_orders'), Order::where('status', 'new')->count())
                ->description(__('admin.new_orders_desc'))->color('warning'),
            Stat::make(__('admin.today_orders'), Order::whereDate('created_at', today())->count()),
            Stat::make(__('admin.month_sales'), number_format((float) $sales, 2).' '.__('common.jd'))->color('success'),
            Stat::make(__('admin.low_stock'), ProductVariant::where('stock', '<=', 2)->count())
                ->description(__('admin.low_stock_desc'))->color('danger'),
        ];
    }
}
