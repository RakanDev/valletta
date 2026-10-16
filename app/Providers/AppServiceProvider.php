<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\Order::observe(\App\Observers\OrderObserver::class);

        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            static $shared;
            $shared ??= [
                'locale' => app()->getLocale(),
                'dir' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr',
                'cartCount' => app(\App\Services\Cart::class)->count(),
            ];
            $view->with($shared);
        });
    }
}
