<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.items');
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product_name')->label(__('admin.product'))->description(fn ($record) => $record->variant_name),
                Tables\Columns\TextColumn::make('qty')->label(__('admin.qty')),
                Tables\Columns\TextColumn::make('unit_price')->label(__('admin.unit_price'))->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                Tables\Columns\TextColumn::make('line_total')->label(__('admin.line_total'))->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
            ])
            ->paginated(false);
    }
}
