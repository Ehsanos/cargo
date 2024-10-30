<?php

namespace App\Filament\Admin\Resources\AccountBalanceResource\Pages;

use App\Filament\Admin\Resources\AccountBalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountBalances extends ListRecords
{
    protected static string $resource = AccountBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
