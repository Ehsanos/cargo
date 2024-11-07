<?php

namespace App\Filament\Admin\BalanceWidget;
use App\Enums\LevelUserEnum;
use App\Models\Balance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BalanceCustomerView extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    protected int|string|array $columnSpan =4;

    protected function getStats(): array
    {

        $totalUsd=Balance::
                whereHas('user',fn($query)=>$query->where('users.level',LevelUserEnum::USER->value))->selectRaw('SUM(credit - debit)as total')

        ->where('balances.is_complete', 1)
        ->where('balances.pending', '=',false)
        ->where('balances.currency_id', '=',1)->first();
        $totalTRY=Balance::
        whereHas('user',fn($query)=>$query->where('users.level',LevelUserEnum::USER->value))->selectRaw('SUM(credit - debit)as total')

            ->where('balances.is_complete', 1)
            ->where('balances.pending', '=',true)
            ->where('balances.currency_id', '=',2)->first();

        $pendingUsd=Balance::
        whereHas('user',fn($query)=>$query->where('users.level',LevelUserEnum::USER->value))->selectRaw('SUM(credit - debit)as total')

            ->where('balances.is_complete', 1)
            ->where('balances.pending', '=',true)
            ->where('balances.currency_id', '=',1)->first();
        $pendingTRY=Balance::
        whereHas('user',fn($query)=>$query->where('users.level',LevelUserEnum::USER->value))->selectRaw('SUM(credit - debit)as total')

            ->where('balances.is_complete', 1)
            ->where('balances.pending', '=',false)
            ->where('balances.currency_id', '=',2)->first();

      return [
          Stat::make('مجموع رصيد الزبائن USD ' , sprintf('%.2f',sprintf('%2.f',$totalUsd->total))),
          Stat::make('مجموع رصيد الزبائن TRY ' , sprintf('%.2f',sprintf('%2.f',$totalTRY->total))),
          Stat::make('مجموع رصيد قيد التحصيل USD ' , sprintf('%.2f',sprintf('%2.f',$pendingUsd->total))),
          Stat::make('مجموع رصيد قيد التحصيل TRY ' , sprintf('%.2f',sprintf('%2.f',$pendingTRY->total))),
      ];
    }
}
