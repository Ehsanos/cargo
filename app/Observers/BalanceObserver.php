<?php

namespace App\Observers;

use App\Models\Balance;

class BalanceObserver
{
    /**
     * Handle the Balance "created" event.
     */
    public function creating(Balance $balance): void
    {
        if ($balance->is_complete || $balance->is_complete == null) {
            $balance->total = \DB::table('balances')->where('user_id', $balance->user_id)->where('is_complete', 1)
                    ->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total ?? 0 + $balance->credit - $balance->debit;
        } else {
            $balance->total = \DB::table('balances')->where('user_id', $balance->user_id)->where('is_complete', 0)
                    ->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total ?? 0 + $balance->credit - $balance->debit;
        }
    }

    /**
     * Handle the Balance "updated" event.
     */
    public function updating(Balance $balance): void
    {
        if ($balance->is_complete || $balance->is_complete == null) {
            $balance->total = \DB::table('balances')->whereNot('id',$balance->id)->where('user_id', $balance->user_id)->where('is_complete', 1)
                    ->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total ?? 0 + $balance->credit - $balance->debit;
        } else {
            $balance->total = \DB::table('balances')->whereNot('id',$balance->id)->where('user_id', $balance->user_id)->where('is_complete', 0)
                    ->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total ?? 0 + $balance->credit - $balance->debit;
        }
    }

    /**
     * Handle the Balance "deleted" event.
     */
    public function deleted(Balance $balance): void
    {
        //
    }

    /**
     * Handle the Balance "restored" event.
     */
    public function restored(Balance $balance): void
    {
        //
    }

    /**
     * Handle the Balance "force deleted" event.
     */
    public function forceDeleted(Balance $balance): void
    {
        //
    }
}
