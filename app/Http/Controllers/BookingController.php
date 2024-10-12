<?php

namespace App\Http\Controllers;

use App\Events\BookingCreatedEvent;
use App\Events\BookingDeletedEvent;
use App\Events\BookingUpdatedEvent;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Booking;
use App\Models\ScheduledNotification;
use App\Notifications\BookingReminder1H;
use App\Notifications\BookingReminder2H;
use App\Notifications\BookingReminder5MIN;
use App\Notifications\BookingStartedNotification;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::query()
            ->with(['user'])
            ->get();
        return view('bookings.index', compact('bookings'));
    }
    public function create()
    {
        return view('bookings.create');
    }
    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $booking = $request->user()->bookings()->create([
            'start' => fromUserDateTime($request->validated('start')),
            'end' => fromUserDateTime($request->validated('end')),
        ]);
        event(new BookingCreatedEvent($booking));
        return redirect()->route('booking.index');
    }
    public function edit(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);
        return view('bookings.edit', compact('booking'));
    }
    public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        $booking->update([
            'start' => fromUserDateTime($request->validated('start')),
            'end' => fromUserDateTime($request->validated('end')),
        ]);
        event(new BookingUpdatedEvent($booking));
        return redirect()->route('booking.index');
    }
    public function destroy(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 404);
        $booking->delete();
        event(new BookingDeletedEvent($booking));
        return redirect()->route('booking.index');
    }
}
