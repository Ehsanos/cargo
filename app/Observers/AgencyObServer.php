<?php

namespace App\Observers;

use App\Enums\BalanceTypeEnum;
use App\Enums\BayTypeEnum;
use App\Enums\TaskAgencyEnum;
use App\Models\Agency;
use App\Models\Balance;
use App\Models\Order;
use App\Models\User;

class AgencyObServer
{
    /**
     * Handle the Agency "created" event.
     */
    public function created(Agency $agency): void
    {

        if($agency->status==TaskAgencyEnum::DELIVER->value){
            /**
             * @var $user User
             * @var $order Order
             */
            $user=$agency->user;
            $order=$agency->order;
            Balance::create([
                'debit'=>$order->price + $order->far,
                'is_complete'=>false,
                'credit'=>0,
                'order_id'=>$order->id,
                'info'=>'أجور شحن + تحصيل',
                'type'=>BalanceTypeEnum::CATCH->value,
                'user_id'=>$user->id,
                'total'=>$user->total_balance + $order->price + $order->far,
            ]);
        }

    }

    /**
     * Handle the Agency "updated" event.
     */
    public function updated(Agency $agency): void
    {
        //
    }

    /**
     * Handle the Agency "deleted" event.
     */
    public function deleted(Agency $agency): void
    {
        //
    }

    /**
     * Handle the Agency "restored" event.
     */
    public function restored(Agency $agency): void
    {
        //
    }

    /**
     * Handle the Agency "force deleted" event.
     */
    public function forceDeleted(Agency $agency): void
    {
        //
    }
}
