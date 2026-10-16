<?php

namespace App\Filament\Widgets;

use App\Models\ProductVariant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LowStock extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function getTableHeading(): string
    {
        return __('admin.low_stock');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ProductVariant::query()->with('product')->where('stock', '<=', 2)->orderBy('stock'))
            ->columns([
                Tables\Columns\TextColumn::make('product')->label(__('admin.product'))->state(fn (ProductVariant $record) => $record->product->tr('name')),
                Tables\Columns\TextColumn::make('name')->label(__('product.color'))->state(fn (ProductVariant $record) => $record->tr('name')),
                Tables\Columns\TextColumn::make('stock')->label(__('admin.stock'))->badge()->color(fn ($state) => $state == 0 ? 'danger' : 'warning'),
            ])
            ->recordUrl(fn (ProductVariant $record) => route('filament.admin.resources.products.edit', ['record' => $record->product]))
            ->paginated([10]);
    }
}
