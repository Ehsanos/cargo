<?php

namespace App\Observers;

use App\Enums\BayTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Balance;
use App\Models\Order;
use Filament\Notifications\Notification;
use App\Enums\LevelUserEnum;
use Illuminate\Foundation\Auth\User;

class OrderObserver
{


    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {

        $totalPrice=$order->price+$order->far;
        if ($order->bay_type?->value == BayTypeEnum::BEFORE->value) {

            $sender = $order->sender;

            if ($totalPrice > 0) {
                Balance::create([
                    'credit' => 0,
                    'debit' => $totalPrice,
                    'order_id' => $order->id,
                    'user_id' => $sender->id,
                    'total' => $sender->total_balance - $totalPrice,
                    'info' => 'قيمة طلب + أجور شحن #' . $order->code,
                    'is_complete' => true,
                ]);
            }

        } //
        elseif ($order->bay_type->value == BayTypeEnum::AFTER->value) {

            $receive = $order->receive;

            if ($totalPrice > 0) {
                Balance::create([
                    'credit' => 0,
                    'debit' => $totalPrice,
                    'order_id' => $order->id,
                    'user_id' => $receive->id,
                    'total' => $receive->total_balance - $totalPrice,
                    'info' => 'قيمة طلب + أجور شحن #' . $order->code,
                    'is_complete' => true,
                ]);
                if ($order->price > 0) {
                    Balance::create([
                        'credit' => $order->price,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $order->sender->id,
                        'total' => $receive->total_balance + $order->price,
                        'info' => 'قيمة طلب  #' . $order->code,
                        'is_complete' => true,
                    ]);
                }
            }

        }


    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        $totalPrice=$order->price+$order->far;
        if ($order->isDirty('receive_id') && $order->price>0 ){
            Balance::where('order_id',$order->id)->delete();

            if ($order->bay_type?->value == BayTypeEnum::BEFORE->value) {

                $sender = $order->sender;
                if ($totalPrice > 0) {
                    Balance::create([
                        'credit' => 0,
                        'debit' => $totalPrice,
                        'order_id' => $order->id,
                        'user_id' => $sender->id,
                        'total' => $sender->total_balance - $totalPrice,
                        'info' => 'قيمة طلب + أجور شحن #' . $order->code,
                        'is_complete' => true,
                    ]);
                }

            } //
            elseif ($order->bay_type->value == BayTypeEnum::AFTER->value) {

                $receive = $order->receive;

                if ($totalPrice > 0) {
                    Balance::create([
                        'credit' => 0,
                        'debit' => $totalPrice,
                        'order_id' => $order->id,
                        'user_id' => $receive->id,
                        'total' => $receive->total_balance - $totalPrice,
                        'info' => 'قيمة طلب + أجور شحن #' . $order->code,
                        'is_complete' => true,
                    ]);
                    if ($order->price > 0) {
                        Balance::create([
                            'credit' => $order->price,
                            'debit' => 0,
                            'order_id' => $order->id,
                            'user_id' => $order->sender->id,
                            'total' => $receive->total_balance + $order->price,
                            'info' => 'قيمة طلب  #' . $order->code,
                            'is_complete' => true,
                        ]);
                    }
                }

            }

        }
        $oldStatus=$order->getOriginal('status');
        $newStatus=$order->status;
        if($oldStatus!== OrderStatusEnum::RETURNED && $newStatus== OrderStatusEnum::RETURNED){
            $balance=Balance::where('order_id',$order->id)->delete();
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        $order->balances()->delete();
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
