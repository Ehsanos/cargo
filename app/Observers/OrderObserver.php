<?php

namespace App\Observers;

use App\Enums\BalanceTypeEnum;
use App\Enums\BayTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Helper\HelperBalance;
use App\Models\Balance;
use App\Models\Order;
use Filament\Notifications\Notification;
use App\Enums\LevelUserEnum;
use Illuminate\Foundation\Auth\User;

class OrderObserver
{

    public function creating(Order $order): void
    {
        if($order->pick_id==null){
            $order->status = OrderStatusEnum::PENDING;
        }
    }


    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        info('PICK:' . $order->pick_id);
        if ($order->pick_id != null) {
            \DB::beginTransaction();
            try {
                HelperBalance::completePicker($order);
                info('complete success order');
                \DB::commit();
            } catch (\Exception | \Error $e) {
                \DB::rollBack();
                info("Error Observe");
                info('Message:' . $e->getMessage());
            }

        }

    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        /*   $sender = $order->sender;
           $receive = $order->receive;

           if ($order->isDirty('status') && $order->status->value == OrderStatusEnum::AGREE->value && $order->getOriginal('status') == OrderStatusEnum::PENDING) {


               /// add far
               if ($order->status === OrderStatusEnum::AGREE) {
                   if ($order->far > 0) {
                       if ($order->far_sender == true) {

                       } //
                       else {
                           Balance::create([
                               'credit' => $order->far,
                               'debit' => 0,
                               'order_id' => $order->id,
                               'user_id' => $receive->id,
                               'total' => $receive->total_balance + $order->far,
                               'info' => 'أجور شحن  #' . $order->code,
                               'type' => BalanceTypeEnum::CATCH->value,
                               'is_complete' => true,
                           ]);
                       }
                   }
               }
   // add price
               if ($order->price > 0) {
                   Balance::create([
                       'credit' =>  $order->price,
                       'debit' =>0,
                       'order_id' => $order->id,
                       'user_id' => $receive->id,
                       'total' => $receive->total_balance + $order->price,
                       'info' => 'أجور شحن  #' . $order->code,
                       'type' => BalanceTypeEnum::CATCH->value,
                       'is_complete' => false,
                   ]);

                   Balance::create([
                       'credit' => 0,
                       'debit' => $order->price,
                       'order_id' => $order->id,
                       'user_id' => $sender->id,
                       'total' => $sender->total_balance - $order->price,
                       'info' => 'أجور شحن  #' . $order->code,
                       'type' => BalanceTypeEnum::CATCH->value,
                       'is_complete' => false,
                   ]);
               }

           }*/


        if ($order->isDirty('status') && $order->status->value == OrderStatusEnum::CANCELED->value && $order->getOriginal('status') != OrderStatusEnum::PENDING) {
            $order->balances()->delete();
        }


        if ($order->isDirty('status') && $order->status->value == OrderStatusEnum::RETURNED->value && $order->getOriginal('status') != OrderStatusEnum::PENDING) {
            $order->balances()->delete();
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
