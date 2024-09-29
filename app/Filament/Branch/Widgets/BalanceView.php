<?php

namespace App\Filament\Branch\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BalanceView extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('الرصيد الحالي', \DB::table('balances')->where('user_id',auth()->id())->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total),
        ];
    }
}
