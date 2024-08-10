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

/*
        if (auth()->user()->id == $order->receive_id) {
            $admin = User::whereLevel('admin')->get();
            Notification::make()->title('لديك طلب جديد', auth()->user()->name)->
            sendToDatabase(array_merge([auth()->user()], $admin));
        }*/

        if ($order->bay_type->value == BayTypeEnum::BEFORE->value) {
            info('before');
            $sender = $order->sender;
            Balance::create([
                'credit' => 0,
                'debit' => $order->price,
                'order_id' => $order->id,
                'user_id' => $sender->id,
                'total' => $sender->total_balance - $order->price,
                'info' => 'أجور شحن طلب رقم ' . $order->code,
            ]);
        }
        //
        elseif ($order->bay_type->value == BayTypeEnum::AFTER->value){
            info('after');
            $receive = $order->receive;
            Balance::create([
                'credit' => 0,
                'debit' => $order->price,
                'order_id' => $order->id,
                'user_id' => $receive->id,
                'total' => $receive->total_balance - $order->price,
                'info' => 'أجور شحن طلب رقم ' . $order->code,
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
        //
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
