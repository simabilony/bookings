<?php

namespace App\Jobs;

use App\Models\ScheduledNotification;
use App\Notifications\BookingReminder1H;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ScheduledNotification $notification;

    public function __construct(int $notificationID)
    {
        try {
            $this->notification = ScheduledNotification::query()
                ->with(['user'])
                ->withWhereHas('notifiable')
                ->findOrFail($notificationID);
        } catch (Exception $exception) {
            // Backup, just try to get the notification by id and fail the job
            $this->notification = ScheduledNotification::query()
                ->find($notificationID);
            $this->fail($exception);
        }
    }

    public function handle(): void
    {
        if ($this->notification->sent || $this->notification->tries >= config('app.notificationAttemptAmount')) {
            return;
        }
        if (!$this->notification->notifiable) {
            // Makes sure that the notifiable is still available
            $this->fail();
            return;
        }
        try {
            switch ($this->notification->notification_class) {
                case BookingReminder1H::class:
                    $this->notification->user->notify(new BookingReminder1H($this->notification->notifiable));
                    break;
            }

            $this->notification->update(['processing' => false, 'sent' => true, 'sent_at' => now()]);
        } catch (Exception $exception) {
            $this->fail($exception);
        }
    }

    public function fail($exception = null)
    {
        $this->notification->update(['processing' => false]);
        $this->notification->increment('tries');
    }
}
