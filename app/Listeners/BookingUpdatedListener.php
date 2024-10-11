<?php

namespace App\Listeners;

use App\Events\BookingUpdatedEvent;
use App\Models\Booking;
use App\Models\ScheduledNotification;
use Carbon\CarbonImmutable;

class BookingUpdatedListener
{
    public function __construct()
    {
    }

    public function handle(BookingUpdatedEvent $event): void
    {
        $booking = $event->booking;
        $booking->load('user');
        $startTime = CarbonImmutable::parse(toUserDateTime($booking->start, $booking->user), $booking->user->timezone);

        $hasScheduledNotifications = ScheduledNotification::query()
            ->where('notifiable_id', $booking->id)
            ->where('notifiable_type', Booking::class)
            ->where('user_id', $booking->user_id)
            ->exists();

        // First we need to check if there are any already scheduled notifications
        if ($hasScheduledNotifications) {
            // Then in this example, we simply delete them. You can however update them if you want.
            $booking->scheduledNotifications()
                ->where('user_id', $booking->user_id)
                ->delete();
        }

        // Since we are clearing the scheduled notifications, we need to create them again for the new date
        $booking->createReminderNotifications($booking, $startTime);
    }
}
