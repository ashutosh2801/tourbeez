<x-admin>
    @section('title','Activity Logs')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    
    <style>
        .activity-list-item {
            padding:0; margin:0; list-style:none;
        }
        .activity-list-item li {
            margin-bottom: 10px; padding:5px 0; margin:0; list-style:none; display:block; border-bottom: 1px solid #e7e7e7;
        }
        .activity-list-item li span {
            display: inline-block; width: 120px;
        }

        .activity-box {
    padding: 12px;
    border-left: 4px solid #007bff;
    background: #f9f9f9;
    border-radius: 6px;
    margin-bottom: 10px;
}

.activity-header {
    display: flex;
    justify-content: space-between;
}

.activity-meta {
    font-size: 12px;
    color: #777;
    margin-bottom: 5px;
}

.old {
    color: red;
    /*text-decoration: line-through;*/
}

.new {
    color: green;
    font-weight: bold;
}
    </style>

    <div class="card-primary mb-3">
        <div class="card-header reports-head">
            <h3 class="card-title">{{ translate('Activity Logs') }}</h3>
        </div>
    </div>
    <div class="card card-primary bg-white border rounded-lg-custom report-filter-box">
    <form method="GET">

        <div class="row">

            {{-- ACTIVITY DATE --}}
            <div class="col-xl-2 col-md-2 col-12 position-relative">
                <label class="filter-label">Activity Date</label>

                <input type="text" id="activity_range" class="form-control"
                    placeholder="Select date range" autocomplete="off">

                @if(request('start_date'))
                    <span class="clear-btn" onclick="clearActivity()">✕</span>
                @endif

                <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
            </div>

            {{-- ORDER NUMBER --}}
            <div class="col-xl-2 col-md-2 col-12">
                <label class="filter-label">Order Number</label>
                <input type="text" name="order_number"
                    value="{{ request('order_number') }}"
                    class="form-control"
                    placeholder="TUU8XXA">
            </div>

            {{-- MODEL --}}
            <div class="col-xl-2 col-md-2 col-12">
                <label class="filter-label">Model</label>
                <select name="model" class="form-control">
                    <option value="">All</option>
                    <option value="App\Models\Order" {{ request('model')=='App\Models\Order'?'selected':'' }}>Order</option>
                    <option value="App\Models\User" {{ request('model')=='App\Models\User'?'selected':'' }}>User</option>
                    <option value="App\Models\Payment" {{ request('model')=='App\Models\Payment'?'selected':'' }}>Payment</option>
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
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                
                <div class="card-body">
                    

       

        
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Properties</th>
                            <th >Action</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($logs as $log)

                    @php
                        $properties = $log->properties ? $log->properties->toArray() : [];
                        $attributes = $properties['attributes'] ?? [];
                        $old = $properties['old'] ?? [];

                        $userName = optional($log->causer)->first_name 
                            ?? optional($log->causer)->name 
                            ?? $log->causer_id;
                    @endphp

                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>
                            {{ class_basename($log->subject_type) ?? '-' }}
                            <br>
                            ID: {{ $log->subject_id }}
                        </td>

                        <td>
                            @php
    $properties = $log->properties ? $log->properties->toArray() : [];
    $attributes = $properties['attributes'] ?? [];
    $old = $properties['old'] ?? [];

    $subject = $log->subject;
@endphp

<div class="activity-box">

    <!-- 🔥 Header -->
    <div class="activity-header">
        <strong>👤 {{ $userName }} {{ optional($log->causer)->first_name ?? 'User #'.$log->causer_id }}</strong>

        <span class="badge badge-info">
            {{ ucfirst($log->description) }}
        </span>
    </div>

    <!-- 🔥 Meta -->
    <div class="activity-meta">
    🕒 {{ $log->created_at->diffForHumans() }} 
    ({{ $log->created_at->format('d M Y, h:i A') }})
</div>

    <!-- 🔥 Order Info -->
    <div class="activity-meta">
        @if($subject && isset($subject->order_number))
            📦 <strong>Order:</strong> {{ $subject->order_number }}
            <br>
            🆔 <strong>Order ID:</strong> {{ $subject->id }}
        @else
            🆔 <strong>ID:</strong> {{ $log->subject_id }}
        @endif
    </div>

    <!-- 🔥 Changes -->
    @if(!empty($attributes))
        <div class="activity-changes">
            <strong>Changes:</strong>
            <ul>
                @foreach($attributes as $key => $value)
                    @if(!is_array($value))

                        @php
                            $oldValue = $old[$key] ?? null;

                            $newFormatted = formatActivityValue($key, $value);
                            $oldFormatted = $oldValue !== null 
                                ? formatActivityValue($key, $oldValue) 
                                : null;
                        @endphp

                        <li>
                            <b>{{ formatActivityKey($key) }}</b> :

                            @if($oldFormatted !== null)
                                <span class="old">{{ $oldFormatted }}</span> →
                            @endif

                            <span class="new">{{ $newFormatted }}</span>
                        </li>

                    @endif
                @endforeach
            </ul>
        </div>
    @endif

</div>
                        </td>

                        

                    </tr>

                    @empty
                    <tr>
                        <td colspan="3">No activity logs found.</td>
                                    </tr>
                                    @endforelse
                </tbody>
                </table>
                </div>
                <div class="aiz-pagination">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('js')
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
    <script>
let start = "{{ request('start_date') }}";
let end   = "{{ request('end_date') }}";

$('#activity_range').daterangepicker({
    autoUpdateInput: false, // 🔥 important
    opens: 'left',
    locale: {
        format: 'DD MMM YYYY',
        cancelLabel: 'Clear'
    }
});

// 👉 If already selected (on reload)
if (start && end) {
    let startMoment = moment(start);
    let endMoment   = moment(end);

    $('#activity_range').data('daterangepicker').setStartDate(startMoment);
    $('#activity_range').data('daterangepicker').setEndDate(endMoment);

    $('#activity_range').val(
        startMoment.format('DD MMM YYYY') + ' - ' + endMoment.format('DD MMM YYYY')
    );
}

// 👉 On apply
$('#activity_range').on('apply.daterangepicker', function(ev, picker) {
    $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
    $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));

    $(this).val(
        picker.startDate.format('DD MMM YYYY') + ' - ' +
        picker.endDate.format('DD MMM YYYY')
    );
});

// 👉 On clear
$('#activity_range').on('cancel.daterangepicker', function(ev, picker) {
    $(this).val('');
    $('#start_date').val('');
    $('#end_date').val('');
});
</script>
</script>
@endsection
</x-admin>
