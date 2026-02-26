<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\NotificationObserver;
use App\Observers\MessageObserver;
use App\Models\Notification;
use App\Models\Message;

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
        Notification::observe(NotificationObserver::class);
        Message::observe(MessageObserver::class);
    }
}
