<?php

namespace App\Filament\Admin\Resources\BalabceTRResource\Pages;

use App\Filament\Admin\Resources\BalabceTRResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBalabceTRS extends ListRecords
{
    protected static string $resource = BalabceTRResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
