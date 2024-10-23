<?php

namespace App\Filament\Admin\Resources\ReportResource\Pages;

use App\Enums\OrderStatusEnum;
use App\Filament\Admin\Resources\ReportResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


}
