<?php

namespace App\Filament\Admin\Resources\BalanceResource\Pages;

use App\Enums\BalanceTypeEnum;
use App\Filament\Admin\Resources\BalanceResource;
use App\Models\Balance;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
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
            Actions\CreateAction::make(),

            /**
             * Add credit
             */
            Actions\Action::make('create_balance_credit')
                ->form([

                    Grid::make(3)->schema([
                        Select::make('user_id')->options(User::get()->mapWithKeys(fn($user) => [$user->id => $user->iban_name]))->searchable()->required()
                            ->label('المستخدم'),
                        TextInput::make('value')->required()->numeric()->label('القيمة'),
                        TextInput::make('info')->label('البيان'),
                    ])
                ])
                //
                ->action(function ($data) {

                    \DB::beginTransaction();

                    try {
                        Balance::create([
                            'type' => BalanceTypeEnum::CATCH->value,
                            'user_id' => $data['user_id'],
                            'debit' => $data['value'],
                            'credit' => 0,
                            'info' => $data['info'],
                            'is_complete' => true,
                        ]);

                        Balance::create([
                            'type' => BalanceTypeEnum::PUSH->value,
                            'user_id' => auth()->id(),
                            'debit' => 0,
                            'credit' => $data['value'],
                            'info' => $data['info'],
                            'is_complete' => true,
                        ]);
                        \DB::commit();
                        Notification::make('success')->title('نجاح العملية')->body('تم إضافة السندات بنجاح')->success()->send();
                    } catch (\Exception | \Error $e) {
                        \DB::rollBack();
                        Notification::make('error')->title('فشل العملية')->body($e->getMessage())->danger()->send();
                    }

                })
                ->label('إضافة سند قبض'),
            /**
             * Add credit
             */
            Actions\Action::make('create_balance_debit')
                ->form([
                        Grid::make(3)->schema([
                            Select::make('user_id')->options(User::get()->mapWithKeys(fn($user) => [$user->id => $user->iban_name]))->searchable()->required()
                                ->label('المستخدم'),
                            TextInput::make('value')->required()->numeric()->label('القيمة'),
                            TextInput::make('info')->label('بيان'),
                        ])
                ])
                //
                ->action(function ($data) {
                    \DB::beginTransaction();
                    if (auth()->user()->total_balance < $data['value']) {
                        Notification::make('error')->title('فشل العملية')->body('لا تملك رصيد كافي')->danger()->send();
                        return;
                    }
                    try {

                        Balance::create([
                            'type' => BalanceTypeEnum::PUSH->value,
                            'user_id' => $data['user_id'],
                            'debit' => 0,
                            'credit' => $data['value'],
                            'info' => $data['info'],
                            'is_complete' => true,
                        ]);
                        Balance::create([
                            'type' => BalanceTypeEnum::CATCH->value,
                            'user_id' => auth()->id(),
                            'debit' => $data['value'],
                            'credit' => 0,
                            'info' => $data['info'],
                            'is_complete' => true,
                        ]);

                        \DB::commit();
                        Notification::make('success')->title('نجاح العملية')->body('تم إضافة السندات بنجاح')->success()->send();
                    } catch (\Exception | \Error $e) {
                        \DB::rollBack();
                        Notification::make('error')->title('فشل العملية')->body($e->getMessage())->danger()->send();
                    }

                })
                ->label('إضافة سند دفع'),

            Actions\ActionGroup::make([
                /**
                 * Add credit
                 */
                Actions\Action::make('create_balance_start_credit')
                    ->form([

                        Repeater::make('quid')->schema([

                            Grid::make(3)->schema([
                                Select::make('user_id')->options(User::get()->mapWithKeys(fn($user) => [$user->id => $user->iban_name]))->searchable()->required()
                                    ->label('المستخدم'),
                                TextInput::make('value')->required()->numeric()->label('القيمة'),
                                TextInput::make('info')->label('بيان'),
                            ])
                        ])->label('سند قبض')
                    ])
                    //
                    ->action(function ($data) {
                        \DB::beginTransaction();
                        try {
                            foreach ($data['quid'] as $user) {
                                Balance::create([
                                    'type' => 'start',
                                    'user_id' => $user['user_id'],
                                    'debit' => $user['value'],
                                    'credit' => 0,
                                    'info' => $user['info'],
                                    'is_complete' => true,
                                ]);
                            }
                            \DB::commit();
                            Notification::make('success')->title('نجاح العملية')->body('تم إضافة السندات بنجاح')->success()->send();
                        } catch (\Exception | \Error $e) {
                            \DB::rollBack();
                            Notification::make('error')->title('فشل العملية')->body($e->getMessage())->danger()->send();
                        }

                    })
                    ->label('إضافة سند قبض بداية المدة'),
                /**
                 * Add credit
                 */
                Actions\Action::make('create_balance_start_debit')
                    ->form([

                        Repeater::make('quid')->schema([

                            Grid::make()->schema([
                                Select::make('user_id')->options(User::get()->mapWithKeys(fn($user) => [$user->id => $user->iban_name]))->searchable()->required()
                                    ->label('المستخدم'),
                                TextInput::make('value')->required()->numeric()->label('القيمة'),
                                TextInput::make('info')->label('بيان'),
                            ])
                        ])->label('سند دفع')
                    ])
                    //
                    ->action(function ($data) {
                        \DB::beginTransaction();
                        try {
                            foreach ($data['quid'] as $user) {
                                Balance::create([
                                    'type' => 'start',
                                    'user_id' => $user['user_id'],
                                    'debit' => 0,
                                    'credit' => $user['value'],
                                    'info' => $user['info'],
                                    'is_complete' => true,
                                ]);
                            }
                            \DB::commit();
                            Notification::make('success')->title('نجاح العملية')->body('تم إضافة السندات بنجاح')->success()->send();
                        } catch (\Exception | \Error $e) {
                            \DB::rollBack();
                            Notification::make('error')->title('فشل العملية')->body($e->getMessage())->danger()->send();
                        }

                    })
                    ->label('إضافة سند دفع بداية المدة'),

            ])->button()->label('سندات بداية المدة'),


            Actions\ActionGroup::make([
                /**
                 * Add credit
                 */
                Actions\Action::make('create_balance_account_credit')
                    ->form([


                        Grid::make(3)->schema([
                            Select::make('user_id')->options(User::accounts()->get()->mapWithKeys(fn($user) => [$user->id => $user->iban_name]))->searchable()->required()
                                ->label('المستخدم'),
                            TextInput::make('value')->required()->numeric()->label('القيمة'),
                            TextInput::make('info')->label('بيان'),
                        ])

                    ])
                    //
                    ->action(function ($data) {
                        \DB::beginTransaction();
                        try {

                            Balance::create([
                                'type' => BalanceTypeEnum::PUSH->value,
                                'user_id' => $data['user_id'],
                                'debit' => $data['value'],
                                'credit' => 0,
                                'info' => $data['info'],
                                'is_complete' => true,
                            ]);
                            Balance::create([
                                'type' => BalanceTypeEnum::CATCH->value,
                                'user_id' => auth()->id(),
                                'debit' => 0,
                                'credit' => $data['value'],
                                'info' => $data['info'],
                                'is_complete' => true,
                            ]);

                            \DB::commit();
                            Notification::make('success')->title('نجاح العملية')->body('تم إضافة السندات بنجاح')->success()->send();
                        } catch (\Exception | \Error $e) {
                            \DB::rollBack();
                            Notification::make('error')->title('فشل العملية')->body($e->getMessage())->danger()->send();
                        }

                    })
                    ->label('إضافة سند قبض من حساب مالي'),
                /**
                 * Add credit
                 */
                Actions\Action::make('create_balance_account_debit')
                    ->form([
                        Grid::make()->schema([
                            Select::make('user_id')->options(User::accounts()->get()->mapWithKeys(fn($user) => [$user->id => $user->iban_name]))->searchable()->required()
                                ->label('المستخدم'),
                            TextInput::make('value')->required()->numeric()->label('القيمة'),
                            TextInput::make('info')->label('بيان'),

                        ])
                    ])
                    //
                    ->action(function ($data) {
                        \DB::beginTransaction();
                        try {
                            if (auth()->user()->total_balance < $data['value']) {
                                Notification::make('error')->title('فشل العملية')->body('لا تملك رصيد كافي')->danger()->send();
                                return;
                            }
                            Balance::create([
                                'type' => BalanceTypeEnum::CATCH->value,
                                'user_id' => $data['user_id'],
                                'debit' => 0,
                                'credit' => $data['value'],
                                'info' => $data['info'],
                                'is_complete' => true,
                            ]);

                            Balance::create([
                                'type' => BalanceTypeEnum::PUSH->value,
                                'user_id' => auth()->id(),
                                'debit' => $data['value'],
                                'credit' => 0,
                                'info' => $data['info'],
                                'is_complete' => true,
                            ]);

                            \DB::commit();
                            Notification::make('success')->title('نجاح العملية')->body('تم إضافة السندات بنجاح')->success()->send();
                        } catch (\Exception | \Error $e) {
                            \DB::rollBack();
                            Notification::make('error')->title('فشل العملية')->body($e->getMessage())->danger()->send();
                        }

                    })
                    ->label('إضافة سند دفع لحساب مالي'),

            ])->button()->label('سندات الحسابات المالية'),

        ];
    }
}
