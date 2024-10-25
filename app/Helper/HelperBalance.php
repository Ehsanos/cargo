<?php

namespace App\Helper;

use App\Enums\BalanceTypeEnum;
use App\Models\Balance;
use App\Models\Order;




class HelperBalance
{

    public static function setPickOrder(Order $order){
        $sender = $order->sender;
       try{
           if($order->far_sender ==true){
               Balance::create([
                   'credit' => $order->far,
                   'debit' => 0,
                   'order_id' => $order->id,
                   'user_id' => $sender->id,
                   'total' => $sender->total_balance + $order->far,
                   'info' => 'أجور شحن  #' . $order->code,
                   'type' => BalanceTypeEnum::CATCH->value,
                   'is_complete' => true,
               ]);
           }
       }catch (\Exception | \Error $e){
           throw new \Exception($e->getMessage());
       }
    }


    public static function completePicker(Order $order ){
        $sender = $order->sender;
        $staff = $order->pick;
        try{
            if($order->far_sender ==true){
                Balance::create([
                    'credit' => 0,
                    'debit' => $order->far,
                    'order_id' => $order->id,
                    'user_id' => $sender->id,
                    'total' => $sender->total_balance - $order->far,
                    'info' => 'دفع أجور شحن  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

                Balance::create([
                    'credit' => $order->far,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $staff->id,
                    'total' => $staff->total_balance - $order->far,
                    'info' => 'دفع أجور شحن  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);
            }
        }catch (\Exception | \Error $e){
            throw new \Exception($e->getMessage());
        }
    }


    public static function completeOrder(Order $order ){

        $sender = $order->sender;
        $receive = $order->receive;
        $staff=$order->given;
        try{
            if($order->far_sender ==false && $order->far>0){
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

                Balance::create([
                    'credit' => 0,
                    'debit' => $order->far,
                    'order_id' => $order->id,
                    'user_id' => $receive->id,
                    'total' => $receive->total_balance - $order->far,
                    'info' => 'دفع أجور شحن  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

                Balance::create([
                    'credit' => $order->far,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $staff->id,
                    'total' => $staff->total_balance - $order->far,
                    'info' => 'دفع أجور شحن  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);
            }
            if($order->price >0){
                Balance::create([
                    'credit' => $order->price,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $receive->id,
                    'total' => $receive->total_balance + $order->price,
                    'info' => 'أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

                Balance::create([
                    'credit' => 0,
                    'debit' => $order->price,
                    'order_id' => $order->id,
                    'user_id' => $receive->id,
                    'total' => $receive->total_balance - $order->price,
                    'info' => 'دفع أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

                Balance::create([
                    'credit' => $order->price,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $staff->id,
                    'total' => $staff->total_balance - $order->price,
                    'info' => 'دفع أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

                Balance::create([
                    'credit' => 0,
                    'debit' => $order->price,
                    'order_id' => $order->id,
                    'user_id' => $sender->id,
                    'total' => $sender->total_balance - $order->price,
                    'info' => 'دفع أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);
            }
        }catch (\Exception | \Error $e){
            throw new \Exception($e->getMessage());
        }


    }

}
