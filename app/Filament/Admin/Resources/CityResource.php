<?php

namespace App\Filament\Admin\Resources;
use App\Enums\ActivateStatusEnum;
use App\Filament\Admin\Resources\CityResource\Pages;
use App\Filament\Admin\Resources\CityResource\RelationManagers;
use App\Models\City;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $pluralModelLabel = 'المدن';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->label('المدينة'),
                Forms\Components\ToggleButtons::make('is_main')->label('مدينة رئيسية')->boolean()
                    ->grouped(),
                Forms\Components\Select::make('status')->options(
                    [
                ActivateStatusEnum::ACTIVE->value=>   ActivateStatusEnum::ACTIVE->getLabel(),
                ActivateStatusEnum::INACTIVE->value=>   ActivateStatusEnum::INACTIVE->getLabel(),
                    ]


                )->label('حالة المدينة')->default('active'),
                Forms\Components\Select::make('city_id')->relationship('city','name')->label('تتبع الى مدينة ...')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
               Tables\Columns\TextColumn::make('name')->label('المدينة'),
                Tables\Columns\ToggleColumn::make('is_main')->label('مدينة رئيسية')
                    ->onColor('success')
                    ->offColor('danger'),
                Tables\Columns\TextColumn::make('status')->label('مفعلة/غير مفعلة')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'edit' => Pages\EditCity::route('/{record}/edit'),
        ];
    }
}
