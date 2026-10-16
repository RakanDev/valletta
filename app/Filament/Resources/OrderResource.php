<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ItemsRelationManager;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'number';

    public static function getModelLabel(): string { return __('admin.order'); }

    public static function getPluralModelLabel(): string { return __('admin.orders'); }

    public static function getNavigationGroup(): ?string { return __('admin.store'); }

    public static function getNavigationBadge(): ?string
    {
        $count = Order::where('status', 'new')->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string { return 'warning'; }

    public static function canCreate(): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.status'))->columns(2)->schema([
                Forms\Components\Select::make('status')->label(__('admin.status'))
                    ->options(Order::statusOptions())->required()->selectablePlaceholder(false)
                    ->helperText(__('admin.status_hint')),
                Forms\Components\Placeholder::make('stock_deducted_info')->label(__('admin.stock_deducted'))
                    ->content(fn (?Order $record) => $record?->stock_deducted ? '✅' : '—'),
                Forms\Components\Textarea::make('admin_notes')->label(__('admin.admin_notes'))->rows(2)->columnSpanFull(),
            ]),
            Forms\Components\Section::make(__('admin.customer'))->columns(2)->schema([
                Forms\Components\TextInput::make('customer_name')->label(__('admin.customer'))->required(),
                Forms\Components\TextInput::make('phone')->label(__('admin.phone'))->required()->extraInputAttributes(['dir' => 'ltr']),
                Forms\Components\Select::make('city')->label(__('admin.city'))
                    ->options(collect(config('valletta.cities'))->map(fn ($c) => $c[app()->getLocale() === 'ar' ? 0 : 1]))->required(),
                Forms\Components\Textarea::make('address')->label(__('admin.address'))->rows(2)->required(),
                Forms\Components\Textarea::make('notes')->label(__('admin.notes'))->rows(2)->columnSpanFull(),
            ]),
            Forms\Components\Section::make(__('admin.total'))->columns(3)->schema([
                Forms\Components\Placeholder::make('subtotal')->label(__('admin.subtotal'))->content(fn (?Order $record) => $record?->subtotal.' '.__('common.jd')),
                Forms\Components\Placeholder::make('delivery_fee')->label(__('admin.delivery_fee'))->content(fn (?Order $record) => $record?->delivery_fee.' '.__('common.jd')),
                Forms\Components\Placeholder::make('total')->label(__('admin.total'))->content(fn (?Order $record) => $record?->total.' '.__('common.jd')),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('number')->label(__('admin.number'))->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('customer_name')->label(__('admin.customer'))->searchable()
                    ->description(fn (Order $record) => $record->phone),
                Tables\Columns\TextColumn::make('city')->label(__('admin.city'))
                    ->formatStateUsing(fn (Order $record) => $record->cityName()),
                Tables\Columns\TextColumn::make('total')->label(__('admin.total'))->formatStateUsing(fn ($state) => number_format((float) $state, 2))->suffix(' '.__('common.jd'))->sortable(),
                Tables\Columns\TextColumn::make('status')->label(__('admin.status'))->badge()
                    ->formatStateUsing(fn (string $state) => __("status.$state"))
                    ->color(fn (string $state) => Order::STATUS_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('created_at')->label(__('admin.date'))->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(__('admin.status'))->options(Order::statusOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('whatsapp')->label(__('admin.whatsapp_customer'))->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')->url(fn (Order $record) => 'https://wa.me/'.$record->phoneIntl())->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [ItemsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
