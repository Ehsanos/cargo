<?php

namespace App\Filament\Admin\Resources\BalanceResource\Pages;

use App\Filament\Admin\Resources\BalanceResource;
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
