<?php

namespace App\Filament\Branch\Resources\TaskResource\Pages;

use App\Filament\Branch\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;
}
