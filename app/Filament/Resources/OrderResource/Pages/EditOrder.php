<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('whatsapp')->label(__('admin.whatsapp_customer'))->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')->url(fn () => 'https://wa.me/'.$this->record->phoneIntl())->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
}
