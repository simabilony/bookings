<?php

namespace App\Listeners;

use App\Events\BookingDeletedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;


class BookingDeletedListener
{
    public function __construct()
    {
    }

    public function handle(BookingDeletedEvent $event): void
    {
        $event->booking->scheduledNotifications()
            ->where('user_id', $event->booking->user_id)
            ->delete();
    }
}
