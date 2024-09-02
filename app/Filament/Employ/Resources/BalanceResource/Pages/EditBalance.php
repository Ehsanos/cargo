<?php

namespace App\Filament\Employ\Resources\BalanceResource\Pages;

use App\Filament\Employ\Resources\BalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBalance extends EditRecord
{
    protected static string $resource = BalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
