<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('phone_filled', function($attribute, $value, $parameters, $validator) {
            $phone = preg_replace("/[^0-9]/", "", $value);
            return strlen($phone) == 10;
        });
        Validator::extend('starts_with_correct_digit', function($attribute, $value, $parameters, $validator) {
            $phone = preg_replace("/[^0-9]/", "", $value);
            return in_array($phone[0], [4, 9]);
        });
        // \DB::listen(function($query) {
        //     \Log::info($query->sql);
        // });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
