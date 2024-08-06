<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Filament\Admin\Resources\OrderResource;

class OrdersOverview extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?string $pollingInterval = '10s';


    protected function getStats(): array
    {
        return [
            Stat::make('عدد الطلبات التي تحتاج تأكيد', '19')->color('success')
                ->description(' ')
                ->descriptionIcon('heroicon-o-truck')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',

                    'wire:click' => "\$dispatch('setStatusFilter', { filter: 'processed' })",

                ])
            ->url(OrderResource::getUrl('index')),
            Stat::make(' عدد المستخدمين', '250')
                ->description('يوميا 5')
                ->descriptionIcon('heroicon-o-user') ->color('info'),
            Stat::make('Average time on page', '3:12')
                ->description('3% increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
        ];
    }




}
