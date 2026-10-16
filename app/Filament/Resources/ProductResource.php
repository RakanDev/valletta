<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Support\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string { return __('admin.product'); }

    public static function getPluralModelLabel(): string { return __('admin.products'); }

    public static function getNavigationGroup(): ?string { return __('admin.store'); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make()->columnSpanFull()->persistTabInQueryString()->tabs([
                Forms\Components\Tabs\Tab::make(__('admin.basic'))->icon('heroicon-o-tag')->columns(2)->schema([
                    Forms\Components\TextInput::make('name.ar')->label(__('admin.name_ar'))->required()->maxLength(120),
                    Forms\Components\TextInput::make('name.en')->label(__('admin.name_en'))->required()->maxLength(120)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, Set $set, ?Product $record) {
                            if (! $record) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),
                    Forms\Components\TextInput::make('slug')->label(__('admin.slug'))->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(120),
                    Forms\Components\TextInput::make('sku')->label(__('admin.sku'))->maxLength(40),
                    Forms\Components\TextInput::make('price')->label(__('admin.price'))->required()->numeric()->minValue(0)->step(0.5),
                    Forms\Components\TextInput::make('dimensions')->label(__('admin.dimensions'))->placeholder(__('admin.dimensions_hint'))->maxLength(60),
                    Forms\Components\TextInput::make('sort')->label(__('admin.sort'))->numeric()->default(0),
                    Forms\Components\Toggle::make('is_active')->label(__('admin.active'))->default(true)->inline(false),
                ]),

                Forms\Components\Tabs\Tab::make(__('admin.content'))->icon('heroicon-o-document-text')->columns(2)->schema([
                    Forms\Components\Textarea::make('short.ar')->label(__('admin.short_ar'))->rows(2)->maxLength(300)->extraInputAttributes(['dir' => 'rtl']),
                    Forms\Components\Textarea::make('short.en')->label(__('admin.short_en'))->rows(2)->maxLength(300)->extraInputAttributes(['dir' => 'ltr']),
                    Forms\Components\Textarea::make('description.ar')->label(__('admin.description_ar'))->rows(6)->extraInputAttributes(['dir' => 'rtl']),
                    Forms\Components\Textarea::make('description.en')->label(__('admin.description_en'))->rows(6)->extraInputAttributes(['dir' => 'ltr']),
                    Forms\Components\Textarea::make('features.ar')->label(__('admin.features_ar'))->rows(8)->extraInputAttributes(['dir' => 'rtl']),
                    Forms\Components\Textarea::make('features.en')->label(__('admin.features_en'))->rows(8)->extraInputAttributes(['dir' => 'ltr']),
                ]),

                Forms\Components\Tabs\Tab::make(__('admin.variants'))->icon('heroicon-o-swatch')->schema([
                    Forms\Components\Repeater::make('variants')->hiddenLabel()
                        ->relationship()
                        ->orderColumn('sort')
                        ->addActionLabel(__('admin.add_variant'))
                        ->itemLabel(fn (array $state) => trim(($state['name']['ar'] ?? '').' · '.($state['name']['en'] ?? '').' · '.__('admin.stock').': '.($state['stock'] ?? 0)))
                        ->collapsible()
                        ->columns(4)
                        ->schema([
                            Forms\Components\TextInput::make('name.ar')->label(__('admin.color_ar'))->required()->maxLength(60),
                            Forms\Components\TextInput::make('name.en')->label(__('admin.color_en'))->required()->maxLength(60),
                            Forms\Components\TextInput::make('stock')->label(__('admin.stock'))->required()->numeric()->integer()->minValue(0)->default(0),
                            Forms\Components\ColorPicker::make('color_hex')->label(__('admin.color_hex')),
                            Forms\Components\Repeater::make('images')->label(__('admin.images'))
                                ->relationship()
                                ->orderColumn('sort')
                                ->addActionLabel(__('admin.add_image'))
                                ->grid(4)
                                ->columnSpanFull()
                                ->simple(self::imageUpload()),
                        ]),
                ]),

                Forms\Components\Tabs\Tab::make(__('admin.images'))->icon('heroicon-o-photo')->schema([
                    Forms\Components\Placeholder::make('general_images_hint')->hiddenLabel()->content(__('admin.image_variant_hint')),
                    Forms\Components\Repeater::make('generalImages')->hiddenLabel()
                        ->relationship('images', fn ($query) => $query->whereNull('product_variant_id'))
                        ->orderColumn('sort')
                        ->addActionLabel(__('admin.add_image'))
                        ->grid(4)
                        ->simple(self::imageUpload()),
                ]),
            ]),
        ]);
    }

    private static function imageUpload(): Forms\Components\FileUpload
    {
        return Forms\Components\FileUpload::make('path')
            ->hiddenLabel()
            ->image()
            // Shrink photos in the browser before upload: ~5MB phone photo -> a few hundred KB
            ->imageResizeMode('contain')
            ->imageResizeTargetWidth('1600')
            ->imageResizeTargetHeight('1600')
            ->imageResizeUpscale(false)
            ->disk(Media::disk())
            ->directory('products')
            ->visibility('public')
            ->maxSize(5120)
            ->required();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['variants', 'images']))
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                Tables\Columns\ImageColumn::make('cover')->label('')
                    ->state(fn (Product $record) => $record->coverUrl())->square()->size(56),
                Tables\Columns\TextColumn::make('name')->label(__('admin.product'))
                    ->state(fn (Product $record) => $record->tr('name'))
                    ->description(fn (Product $record) => $record->sku)
                    ->searchable(query: fn ($query, string $search) => $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere("sku", "like", "%{$search}%"))),
                Tables\Columns\TextColumn::make('price')->label(__('admin.price'))->formatStateUsing(fn ($state) => number_format((float) $state, 2))->sortable(),
                Tables\Columns\TextColumn::make('colors')->label(__('admin.colors'))
                    ->state(fn (Product $record) => $record->variants->map(fn ($v) => $v->tr('name').' ('.$v->stock.')')->implode('، '))
                    ->wrap(),
                Tables\Columns\TextColumn::make('total_stock')->label(__('admin.total_stock'))
                    ->state(fn (Product $record) => $record->totalStock())
                    ->badge()->color(fn ($state) => $state == 0 ? 'danger' : ($state <= 5 ? 'warning' : 'success')),
                Tables\Columns\ToggleColumn::make('is_active')->label(__('admin.active')),
            ])
            ->actions([
                Tables\Actions\Action::make('view')->label(__('admin.view_on_site'))->icon('heroicon-o-eye')
                    ->url(fn (Product $record) => route('product', $record))->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
