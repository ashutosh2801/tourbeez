<x-admin>
@section('title','Order Logs')

<style>
.progress-bar-mini {
    height: 12px;
    border-radius: 5px;
    font-size: 10px;
    text-align: center;
    color: #fff;
}

.log-box {
    padding: 10px;
    border-left: 4px solid #007bff;
    background: #f8f9fa;
    border-radius: 6px;
}
</style>

<div class="card-primary mb-3">
    <div class="card-header reports-head">
        <h3 class="card-title">Order Logs</h3>
    </div>
</div>

<!-- FILTER -->
<div class="card card-primary bg-white border rounded-lg-custom report-filter-box">
    <form method="GET">
        <div class="row">

            <div class="col-md-3 col-12">
                <input type="text" name="order_id" placeholder="Order ID"
                    value="{{ request('order_id') }}" class="form-control">
            </div>

            <div class="col-md-3 col-12">
                <input type="text" name="stage" placeholder="Stage"
                    value="{{ request('stage') }}" class="form-control">
            </div>

            <div class="col-md-3 col-12">
                <select name="status" class="form-control">
                    <option value="">Status</option>
                    <option value="success" {{ request('status')=='success'?'selected':'' }}>Success</option>
                    <option value="failed" {{ request('status')=='failed'?'selected':'' }}>Failed</option>
                    <option value="error" {{ request('status')=='error'?'selected':'' }}>Error</option>
                </select>
            </div>

            <div class="col-md-3 col-12">
                <input type="text" name="search" placeholder="Search"
                    value="{{ request('search') }}" class="form-control">
            </div>

            <div class="col-md-2">
                <input type="date" name="start_date"
                    value="{{ request('start_date') }}" class="form-control">
            </div>

            <div class="col-md-2">
                <input type="date" name="end_date"
                    value="{{ request('end_date') }}" class="form-control">
            </div>

            <div class="col-md-2 mt-2">
                <button class="btn btn-primary w-100">Apply</button>
            </div>

        </div>
    </form>
</div>
<!-- EXPECTED FLOW -->
<div class="card mb-3">
    <div class="card-body">

        <h5 class="mb-2"><b>Order Flow</b></h5>

        @php
            $flow = [
                'cart' => 'Cart',
                'customer' => 'Customer',
                'promo' => 'Promo',
                'payment' => 'Payment',
                'email' => 'Email'
            ];
        @endphp

        <div style="display:flex; flex-wrap:wrap; align-items:center; gap:10px;">

            @foreach($flow as $key => $label)

                <div style="
                    padding:6px 12px;
                    background:#007bff;
                    color:white;
                    border-radius:20px;
                    font-size:12px;
                ">
                    {{ $label }}
                </div>

                @if(!$loop->last)
                    <span style="font-size:14px;">→</span>
                @endif

            @endforeach

        </div>

        <small class="text-muted d-block mt-2">
            Flow: Cart → Customer → Promo → Payment → Email
        </small>

    </div>
</div>

<!-- TABLE -->
<div class="card">
<div class="card-body">

<table class="table table-bordered table-striped">

<thead>
<tr>
    <th>Order</th>
    <th>Progress</th>
    <th>Last Stage</th>
    <th>Status</th>
    <th>Error</th>
    <th>Time</th>
</tr>
</thead>

<tbody>

@foreach($logs as $row)

@php
    $steps = collect($row->steps ?? []);

    $flow = ['cart','customer','promo','payment','email'];

    $completedStages = $steps->pluck('stage')->unique();

    $progress = round(($completedStages->count() / count($flow)) * 100);
    $progress = min($progress, 100);

    $log = $steps->sortBy('id')->last();
    $error = $row->lastError;

    $createdAt = $log?->created_at;
@endphp

<tr data-toggle="collapse" data-target="#log-{{ $row->order_id }}" style="cursor:pointer;">

    <td><b>#{{ $row->order_id }}</b></td>

    <td>
        <div style="background:#eee;">
            <div class="progress-bar-mini"
                style="width:{{ $progress }}%;
                background:{{ $progress == 100 ? 'green' : '#007bff' }}">
                {{ $progress }}%
            </div>
        </div>
    </td>

    <td>
        {{ strtoupper($log->stage ?? '-') }} <br>
        <small>{{ $log->step ?? '-' }}</small>
    </td>

    <td>
        <span class="badge
            {{ ($log->status ?? '') == 'success' ? 'badge-success' : '' }}
            {{ ($log->status ?? '') == 'failed' ? 'badge-warning' : '' }}
            {{ ($log->status ?? '') == 'error' ? 'badge-danger' : '' }}
        ">
            {{ strtoupper($log->status ?? '-') }}
        </span>
    </td>

    <td>
        @if($error)
            <span class="text-danger">
                {{ \Illuminate\Support\Str::limit($error->message ?? '', 50) }}
            </span>
        @else
            -
        @endif
    </td>

    <td>
        @if($createdAt)
            {{ \Carbon\Carbon::parse($createdAt)->diffForHumans() }}
            <br>
            <small>{{ \Carbon\Carbon::parse($createdAt)->format('d M Y, h:i A') }}</small>
        @else
            <span class="text-muted">No time</span>
        @endif
    </td>

</tr>

<!-- EXPAND -->
<tr class="collapse" id="log-{{ $row->order_id }}">
<td colspan="6">

    @foreach($steps->sortByDesc('id') as $l)

    <div class="log-box mb-2">

        <div class="d-flex justify-content-between">
            <b>{{ strtoupper($l->stage ?? '-') }} → {{ $l->step ?? '-' }}</b>

            <span class="badge
                {{ $l->status == 'success' ? 'badge-success' : '' }}
                {{ $l->status == 'failed' ? 'badge-warning' : '' }}
                {{ $l->status == 'error' ? 'badge-danger' : '' }}
            ">
                {{ strtoupper($l->status ?? '-') }}
            </span>
        </div>

        <div style="font-size:12px;">
            {{ $l->message ?? '-' }}
        </div>

        <div style="font-size:11px; color:#777;">
            {{ $l->created_at ? \Carbon\Carbon::parse($l->created_at)->format('d M Y, h:i A') : '-' }}
        </div>

    </div>

    @endforeach

</td>
</tr>

@endforeach

</tbody>
</table>

<div class="mt-3">
    {{ $logs->links() }}
</div>

</div>
</div>

</x-admin>