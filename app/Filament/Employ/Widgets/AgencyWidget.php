<?php

namespace App\Filament\Employ\Widgets;

use App\Enums\ActivateAgencyEnum;
use App\Enums\BalanceTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TaskAgencyEnum;
use App\Models\Agency;
use App\Models\Balance;
use Error;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class AgencyWidget extends BaseWidget
{
    protected static ?string $heading = "المهام";


    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn() => Agency::where([
                    'agencies.activate' => ActivateAgencyEnum::PENDING->value,
                    'user_id' => auth()->id(),
                ])->orderBy('order_id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('task')->label('المهمة'),
                Tables\Columns\TextColumn::make('status')->label('نوع المهمة'),
                Tables\Columns\TextColumn::make('activate')->label('حالة المهمة'),
                Tables\Columns\TextColumn::make('msg')->label('ملاحظات'),


                Tables\Columns\TextColumn::make('order.code')->label('كود الطلب')->searchable(),


            ])->actions([
                Tables\Actions\Action::make('complete_task')
                    ->form(function ($record) {

                        if ($record->status == TaskAgencyEnum::TAKE && $record->order->far > 0 && $record->order->far_sender == true) {
                            return [
                                Placeholder::make('place')->content('هل أنت متأكد من إنهاء المهمة وإستلام أجور الشحن ' . $record->order->far . ' $')
                            ];

                        }
                        return [Placeholder::make('place')->label('هل أنت متأكد من إنهاء المهمة')];


                    })
                    ->action(function ($record) {

                        \DB::beginTransaction();
                        try {
                            $record?->update([
                                'activate' => ActivateAgencyEnum::COMPLETE->value,
                            ]);
                            if ($record->status == TaskAgencyEnum::TAKE && $record->order->far > 0 && $record->order->far_sender == true) {
                                Balance::create([
                                    'type' => BalanceTypeEnum::CATCH->value,
                                    'credit' => 0,
                                    'debit' => $record->order->far,
                                    'info' => 'تحصيل أجور شحن الطلب #' . $record->id,
                                    'user_id' => $record->user->id,
                                    'total' => $record->user->total_balance - $record->far,
                                    'is_complete' => true,
                                   // 'order_id' => $record->order->id
                                ]);
                                $user = $record->order->sender;
                                if ($record->order->far_sender === false) {
                                    $user = $record->order->receive;
                                }

                                Balance::create([
                                    'type' => BalanceTypeEnum::PUSH->value,
                                    'credit' => 0,
                                    'debit' => $record->order->far,
                                    'info' => ' أجور شحن الطلب #' . $record->order->id,
                                    'user_id' => $user->id,
                                    'total' => $user->total_balance - $record->order->far,
                                    'is_complete' => true,
                                   // 'order_id' => $record->order->id
                                ]);
                                Balance::create([
                                    'type' => BalanceTypeEnum::PUSH->value,
                                    'credit' => $record->order->far,
                                    'debit' => 0,
                                    'info' => 'دفع أجور شحن الطلب #' . $record->order->id,
                                    'user_id' => $user->id,
                                    'total' => $user->total_balance + $record->order->far,
                                    'is_complete' => true,
                                   // 'order_id' => $record->order->id
                                ]);
                            }

                            \DB::commit();
                            Notification::make('success')->title('تمت العملية بنجاح')->success()->send();
                        } catch (\Exception | Error $e) {

                            DB::rollBack();
                            Notification::make('success')->title('فشلت العملية ')->body($e->getMessage() . '- ' . $e->getLine())->danger()->send();
                        }

                    })
                    ->requiresConfirmation()->label('إنهاء المهمة')
                    ->visible(fn($record) => $record->status != TaskAgencyEnum::DELIVER),
                //complete
                Tables\Actions\Action::make('complete_task_deliver')->label('إنهاء المهمة')
                    ->form(fn($record) => [
                        Placeholder::make('success')->label('تنبيه')->content(function ($record) {
                            if ($record->order->far_sender == false) {
                                return "أنت على وشك إنهاء الطلب و إستلام مبلغ {$record->order->total_price} $";
                            }
                            return "أنت على وشك إنهاء الطلب و إستلام مبلغ {$record->order->price} $";

                        })->extraAttributes(['style' => 'color:red'])
                    ])
                    ->action(function ($record) {
                        \DB::beginTransaction();
                        try {
                            $record->update([
                                'activate' => ActivateAgencyEnum::COMPLETE->value,
                            ]);
                            $record->order->update([
                                'status' => OrderStatusEnum::SUCCESS->value
                            ]);
                            $price = $record->order->total_price;

                            if ($record->order->far_sender == true) {
                                $price = $record->order->price;
                            }
                            Balance::create([
                                'debit' => $price,
                                'credit' => 0,
                                'type' => BalanceTypeEnum::CATCH->value,
                                'info' => 'إستلام قيمة الطلب #' . $record->order->id,
                                'is_complete' => true,
                                'order_id' => $record->order->id,
                                'total' => $record->user->total_balance - $price,
                                'user_id' => $record->user->id,
                            ]);
                            Balance::create([
                                'debit' => 0,
                                'credit' => $price,
                                'type' => BalanceTypeEnum::PUSH->value,
                                'info' => 'إستلام قيمة الطلب #' . $record->order->id,
                                'is_complete' => true,
                                'order_id' => $record->order->id,
                                'total' => $record->user->total_balance + $price,
                                'user_id' => $record->order->receive->id,
                            ]);
                            Balance::where('order_id', $record->order->id)->where('user_id',$record->order->receive->id)->update(['is_complete'=>true]);
                            \DB::commit();
                            Notification::make('success')->title('نجاح العملية')->success()->send();
                        } catch (\Exception | Error $e) {
                            \DB::rollBack();
                            Notification::make('success')->title('فشل العملية')->danger()->body($e->getMessage())->send();
                        }

                    })
                    ->visible(fn($record) => $record->status == TaskAgencyEnum::DELIVER)
            ]);
    }
}
