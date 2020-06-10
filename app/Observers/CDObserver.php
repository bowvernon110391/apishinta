<?php

namespace App\Observers;

use App\CD;

class CDObserver
{
    /**
     * Handle the c d "created" event.
     *
     * @param  \App\CD  $cD
     * @return void
     */
    public function created(CD $cD)
    {
        // 
    }

    /**
     * Handle the c d "updated" event.
     *
     * @param  \App\CD  $cD
     * @return void
     */
    public function updated(CD $cD)
    {
        //
    }

    /**
     * Handle the c d "deleted" event.
     *
     * @param  \App\CD  $cD
     * @return void
     */
    public function deleted(CD $c)
    {
        // gotta delete all related docs
        $c->spp()->delete();
        $c->st()->delete();
        $c->imporSementara()->delete();
        $c->sspcp()->delete();
        $c->details()->delete();
    }

    /**
     * Handle the c d "restored" event.
     *
     * @param  \App\CD  $cD
     * @return void
     */
    public function restored(CD $c)
    {
        // when restoring, only restore the details
        $c->details()->withTrashed()->restore();

        // better restore all related docs
        $c->spp()->withTrashed()->restore();
        $c->st()->withTrashed()->restore();
        $c->imporSementara()->withTrashed()->restore();
        $c->sspcp()->withTrashed()->restore();
        $c->details()->withTrashed()->restore();
    }

    /**
     * Handle the c d "force deleted" event.
     *
     * @param  \App\CD  $cD
     * @return void
     */
    public function forceDeleted(CD $cD)
    {
        //
    }
}
