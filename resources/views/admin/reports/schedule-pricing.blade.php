,<x-admin>
@section('title', 'Schedule Pricing')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<style>
    /* TABLE FIX */
    .table {
        font-size: 12px; /* smaller text */
    }

    .table th,
    .table td {
        white-space: nowrap;     /* prevent line break */
        font-size: 12px;         /* consistent small text */
        padding: 6px 10px;       /* tighter spacing */
        vertical-align: middle;
    }

    /* Optional: header slightly bold but compact */
    .table th {
        font-weight: 600;
        font-size: 12px;
    }

    /* Smooth horizontal scroll */
    .table-wrapper {
        overflow-x: auto;
        width: 100%;
    }

    /* Optional: make numbers align nicely */
    .table td {
        text-align: left;
    }
    .pagination {
        justify-content: center;
    }
        /* FULL FIX FOR SELECT2 HEIGHT */
    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
        display: flex !important;
        align-items: center !important;
    }

    /* TEXT FIX */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: normal !important;
        padding-left: 10px !important;
        color: #495057 !important;
    }

    /* PLACEHOLDER COLOR */
    .select2-container--default .select2-selection__placeholder {
        color: #6c757d !important;
    }

    /* ARROW ALIGNMENT */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        right: 10px !important;
    }

    /* FIX WHEN SELECTED (THIS IS YOUR BUG) */
    .select2-container--default.select2-container--open .select2-selection--single,
    .select2-container--default.select2-container--focus .select2-selection--single {
        height: 38px !important;
    }

    /* FORCE CONSISTENT HEIGHT ALWAYS */
    .select2-container {
        width: 100% !important;
    }
</style>

<!-- <div class="card-primary mb-3">
    <div class="card-header reports-head">
        <h3 class="card-title">Schedule Pricing</h3>
    </div>
</div> -->


{{-- ================= FILTER ================= --}}
<div class="card card-primary bg-white border rounded-lg-custom report-filter-box">
         <form class="my-0" id="filterForm" method="GET" action="{{ route('admin.report.schedule-pricing-report') }}">
            <div class="card-header">
                <div class="search-options">
                    <div class="row">
                        <div class="col-md-2 col-6">
                            <input type="text" name="search" class="form-control" placeholder="Search tour" value="{{ request('search') }}" />
                        </div>                        
                        <!-- <div class="col-md-2 col-6">
                            <input placeholder="date range" class="form-control datarange-pickur" type="text" />
                        </div> -->
                        <!-- <div class="col-md-2 col-6">
                            <select name="city" id="city-select" class="form-control">
                                @if(request('city'))
                                    <option value="{{ request('city') }}" selected>{{ ucwords(optional(\App\Models\City::find(request('city')))->name) }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <select name="category" id="category-select" class="form-control">
                                @if(request('category'))
                                    <option value="{{ request('category') }}" selected>{{ ucwords(optional(\App\Models\Category::find(request('category')))->name) }}</option>
                                @endif
                            </select>
                        </div> -->
                       
                        <div class="col-md-2 col-6">
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                    <option value="0" {{ request('staus') === 0 ? 'selected' : '' }}>
                                        Pending
                                    </option>
                                    <option value="1" {{ request('staus') === 1 ? 'selected' : '' }}>
                                        Active
                                    </option>
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <select name="special_deposit" class="form-control">
                                <option value="">Special Deposit</option>
                                @foreach (['Active','Not_Active'] as $special_deposit)
                                    <option value="{{ strtolower($special_deposit) }}" {{ request('special_deposit') == strtolower($special_deposit) ? 'selected' : '' }}>
                                        {{ str_replace('_', ' ', $special_deposit) }} 
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <select name="schedule" class="form-control">
                                <option value="">Schedule</option>
                                @foreach (['Active','Not_Active'] as $schedule)
                                    <option value="{{ strtolower($schedule) }}" {{ request('schedule') == strtolower($schedule) ? 'selected' : '' }}>
                                        {{ str_replace('_', ' ', $schedule) }} 
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <!-- <div class="col-md-2 col-6">
                            <select name="trustpilot_review" class="form-control" onchange="this.form.submit()">
                                <option value="">TrustPilot Review</option>
                                    <option value="0" {{ request('trustpilot_review') === 0 ? 'selected' : '' }}>
                                        No
                                    </option>
                                    <option value="1" {{ request('trustpilot_review') === 1 ? 'selected' : '' }}>
                                        Yes
                                    </option>
                            </select>
                        </div> -->
                        <div class="col-md-2 col-6">
                            <select name="schedule_expiry" class="form-control">
                                <option value="">Schedule Expiry</option>
                                <option value="today" {{ request('schedule_expiry') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="last_7" {{ request('schedule_expiry') == 'last_7' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="last_15" {{ request('schedule_expiry') == 'last_15' ? 'selected' : '' }}>Last 15 Days</option>
                                <option value="this_week" {{ request('schedule_expiry') == 'this_week' ? 'selected' : '' }}>This Week</option>
                                <option value="upcoming_15" {{ request('schedule_expiry') == 'upcoming_15' ? 'selected' : '' }}>Upcoming 15 Days</option>
                                <option value="expired" {{ request('schedule_expiry') == 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2 col-6">
                            <select name="last_updated" class="form-control">
                                <option value="">Last updated</option>
                                <option value="today" {{ request('last_updated') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="last_7" {{ request('last_updated') == 'last_7' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="last_15" {{ request('last_updated') == 'last_15' ? 'selected' : '' }}>Last 15 Days</option>
                                <option value="this_week" {{ request('last_updated') == 'this_week' ? 'selected' : '' }}>This Week</option>
                                <option value="upcoming_15" {{ request('last_updated') == 'upcoming_15' ? 'selected' : '' }}>Upcoming 15 Days</option>
                                <option value="expired" {{ request('last_updated') == 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2 col-6">
                            <select name="has_sub_tour" class="form-control">
                                <option value="">Has Sub Tour</option>
                                @foreach (['Yes','No'] as $hasSubTour)
                                    <option value="{{ strtolower($hasSubTour) }}" {{ request('has_sub_tour') == strtolower($hasSubTour) ? 'selected' : '' }}>
                                        {{ str_replace('_', ' ', $hasSubTour) }} 
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <select name="per_page" class="form-control">
                                @foreach (['All',10, 25, 50, 100] as $number)
                                    <option value="{{ $number }}" {{ request('per_page', 10) == $number ? 'selected' : '' }}>
                                        {{ $number }} per page
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3 col-12">
                            <div class="d-flex column-gap-10 ">
                                <button type="submit" class="btn btn-apply flex-fill mt-0">Apply</button>

                                <a href="{{ route('admin.report.schedule-pricing-report') }}" class="btn btn-secondary flex-fill mt-0">Reset</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- @if(!request()->hasAny(['booking_date','tour_date','product','order_status','payment_status','partner','action_type']))
        <div class="alert alert-info">
            Please apply filters to view report data.
        </div>
    @endif -->
<div class="active-filters mb-3">
        @if(request()->hasAny([
            'booking_date','tour_date','product',
            'order_status','action_type','partner'
        ]))



            <div class="d-flex flex-wrap gap-2">

                {{-- BOOKING DATE --}}
                @if(request('booking_date'))
                    <span class="badge bg-dark ">
                        Booking: {{ request('booking_date') }}
                        <a href="{{ request()->fullUrlWithQuery(['booking_date' => null]) }}" class="remove-filter" data-filter="booking_date"><span class="ml-2">✕</span></a>
                    </span>
                @endif

                {{-- TOUR DATE --}}
                @if(request('tour_date'))
                    <span class="badge bg-dark  ml-2">
                        Tour: {{ request('tour_date') }}
                        <a href="{{ request()->fullUrlWithQuery(['tour_date' => null]) }}" class="remove-filter" data-filter="tour_date"><span class="ml-2">✕</span></a>
                    </span>
                @endif

                {{-- PRODUCT --}}
                @if(request('product'))
                    <span class="badge bg-dark  ml-2">
                        Product: {{ request('product_text') ?? request('product') }}
                        <a href="{{ request()->fullUrlWithQuery(['product' => null, 'product_text' => null]) }}" class="remove-filter" data-filter="product"><span class="ml-2">✕</span></a>
                    </span>
                @endif

                {{-- STATUS --}}
                @if(request('order_status'))
                    <span class="badge bg-dark  ml-2">
                        Status: {{ $status_with_code[request('order_status')] ?? request('order_status') }}
                        <a href="{{ request()->fullUrlWithQuery(['order_status' => null]) }}" class="remove-filter" data-filter="order_status"><span class="ml-2">✕</span></a>
                    </span>
                @endif

                {{-- PAY TYPE --}}
                @if(request('action_type'))
                    <span class="badge bg-dark ml-2">
                        Pay: {{ request('action_type') }}
                        <a href="{{ request()->fullUrlWithQuery(['action_type' => null]) }}" class="remove-filter" data-filter="action_type"><span class="ml-2">✕</span></a>
                    </span>
                @endif
                 @if(request('partner'))
                    <span class="badge bg-dark ml-2">
                        Pay: {{ request('partner') }}
                        <a href="{{ request()->fullUrlWithQuery(['partner' => null]) }}" class="remove-filter" data-filter="action_type"><span class="ml-2">✕</span></a>
                    </span>
                @endif

            </div>
        @endif
    </div>
{{-- ================= TABLE ================= --}}
<div class="card card-primary bg-white border rounded-lg-custom report-table">

     <div class="card-header report-table-head"> 
             <div class="row">
                <div class="col-md-8 col-12">
                    <h3 class="card-title">Schedule Pricing</h3>
                </div>
                <div class="col-md-4 col-12">
                    <div class="card-tools">
                        <a href="{{ route('admin.report.schedule.export', request()->all()) }}"
                           class="btn btn-success btn-sm">
                            Download Excel
                        </a>
                    </div>
                </div>
            </div> 
         </div> 

    <div class="table-wrapper">
    <table class="table table-bordered">
        <thead>

            {{-- HEADER LEVEL 1 --}}
            <tr>
                <th rowspan="2">Product</th>

                <th colspan="3" class="text-center ">
                    REVENUE (CAD $)
                </th>

                <th colspan="3" class="text-center">
                    COST (CAD $)
                </th>

                <th rowspan="2" class="text-center ">
                    PROFIT
                </th>
            </tr>

            {{-- HEADER LEVEL 2 --}}
            <tr>
                <th>Price</th>
                <th>Tax</th>
                <th>Total</th>

                <th>Price</th>
                <th>Tax</th>
                <th>Total</th>
            </tr>

        </thead>

        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>
                        <strong>{{ $row['tour_name'] }}</strong><br>
                        {{ $row['label'] }}
                    </td>
                    {{-- COST --}}
                    <td>{{ number_format_with_currency($row['cost_price'], 2) }}</td>
                    <td>{{ number_format_with_currency($row['cost_tax'], 2) }}</td>
                    <td>{{ number_format_with_currency($row['cost_total'], 2) }}</td>

                    {{-- REVENUE --}}
                    <td>{{ number_format_with_currency($row['revenue_price'], 2) }}</td>
                    <td>{{ number_format_with_currency($row['revenue_tax'], 2) }}</td>
                    <td>{{ number_format_with_currency($row['revenue_total'], 2) }}</td>

                    

                    {{-- PROFIT --}}
                    <td class="text-success">
                        <strong>{{ number_format_with_currency($row['profit'], 2) }}</strong>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No Data Found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if(method_exists($rows, 'links'))
        <div class="mt-3">
            {{ $rows->links() }}
        </div>
    @endif
</div>
</div>




@section('js')
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const today = moment();
</script>
    <script>

        
    $(document).ready(function () {
        // ✅ Select2 (optimized)
        $('#productFilter').select2({
            placeholder: 'Select Tour',
            minimumInputLength: 3,
            ajax: {
                url: '{{ route("admin.tours.tours-list") }}',
                dataType: 'json',
                delay: 0,
                cache: true,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return {
                        results: data.map(tour => ({
                            id: tour.id,
                            text: `${tour.title} (${tour.unique_code ?? 'N/A'})`
                        }))
                    };
                }
            }
        });

    });
    
        
        $('#productFilter').on('select2:select', function (e) {
    let data = e.params.data;

    $('#product_text').val(data.text);
});

        function clearBooking() {
    $('#booking_range').val('');

    // remove from URL (important UX)
    let url = new URL(window.location.href);
    url.searchParams.delete('booking_date');
    window.location.href = url.toString();
}

function clearTour() {
    $('input[name="tour_date"]').val('');

    let url = new URL(window.location.href);
    url.searchParams.delete('tour_date');
    window.location.href = url.toString();
}

        if ($('#productFilter').val() && !$('#product_text').val()) {
    let selectedText = $('#productFilter option:selected').text();
    $('#product_text').val(selectedText);
}
</script>

@endsection

</x-admin>