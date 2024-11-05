<?php

namespace App\Helper;

use App\Enums\BalanceTypeEnum;
use App\Models\Balance;
use App\Models\Order;
use App\Models\User;


class HelperBalance
{

    public static function setPickOrder(Order $order)
    {
      /*  $sender = $order->sender;
        try {
            if ($order->far_sender == true) {
                //
            }
        } catch (\Exception | \Error $e) {
            throw new \Exception($e->getMessage());
        }*/
    }


    public static function completePicker(Order $order)
    {
        $sender = $order->sender;
        $staff = $order->pick;
        try {
            if ($order->far_sender == true) {
                Balance::create([
                    'credit' => $order->far,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $sender->id,
                    'info' => 'أجور شحن  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);
                Balance::create([
                    'credit' => 0,
                    'debit' => $order->far,
                    'order_id' => $order->id,
                    'user_id' => $sender->id,

                    'info' => 'دفع أجور شحن  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);
                Balance::create([
                    'credit' => $order->far,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $staff->id,

                    'info' => 'دفع أجور شحن  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

            }
            self:: pendingBalancePick($order);
        } catch (\Exception | \Error $e) {
            info("HELPER BALANCE");
            info('Message HELPER BALANCE:'.$e->getMessage());
            info('File HELPER BALANCE:'.$e->getFile(). ' Line:'.$e->getLine());
            throw new \Exception($e->getMessage());
        }
    }


    public static function completeOrder(Order $order)
    {

        $sender = $order->sender;
        $receive = $order->receive;
        $staff = $order->given;
        try {
            if ($order->far_sender == false && $order->far > 0) {
                Balance::create([
                    'credit' => $order->far,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $receive->id,

                    'info' => 'أجور شحن  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

                Balance::create([
                    'credit' => 0,
                    'debit' => $order->far,
                    'order_id' => $order->id,
                    'user_id' => $receive->id,

                    'info' => 'دفع أجور شحن  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

                Balance::create([
                    'credit' => $order->far,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $staff->id,

                    'info' => 'دفع أجور شحن  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);
            }
            if ($order->price > 0) {
                Balance::create([
                    'credit' => $order->price,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $receive->id,

                    'info' => 'أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

                Balance::create([
                    'credit' => 0,
                    'debit' => $order->price,
                    'order_id' => $order->id,
                    'user_id' => $receive->id,

                    'info' => 'دفع أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

                Balance::create([
                    'credit' => $order->price,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $staff->id,

                    'info' => 'دفع أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

                Balance::create([
                    'credit' => 0,
                    'debit' => $order->price,
                    'order_id' => $order->id,
                    'user_id' => $sender->id,

                    'info' => 'دفع أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);
            }
            Balance::where('order_id',$order->id)->where('pending',true)->delete();
        } catch (\Exception | \Error $e) {
            throw new \Exception($e->getMessage());
        }


    }


    public static function pendingBalancePick(Order $order)
    {
        $sender = $order->sender;
        $receive = $order->receive;
        $staff = $order->pick;

        try {
            if ($order->far_sender == false && $order->far > 0) {
                Balance::create([
                    'user_id'=>$receive->id,
                    'debit' =>0,
                    'credit' => $order->far,
                    'info' => 'اجور شحن الطلب #' . $order->id,
                    'pending' => true,
                    'order_id' => $order->id
                ]);
            }

            if($order->price>0){
                Balance::create([
                    'user_id'=>$receive->id,
                    'debit' =>  0,
                    'credit' =>$order->price,
                    'info' => 'قيمة تحصيل الطلب #' . $order->id,
                    'pending' => true,
                    'order_id' => $order->id
                ]);

                Balance::create([
                    'user_id'=>$sender->id,
                    'debit' =>$order->price,
                    'credit' => 0,
                    'info' => 'قيمة تحصيل الطلب #' . $order->id,
                    'pending' => true,
                    'order_id' => $order->id
                ]);
            }

        }catch (\Exception $e){
            info("Error Observe in created function22");
            info('Message22:'.$e->getMessage());
            info('File22:'.$e->getFile(). ' Line:'.$e->getLine());
            throw new \Exception('Error Pick Pending');
        }

    }

    public static function getMaxCodeAccount(){
        $user=User::withoutGlobalScope('userOnly')->where('is_account',true)->orderBy('iban','desc')->max('iban')??1;
        return (int)$user+1;
    }

}
