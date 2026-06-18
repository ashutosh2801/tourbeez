<x-admin>
@section('title', 'Order Manifest')

{{-- Include Bootstrap Icons --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<div class="card-primary mb-3">
    <form method="GET" action="{{ route('admin.orders.manifest') }}">
        <div class="card-header order-manifest-head">
            <div class="d-flex justify-content-between align-items-center w-100 mb-manifest">
                <div class="d-flex column-gap-10">
                    <button type="button" class="btn btn-sm today-btn" id="today-date">
                        Today
                    </button>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center left-btn" id="prev-date">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <input type="date" name="date" id="filter-date" class="form-control form-control-sm filterDate" value="{{ request('date', \Carbon\Carbon::today()->toDateString()) }}" />
                        <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center right-btn" id="next-date">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <a href="{{ route('admin.orders.manifest.download', ['date' => request('date')]) }}"
                   class="btn btn-download btn-sm">
                   <i class="bi bi-download"></i> Download Excel
                </a>
            </div>
        </div>
    </form>
</div>

<div class="manifest-body">
    <div class="card-primary">
        <div class="card-body p-0">
            @forelse($sessions as $index => $session)
                <div class="manifest-card">
                    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#session-{{ $index }}" aria-expanded="false" aria-controls="session-{{ $index }}">
                        <i class="bi bi-chevron-right toggle-icon font-bold" id="icon-{{ $index }}"></i>
                        <strong>{{ $session['slot_time']   }} - {{ $session['tour_title']   }}</strong>
                        <span>|</span>
                        <div class="d-flex align-items-center gap-3">
                            <p>
                                {{ $session['orders']->count() }} Order{{ $session['orders']->count() > 1 ? 's' : '' }} |
                                {{ $session['orders']->sum('number_of_guests') }} Participants
                            </p>
                            <!-- <i class="bi bi-chevron-down toggle-icon" id="icon-{{ $index }}"></i> -->
                        </div>
                    </div>

                    <div id="session-{{ $index }}" class="collapse">
                        <div class="card-body">
                            <div class="table-viewport">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th>Phone</th>
                                            <th>Guests</th>
                                            <th>Extras</th>
                                            <th>Balance</th>
                                            <th>Total</th>
                                            <th>Paid</th>
                                            <th>Pickup</th>
                                            <th>Instructions</th>
                                            <th>Internal Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($session['orders'] as $order)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.orders.edit', encrypt($order->id)) }}" class="alink" target="_blank">
                                                        {{ $order->order_number }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.customers.show', encrypt($order->customer?->id)) }}"
                                                        class="alink" target="_blank">
                                                        {{ $order->customer?->name }}
                                                    </a>
                                                </td>


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
                                                            $amountClass = 'text-success'; // red
                                                        }
                                                    
                                                    } else {
                                                        $amountClass = 'text-success'; // green
                                                    }

                                                    if ($order->order_status == 6) {
                                                        $amountClass = 'text-secondary'; // grey
                                                    } 
                                                @endphp
                                                <td>{{ $order->customer?->phone }}</td>
                                                <td>{{ $order->guest_summary }}</td>
                                                <td>{{ $order->extras_summary }}</td>
                                                <td class="text-danger">{{ price_format_with_currency($total-$paid, $order->currency) }}</td>
                                                <td>{{ price_format_with_currency($total, $order->currency) }}</td>
                                                <td class="{{ $amountClass}}">{{ price_format_with_currency($paid, $order->currency) }}</td>
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
                </div>
            @empty
                <p class="m-0 p-3">No sessions found for this date.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Scripts --}}
@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

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

    // Submit on manual date change
    dateInput.addEventListener('change', function () {
        this.form.submit();
    });

    function changeDate(days) {
        const currentDate = new Date(dateInput.value || new Date());
        currentDate.setDate(currentDate.getDate() + days);
        const yyyy = currentDate.getFullYear();
        const mm = String(currentDate.getMonth() + 1).padStart(2, '0');
        const dd = String(currentDate.getDate()).padStart(2, '0');
        const newDate = `${yyyy}-${mm}-${dd}`;
        dateInput.value = newDate;
        dateInput.form.submit();
    }

    prevBtn.addEventListener('click', () => changeDate(-1));
    nextBtn.addEventListener('click', () => changeDate(1));
</script>

<script>
    const todayBtn = document.getElementById('today-date');
    todayBtn.addEventListener('click', () => {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');

        const formatted = `${yyyy}-${mm}-${dd}`;
        dateInput.value = formatted;
        dateInput.form.submit();
    });
</script>


@endsection

</x-admin>
