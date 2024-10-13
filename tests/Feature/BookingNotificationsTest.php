<?php
namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingReminder1H;
use App\Notifications\BookingReminder2H;
use App\Notifications\BookingReminder5MIN;
use App\Notifications\BookingStartedNotification;
use Artisan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_booking_schedules_notifications(): void
    {
        $user = User::factory()->create([
            'timezone' => 'America/New_York'
        ]);
        $startTime = now()->addDay();
        $this->actingAs($user)
            ->post(route('booking.store'), [
                'start' => $startTime,
                'end' => now()->addDay()->addHours(2)
            ])
            ->assertRedirect(route('booking.index'));

        $this->assertEquals(4, $user->scheduledNotifications()->count());

        $startTimeToCheck = Carbon::parse(fromUserDateTime($startTime, $user), 'UTC')->setSeconds(0);
        $fiveMinTimeToCheck = Carbon::parse(fromUserDateTime($startTime->copy()->subMinutes(5)), 'UTC')->setSeconds(0);
        $oneHourTimeToCheck = Carbon::parse(fromUserDateTime($startTime->copy()->subHour()), 'UTC')->setSeconds(0);
        $twoHoursTimeToCheck = Carbon::parse(fromUserDateTime($startTime->copy()->subHours(2)), 'UTC')->setSeconds(0);

        $this->assertNotNull($user->scheduledNotifications()->where('scheduled_at', $startTimeToCheck)->first());
        $this->assertNotNull($user->scheduledNotifications()->where('scheduled_at', $fiveMinTimeToCheck)->first());
        $this->assertNotNull($user->scheduledNotifications()->where('scheduled_at', $oneHourTimeToCheck)->first());
        $this->assertNotNull($user->scheduledNotifications()->where('scheduled_at', $twoHoursTimeToCheck)->first());
    }

    public function test_creating_booking_that_is_sooner_than_some_notifications_dont_schedule_them(): void
    {
        $user = User::factory()->create([
            'timezone' => 'America/New_York'
        ]);
        $startTime = now()->addMinutes(30);
        $this->actingAs($user)
            ->post(route('booking.store'), [
                'start' => $startTime,
                'end' => now()->addDay()->addHours(2)
            ])
            ->assertRedirect(route('booking.index'));

        $this->assertEquals(2, $user->scheduledNotifications()->count());

        $startTimeToCheck = Carbon::parse(fromUserDateTime($startTime, $user), 'UTC')->setSeconds(0);
        $fiveMinTimeToCheck = Carbon::parse(fromUserDateTime($startTime->copy()->subMinutes(5)), 'UTC')->setSeconds(0);

        $this->assertNotNull($user->scheduledNotifications()->where('scheduled_at', $startTimeToCheck)->first());
        $this->assertNotNull($user->scheduledNotifications()->where('scheduled_at', $fiveMinTimeToCheck)->first());
    }

    public function test_scheduler_sends_the_notifications_out(): void
    {
        $user = User::factory()->create([
            'timezone' => 'America/New_York'
        ]);
        $startTime = now()->addDay();
        $this->actingAs($user)
            ->post(route('booking.store'), [
                'start' => $startTime,
                'end' => now()->addDay()->addHours(2)
            ])
            ->assertRedirect(route('booking.index'));

        $booking = Booking::where('user_id', $user->id)->first();

        $startTimeToCheck = Carbon::parse(fromUserDateTime($startTime, $user), 'UTC')->setSeconds(0);
        $fiveMinTimeToCheck = Carbon::parse(fromUserDateTime($startTime->copy()->subMinutes(5)), 'UTC')->setSeconds(0);
        $oneHourTimeToCheck = Carbon::parse(fromUserDateTime($startTime->copy()->subHour()), 'UTC')->setSeconds(0);
        $twoHoursTimeToCheck = Carbon::parse(fromUserDateTime($startTime->copy()->subHours(2)), 'UTC')->setSeconds(0);

        date_default_timezone_set('UTC');

        Notification::fake();
        Carbon::setTestNow(now()->addHours(6));
        Artisan::call('send:scheduled-notifications');
        Notification::assertNothingSent();

        Notification::fake();
        Carbon::setTestNow($twoHoursTimeToCheck);
        Artisan::call('send:scheduled-notifications');
        Notification::assertSentTo([$user], BookingReminder2H::class);
        $this->assertTrue($booking->scheduledNotifications()->where('scheduled_at', $twoHoursTimeToCheck)->first()->sent);

        Notification::fake();
        Artisan::call('send:scheduled-notifications');
        Notification::assertNothingSentTo($user);

        Notification::fake();
        Carbon::setTestNow($oneHourTimeToCheck);
        Artisan::call('send:scheduled-notifications');
        Notification::assertSentTo([$user], BookingReminder1H::class);
        $this->assertTrue($booking->scheduledNotifications()->where('scheduled_at', $oneHourTimeToCheck)->first()->sent);

        Notification::fake();
        Carbon::setTestNow($fiveMinTimeToCheck);
        Artisan::call('send:scheduled-notifications');
        Notification::assertSentTo([$user], BookingReminder5MIN::class);
        $this->assertTrue($booking->scheduledNotifications()->where('scheduled_at', $fiveMinTimeToCheck)->first()->sent);

        Notification::fake();
        Carbon::setTestNow($startTimeToCheck);
        Artisan::call('send:scheduled-notifications');
        Notification::assertSentTo([$user], BookingStartedNotification::class);
        $this->assertTrue($booking->scheduledNotifications()->where('scheduled_at', $startTimeToCheck)->first()->sent);
    }

    public function test_sends_out_all_notifications_if_something_went_wrong(): void
    {
        $user = User::factory()->create([
            'timezone' => 'America/New_York'
        ]);
        $startTime = now()->addDay();
        $this->actingAs($user)
            ->post(route('booking.store'), [
                'start' => $startTime,
                'end' => now()->addDay()->addHours(2)
            ])
            ->assertRedirect(route('booking.index'));

        $booking = Booking::where('user_id', $user->id)->first();

        $startTimeToCheck = Carbon::parse(fromUserDateTime($startTime, $user), 'UTC')->setSeconds(0);
        $fiveMinTimeToCheck = Carbon::parse(fromUserDateTime($startTime->copy()->subMinutes(5)), 'UTC')->setSeconds(0);
        $oneHourTimeToCheck = Carbon::parse(fromUserDateTime($startTime->copy()->subHour()), 'UTC')->setSeconds(0);
        $twoHoursTimeToCheck = Carbon::parse(fromUserDateTime($startTime->copy()->subHours(2)), 'UTC')->setSeconds(0);

        date_default_timezone_set('UTC');

        Notification::fake();
        Carbon::setTestNow($startTimeToCheck);
        Artisan::call('send:scheduled-notifications');
        Notification::assertSentTo([$user], BookingReminder2H::class);
        $this->assertTrue($booking->scheduledNotifications()->where('scheduled_at', $twoHoursTimeToCheck)->first()->sent);

        Notification::assertSentTo([$user], BookingReminder1H::class);
        $this->assertTrue($booking->scheduledNotifications()->where('scheduled_at', $oneHourTimeToCheck)->first()->sent);

        Notification::assertSentTo([$user], BookingReminder5MIN::class);
        $this->assertTrue($booking->scheduledNotifications()->where('scheduled_at', $fiveMinTimeToCheck)->first()->sent);

        Notification::assertSentTo([$user], BookingStartedNotification::class);
        $this->assertTrue($booking->scheduledNotifications()->where('scheduled_at', $startTimeToCheck)->first()->sent);
    }

    public function test_tries_are_increasing_when_failed(): void
    {
        $user = User::factory()->create([
            'timezone' => 'America/New_York'
        ]);
        $startTime = now()->addDay();
        $this->actingAs($user)
            ->post(route('booking.store'), [
                'start' => $startTime,
                'end' => now()->addDay()->addHours(2)
            ])
            ->assertRedirect(route('booking.index'));

        $booking = Booking::where('user_id', $user->id)->first();

        $startTimeToCheck = Carbon::parse(fromUserDateTime($startTime, $user), 'UTC')->setSeconds(0);
        $fiveMinTimeToCheck = Carbon::parse(fromUserDateTime($startTime->copy()->subMinutes(5)), 'UTC')->setSeconds(0);
        $oneHourTimeToCheck = Carbon::parse(fromUserDateTime($startTime->copy()->subHour()), 'UTC')->setSeconds(0);
        $twoHoursTimeToCheck = Carbon::parse(fromUserDateTime($startTime->copy()->subHours(2)), 'UTC')->setSeconds(0);

        date_default_timezone_set('UTC');

        $booking->delete();

        Notification::fake();
        Carbon::setTestNow($startTimeToCheck);

        for ($i = 1; $i <= 2; $i++) {
            // Each time we run the command, the tries will be increased by 2
            // as it will attempt to send the notification 2 times
            Artisan::call('send:scheduled-notifications');
            Notification::assertNothingSent();
            $this->assertEquals($i * 2, $booking->scheduledNotifications()->where('scheduled_at', $twoHoursTimeToCheck)->first()->tries);
            $this->assertEquals($i * 2, $booking->scheduledNotifications()->where('scheduled_at', $oneHourTimeToCheck)->first()->tries);
            $this->assertEquals($i * 2, $booking->scheduledNotifications()->where('scheduled_at', $fiveMinTimeToCheck)->first()->tries);
            $this->assertEquals($i * 2, $booking->scheduledNotifications()->where('scheduled_at', $startTimeToCheck)->first()->tries);
        }

        // After 3 tries, we make sure that we didn't exceed our limit of attempts:
        Artisan::call('send:scheduled-notifications');
        Notification::assertNothingSent();
        $this->assertEquals(config('app.notificationAttemptAmount'), $booking->scheduledNotifications()->where('scheduled_at', $twoHoursTimeToCheck)->first()->tries);
        $this->assertEquals(config('app.notificationAttemptAmount'), $booking->scheduledNotifications()->where('scheduled_at', $oneHourTimeToCheck)->first()->tries);
        $this->assertEquals(config('app.notificationAttemptAmount'), $booking->scheduledNotifications()->where('scheduled_at', $fiveMinTimeToCheck)->first()->tries);
        $this->assertEquals(config('app.notificationAttemptAmount'), $booking->scheduledNotifications()->where('scheduled_at', $startTimeToCheck)->first()->tries);
    }

    public function test_editing_booking_start_date_changes_scheduled_notifications(): void
    {
        $user = User::factory()->create([
            'timezone' => 'America/New_York'
        ]);
        $startTime = now()->addDay();
        $this->actingAs($user)
            ->post(route('booking.store'), [
                'start' => $startTime,
                'end' => now()->addDay()->addHours(2)
            ])
            ->assertRedirect(route('booking.index'));

        $this->assertEquals(4, $user->scheduledNotifications()->count());

        $booking = Booking::where('user_id', $user->id)->first();

        $newStartTime = now()->addDays(2);
        $this->actingAs($user)
            ->put(route('booking.update', $booking->id), [
                'start' => $newStartTime,
                'end' => now()->addDay()->addHours(2)
            ])
            ->assertRedirect(route('booking.index'));

        $startTimeToCheck = Carbon::parse(fromUserDateTime($newStartTime, $user), 'UTC')->setSeconds(0);
        $fiveMinTimeToCheck = Carbon::parse(fromUserDateTime($newStartTime->copy()->subMinutes(5)), 'UTC')->setSeconds(0);
        $oneHourTimeToCheck = Carbon::parse(fromUserDateTime($newStartTime->copy()->subHour()), 'UTC')->setSeconds(0);
        $twoHoursTimeToCheck = Carbon::parse(fromUserDateTime($newStartTime->copy()->subHours(2)), 'UTC')->setSeconds(0);

        $this->assertNotNull($booking->scheduledNotifications()->where('scheduled_at', $startTimeToCheck)->first());
        $this->assertNotNull($booking->scheduledNotifications()->where('scheduled_at', $fiveMinTimeToCheck)->first());
        $this->assertNotNull($booking->scheduledNotifications()->where('scheduled_at', $oneHourTimeToCheck)->first());
        $this->assertNotNull($booking->scheduledNotifications()->where('scheduled_at', $twoHoursTimeToCheck)->first());
    }

    public function test_deleting_a_booking_deletes_scheduled_events(): void
    {
        $user = User::factory()->create([
            'timezone' => 'America/New_York'
        ]);
        $startTime = now()->addDay();
        $this->actingAs($user)
            ->post(route('booking.store'), [
                'start' => $startTime,
                'end' => now()->addDay()->addHours(2)
            ])
            ->assertRedirect(route('booking.index'));

        $booking = Booking::where('user_id', $user->id)->first();

        $this->actingAs($user)
            ->delete(route('booking.destroy', $booking->id));

        $this->assertEquals(0, $booking->scheduledNotifications()->where('notifiable_id', $booking->id)->count());
    }
}
