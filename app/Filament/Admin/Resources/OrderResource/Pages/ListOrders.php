<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Enums\OrderStatusEnum;
use App\Filament\Admin\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;

use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            Tab::make('pending')->modifyQueryUsing(fn($query)=>$query->where('status',OrderStatusEnum::PENDING->value))->badge(Order::where('status',OrderStatusEnum::PENDING->value)->count())->label('بإنتظار الموافقة'),
            Tab::make('agree')->modifyQueryUsing(fn($query)=>$query->where('status',OrderStatusEnum::AGREE->value))->badge(Order::where('status',OrderStatusEnum::AGREE->value)->count())->label('بإنتظار الإنهاء'),
            Tab::make('success')->modifyQueryUsing(fn($query)=>$query->where('status',OrderStatusEnum::SUCCESS->value))/*->badge(Order::where('status','success')->count())*/->label('منتهي'),
            Tab::make('canceled')->modifyQueryUsing(fn($query)=>$query->where('status',OrderStatusEnum::CANCELED->value))/*->badge(Order::where('status','success')->count())*/->label('ملغي'),
            Tab::make('returned')->modifyQueryUsing(fn($query)=>$query->where('status',OrderStatusEnum::RETURNED->value))/*->badge(Order::where('status','success')->count())*/->label('مرتجع'),
        ];
    }
}
