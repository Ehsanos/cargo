<?php

namespace App\Filament\Admin\Resources\AccountBalanceResource\Pages;

use App\Enums\BalanceTypeEnum;
use App\Filament\Admin\Resources\AccountBalanceResource;
use App\Models\Balance;
use App\Models\User;
use Closure;
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
                Grid::make(3)->schema([
                    Select::make('from')->options(User::accounts()->pluck('name', "id"))->required()->label('الحساب الرئيسي')->searchable(),
                    Select::make('to')->options(User::accounts()->pluck('name', "id"))->required()->label('الحساب المقابل')->searchable(),
                    TextInput::make('value')->numeric()->rules([
                        fn (): Closure => function (string $attribute, $value, Closure $fail) {
                            if ($value <=0) {
                                $fail('يجب ان تكون القيمة أكبر من 0');
                            }
                        },
                    ])->required()->label('القيمة بعملة الحساب الرئيسي'),

                ]),
                Textarea::make('info')->label('البيان')
            ])->action(function ($data) {
                $accountSource = User::accounts()->find($data['from']);

                $accountTarget = User::accounts()->find($data['to']);
                \DB::beginTransaction();
                try{
                    if($accountSource==null || $accountTarget==null){
                        throw new \Exception('تأكد من تحديد الحسابات بشكل صحيح');
                    }
                    $currencySource=$accountSource->currency;
                    $currencyTarget=$accountTarget->currency;
                    $value=$data['value']/$currencySource->value;
                    Balance::create([
                        'credit'=>0,
                        'debit'=>$data['value'],
                        'is_complete'=>true,
                        'pending'=>false,
                        'user_id'=>$accountSource->id,
                        'currency_id'=>$accountSource->currency_id,
                        'ex_cur'=>$accountSource->currency?->value,
                        'info'=>'تحويل إلى حساب #'.$accountTarget->name.' - '. $data['info'],
                    ]);
                    Balance::create([
                        'credit'=>$value*$currencyTarget->value,
                        'debit'=>0,
                        'is_complete'=>true,
                        'pending'=>false,
                        'user_id'=>$accountTarget->id,
                        'currency_id'=>$accountTarget->currency_id,
                        'ex_cur'=>$accountTarget->currency?->value,
                        'info'=>'تحويل من حساب #'.$accountSource->name.' - '. $data['info'],
                    ]);
                    \DB::commit();
                    Notification::make('success')->success()->title('نجاح العملية')->body('تم التحويل')->send();
                }catch (\Exception | \Error $e){
                    \DB::rollBack();
                    Notification::make('error')->danger()->title('فشل العملية')->body($e->getMessage())->send();
                }

            })->label('إضافة سند')
        ];
    }
}
