<?php

namespace App\Filament\Branch\Resources\BalanceResource\Pages;

use App\Filament\Branch\Resources\BalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBalances extends ListRecords
{
    protected static string $resource = BalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
