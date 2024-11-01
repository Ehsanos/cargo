<?php

namespace App\Filament\Admin\Resources\AccountBalanceResource\Pages;

use App\Enums\BalanceTypeEnum;
use App\Filament\Admin\Resources\AccountBalanceResource;
use App\Models\Balance;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAccountBalances extends ListRecords
{
    protected static string $resource = AccountBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('created')->form([
                Grid::make()->schema([
                    Select::make('from_user')->options(User::accounts()->pluck('name', "id"))->required()->label('من حساب'),
                    Select::make('to_user')->options(User::accounts()->pluck('name', "id"))->required()->label('إلى حساب'),
                    TextInput::make('value')->numeric()->gt(0)->required()->label('المبلغ'),

                ]),
                Textarea::make('info')->label('البيان')
            ])->action(function ($data) {
                \DB::beginTransaction();
                try {
                    $userFrom = User::accounts()->where('id', $data['from_user'])->first();
                    if (!$userFrom || ($userFrom->total_balance < $data['value'])) {
                        throw  new \Exception("الحساب : {$userFrom?->name} ليس لديه رصيد كافي");
                    }
                    Balance::create([
                        'credit' => 0,
                        'debit' => $data['value'],
                        'user_id' => $data['from_user'],
                        'info' => $data['info'],
                        'type' => BalanceTypeEnum::PUSH->value,
                        'is_complete'=>true,
                    ]);
                    Balance::create([
                        'credit' => $data['value'],
                        'debit' => 0,
                        'user_id' => $data['to_user'],
                        'info' => $data['info'],
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete'=>true,
                    ]);
                    \DB::commit();
                } catch (\Exception | \Error $e) {
                    \DB::rollBack();
                    Notification::make('error')->title('فشل العملية')->body($e->getMessage())->danger()->send();
                }

            })
        ];
    }
}
