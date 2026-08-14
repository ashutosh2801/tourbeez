<x-admin>
@section('title','Activity Timeline')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<style>
.timeline-card {
    border-left: 4px solid #007bff;
    background: #f9f9f9;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 12px;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    font-weight: 600;
}

.timeline-meta {
    font-size: 12px;
    color: #777;
}

.timeline-changes {
    margin-top: 8px;
}

.timeline-changes li {
    font-size: 13px;
}
.old { color:red; }
.new { color:green; font-weight:600; }

.activity-text {
    font-size: 14px;
    line-height: 1.6;
}

.activity-user {
    font-weight: 600;
    color: #2c3e50;
}

.activity-action {
    font-weight: 500;
}

.action-created {
    color: #28a745; /* green */
}

.action-updated {
    color: #007bff; /* blue */
}

.action-deleted {
    color: #dc3545; /* red */
}

.activity-order {
    color: #6c757d;
    font-weight: 500;
}

.change-old {
    color: #dc3545;
}

.change-new {
    color: #28a745;
    font-weight: 600;
}
.user {
    font-weight: 600;
    color: #2c3e50;
}

.color-green { color: #28a745; }
.color-blue { color: #007bff; }
.color-red { color: #dc3545; }
.color-default { color: #6c757d; }

.order {
    color: #6c757d;
    font-weight: 500;
}

.old {
    color: #dc3545;
}

.new {
    color: #28a745;
    font-weight: 600;
}
.activity-table {
    table-layout: fixed; /* 🔥 prevents layout breaking */
    width: 100%;
}

.activity-table td,
.activity-table th {
    vertical-align: top;
}

.activity-description {
    word-break: break-word;     /* 🔥 breaks long words */
    overflow-wrap: anywhere;    /* 🔥 modern fix */
    white-space: normal;        /* 🔥 allow wrapping */
    line-height: 1.5;
    max-width: 600px;           /* optional control */
}

.text-nowrap {
    white-space: nowrap;
}
</style>



{{-- ✅ FILTER SAME AS BEFORE --}}
<div class="card card-primary bg-white border rounded-lg-custom report-filter-box">
    <form method="GET">

        <div class="row">

            {{-- ACTIVITY DATE --}}
            <div class="col-xl-3 col-md-3 col-12 position-relative">
                <label class="filter-label">Activity Date</label>

                <input 
                    type="text" 
                    name="activity_date"
                    
                    class="form-control aiz-date-range"
                    data-advanced-range="true"
                    data-separator=" - "
                    data-show-dropdown="true"
                    placeholder="Select date range"
                    autocomplete="off"
                    value="{{ request('activity_date') }}"
                >

                @if(request('activity_date'))
                    <span class="clear-btn" onclick="clearBooking()">✕</span>
                @endif
            </div>
            <!-- <div class="col-xl-2 col-md-2 col-12 position-relative">
                <label class="filter-label">Activity Date</label>

                <input type="text" id="activity_range" class="form-control"
                    placeholder="Select date range" autocomplete="off">

                @if(request('start_date'))
                    <span class="clear-btn" onclick="clearActivity()">✕</span>
                @endif

                <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
            </div> -->

            {{-- ORDER NUMBER --}}
            <div class="col-xl-2 col-md-2 col-12">
                <label class="filter-label">Order Number</label>
                <input type="text" name="order_number"
                    value="{{ request('order_number') }}"
                    class="form-control"
                    placeholder="Order Numbers">
            </div>

            {{-- MODEL --}}

            <div class="col-xl-2 col-md-2 col-12">
                <label class="filter-label">Model</label>
                <select name="model" class="form-control">
                    <option value="">All</option>

                    @foreach(activity_models_list() as $class => $label)
                        <option value="{{ $class }}"
                            {{ request('model') == $class ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ACTION --}}
            <div class="col-xl-2 col-md-2 col-12">
                <label class="filter-label">Action</label>
                <select name="action" class="form-control">
                    <option value="">All</option>
                    <option value="created" {{ request('action')=='created'?'selected':'' }}>Created</option>
                    <option value="updated" {{ request('action')=='updated'?'selected':'' }}>Updated</option>
                    <option value="deleted" {{ request('action')=='deleted'?'selected':'' }}>Deleted</option>
                </select>
            </div>

            {{-- USER --}}
            <div class="col-xl-2 col-md-2 col-12">
                <label class="filter-label">User</label>
                <input type="text" name="user_id"
                    value="{{ request('user_id') }}"
                    class="form-control"
                    placeholder="User">
            </div>

            {{-- SEARCH --}}
            <div class="col-xl-2 col-md-2 col-12">
                <label class="filter-label">Search</label>
                <input type="text" name="search"
                    value="{{ request('search') }}"
                    class="form-control"
                    placeholder="Search action...">
            </div>

            {{-- PROPERTY --}}
            <div class="col-xl-2 col-md-2 col-12">
                <label class="filter-label">Properties</label>
                <input type="text" name="property"
                    value="{{ request('property') }}"
                    class="form-control"
                    placeholder="Search JSON...">
            </div>

            {{-- MODEL ID --}}
            <div class="col-xl-2 col-md-2 col-12">
                <label class="filter-label">Model ID</label>
                <input type="text" name="model_id"
                    value="{{ request('model_id') }}"
                    class="form-control"
                    placeholder="ID">
            </div>

            {{-- BUTTONS --}}
            <div class="col-xl-2 col-md-2 col-12">
                <label class="filter-label">&nbsp;</label>
                <div class="d-flex column-gap-10">
                    <button class="btn btn-apply flex-fill mt-0" style="
    height: fit-content;
">Apply</button>
                    <a href="{{ url()->current() }}" class="btn btn-secondary flex-fill mt-0" style="
    height: fit-content;
">Reset</a>
                </div>
            </div>

        </div>
    </form>
</div>

{{-- ✅ TIMELINE --}}
<div class="card mt-3">
<div class="card-body">

<table class="table table-bordered">
    <thead>
        <tr>
            <th width="120">Date</th>
            <th width="120">Who/what</th>
            
            <th>Description</th>
        </tr>
    </thead>

    <tbody>

    @forelse($logs as $log)

    @php
        $user = optional($log->causer)->first_name 
            ?? optional($log->causer)->name 
            ?? 'User #'.$log->causer_id;

        $model = class_basename($log->subject_type);
    @endphp

    <tr>
        {{-- DATE --}}
        <td class="text-nowrap">
            {{ $log->created_at->format('D j M Y, h:i A') }}
        </td>

        {{-- USER --}}
        <td class="text-nowrap">
            <strong>{{ $user }}</strong><br>
            <small class="text-muted">{{ $model }}</small>
        </td>

        

        {{-- DESCRIPTION --}}
        <td class="activity-description">
            {!! activity_sentence_full($log) !!}
        </td>
    </tr>

    @empty

    <tr>
        <td colspan="4">No activity logs found</td>
    </tr>

    @endforelse

    </tbody>
</table>

{{ $logs->links() }}
</div>
</div>

{{-- DATE SCRIPT --}}
@section('js')
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
$('#activity_range').daterangepicker({
    autoUpdateInput: false,
    locale: { format: 'DD MMM YYYY' }
});

$('#activity_range').on('apply.daterangepicker', function(ev, picker) {
    $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
    $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));

    $(this).val(
        picker.startDate.format('DD MMM YYYY') + ' - ' +
        picker.endDate.format('DD MMM YYYY')
    );
});
</script>
@endsection

</x-admin>