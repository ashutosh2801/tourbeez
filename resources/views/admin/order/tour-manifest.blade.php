<x-admin>
@section('title', 'Tour Manifest')

{{-- Include Bootstrap Icons --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<div class="card">
    <form method="GET" action="{{ route('admin.orders.tour.manifest') }}">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center w-100">
                <h3 class="card-title mb-0">Session Manifest</h3>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center" id="prev-date" style="height: 32px; width: 32px;">
                        <i class="bi bi-chevron-left"></i>
                    </button>

                    <input type="date" name="date" id="filter-date" class="form-control form-control-sm"
                           value="{{ request('date', \Carbon\Carbon::today()->toDateString()) }}"
                           style="height: 32px;" />

                    <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center" id="next-date" style="height: 32px; width: 32px;">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <a href="{{ route('admin.orders.tour.manifest.download', ['date' => request('date')]) }}"
                   class="btn btn-outline-success btn-sm">
                   <i class="bi bi-download"></i> Download PDF
                </a>
            </div>
        </div>
    </form>

    <div class="card-body">
        @forelse($sessions as $slotTime => $session)
    <div class="card mb-2 border">
        <div class="card-header d-flex justify-content-between align-items-center bg-light"
             data-bs-toggle="collapse"
             data-bs-target="#session-{{ \Illuminate\Support\Str::slug($slotTime) }}"
             aria-expanded="false"
             aria-controls="session-{{ \Illuminate\Support\Str::slug($slotTime) }}">
            <strong>{{ $slotTime }}</strong>

            <div class="d-flex align-items-center gap-3">
                <span>
                    {{ count($session['orders']) }} Order{{ count($session['orders']) > 1 ? 's' : '' }} |
                    {{ collect($session['orders'])->sum('number_of_guests') }} Participants
                </span>
            </div>
            <i class="bi bi-chevron-down toggle-icon font-bold"></i>
        </div>

        <div id="session-{{ \Illuminate\Support\Str::slug($slotTime) }}" class="collapse">
            <div class="card-body">
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($session['orders'] as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->customer?->name }}</td>
                                <td>{{ $order->customer?->phone }}</td>
                                <td>{{ $order->guest_summary }}</td>
                                <td>{{ $order->extras_summary }}</td>
                                <td>{{ price_format_with_currency($order->balance_amount, $order->currency) }}</td>
                                <td>{{ price_format_with_currency($order->total_amount, $order->currency) }}</td>
                                <td>{{ price_format_with_currency($order->paid_amount, $order->currency) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@empty
    <p>No sessions found for this date.</p>
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


@endsection

</x-admin>
