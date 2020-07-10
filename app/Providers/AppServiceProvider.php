<?php

namespace App\Providers;

use App\CD;
use App\Lampiran;
use App\Observers\CDObserver;
use App\Observers\LampiranObserver;
use App\Services\SSO;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // try to register it?
        $this->app->bind(SSO::class, function () {
            // resolve for request object
            $request = app(Request::class);

            return new SSO($request);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        // register our observers here
        CD::observe(CDObserver::class);
        Lampiran::observe(LampiranObserver::class);
    }
}
