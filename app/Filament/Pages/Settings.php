<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 9;

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public static function getNavigationLabel(): string { return __('admin.settings'); }

    public function getTitle(): string { return __('admin.settings'); }

    public function mount(): void
    {
        $this->form->fill(collect(Setting::DEFAULTS)->mapWithKeys(fn ($v, $k) => [$k => Setting::get($k)])->all());
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->columns(2)->schema([
            Forms\Components\TextInput::make('whatsapp')->label(__('admin.whatsapp_number'))->helperText(__('admin.whatsapp_hint'))
                ->required()->regex('/^\d{10,15}$/')->extraInputAttributes(['dir' => 'ltr']),
            Forms\Components\TextInput::make('delivery_fee')->label(__('admin.delivery_fee'))->required()->numeric()->minValue(0)->suffix(__('common.jd')),
            Forms\Components\TextInput::make('instagram')->label(__('admin.instagram'))->url()->extraInputAttributes(['dir' => 'ltr']),
            Forms\Components\TextInput::make('facebook')->label(__('admin.facebook'))->url()->extraInputAttributes(['dir' => 'ltr']),
            Forms\Components\TextInput::make('slogan_ar')->label(__('admin.slogan_ar'))->required()->extraInputAttributes(['dir' => 'rtl']),
            Forms\Components\TextInput::make('slogan_en')->label(__('admin.slogan_en'))->required()->extraInputAttributes(['dir' => 'ltr']),
        ]);
    }

    public function save(): void
    {
        Setting::put($this->form->getState());
        Notification::make()->title(__('admin.saved'))->success()->send();
    }
}
