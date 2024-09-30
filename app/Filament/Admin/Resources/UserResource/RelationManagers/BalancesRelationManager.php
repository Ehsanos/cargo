<?php

namespace App\Filament\Admin\Resources\UserResource\RelationManagers;

use App\Enums\BalanceTypeEnum;
use App\Models\Balance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Exceptions\Halt;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BalancesRelationManager extends RelationManager
{
    protected static string $relationship = 'balances';

protected static ?string $title='الرصيد';
    protected function canEdit(Model $record): bool
    {
        return false; // TODO: Change the autogenerated stub
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('credit')
                    ->required()
                    ->minValue(0.1)->label('القيمة'),
                Forms\Components\TextInput::make('info')->label('ملاحظات')->required(),

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
                Tables\Actions\CreateAction::make()->action(function ($data, $livewire) {
                    $user = $livewire->ownerRecord;
                    try {
                        if ($data['credit'] <= 0) {
                            throw new \Exception('يرجى إدخال قيمة أكبر من 0');
                        }
                        Balance::create([
                            'credit' => $data['credit'],
                            'debit' => 0,
                            'info' => $data['info'],
                            'user_id' => $user->id,
                            'type' => BalanceTypeEnum::CATCH->value,
                            'is_complete' => true,
                        ]);
                        Notification::make('success')->title('نجاح العملية')->body('تم إضافة المبلغ إلى الرصيد')->danger()->send();

                    } catch (\Exception | \Error $exception) {
                        Notification::make('error')->title('فشلت العملية')->body($exception->getMessage())->danger()->send();
                    }


                })->label('إضافة رصيد'),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                //   Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
