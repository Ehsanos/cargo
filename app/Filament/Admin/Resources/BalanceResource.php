<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BalanceResource\Pages;
use App\Filament\Admin\Resources\BalanceResource\RelationManagers;
use App\Models\Balance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BalanceResource extends Resource
{
    protected static ?string $model = Balance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table ->modifyQueryUsing(fn($query) => $query->where('user_id', auth()->id()))
            ->columns([
                 Tables\Columns\TextColumn::make('credit')->label('إيداع'),
                Tables\Columns\TextColumn::make('debit')->label('قبض'),
                Tables\Columns\TextColumn::make('customer_name')->label('اسم الزبون المستلم'),
                Tables\Columns\TextColumn::make('info')->label('الملاحظات'),
                Tables\Columns\TextColumn::make('total')->label('الرصيد'),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBalances::route('/'),
            'create' => Pages\CreateBalance::route('/create'),
            'edit' => Pages\EditBalance::route('/{record}/edit'),
        ];
    }
}
