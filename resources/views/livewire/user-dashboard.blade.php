<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">My Bookings</h1>

    @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($bookings->isEmpty())
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg">You have no bookings yet.</p>
            <a href="/" class="mt-4 inline-block bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition font-medium">Browse Facilities</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($bookings as $booking)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="font-bold text-lg text-gray-900">{{ $booking->court->name }}</h3>
                                @php
                                    $statusColors = [
                                        'confirmed' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'completed' => 'bg-blue-100 text-blue-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                    ];
                                    $color = $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $color }}">{{ ucfirst($booking->status) }}</span>
                            </div>
                            <p class="text-sm text-gray-500">{{ $booking->court->facility->name }}</p>
                            <div class="mt-2 text-sm text-gray-600 space-y-1">
                                <p><span class="font-medium">Date:</span> {{ $booking->start_time->format('D, M j, Y') }}</p>
                                <p><span class="font-medium">Time:</span> {{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}</p>
                                <p><span class="font-medium">Total:</span> {{ number_format($booking->total_price / 100, 0) }} MKD</p>
                            </div>
                        </div>

                        <div class="flex flex-col items-center gap-3">
                            <div class="bg-white p-2 rounded-lg border">
                                {!! QrCode::size(120)->generate($booking->qr_code) !!}
                            </div>
                            <span class="text-xs text-gray-400 font-mono">{{ Str::limit($booking->qr_code, 13) }}</span>
                        </div>

                        <div class="flex flex-col gap-2">
                            @if($booking->status === 'confirmed' && $booking->start_time->gt(now()->addHours(24)))
                                <button wire:click="cancelBooking('{{ $booking->id }}')" wire:confirm="Are you sure you want to cancel this booking?" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition text-sm font-medium">
                                    Cancel Booking
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
