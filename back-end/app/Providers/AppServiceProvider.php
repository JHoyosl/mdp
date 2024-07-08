<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
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
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Passport::enablePasswordGrant();
        Passport::loadKeysFrom(base_path('secrets/oauth'));
        Passport::hashClientSecrets();
        Client::creating(function (Client $client) {
            $client->incrementing = false;
            $client->id = Str::uuid();
        });
    }
}
