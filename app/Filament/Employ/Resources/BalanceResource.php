<?php

namespace App\Filament\Employ\Resources;

use App\Enums\BalanceTypeEnum;
use App\Filament\Employ\Resources\BalanceResource\Pages;
use App\Filament\Employ\Resources\BalanceResource\RelationManagers;
use App\Models\Balance;
use App\Models\Order;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BalanceResource extends Resource
{
    protected static ?string $model = Balance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralModelLabel = 'حركة الرصيد';
    protected static ?string $label = 'حركة رصيد';
    protected static ?string $navigationLabel = 'حركة الرصيد';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('سندات')->schema([
                    Forms\Components\Select::make('type')->options([
                        BalanceTypeEnum::CATCH->value => BalanceTypeEnum::CATCH->getLabel(),
                        BalanceTypeEnum::PUSH->value => BalanceTypeEnum::PUSH->getLabel(),
                    ])->default(BalanceTypeEnum::PUSH->value)->live()->rules([
                        fn(): Closure => function (string $attribute, $value, Closure $fail) {
                            $validateArray = [
                                BalanceTypeEnum::CATCH->value,
                                BalanceTypeEnum::PUSH->value,
                            ];
                            if (empty($value) || in_array($value, $validateArray)) {
                                $fail('يجب إختيار نوع سند صحيح');
                            }
                        },
                    ])->required()->label('نوع السند'),
                    Forms\Components\TextInput::make('credit')->label('القيمة')->numeric()->visible(fn($get) => $get('type') === BalanceTypeEnum::PUSH->value)->required()
                        ->rules([
                            fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                if ($value <= 0) {
                                    $fail('يجب أن تكون القيمة أكبر من 0');
                                }
                            },
                        ]),
                    Forms\Components\TextInput::make('debit')
                        ->label('القيمة')
                        ->numeric()
                        ->visible(fn($get) => $get('type') === BalanceTypeEnum::CATCH->value)
                        ->required()->rules([
                            fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                if ($value <= 0) {
                                    $fail('يجب أن تكون القيمة أكبر من 0');
                                }
                            },
                        ]),
                    Forms\Components\Select::make('user_id')->options(function () {
                        $orders = auth()->user()->pendingBalances->pluck('order_id')->toArray();
                        return Order::whereIn('id', $orders)->pluck('id', 'code');
                    })->label('مرتبط بالطلب رقم'),
                    Forms\Components\TextInput::make('info')->label('ملاحظات'),
                    Forms\Components\TextInput::make('customer_name')->required()->label('اسم الزبون  المستلم')

                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->where('user_id', auth()->id()))
            ->columns([
                Tables\Columns\TextColumn::make('credit')->label('إيداع'),
                Tables\Columns\TextColumn::make('debit')->label('قبض'),
                Tables\Columns\TextColumn::make('customer_name')->label('اسم الزبون المستلم'),
                Tables\Columns\TextColumn::make('info')->label('الملاحظات'),
                Tables\Columns\TextColumn::make('total')->label('الرصيد'),

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
            'index' => Pages\ListBalances::route('/'),
            'create' => Pages\CreateBalance::route('/create'),
            'edit' => Pages\EditBalance::route('/{record}/edit'),
        ];
    }
}
