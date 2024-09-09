<?php

namespace App\Observers;

use App\Enums\BayTypeEnum;
use App\Enums\TaskAgencyEnum;
use App\Models\Agency;
use App\Models\Balance;
use App\Models\Order;

class AgencyObServer
{
    /**
     * Handle the Agency "created" event.
     */
    public function created(Agency $agency): void
    {
        /**
         * @var $order Order
         */
       /* $order = $agency->order;
        if ($order->bay_type?->value == BayTypeEnum::BEFORE->value && $agency->status->value == TaskAgencyEnum::TAKE->value) {
            $user = $agency->user;
            Balance::create([
                'credit' => 0,
                'debit' => $order->price,
                'order_id' => $order->id,
                'user_id' => $user->id,
                'is_complete' => false,
                'total' => $user->pending_balance + $order->price,
                'info' => 'أجور شحن طلب رقم ' . $order->code . ' غير مستلمة ',
            ]);

        } elseif ($order->bay_type?->value == BayTypeEnum::AFTER->value && $agency->status->value == TaskAgencyEnum::DELIVER->value) {

            $user = $agency->user;
            Balance::create([
                'credit' => 0,
                'debit' => $order->price,
                'order_id' => $order->id,
                'user_id' => $user->id,
                'is_complete' => false,
                'total' => $user->pending_balance + $order->price,
                'info' => 'أجور شحن طلب رقم ' . $order->code . ' غير مستلمة ',
            ]);

        }*/

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
