<?php

namespace App\Providers;
use App\Events\BookingCreatedEvent;
use App\Events\BookingDeletedEvent;
use App\Events\BookingUpdatedEvent;
use App\Listeners\BookingCreatedListener;
use App\Listeners\BookingDeletedListener;
use App\Listeners\BookingUpdatedListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // ...
        BookingCreatedEvent::class => [
            BookingCreatedListener::class,
        ],
        BookingUpdatedEvent::class => [
            BookingUpdatedListener::class,
        ],
        BookingDeletedEvent::class => [
            BookingDeletedListener::class,
        ],
    ];
// ...
}
