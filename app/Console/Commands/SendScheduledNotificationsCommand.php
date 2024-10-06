<?php

namespace App\Console\Commands;

use App\Models\ScheduledNotification;
use Exception;
use Illuminate\Console\Command;

class SendScheduledNotificationsCommand extends Command
{
    protected $signature = 'send:scheduled-notifications';

    protected $description = 'Sends scheduled notifications to the users';

    public function handle(): void
    {
        $notificationsToSend = ScheduledNotification::query()
            ->where('sent', false)
            ->where('processing', false)
            ->where('tries', '<=', config('app.notificationAttemptAmount'))
            ->where('scheduled_at', '<=', now()->format('Y-m-d H:i'))
            ->get();

        // Lock jobs as processing
        ScheduledNotification::query()
            ->whereIn('id', $notificationsToSend->pluck('id'))
            ->update(['processing' => true]);

        foreach ($notificationsToSend as $notification) {
            try {
                dispatch(new ProcessNotificationJob($notification->id));
            } catch (Exception $exception) {
                $notification->increment('tries');
                $notification->update(['processing' => false]);
            }
        }
    }
}
