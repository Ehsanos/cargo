<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class BalanceUserWidget extends BaseWidget
{
  protected static ?string $heading="أرصدة الزبائن";
    public function table(Table $table): Table
    {
        return $table
            ->query(
               fn()=> User::select('users.*')
                   ->selectSub(function ($query) {
                       $query->from('balances')
                           ->selectRaw('SUM(credit - debit)')
                           ->whereColumn('user_id', 'users.id');
                   }, 'net_balance')
                   /*->orderByDesc('net_balance')*/
                   ->having('net_balance', '!=', 0),
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('المستخدم')->searchable(),
                Tables\Columns\TextColumn::make('net_balance')->label('الرصيد الحالي')->sortable()
            ])
            ;
    }
}
