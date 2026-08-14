<x-admin>
    @section('title','Tour Wise Overview
')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<style>
    body.sidebar-open {
        overflow: hidden;
    }
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
        padding-left: 7px !important;
        color: #898b92 !important;
        font-size: 14px;
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
    .report-search-panel{padding:20px;border:1px solid #e5e7eb;border-radius:10px;background:linear-gradient(180deg,#f8fafc,#fff);margin-bottom:12px;}
    .report-search-head{display:flex;align-items:flex-start;justify-content:space-between;gap:15px;margin-bottom:16px;}
    .report-search-head h5{margin:0 0 3px;font-size:17px;font-weight:700;color:#172033}.report-search-head p{margin:0;font-size:13px;color:#6b7280}
    .report-filter-toggle{height:38px;padding:0 13px;border:1px solid #c7d2fe;border-radius:7px;background:#fff;color:#4338ca;font-size:13px;font-weight:600}
    .report-primary-search{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px;align-items:end}.report-primary-search label,.report-filter-grid label{display:block;margin:0 0 6px;font-size:12px;font-weight:600;color:#374151}
    .report-primary-search .form-control,.report-filter-grid .form-control{height:42px;border-color:#d7dce3;border-radius:7px}.report-apply{height:42px;min-width:120px;border:0;border-radius:7px;background:#4f46e5;color:#fff;font-size:13px;font-weight:600}
    .report-filter-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px;margin-top:16px;padding-top:16px;border-top:1px solid #e5e7eb}.report-filter-grid.is-hidden{display:none}
    @media(max-width:991px){.report-filter-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:575px){.report-search-head{display:block}.report-filter-toggle{margin-top:10px}.report-primary-search{grid-template-columns:1fr auto}.report-filter-grid{grid-template-columns:1fr}}
</style>

<div class="dashboard-body">
    <div class="report-search-panel">
        <div class="report-search-head">
            <div><h5><i class="fas fa-chart-pie mr-2 text-primary"></i>Tour-wise report</h5><p>Filter performance by booking date, tour and order details.</p></div>
            <button type="button" id="toggleFilter" class="report-filter-toggle"><i class="fas fa-filter mr-1"></i> Filters</button>
        </div>
        <div class="report-primary-search">
            <div>
                    <label for="bookingDate">Order date</label>
                    <input 
                        type="text" 
                        name="booking_date"
                        id="bookingDate"
                        class="form-control aiz-date-range"
                        placeholder="Order Date"
                        autocomplete="off"
                        data-advanced-range="true"
                        data-separator=" - "
                        value="{{ request('booking_date') }}"
                    >
            </div>
            <button type="button" id="applyFilter" class="report-apply"><i class="fas fa-search mr-1"></i> Apply</button>
        </div>
        <div id="filterPanel" class="report-filter-grid {{ request()->hasAny(['tour_date','product','order_status','action_type','partner']) ? '' : 'is-hidden' }}">

                <!-- TOUR DATE -->
                <div>
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
                <div>
                    <label>Product</label>
                    <select id="productFilter" name="product" class="form-control">
                        @if(request('product') && request('product_text'))
                            <option value="{{ request('product') }}" selected>
                                {{ request('product_text') }}
                            </option>
                        @endif
                    </select>
                    <input type="hidden" id="product_text" value="{{ request('product_text') }}">
                </div>

                <!-- ORDER STATUS -->
                <div>
                    <label>Order Status</label>
                    <select name="order_status" id="orderStatus" class="form-control">
                        <option value="">All</option>
                        <option value="3" {{ request('order_status') === '3' ? 'selected' : '' }}>Pending supplier</option>
                        <option value="4" {{ request('order_status') === '4' ? 'selected' : '' }}>Pending customer</option>
                        <option value="5" {{ request('order_status') === '5' ? 'selected' : '' }}>Confirmed</option>
                    </select>
                </div>

                <!-- PAY TYPE -->
                <div>
                    <label>Pay Type</label>
                    <select name="action_type" id="actionType" class="form-control">
                        <option value="">All</option>
                        <option value="pay_now" {{ request('action_type') === 'pay_now' ? 'selected' : '' }}>Pay Now</option>
                        <option value="pay_later" {{ request('action_type') === 'pay_later' ? 'selected' : '' }}>Pay Later</option>
                    </select>
                </div>

                <!-- SOURCE -->
                <div>
                    <label>Source</label>
                    <select name="partner" id="partner" class="form-control">
                        <option value="">All</option>
                        @foreach($partners as $partner)
                            <option value="{{ ucfirst($partner->slug) }}" {{ request('partner') === ucfirst($partner->slug) ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                        @endforeach
                        <option value="Tourbeez" {{ request('partner') == 'Tourbeez' ? 'selected' : '' }}>Tourbeez</option>
                        <option value="Internal" {{ request('partner') == 'Internal' ? 'selected' : '' }}>Internal</option>
                    </select>
                </div>

        </div>
        <div id="activeFilters" class="active-filters mt-2"></div>
    </div>

   <!--  @if(!request()->hasAny(['booking_date','tour_date','product','order_status','payment_status','partner','action_type']))
        <div class="alert alert-info">
            Please apply filters to view report data.weww
        </div>
    @endif -->
    
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
const statusMap = {
        3: 'Pending supplier',
        4: 'Pending customer',
        5: 'Confirmed'
    };

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
        product_text: document.getElementById('product_text').value,
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
closeFilterSidebar();

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
            <div class="alert alert-info no">
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
        html += badge('Status', statusMap[filters.order_status], 'order_status');
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
        document.getElementById('product_text').value = '';
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
document.getElementById('toggleFilter').onclick = function () {
    const panel = document.getElementById('filterPanel');
    panel.classList.toggle('is-hidden');
    this.setAttribute('aria-expanded', panel.classList.contains('is-hidden') ? 'false' : 'true');
};

</script>
    <script>

        
    $(document).ready(function () {
        // ✅ Select2 (optimized)
        $('#productFilter').select2({
            dropdownParent: $('#filterSidebar'),
            placeholder: 'Select Tour',
            width: '100%',
            minimumInputLength: 3,
            ajax: {
                url: '{{ route("admin.tours.tours-list") }}',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(item => ({
                            id: item.id,
                            text: item.title
                        }))
                    };
                }
            }
        });

        renderFilters();

    });
    
        
        $('#productFilter').on('select2:select', function (e) {
    let data = e.params.data;

    $('#product_text').val(data.text);
});
</script>
@endsection
</x-admin>
