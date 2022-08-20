<?php

namespace App\Providers;

use App\Models\Account;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\EloquentStorage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Client::class, function ($app) {

            return (new Client())->init(new EloquentStorage([
                'domain'    => env('AMO_SUBDOMAIN'),
                'client_id' => env('AMO_CLIENT_ID'),
                'client_secret' => env('AMO_SECRET'),
                'redirect_uri'  => env('AMO_REDIRECT_URI'),
            ], Account::query()->first()));
        });

        $this->app->bind(\App\Services\AlfaCRM\Client::class, function ($app) {

            return (new \App\Services\AlfaCRM\Client())->init();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
