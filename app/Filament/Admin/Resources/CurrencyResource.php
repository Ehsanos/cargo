<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CurrencyResource\Pages;
use App\Filament\Admin\Resources\CurrencyResource\RelationManagers;
use App\Models\Currency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $main=Currency::where('is_main',true)->first();
        return $form
            ->schema([
                Forms\Components\Section::make('العملات')->schema([
                    Forms\Components\Grid::make()->schema([
                        Forms\Components\TextInput::make('name')->label('اسم العملة')->unique(ignoreRecord: true)->required(),
                        Forms\Components\TextInput::make('code')->label('رمز العملة')->unique(ignoreRecord: true)->required(),
                    ]),
                    Forms\Components\Grid::make()->schema([
                        Forms\Components\TextInput::make('value')->label(fn()=>'كل 1 '.$main?->name .' تساوي')->required(),
                        Forms\Components\Toggle::make('is_main')->label('عملة رئيسية')->visible($main==null),

                    ])
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('اسم العملة'),
                Tables\Columns\TextColumn::make('code')->label('رمز العملة'),
                Tables\Columns\TextColumn::make('is_main')->label('نوع العملة')->formatStateUsing(fn($state)=>$state?'رئيسية':'')->color(fn($state)=>$state?'danger':null),
                Tables\Columns\TextInputColumn::make('value')->label('سعر 1 من العملة الرئيسية')->extraInputAttributes(fn($record)=>$record->is_main?['readonly'=>'readonly']:[]),
                Tables\Columns\TextColumn::make('updated_at')->since()->label('آخر تعديل'),


            ])
            ->filters([
                //
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
            'index' => Pages\ListCurrencies::route('/'),
            'create' => Pages\CreateCurrency::route('/create'),
            'edit' => Pages\EditCurrency::route('/{record}/edit'),
        ];
    }
}
