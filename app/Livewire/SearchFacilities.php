<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Facility;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SearchFacilities extends Component
{
    use WithPagination;

    public string $city = '';
    public string $type = '';
    public string $date = '';
    public array $amenityFilters = [];

    public function updatingCity()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
    }

    public function search()
    {
        $this->resetPage();
    }

    public function getCities()
    {
        return Facility::distinct()->pluck('city')->filter()->sort()->values();
    }

    public function render()
    {
        $query = Facility::with(['courts', 'amenities', 'reviews']);

        if ($this->city) {
            $query->where('city', $this->city);
        }

        if ($this->type) {
            $query->whereHas('courts', function ($q) {
                $q->where('type', $this->type);
            });
        }

        if ($this->date) {
            $date = Carbon::parse($this->date);
            $query->whereHas('courts', function ($q) use ($date) {
                $q->whereDoesntHave('bookings', function ($bq) use ($date) {
                    $bq->where('status', '!=', 'cancelled')
                        ->whereDate('start_time', $date);
                })->orWhereHas('bookings', function ($bq) use ($date) {
                    $bq->where('status', '!=', 'cancelled')
                        ->whereDate('start_time', $date);
                }, '<', 14);
            });
        }

        if (!empty($this->amenityFilters)) {
            foreach ($this->amenityFilters as $amenityId) {
                $query->whereHas('amenities', function ($q) use ($amenityId) {
                    $q->where('amenities.id', $amenityId);
                });
            }
        }

        $facilities = $query->paginate(12);

        return view('livewire.search-facilities', [
            'facilities' => $facilities,
            'cities' => $this->getCities(),
        ]);
    }
}
