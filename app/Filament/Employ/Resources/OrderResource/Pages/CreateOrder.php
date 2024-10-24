<?php

namespace App\Filament\Employ\Resources\OrderResource\Pages;

use App\Filament\Employ\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code']="FC".now()->format('dHis');
        return parent::mutateFormDataBeforeCreate($data); // TODO: Change the autogenerated stub
    }
}
