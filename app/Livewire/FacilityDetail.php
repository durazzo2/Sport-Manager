<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Facility;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class FacilityDetail extends Component
{
    public Facility $facility;
    public string $selectedDate = '';
    public array $availableSlots = [];

    public bool $showBookingModal = false;
    public ?string $selectedCourtId = null;
    public array $selectedSlot = [];
    public array $selectedRentals = [];
    public int $calculatedPrice = 0;
    public $rentals = [];

    public function mount($id)
    {
        $this->facility = Facility::with(['courts', 'amenities', 'reviews.user'])->findOrFail($id);
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->loadAvailableSlots();
        $this->rentals = collect();
    }

    public function updatedSelectedDate()
    {
        $this->loadAvailableSlots();
    }

    public function loadAvailableSlots()
    {
        $slots = [];
        $date = Carbon::parse($this->selectedDate);

        foreach ($this->facility->courts as $court) {
            $courtSlots = [];
            for ($hour = 8; $hour < 22; $hour++) {
                $startTime = $date->copy()->setHour($hour)->setMinute(0)->setSecond(0);
                $endTime = $startTime->copy()->addHour();

                $isBooked = Booking::where('court_id', $court->id)
                    ->where('status', '!=', 'cancelled')
                    ->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime)
                    ->exists();

                $courtSlots[] = [
                    'start' => $startTime->format('H:i'),
                    'end' => $endTime->format('H:i'),
                    'available' => !$isBooked,
                    'price' => $court->base_price_per_hour,
                ];
            }
            $slots[$court->id] = [
                'court' => $court,
                'slots' => $courtSlots,
            ];
        }

        $this->availableSlots = $slots;
    }

    public function selectSlot($courtId, $slot)
    {
        $this->selectedCourtId = $courtId;
        $this->selectedSlot = $slot;
        $this->selectedRentals = [];
        $this->showBookingModal = true;

        $court = Court::find($courtId);
        if ($court) {
            $this->rentals = Rental::all()->filter(fn ($rental) => $rental->isSuitableFor($court->type));
        } else {
            $this->rentals = collect();
        }

        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        if (!$this->selectedCourtId || empty($this->selectedSlot)) {
            $this->calculatedPrice = 0;
            return;
        }

        $court = Court::find($this->selectedCourtId);
        if (!$court) {
            $this->calculatedPrice = 0;
            return;
        }

        $startTime = Carbon::parse($this->selectedDate . ' ' . $this->selectedSlot['start']);
        $endTime = Carbon::parse($this->selectedDate . ' ' . $this->selectedSlot['end']);

        $rentals = [];
        foreach ($this->selectedRentals as $rentalId) {
            $rentals[] = ['id' => $rentalId, 'quantity' => 1];
        }

        $this->calculatedPrice = Booking::calculatePrice($court, $startTime, $endTime, $rentals);
    }

    public function toggleRental($rentalId)
    {
        if (in_array($rentalId, $this->selectedRentals)) {
            $this->selectedRentals = array_values(array_diff($this->selectedRentals, [$rentalId]));
        } else {
            $this->selectedRentals[] = $rentalId;
        }
        $this->calculateTotal();
    }

    public function confirmBooking()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $court = Court::find($this->selectedCourtId);
        if (!$court || $court->facility_id !== $this->facility->id) {
            session()->flash('error', 'Invalid court selection.');
            $this->showBookingModal = false;
            return;
        }

        if (empty($this->selectedSlot) || !isset($this->selectedSlot['start']) || !isset($this->selectedSlot['end'])) {
            session()->flash('error', 'Invalid time slot.');
            $this->showBookingModal = false;
            return;
        }

        $startHour = (int) explode(':', $this->selectedSlot['start'])[0];
        if ($startHour < 8 || $startHour >= 22) {
            session()->flash('error', 'Invalid booking hours. Must be between 08:00 and 22:00.');
            $this->showBookingModal = false;
            return;
        }

        $startTime = Carbon::parse($this->selectedDate . ' ' . $this->selectedSlot['start']);
        $endTime = Carbon::parse($this->selectedDate . ' ' . $this->selectedSlot['end']);

        if ($startTime->isPast()) {
            session()->flash('error', 'Cannot book a time slot in the past.');
            $this->showBookingModal = false;
            $this->loadAvailableSlots();
            return;
        }

        $isBooked = Booking::where('court_id', $court->id)
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        if ($isBooked) {
            session()->flash('error', 'This slot is no longer available.');
            $this->showBookingModal = false;
            $this->loadAvailableSlots();
            return;
        }

        $rentals = [];
        foreach ($this->selectedRentals as $rentalId) {
            $rentals[] = ['id' => $rentalId, 'quantity' => 1];
        }

        $totalPrice = Booking::calculatePrice($court, $startTime, $endTime, $rentals);

        $booking = Booking::create([
            'user_id' => auth()->id(),
            'court_id' => $court->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'confirmed',
            'total_price' => $totalPrice,
            'qr_code' => Str::uuid()->toString(),
        ]);

        foreach ($this->selectedRentals as $rentalId) {
            $booking->rentals()->attach($rentalId, ['quantity' => 1]);
        }

        $this->showBookingModal = false;

        return redirect()->route('dashboard')->with('success', 'Booking confirmed successfully!');
    }

    public function render()
    {
        return view('livewire.facility-detail');
    }
}
