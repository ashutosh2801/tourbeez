<x-admin>
@section('title', 'Manifest')

<div class="card">
    <form method="GET" action="{{ route('admin.orders.manifest') }}">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Session Manifest</h3>
            <div class="d-flex gap-2">
                <input type="date" name="date" class="form-control form-control-sm"
                       value="{{ request('date', \Carbon\Carbon::today()->toDateString()) }}" />
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </div>
        </div>
    </form>

    <div class="card-body">
        @forelse($sessions as $index => $session)
            <div class="card mb-2 border">
                <div class="card-header d-flex justify-content-between align-items-center bg-light"
                     style="cursor: pointer;"
                     data-bs-toggle="collapse" data-bs-target="#session-{{ $index }}" aria-expanded="false">
                    <strong>{{ $session['slot_time'] }}</strong>
                    <span>
                        {{ $session['orders']->count() }} Order{{ $session['orders']->count() > 1 ? 's' : '' }} |
                        {{ $session['orders']->sum('number_of_guests') }} Participants
                    </span>
                </div>

                <div id="session-{{ $index }}" class="collapse">
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
                                        <td>
                                            <a href="{{ route('admin.orders.edit', encrypt($order->id)) }}" class="alink">
                                                {{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.customers.show', encrypt($order->customer?->id)) }}"
                                               class="alink" target="_blank">
                                                {{ $order->customer?->name }}
                                            </a>
                                        </td>
                                        <td>{{ $order->customer?->phone }}</td>
                                        <td>{{ $order->guest_summary }}</td>
                                        <td>{{ $order->extras_summary }}</td>
                                        <td>${{ number_format($order->balance_amount, 2) }}</td>
                                        <td>${{ number_format($order->total_amount, 2) }}</td>
                                        <td>${{ number_format($order->paid_amount, 2) }}</td>
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

@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const first = document.querySelector('.collapse');
    if (first) first.classList.add('show');
</script>
@endsection
</x-admin>
