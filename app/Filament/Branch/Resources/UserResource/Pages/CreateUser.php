<?php

namespace App\Filament\Branch\Resources\UserResource\Pages;

use App\Filament\Branch\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
