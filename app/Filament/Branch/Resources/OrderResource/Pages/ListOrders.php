<?php

namespace App\Filament\Branch\Resources\OrderResource\Pages;

use App\Enums\OrderStatusEnum;
use App\Filament\Branch\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;


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

    public function getTabs(): array
    {
        return [
            Tab::make('all')->modifyQueryUsing(fn($query)=>$query->where(fn($query)=>$query->orWhere([
                'pick_id'=>auth()->id(),
                'given_id'=>auth()->id(),
                'branch_source_id'=>auth()->user()->branch_id,
                'branch_target_id'=>auth()->user()->branch_id,
            ])))->badge(Order::all()->count())->label('الكل'),
            Tab::make('pick')->modifyQueryUsing(fn($query)=>$query
                ->where('status',OrderStatusEnum::PICK->value)
                ->where(fn($query)=>$query->orWhere([
                    'pick_id'=>auth()->id(),
                    'given_id'=>auth()->id(),
                    'branch_source_id'=>auth()->user()->branch_id,
                    'branch_target_id'=>auth()->user()->branch_id,
                    ]))

            )->badge(Order::where('status',OrderStatusEnum::PICK->value)->where(fn($query)=>$query->orWhere([
                'pick_id'=>auth()->id(),
                'given_id'=>auth()->id(),
                'branch_source_id'=>auth()->user()->branch_id,
                'branch_target_id'=>auth()->user()->branch_id,
            ]))->count())->label('تم الإلتقاط'),
            Tab::make('transfer')->modifyQueryUsing(fn($query)=>$query->where('status',OrderStatusEnum::TRANSFER->value)->where(fn($query)=>$query->orWhere([
                'pick_id'=>auth()->id(),
                'given_id'=>auth()->id(),
                'branch_source_id'=>auth()->user()->branch_id,
                'branch_target_id'=>auth()->user()->branch_id,
            ])))->badge(Order::where('status',OrderStatusEnum::TRANSFER->value)->where(fn($query)=>$query->orWhere([
                'pick_id'=>auth()->id(),
                'given_id'=>auth()->id(),
                'branch_source_id'=>auth()->user()->branch_id,
                'branch_target_id'=>auth()->user()->branch_id,
            ]))->count())->label('بإنتظار التسليم'),
            Tab::make('success')->modifyQueryUsing(fn($query)=>$query->where('status',OrderStatusEnum::SUCCESS->value)->where(fn($query)=>$query->orWhere([
                'pick_id'=>auth()->id(),
                'given_id'=>auth()->id(),
                'branch_source_id'=>auth()->user()->branch_id,
                'branch_target_id'=>auth()->user()->branch_id,
            ])))/*->badge(Order::where('status','success')->count())*/->label('منتهي'),
            Tab::make('canceled')->modifyQueryUsing(fn($query)=>$query->where('status',OrderStatusEnum::CANCELED->value)->where(fn($query)=>$query->orWhere([
                'pick_id'=>auth()->id(),
                'given_id'=>auth()->id(),
                'branch_source_id'=>auth()->user()->branch_id,
                'branch_target_id'=>auth()->user()->branch_id,
            ])))/*->badge(Order::where('status','success')->count())*/->label('ملغي'),
            Tab::make('returned')->modifyQueryUsing(fn($query)=>$query->where('status',OrderStatusEnum::RETURNED->value)->where(fn($query)=>$query->orWhere([
                'pick_id'=>auth()->id(),
                'given_id'=>auth()->id(),
                'branch_source_id'=>auth()->user()->branch_id,
                'branch_target_id'=>auth()->user()->branch_id,
            ])))/*->badge(Order::where('status','success')->count())*/->label('مرتجع'),

        ];
    }



}
