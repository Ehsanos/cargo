<?php

namespace App\Filament\Branch\Resources\OrderResource\Pages;

use App\Enums\OrderStatusEnum;
use App\Filament\Branch\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;
    use ExposesTableToWidgets;
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


  /*  public function getTabs(): array
    {
        return [
            'all'=>  Tab::make('all')->query(fn($query)=>$query->where(function ($query) {
                $query->where('branch_source_id', auth()->user()->branch_id);
                $query->orWhere('branch_target_id', auth()->user()->branch_id);
            })->orWhere(fn($query) => $query->where('pick_id', auth()->id())->orWhere('given_id', auth()->id())))->label('الكل'),

            'pick'=>  Tab::make('pick')
                ->query(fn($query) => $query->where('status',OrderStatusEnum::PICK->value)->where(function ($query) {
                    $query->where('branch_source_id', auth()->user()->branch_id);
                    $query->orWhere('branch_target_id', auth()->user()->branch_id);
                })->orWhere(fn($query) => $query->where('pick_id', auth()->id())->orWhere('given_id', auth()->id())))->label('تم الإلتقاط'),

            'transfer'=>  Tab::make('transfer')  ->query(fn($query) => $query->where('status',OrderStatusEnum::TRANSFER->value)->where(function ($query) {
                $query->where('branch_source_id', auth()->user()->branch_id);
                $query->orWhere('branch_target_id', auth()->user()->branch_id);
            })->orWhere(fn($query) => $query->where('pick_id', auth()->id())->orWhere('given_id', auth()->id())))->label('بإنتظار التسليم'),

            'success'=> Tab::make('success')->query(fn($query) =>
            $query->where('status', OrderStatusEnum::SUCCESS->value))->label('منتهي'),
            'canceled'=> Tab::make('canceled')->query(fn($query) =>
            $query->where('status', OrderStatusEnum::CANCELED->value))->label('ملغي'),
            'returned'=> Tab::make('returned')->query(fn($query) =>
            $query->where('status', OrderStatusEnum::RETURNED->value))->label('مرتجع'),
        ];
    }*/



}
