<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BranchResource\Pages;
use App\Filament\Admin\Resources\BranchResource\RelationManagers;
use App\Models\Branch;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use App\Enums\ActivateStatusEnum;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use PHPUnit\Util\Filter;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;
    protected static ?string $pluralModelLabel = 'الأفرع';
    protected static ?string $label='فرع';
    protected static ?string $navigationLabel='الأفرع';
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')->options([
                    ActivateStatusEnum::ACTIVE->value=>ActivateStatusEnum::ACTIVE->getLabel(),
                    ActivateStatusEnum::INACTIVE->value=>   ActivateStatusEnum::INACTIVE->getLabel()])->label('مفعل/غير مفعل')
                ,
                Forms\Components\TextInput::make('name')->label('اسم الفرع'),
                Forms\Components\Select::make('city_id')->relationship('city','name')->label('يتبع الى مدينة/قرية')

                ->createOptionForm([
                    Forms\Components\TextInput::make('name')->label('المدينة'),
                    Forms\Components\ToggleButtons::make('is_main')->label('مدينة رئيسية')->boolean()
                        ->grouped(),
                    Forms\Components\Select::make('status')->options(
                        [
                            ActivateStatusEnum::ACTIVE->value=>ActivateStatusEnum::ACTIVE->getLabel(),
                            ActivateStatusEnum::INACTIVE->value=>ActivateStatusEnum::INACTIVE->getLabel(),
                        ]


                    )->label('حالة المدينة')->default('active'),
                    Forms\Components\Select::make('city_id')->relationship('city','name')->label('تتبع الى مدينة ...')


                ])
                ,

                Forms\Components\RichEditor::make('address')->label('عنوان الفرع ')->columnSpan(2),

            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الفرع')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')->label('حالة الفرع')->searchable()->sortable(),

                Tables\Columns\TextColumn::make('city.name')->label('يتبع إلى مدينة/قرية')->searchable()->sortable()

            ])
            ->filters([


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
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
