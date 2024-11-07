<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\LevelUserEnum;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class BalanceEmployeeTRWidget extends BaseWidget
{
    protected static ?string $heading = "أرصدة الزبائن TRY";

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn() => User::select('users.*')
                    ->where('level', LevelUserEnum::STAFF->value)->orWhere('level', LevelUserEnum::BRANCH->value)->orWhere('level', LevelUserEnum::ADMIN->value)
                    ->selectSub(function ($query) {
                        $query->from('balances')
                            ->selectRaw('SUM(credit - debit)')
                            ->whereColumn('user_id', 'users.id')
                            ->where('balances.is_complete', 1)
                            ->where('balances.pending', '=', false)
                            ->where('balances.currency_id', '=', 2);
                    }, 'net_balance')
                    ->having('net_balance', '!=', 0),
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('المستخدم'),
                Tables\Columns\TextColumn::make('net_balance')->label('الرصيد الحالي')->sortable()
            ]);
    }
}
