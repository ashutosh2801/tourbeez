<x-admin>
@section('title', 'Tour Manifest')

{{-- Include Bootstrap Icons --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<div class="card-primary mb-3">
    <form method="GET" action="{{ route('admin.orders.tour.manifest') }}">
        <div class="card-header order-manifest-head">
            <div class="d-flex justify-content-between align-items-center w-100 mb-manifest">
                <h3 class="card-title text-white">Session Manifest</h3>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center left-btn" id="prev-date">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <input type="date" name="date" id="filter-date" class="form-control form-control-sm filterDate" value="{{ request('date', \Carbon\Carbon::today()->toDateString()) }}" />
                    <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center right-btn" id="next-date">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <a href="{{ route('admin.orders.tour.manifest.download', ['date' => request('date')]) }}"
                   class="btn btn-success btn-sm">
                   <i class="bi bi-download"></i> Download Excel
                </a>
            </div>
        </div>
    </form>
</div>
<div class="card-primary bg-white border rounded-lg-custom">
    <div class="card-body p-0">
        @forelse($sessions as $slotTime => $session)
            <div class="card mb-2 border b-radius-0">
                <div class="card-header d-flex justify-content-between align-items-center bg-light b-radius-0"
                    data-bs-toggle="collapse"
                    data-bs-target="#session-{{ \Illuminate\Support\Str::slug($slotTime) }}"
                    aria-expanded="false"
                    aria-controls="session-{{ \Illuminate\Support\Str::slug($slotTime) }}">
                    <strong>{{ $slotTime }}</strong>

                    <div class="d-flex align-items-center gap-3">
                        <span>
                            {{ count($session['orders']) }} Order{{ count($session['orders']) > 1 ? 's' : '' }} |
                            {{ $session['total_guests'] }} Participants
                        </span>
                    </div>
                    <i class="bi bi-chevron-down toggle-icon font-bold"></i>
                </div>

                <div id="session-{{ \Illuminate\Support\Str::slug($slotTime) }}" class="collapse">
                    <div class="card-body">
                        <table class="table table-bordered table-striped" style="table-layout: fixed; width:100%;">

                            <thead >
                                <tr>
                                    <th class="px-1" style="width:10%; white-space: nowrap;">Order <br> Number</th>
                                    <th style="width:10%; white-space: nowrap;">Customer</th>
                                    <th style="width:12%; white-space: nowrap;">Phone</th>
                                    <th style="width:12%; white-space: nowrap;">Guests</th>
                                    <th style="width:14%; white-space: nowrap;">Extras</th>
                                    <th style="width:10%; white-space: nowrap;">Balance</th>
                                    <th style="width:10%; white-space: nowrap;">Total</th>
                                    <th style="width:10%; white-space: nowrap;">Paid</th>
                                    <th style="width:14%; white-space: nowrap;">Pickup</th>
                                    <th style="width:14%; white-space: nowrap;">Intruction</th>
                                    <th style="width:14%; white-space: nowrap;">Internal <br> Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($session['orders'] as $order)
                                    <tr>
                                        <td class="px-1">
                                            <a href="{{ route('admin.orders.edit', encrypt($order->id)) }}" class="alink" target="_blank">
                                                {{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td class="px-1">
                                            <a href="{{ route('admin.customers.show', encrypt($order->customer?->id)) }}"
                                                class="alink" target="_blank">
                                                {{ $order->customer?->name }}
                                            </a>
                                        </td>
                                        <td class="px-1">{{ $order->customer?->phone }}</td>
                                        <td class="px-1">{{ $order->guest_summary }}</td>
                                        <td class="px-1">{{ $order->extras_summary }}</td>


                                        @php
                                            $pickName = '';
                                            $instruction = '';
                                            if($order->customer && $order->customer->pickup_name){
                                                $pickName = $order->customer->pickup_name;
                                                $instruction = $order->customer->instructions;
                                            } elseif($order->customer && $order->customer->pickup_id) {
                                                $pickLocation = \App\Models\PickupLocation::find($order->customer->pickup_id);
                                                $pickName = $pickLocation->location . " - " . $pickLocation->address . " - " . $pickLocation->time;
                                                $instruction = $order->customer->instructions;
                                            }
                                        @endphp
                                        

                                        @php
                                            $total = round($order->total_amount);
                                           // $paid = round($order->booked_amount) ?? 0; 

                                            $paid = round($order->payments->where('status', 'succeeded')->sum('amount') - $order->payments->where('status', 'refunded')->sum('amount') + $order->payments->where('status', 'partial_refunded')->sum('amount'));


                                            $hasUncaptured = $order->payments->contains('status', 'uncaptured');

                                            if ($paid < $total) {
                                                if($paid == 0 && $hasUncaptured){
                                                    $amountClass = 'text-orange';
                                                } else{
                                                    $amountClass = 'text-danger'; // red
                                                }
                                               
                                            } else {
                                                $amountClass = 'text-success'; // green
                                            }

                                            if ($order->order_status == 6) {
                                                $amountClass = 'text-secondary'; // grey
                                            } 
                                        @endphp

                                        <td class="{{ $amountClass}} px-1">{{ price_format_with_currency($total-$paid, $order->currency) }}</td>
                                        <td class="px-1">{{ price_format_with_currency($total, $order->currency) }}</td>
                                        <td class="{{ $amountClass}} px-1">{{ price_format_with_currency($paid, $order->currency) }}</td>
                                        <td class="px-1">{{ $pickName }}</td>
                                        <td class="px-1">{{ $order->customer?->instructions ?? '-' }}</td>
                                        <td class="px-1">{{ $order->internal_notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
        <p class="m-0 p-3">No sessions found for this date.</p>
        @endforelse
    </div>
</div>

{{-- Scripts --}}
@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">


<style>
    .toggle-icon {
        transition: transform 0.3s ease;
        font-size: 1rem;
    }

    .card-header[aria-expanded="true"] .toggle-icon {
        transform: rotate(180deg);
    }

    .card-header:hover {
        background-color: #f0f4f8;
    }
</style>

<script>
    // Auto-expand first accordion
    const first = document.querySelector('.collapse');
    if (first) {
        first.classList.add('show');
        const firstIcon = first.previousElementSibling.querySelector('.toggle-icon');
        if (firstIcon) firstIcon.style.transform = 'rotate(180deg)';
    }

    // Toggle icon rotation on collapse/expand
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(header => {
        const icon = header.querySelector('.toggle-icon');
        const targetId = header.getAttribute('data-bs-target');
        const collapseEl = document.querySelector(targetId);

        header.addEventListener('click', () => {
            setTimeout(() => {
                const isShown = collapseEl.classList.contains('show');
                icon.style.transform = isShown ? 'rotate(180deg)' : 'rotate(0deg)';
            }, 300); // Matches Bootstrap collapse transition
        });
    });
</script>

<script>
    const dateInput = document.getElementById('filter-date');
    const prevBtn = document.getElementById('prev-date');
    const nextBtn = document.getElementById('next-date');

    dateInput.addEventListener('change', function () {
        this.form.submit();
    });

    function parseLocalDate(dateStr) {
        if (!dateStr) return new Date();
        const [year, month, day] = dateStr.split('-').map(Number);
        return new Date(year, month - 1, day);
    }

    function changeDate(days) {
        const currentDate = parseLocalDate(dateInput.value);
        currentDate.setDate(currentDate.getDate() + days);

        const yyyy = currentDate.getFullYear();
        const mm = String(currentDate.getMonth() + 1).padStart(2, '0');
        const dd = String(currentDate.getDate()).padStart(2, '0');

        dateInput.value = `${yyyy}-${mm}-${dd}`;
        dateInput.form.submit();
    }

    prevBtn.addEventListener('click', () => changeDate(-1));
    nextBtn.addEventListener('click', () => changeDate(1));
</script>


@endsection

</x-admin>
