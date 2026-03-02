<?php

namespace App\Livewire;

use App\Models\Review;
use Livewire\Component;

class LeaveReview extends Component
{
    public $facilityId;
    public int $rating = 5;
    public string $comment = '';

    public function mount($facilityId)
    {
        $this->facilityId = $facilityId;
    }

    public function submit()
    {
        Review::create([
            'user_id' => auth()->id(),
            'facility_id' => $this->facilityId,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ]);

        $this->rating = 5;
        $this->comment = '';

        $this->dispatch('reviewSubmitted');
    }

    public function render()
    {
        return view('livewire.leave-review');
    }
}
