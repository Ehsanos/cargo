<?php

namespace App\Observers;

use App\Enums\BayTypeEnum;
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

        if ($order->bay_type->value == BayTypeEnum::BEFORE->value) {

            $sender = $order->sender;
            Balance::create([
                'credit' => 0,
                'debit' => $order->total_price,
                'order_id' => $order->id,
                'user_id' => $sender->id,
                'total' => $sender->total_balance - $order->price,
                'info' => 'قيمة طلب + أجور شحن #' . $order->code,
                'is_complete'=>true,
            ]);
        } //
        elseif ($order->bay_type->value == BayTypeEnum::AFTER->value) {

            $receive = $order->receive;
            Balance::create([
                'credit' => 0,
                'debit' => $order->total_price,
                'order_id' => $order->id,
                'user_id' => $receive->id,
                'total' => $receive->total_balance - $order->price,
                'info' => 'قيمة طلب + أجور شحن #' . $order->code,
                'is_complete'=>true,
            ]);
        }



    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {

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
