<?php

namespace App\Filament\Employ\Resources\BalanceResource\Pages;

use App\Enums\BalanceTypeEnum;
use App\Filament\Employ\Resources\BalanceResource;
use App\Models\Balance;
use App\Models\User;
use Closure;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListBalances extends ListRecords
{
    protected static string $resource = BalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('add')->form([
                Select::make('type')->options([
                    BalanceTypeEnum::CATCH->value => BalanceTypeEnum::CATCH->getLabel(),
                    BalanceTypeEnum::PUSH->value => BalanceTypeEnum::PUSH->getLabel(),
                ])->default(BalanceTypeEnum::PUSH->value)->live()->rules([
                    fn(): Closure => function (string $attribute, $value, Closure $fail) {
                        $validateArray = [
                            BalanceTypeEnum::CATCH->value,
                            BalanceTypeEnum::PUSH->value,
                        ];
                        if (empty($value) || !in_array($value, $validateArray)) {
                            $fail('يجب إختيار نوع سند صحيح');
                        }
                    },
                ])->required()->label('نوع السند'),


//                TextInput::make('value')->label('القيمة')->numeric()->visible(fn($get) => $get('type') === BalanceTypeEnum::PUSH->value)->required()
//                    ->rules([
//                        fn(): Closure => function (string $attribute, $value, Closure $fail) {
//                            if ($value <= 0) {
//                                $fail('يجب أن تكون القيمة أكبر من 0');
//                            }
//                        },
//                    ]),


                TextInput::make('value')->label('القيمة')->numeric()->required()
                    ->rules([
                        fn(): Closure => function (string $attribute, $value, Closure $fail) {
                            if ($value <= 0) {
                                $fail('يجب أن تكون القيمة أكبر من 0');
                            }
                        },
                    ]),



                Select::make('user_id')->options(User::pluck('name', 'id'))->searchable()->label('الطرف الثاني في القيد'),
                TextInput::make('info')->label('ملاحظات')
            ])
                ->action(function ($data) {
                $user=User::find($data['user_id']);
                if(!$user){
                    Notification::make('success')->title('فشل العملية')->body('لم يتم العثور على المستخدم')->danger()->send();

                    return ;
                }
                if ($data['type'] == BalanceTypeEnum::CATCH->value) {
                    \DB::beginTransaction();
                    try {
                        Balance::create([
                            'credit' => 0,
                            'debit' => $data['value'],
                            'type' => BalanceTypeEnum::CATCH->value,
                            'is_complete' => true,
                            'user_id' => auth()->id(),
                            'total' => auth()->user()->total_balance - $data['value'],
                            'info' => $data['info'],
                        ]);
                        Balance::create([
                            'credit' => $data['value'],
                            'debit' => 0,
                            'type' => BalanceTypeEnum::PUSH->value,
                            'is_complete' => true,
                            'user_id' => $data['user_id'],
                            'total' => $user->total_balance + $data['value'],
                            'info' => $data['info'],
                        ]);
                        \DB::commit();
                        Notification::make('success')->title('نجاح العملية')->body('تم إضافة السند')->success()->send();
                    } catch (\Exception | \Error $e) {
                        \DB::rollBack();
                        Notification::make('success')->title('فشل العملية')->body('لم يتم إضافة السند')->danger()->send();

                    }
                }
                //
                else{
                    \DB::beginTransaction();
                    try {
                        Balance::create([
                            'credit' => $data['value'],
                            'debit' => 0,
                            'type' => BalanceTypeEnum::PUSH->value,
                            'is_complete' => true,
                            'user_id' => auth()->id(),
                            'total' => auth()->user()->total_balance + $data['value'],
                            'info' => $data['info'],
                        ]);

                        Balance::create([
                            'credit' => 0,
                            'debit' => $data['value'],
                            'type' => BalanceTypeEnum::CATCH->value,
                            'is_complete' => true,
                            'user_id' => $data['user_id'],
                            'total' => $user->total_balance - $data['value'],
                            'info' => $data['info'],
                        ]);
                        \DB::commit();
                        Notification::make('success')->title('نجاح العملية')->body('تم إضافة السند')->success()->send();
                    } catch (\Exception | \Error $e) {
                        \DB::rollBack();
                        Notification::make('success')->title('فشل العملية')->body('لم يتم إضافة السند')->danger()->send();

                    }
                }
            })->label('إضافة سند'),
        ];
    }
}
