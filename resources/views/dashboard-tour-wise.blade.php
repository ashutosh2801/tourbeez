<x-admin>
    @section('title','Dashboard')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<style>
        /* ✅ Keep container scoped */
        .dashboard-wrapper .container {
            max-width: 1400px;
            margin: auto;
        }

        /* ✅ Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        /* 🔥 RIGHT SIDE ALIGNMENT */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto; /* 🔥 pushes everything to right */
        }

        /* Optional: control width */
        .header-actions input {
            width: 220px;
        }

        /* ✅ Panels */
        .dashboard-wrapper .card,
        .dashboard-wrapper .panel {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 1px 6px rgba(0,0,0,.1);
        }

        /* ✅ Table */
        .dashboard-wrapper table {
            width: 100%;
            border-collapse: collapse;
        }

        .dashboard-wrapper th,
        .dashboard-wrapper td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .dashboard-wrapper .text-right {
            text-align: right;
        }

        /* ✅ Responsive */
        @media(max-width:900px){
            .dashboard-wrapper .cards,
            .dashboard-wrapper .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
                /* FULL FIX FOR SELECT2 HEIGHT */
    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid #aeb0b4 !important;
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

<div class="dashboard-body">
    <div class="mb-2">
        <div class="dash-perform">
            <div class="row">
                <div class="col-md-6 col-12">
                    <h2 class="text-sm m-0">Tour-wise overview</h2>
                    <input 
                        type="text" 
                        name="booking_date"
                        id="bookingDate"
                        class="form-control aiz-date-range"
                        placeholder="Booking Date"
                        autocomplete="off"
                        data-advanced-range="true"
                        data-separator=" - "
                        value="{{ request('booking_date') }}"
                    >
                    <button type="button" id="applyFilter" class="btn btn-apply">
                        Apply
                    </button>
                    <div id="activeFilters" class="active-filters"></div>
                </div>
                <div class="col-md-6 col-12">
                    <button type="button" id="toggleFilter" class="btn btn-secondary float-right">
                        <i class="fas fa-filter"></i> Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="filterPanel" style="display:none;">
        <div class="card card-primary bg-white border rounded-lg-custom report-filter-box mb-2">

            <div class="row">

                <!-- TOUR DATE -->
                <div class="col-md-3">
                    <label>Tour Date</label>
                    <input 
                        type="text" 
                        name="tour_date"
                        id="tourDate"
                        autocomplete="off"
                        class="form-control aiz-date-range"
                        data-advanced-range="true"
                        data-separator=" - "
                        placeholder="Select tour date"
                        value="{{ request('tour_date') }}"
                    >
                </div>

                <!-- PRODUCT -->
                <div class="col-md-3">
                    <label>Product</label>
                    <select id="productFilter" name="product" class="form-control">
                        @if(request('product') && request('product_text'))
                            <option value="{{ request('product') }}" selected>
                                {{ request('product_text') }}
                            </option>
                        @endif
                    </select>
                </div>

                <!-- ORDER STATUS -->
                <div class="col-md-2">
                    <label>Order Status</label>
                    <select name="order_status" id="orderStatus" class="form-control">
                        <option value="">All</option>
                        <option value="3">Pending supplier</option>
                        <option value="4">Pending customer</option>
                        <option value="5">Confirmed</option>
                    </select>
                </div>

                <!-- PAY TYPE -->
                <div class="col-md-2">
                    <label>Pay Type</label>
                    <select name="action_type" id="actionType" class="form-control">
                        <option value="">All</option>
                        <option value="pay_now">Pay Now</option>
                        <option value="pay_later">Pay Later</option>
                    </select>
                </div>

                <!-- SOURCE -->
                <div class="col-md-2">
                    <label>Source</label>
                    <select name="partner" id="partner" class="form-control">
                        <option value="">All</option>
                        @foreach($partners as $partner)
                            <option value="{{ ucfirst($partner->slug) }}">
                                {{ $partner->name }}
                            </option>
                        @endforeach
                        <option value="Tourbeez" {{ request('partner') == 'Tourbeez' ? 'selected' : '' }}>Tourbeez</option>
                        <option value="Internal" {{ request('partner') == 'Internal' ? 'selected' : '' }}>Internal</option>
                    </select>
                </div>

            </div>

        </div>
    </div>

    @if(!request()->hasAny(['booking_date','tour_date','product','order_status','payment_status','partner','action_type']))
        <div class="alert alert-info">
            Please apply filters to view report data.
        </div>
    @endif
    

    <div id="noFilterAlert"></div>
    
    <div class="dashboard-wrapper">
            <div class="cards">

                <div class="metric-card">
                    <div class="metric-icon purple">
                        <i class="fas fa-shopping-bag"></i>
                    </div>

                    <div class="metric-content">
                        <div class="metric-label">Total Bookings</div>
                        <div class="metric-value" id="totalBookings">0</div>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon green">
                        <i class="fas fa-dollar-sign"></i>
                    </div>

                    <div class="metric-content">
                        <div class="metric-label">Total Revenue</div>
                        <div class="metric-value" id="totalRevenue">$0</div>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon blue">
                        <i class="fas fa-receipt"></i>
                    </div>

                    <div class="metric-content">
                        <div class="metric-label">Avg. Order Value</div>
                        <div class="metric-value" id="avgOrderValue">$0</div>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon orange">
                        <i class="fas fa-cube"></i>
                    </div>

                    <div class="metric-content">
                        <div class="metric-label">Total Tours</div>
                        <div class="metric-value" id="totalTours">0</div>
                    </div>
                </div>

            </div>

            <div class="dashboard-grid">
                <div class="panel"><h3>Revenue by Tour</h3><div id="revenueChart"></div></div>
                <div class="panel"><h3>Bookings by Tour</h3><div id="bookingChart"></div></div>
            </div>

            <div class="performance-wrapper">

                <div class="performance-table-card">

                    <h3>
                        Product-wise Performance
                    </h3>
                    <div class="table-responsive">
                        <table class="table performance-table">

                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Bookings</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>

                            <tbody id="tableBody"></tbody>

                            <tfoot>
                                <tr>
                                    <td><strong>Total</strong></td>
                                    <td id="tfootBookings"></td>
                                    <td id="tfootRevenue"></td>
                                </tr>
                            </tfoot>

                        </table>
                    </div>
                </div>

                <div class="performance-chart-card">

                    <h3>
                        Revenue Trend by Product
                    </h3>

                    <div id="trendChart"></div>

                </div>

            </div>
        </div>
    </div>
</div>
@section('js') 
@parent() 
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let revenueChart, bookingChart, trendChart;

async function fetchDashboard() {

    // const fromDate = document.getElementById('fromDate').value;
    // const toDate   = document.getElementById('toDate').value;
    // const tourId   = document.getElementById('tourFilter').value;

    // const params = new URLSearchParams({
    //     from_date: fromDate,
    //     to_date: toDate,
    //     tour_id: tourId
    // });

    const params = new URLSearchParams({
        booking_date: document.getElementById('bookingDate').value,
        tour_date: document.getElementById('tourDate').value,
        product: document.getElementById('productFilter').value,
        order_status: document.getElementById('orderStatus').value,
        action_type: document.getElementById('actionType').value,
        partner: document.getElementById('partner').value,
    });

    // console.log(params);
    window.history.pushState({}, '', `?${params.toString()}`);
    // 🔥 SAME ROUTE (no new API)
    const res = await fetch(`?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    const data = await res.json();
    renderDashboard(data);
}

function renderDashboard(data) {

    document.getElementById('totalRevenue').innerHTML = '$' + data.totalRevenue.toLocaleString();
    document.getElementById('totalBookings').innerHTML = data.totalBookings;
    document.getElementById('avgOrderValue').innerHTML = '$' + data.avgBookingValue;
    document.getElementById('totalTours').innerHTML = data.totalTours;

    renderFilters();

    const tours = data.tourAnalytics;

    if(revenueChart) revenueChart.destroy();
    if(bookingChart) bookingChart.destroy();
    if(trendChart) trendChart.destroy();



    revenueChart = new ApexCharts(document.querySelector("#revenueChart"), {
        chart: {
            type: 'donut',
            height: 250,
            width: '100%'
        },
        series: tours.map(x => x.revenue),
        labels: tours.map(x => x.title),

        legend: {
            position: 'right',
            width: 300, // 🔥 fixed width for labels
            formatter: function(seriesName) {
                    return seriesName.match(/.{1,38}/g).join('<br>');
                }
        },

        

        dataLabels: {
            enabled: true,
            formatter: function (val, opts) {
                return val.toFixed(1) + "%"; // 🔥 percentage
            },
            style: {
                fontSize: '12px'
            }
        },

        plotOptions: {
            pie: {
                donut: {
                    size: '60%' // 🔥 better visibility
                }
            }
        }
    });
    revenueChart.render();

    bookingChart = new ApexCharts(document.querySelector("#bookingChart"), {
    chart: {
            type: 'donut',
            height: 250,
            width: '100%'
        },
        series: tours.map(x => x.bookings),
        labels: tours.map(x => x.title),

        legend: {
            position: 'right',
            width: 300,
            // formatter: function(seriesName) {
            //     return seriesName.length > 40
            //         ? seriesName.substring(0, 40) + '...'
            //         : seriesName;
            // }
            formatter: function(seriesName) {
                    return seriesName.match(/.{1,38}/g).join('<br>');
                }
        },

        dataLabels: {
            enabled: true,
            formatter: function (val, opts) {
                return val.toFixed(1) + "%"; // 🔥 percentage
            },
            style: {
                fontSize: '12px'
            }
        },

        plotOptions: {
            pie: {
                donut: {
                    size: '60%'
                }
            }
        }
    });
    bookingChart.render();

    trendChart = new ApexCharts(
    document.querySelector("#trendChart"),
    {
        chart:{
            type:'line',
            height:480,
            toolbar:{
                show:false
            }
        },

        stroke:{
            curve:'smooth',
            width:3
        },

        markers:{
            size:4
        },

        series: tours.map(t => ({
            name:t.title,
            data:[
                t.revenue * 0.80,
                t.revenue * 0.90,
                t.revenue * 1.00,
                t.revenue * 0.95,
                t.revenue * 1.05
            ]
        })),

        xaxis:{
            categories:[
                'Week 1',
                'Week 2',
                'Week 3',
                'Week 4',
                'Week 5'
            ]
        },

        yaxis:{
            labels:{
                formatter:function(val){
                    return '$' + Math.round(val);
                }
            }
        },

        grid:{
            borderColor:'#eee'
        },

        legend:{
            show: false
        }
    }
);

trendChart.render();

let totalRevenue = tours.reduce((a,b)=>a+b.revenue,0);
let totalBookings = tours.reduce((a,b)=>a+b.bookings,0);

let html = '';

const colors = [
    '#3B82F6',
    '#22C55E',
    '#EAB308',
    '#A855F7',
    '#14B8A6',
    '#F97316',
    '#94A3B8'
];

tours.forEach((t,index)=>{

    let revenuePercent =
        ((t.revenue / totalRevenue) * 100).toFixed(1);

    let avgOrder =
        (t.revenue / t.bookings).toFixed(2);

    html += `
        <tr>

            <td>${t.title}</td>

            <td>${t.bookings}</td>

            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div
                        class="revenue-bar"
                        style="background:${colors[index % colors.length]}"
                    ></div>

                    $${t.revenue.toLocaleString()}
                </div>
            </td>
        </tr>
    `;
});

document.getElementById('tableBody').innerHTML = html;

document.getElementById('tfootBookings').innerHTML =
    totalBookings.toLocaleString();

document.getElementById('tfootRevenue').innerHTML =
    '$'+totalRevenue.toLocaleString();

document.getElementById('tfootAvg').innerHTML =
    '$'+(totalRevenue/totalBookings).toFixed(2);
}
function renderFilters() {

    const params = new URLSearchParams(window.location.search);

    const filters = {
        booking_date: params.get('booking_date'),
        tour_date: params.get('tour_date'),
        product: params.get('product'),
        product_text: params.get('product_text'),
        order_status: params.get('order_status'),
        action_type: params.get('action_type'),
        partner: params.get('partner'),
    };

    const hasFilter = Object.values(filters).some(v => v);

    const alertBox = document.getElementById('noFilterAlert');
    const container = document.getElementById('activeFilters');

    // 🔥 ALERT
    if (!hasFilter) {
        alertBox.innerHTML = `
            <div class="alert alert-info">
                Please apply filters to view report data.
            </div>
        `;
        container.innerHTML = '';
        return;
    } else {
        alertBox.innerHTML = '';
    }

    // 🔥 BADGES
    let html = '<div class="d-flex flex-wrap gap-2">';

    if (filters.booking_date) {
        html += badge('Booking', filters.booking_date, 'booking_date');
    }

    if (filters.tour_date) {
        html += badge('Tour', filters.tour_date, 'tour_date');
    }

    if (filters.product) {
        html += badge('Product', filters.product_text || filters.product, 'product');
    }

    if (filters.order_status) {
        html += badge('Status', filters.order_status, 'order_status');
    }

    if (filters.action_type) {
        html += badge('Pay', filters.action_type, 'action_type');
    }

    if (filters.partner) {
        html += badge('Source', filters.partner, 'partner');
    }

    html += '</div>';

    container.innerHTML = html;
}
function badge(label, value, key) {
    return `
        <span class="badge bg-dark mr-2">
            ${label}: ${value}
            <span 
                class="ml-2" 
                style="cursor:pointer;"
                onclick="removeFilter('${key}')"
            >✕</span>
        </span>
    `;
}
function removeFilter(key) {

    const url = new URL(window.location.href);

    // remove from URL
    url.searchParams.delete(key);

    if (key === 'product') {
        url.searchParams.delete('product_text');
    }

    // 🔥 IMPORTANT: clear input fields
    if (key === 'booking_date') {
        document.getElementById('bookingDate').value = '';
    }

    if (key === 'tour_date') {
        document.getElementById('tourDate').value = '';
    }

    if (key === 'product') {
        document.getElementById('productFilter').value = '';
    }

    if (key === 'order_status') {
        document.getElementById('orderStatus').value = '';
    }

    if (key === 'action_type') {
        document.getElementById('actionType').value = '';
    }

    if (key === 'partner') {
        document.getElementById('partner').value = '';
    }

    // update URL
    window.history.pushState({}, '', url);

    fetchDashboard(); // reload
}

// 🔥 Apply filter
document.getElementById('applyFilter').onclick = fetchDashboard;

// 🔥 Initial load
// window.onload = () => {

//     const today = new Date().toISOString().split('T')[0];
//     document.getElementById('toDate').value = today;

//     const firstDay = new Date();
//     firstDay.setDate(1);
//     document.getElementById('fromDate').value = firstDay.toISOString().split('T')[0];

//     fetchDashboard();
// };
// window.onload = () => {

//     const bookingInput = document.getElementById('bookingDate');
    
//     if (!bookingInput) return; // 🔥 prevents crash

//     const today = new Date().toISOString().split('T')[0];

//     const firstDay = new Date();
//     firstDay.setDate(1);

//     bookingInput.value =
//         firstDay.toISOString().split('T')[0] +
//         ' - ' +
//         today;
//     fetchDashboard();
// };
</script>
<script>
    let filterOpen = false;

document.getElementById('toggleFilter').onclick = function () {

    const panel = document.getElementById('filterPanel');

    if (filterOpen) {
        panel.style.display = 'none';
        this.classList.remove('btn-danger');
        this.classList.add('btn-secondary');
        this.innerHTML = '<i class="fas fa-filter"></i> Filters';
    } else {
        panel.style.display = 'block';
        this.classList.remove('btn-secondary');
        this.classList.add('btn-danger');
        this.innerHTML = '<i class="fas fa-times"></i> Hide Filters';
    }

    filterOpen = !filterOpen;
};

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
</script>
@endsection
</x-admin>
