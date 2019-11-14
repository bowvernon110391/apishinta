<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /* Relation::morphMap([
            'cd_header' => 'App\CD',
            'cd_detail' => 'App\DetailCD',
            'sspcp_header' => 'App\SSPCP',
            'sspcp_detail' => 'App\DetailSSPCP',
            'kurs'  => 'App\Kurs'
        ]); */
    }
}
