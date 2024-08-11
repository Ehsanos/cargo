<?php

namespace App\Filament\Admin\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PendingBalancesRelationManager extends RelationManager
{
    protected static string $relationship = 'pendingBalances';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('total')
            ->columns([
                Tables\Columns\TextColumn::make('credit')->label('إيداع'),
                Tables\Columns\TextColumn::make('debit')->label('سحب'),
                Tables\Columns\TextColumn::make('total')->label('الرصيد'),
                Tables\Columns\TextColumn::make('info')->label('البيان'),
                Tables\Columns\TextColumn::make('order.code')->label('رقم الطلب'),
                Tables\Columns\TextColumn::make('created_at')->date('Y-m-d')->label('تاريخ العملية'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
