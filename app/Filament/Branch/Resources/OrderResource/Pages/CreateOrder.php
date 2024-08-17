<?php

namespace App\Filament\Branch\Resources\OrderResource\Pages;

use App\Filament\Branch\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
