<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Booking;
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
        $request->user()->bookings()->create([
            'start' => fromUserDateTime($request->validated('start')),
         'end' => fromUserDateTime($request->validated('end')),
       ]);

        return redirect()->route('booking.index');
    }

    public function edit(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        return view('bookings.edit', compact('booking'));
    }

    public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        $booking->update([
            'start' => fromUserDateTime($request->validated('start')),
            'end' => fromUserDateTime($request->validated('end')),
        ]);

        return redirect()->route('booking.index');
    }

    public function destroy(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        $booking->delete();

        return redirect()->route('booking.index');
    }
}
