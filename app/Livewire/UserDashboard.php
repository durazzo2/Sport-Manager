<?php

namespace App\Livewire;

use App\Models\Booking;
use Livewire\Attributes\Layout;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

#[Layout('layouts.app')]
class UserDashboard extends Component
{
    public function cancelBooking($bookingId)
    {
        $booking = Booking::where('id', $bookingId)
            ->where('user_id', auth()->id())
            ->where('status', 'confirmed')
            ->firstOrFail();

        if ($booking->start_time->gt(now()->addHours(24))) {
            $booking->update(['status' => 'cancelled']);
            session()->flash('success', 'Booking cancelled successfully.');
        }
    }

    public function render()
    {
        $bookings = Booking::with('court.facility')
            ->where('user_id', auth()->id())
            ->orderBy('start_time', 'desc')
            ->get();

        return view('livewire.user-dashboard', [
            'bookings' => $bookings,
        ]);
    }
}
