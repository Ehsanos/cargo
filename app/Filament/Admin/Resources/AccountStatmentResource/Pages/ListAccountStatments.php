<?php

namespace App\Filament\Admin\Resources\AccountStatmentResource\Pages;

use App\Filament\Admin\Resources\AccountStatmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountStatments extends ListRecords
{
    protected static string $resource = AccountStatmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
