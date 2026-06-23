<x-admin>
@section('title','Date Comparison Report')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>

.card h3{font-size:16px;margin-bottom:15px}

.diff{
    margin-top:10px;
    font-size:14px
}
.green{color:#28a745}
.red{color:#dc3545}

.table{width:100%;border-collapse:collapse}
.table th,.table td{padding:8px;border-bottom:1px solid #eee;text-align:center}

.badge-green{color:#28a745;font-weight:bold}
.badge-red{color:#dc3545;font-weight:bold}

.insights div{
    padding:10px;
    border-radius:8px;
    margin-bottom:10px;
    font-size:14px
}
.insight-green{background:#eaf7ef}
.insight-purple{background:#f3ecff}
.insight-blue{background:#eef5ff}
.insight-orange{background:#fff4ea}
.insight-red{background:#fdeeee}
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

<div class="comparison-body">
    <div class="card card-primary bg-white border rounded-lg-custom mb-3 top-search-bar">
        <div class="row">
            <div class="col-xl-12 col-12">
                <b class="text-sm">Compare performance between two selected dates</b>
            </div>

            <div class="col-xl-4 col-md-3 col-12">
                <div class="form-group">
                    <input type="date" id="date1" class="form-control" required>
                </div>
            </div>

            <div class="col-xl-4 col-md-3 col-12">
                <div class="form-group">
                    <input type="date" id="date2" class="form-control" required>
                </div>
            </div>

            <div class="col-xl-4 col-md-3 col-12">
                <div class="form-group">
                    <select id="productFilter" name="product" class="form-control">
                        @if(request('product') && request('product_text'))
                            <option value="{{ request('product') }}" selected>
                                {{ request('product_text') }}
                            </option>
                        @endif
                    </select>
                </div>
            </div>

            <div class="col-xl-3 col-md-3 col-12">
                <div class="form-group">
                    <select name="order_status" id="order_status" class="form-control">
                        <option value="">All</option>
                        <option value="3">Pending supplier</option>
                        <option value="4">Pending customer</option>
                        <option value="5">Confirmed</option>
                    </select>
                </div>
            </div>

            <div class="col-xl-3 col-md-3 col-12">
                <div class="form-group">
                    <select name="action_type" id="action_type" class="form-control">
                        <option value="">All</option>
                        <option value="pay_now">Pay Now</option>
                        <option value="pay_later">Pay Later</option>
                    </select>
                </div>
            </div>

            <div class="col-xl-3 col-md-3 col-12">
                <div class="form-group">
                    <select id="partner" class="form-control">
                        <option value="">All Channels</option>
                        @foreach($partners as $p)
                            <option value="{{ $p->name }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-xl-3 col-md-12 col-12">
                <button id="applyBtn" class="btn btn-search">Apply</button>
            </div>

            <div class="col-12">
                <div id="activeFilters" class="mb-2"></div>
            </div>
        </div>
    </div>

   <!--  @if(!request()->hasAny(['booking_date','tour_date','product','order_status','payment_status','partner','action_type']))
        <div class="alert alert-info">
            Please apply filters to view report data.
        </div>
    @endif -->
    

    <div id="noFilterAlert"></div>
    <!-- <div id="activeFilters" class="mb-3"></div> -->

    <!-- FILTER ALERT -->
    <div id="filterAlert" class="mb-2"></div>

    <div class="stats-cards">

        <!-- Revenue -->
        <div class="stat-card card-revenue">

            <div class="stat-top">

                <div>
                    <div class="stat-title">Total Revenue</div>
                </div>

                <div class="stat-icon icon-green">
                    <i class="fas fa-dollar-sign"></i>
                </div>

            </div>

            <div class="metric-row">

                <div>
                    <div class="metric-label">Yesterday</div>
                    <div class="metric-value" id="rev1"></div>
                </div>

                <div>
                    <div class="metric-label">Today</div>
                    <div class="metric-value green" id="rev2"></div>
                </div>

            </div>

            <div class="diff" id="revDiff"></div>

        </div>

        <!-- Passenger -->
        <div class="stat-card card-passenger">

            <div class="stat-top">

                <div class="stat-title">
                    Passenger Count
                </div>

                <div class="stat-icon icon-blue">
                    <i class="fas fa-users"></i>
                </div>

            </div>

            <div class="metric-row">

                <div>
                    <div class="metric-label">Yesterday</div>
                    <div class="metric-value" id="pass1"></div>
                </div>

                <div>
                    <div class="metric-label">Today</div>
                    <div class="metric-value green" id="pass2"></div>
                </div>

            </div>

            <div class="diff" id="passDiff"></div>

        </div>

        <!-- Bookings -->
        <div class="stat-card card-booking">

            <div class="stat-top">

                <div class="stat-title">
                    Total Bookings
                </div>

                <div class="stat-icon icon-orange">
                    <i class="fas fa-ticket-alt"></i>
                </div>

            </div>

            <div class="metric-row">

                <div>
                    <div class="metric-label">Yesterday</div>
                    <div class="metric-value" id="book1"></div>
                </div>

                <div>
                    <div class="metric-label">Today</div>
                    <div class="metric-value green" id="book2"></div>
                </div>

            </div>

            <div class="diff" id="bookDiff"></div>

        </div>

        <!-- Avg -->
        <div class="stat-card card-avg">

            <div class="stat-top">

                <div class="stat-title">
                    Avg Revenue / Passenger
                </div>

                <div class="stat-icon icon-purple">
                    <i class="fas fa-chart-line"></i>
                </div>

            </div>

            <div class="metric-row">

                <div>
                    <div class="metric-label">Yesterday</div>
                    <div class="metric-value" id="avg1"></div>
                </div>

                <div>
                    <div class="metric-label">Today</div>
                    <div class="metric-value red" id="avg2"></div>
                </div>

            </div>

            <div class="diff" id="avgDiff"></div>

        </div>

    </div>

    <div class="comparison-chart">

        <div class="panel">
            <h4>Revenue Comparison</h4>
            <div id="revChart"></div>
        </div>

        <div class="panel">
            <h4>Passenger Count Comparison</h4>
            <div id="passChart"></div>
        </div> 

    </div>

    <div class="comparison-chart">

        <div class="panel">
            <h4>Product-wise Comparison</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Yesterday</th>
                            <th>Today</th>
                            <th>Change</th>
                        </tr>
                    </thead>
                    <tbody id="productTable"></tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <h4>Key Insights</h4>
            <div class="insights" id="insights"></div>
        </div>

    </div>

</div>
@section('js')
@parent() 
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let revChart, passChart;
const statusMap = {
        3: 'Pending supplier',
        4: 'Pending customer',
        5: 'Confirmed'
    };


function render(data) {

    const d1 = data.date1;
    const d2 = data.date2;

    set('rev1', d1.revenue);
    set('rev2', d2.revenue);

    set('pass1', d1.passengers);
    set('pass2', d2.passengers);

    set('book1', d1.bookings);
    set('book2', d2.bookings);

    set('avg1', d1.avg);
    set('avg2', d2.avg);

    // 🔥 DIFF
    diff('revDiff', d1.revenue, d2.revenue);
    diff('passDiff', d1.passengers, d2.passengers);
    diff('bookDiff', d1.bookings, d2.bookings);
    diff('avgDiff', d1.avg, d2.avg);

    // 🔥 DESTROY OLD CHARTS
    if (revChart) revChart.destroy();
    if (passChart) passChart.destroy();

    const date1Label = formatDate(document.getElementById('date1').value) || 'Date 1';
    const date2Label = formatDate(document.getElementById('date2').value) || 'Date 2';

revChart = new ApexCharts(
document.querySelector("#revChart"),
{
    chart:{
        type:'bar',
        height:350,
        toolbar:{show:false}
    },

    series:[{
        name:'Revenue',
        data:[d1.revenue,d2.revenue]
    }],

    legend: {
        show: false
    },

    plotOptions:{
        bar:{
            distributed:true,
            borderRadius:8,
            columnWidth:'50%'
        }
    },

    colors:['#94a3b8','#22c55e'],

    dataLabels:{
        enabled:true
    },

    xaxis:{
        categories:['Yesterday','Today']
    }
});

revChart.render();

passChart = new ApexCharts(
document.querySelector("#passChart"),
{
    chart:{
        type:'bar',
        height:350,
        toolbar:{show:false}
    },

    series:[{
        name:'Passengers',
        data:[d1.passengers,d2.passengers]
    }],

    legend: {
        show: false
    },

    plotOptions:{
        bar:{
            distributed:true,
            borderRadius:8,
            columnWidth:'50%'
        }
    },

    colors:['#94a3b8','#3b82f6'],

    dataLabels:{
        enabled:true
    },

    xaxis:{
        categories:['Yesterday','Today']
    }
});

passChart.render();

    // ============================================================
    // 🔥 PRODUCT TABLE (FIXED — THIS WAS MISSING)
    // ============================================================

    let tableHTML = '';

    if (data.products && data.products.length) {

        data.products.forEach(p => {

            let cls = p.change >= 0 ? 'badge-green' : 'badge-red';

            tableHTML += `
                <tr>
                    <td>${p.product}</td>
                    <td>$${Number(p.date1).toLocaleString()}</td>
                    <td>$${Number(p.date2).toLocaleString()}</td>
                    <td class="${cls}">
                        ${p.change >= 0 ? '+' : ''}${p.change}%
                    </td>
                </tr>
            `;
        });

    } else {
        tableHTML = `
            <tr>
                <td colspan="4">No data available</td>
            </tr>
        `;
    }

    const tableEl = document.getElementById('productTable');
    if (tableEl) {
        tableEl.innerHTML = tableHTML;
    }

    // ============================================================
    // 🔥 INSIGHTS (FIXED)
    // ============================================================

    const insightsEl = document.getElementById('insights');

    if (insightsEl) {
        insightsEl.innerHTML = `
            <div class="insight-green">
                Revenue change: ${calcText(d1.revenue, d2.revenue)}
            </div>

            <div class="insight-purple">
                Passenger change: ${calcText(d1.passengers, d2.passengers)}
            </div>

            <div class="insight-blue">
                Booking change: ${calcText(d1.bookings, d2.bookings)}
            </div>

            <div class="insight-orange">
                Avg change: ${calcText(d1.avg, d2.avg)}
            </div>
        `;
    }
}


/*
|--------------------------------------------------------------------------
| 🔥 APPLY FILTER (RELOAD PAGE)
|--------------------------------------------------------------------------
*/
document.getElementById('applyBtn').onclick = function () {

    const date1 = document.getElementById('date1').value;
    const date2 = document.getElementById('date2').value;
    const product = document.getElementById('productFilter').value;
    const partner = document.getElementById('partner').value;
    const order_status  = document.getElementById('order_status').value;
    const action_type   = document.getElementById('action_type').value;
   
    if (!date1 || !date2) {
        showAlert("Please select both Date 1 and Date 2");
        return;
    }

    const params = new URLSearchParams();

    params.set('date1', date1);
    params.set('date2', date2);

    if (product) params.set('product', product);
    if (partner) params.set('partner', partner);
    if (order_status) params.set('order_status', order_status);
    if (action_type) params.set('action_type', action_type);

    // ✅ FULL RELOAD
    window.location.href = `{{ route('admin.report.comparison') }}?${params.toString()}`;
};


/*
|--------------------------------------------------------------------------
| 🔥 FETCH DATA (ALWAYS WORKS)
|--------------------------------------------------------------------------
*/
async function fetchData() {

    const params = new URLSearchParams(window.location.search);

    if (!params.toString()) return;

    const res = await fetch(`{{ route('admin.report.comparison.data') }}?${params}`);
    const data = await res.json();

    render(data);
}


/*
|--------------------------------------------------------------------------
| 🔥 LOAD FROM URL
|--------------------------------------------------------------------------
*/
function loadFromURL() {

    const params = new URLSearchParams(window.location.search);

    const date1 = params.get('date1');
    const date2 = params.get('date2');

    document.getElementById('date1').value = date1 || '';
    document.getElementById('date2').value = date2 || '';
    document.getElementById('partner').value = params.get('partner') || '';
    document.getElementById('action_type').value = params.get('action_type') || '';
    document.getElementById('order_status').value = params.get('order_status') || '';

    // 🔥 restore select2
    if (params.get('product') && params.get('product_text')) {
        const option = new Option(params.get('product_text'), params.get('product'), true, true);
        $('#productFilter').append(option).trigger('change');
    }

    renderFilters();

    // ✅ ALWAYS CALL FETCH
    fetchData();
}


/*
|--------------------------------------------------------------------------
| 🔥 FILTER TAGS
|--------------------------------------------------------------------------
*/
function renderFilters() {

    const alertBox = document.getElementById('filterAlert');
    const container = document.getElementById('activeFilters');

    // ✅ SAFETY CHECK (CRITICAL)
    if (!alertBox || !container) return;

    const params = new URLSearchParams(window.location.search);

    const filters = {
        date1: params.get('date1'),
        date2: params.get('date2'),
        product: params.get('product'),
        partner: params.get('partner'),
        order_status: params.get('order_status'),
        action_type: params.get('action_type'),
    };

    // ❌ NO DATE SELECTED
    if (!filters.date1 || !filters.date2) {
        alertBox.innerHTML = `
            <div class="alert alert-info">
                Please select both Date 1 and Date 2 to view report.
            </div>
        `;
        container.innerHTML = '';
        return;
    }


    alertBox.innerHTML = '';

    let html = '<div class="d-flex flex-wrap gap-2">';

    html += badge('Date 1', formatDate(filters.date1), 'date1');
    html += badge('Date 2', formatDate(filters.date2), 'date2');

    if (filters.product && filters.product !== 'null') {
        html += badge('Product', filters.product, 'product');
    }

    if (filters.partner && filters.partner !== 'null') {
        html += badge('Channel', filters.partner, 'partner');
    }
    if (filters.order_status && filters.order_status !== 'null') {
        html += badge('Status', statusMap[filters.order_status], 'order_status');
    }

    if (filters.action_type && filters.action_type !== 'null') {
        html += badge('Pay', filters.action_type, 'action_type');
    }

    html += '</div>';

    container.innerHTML = html;
}


/*
|--------------------------------------------------------------------------
| 🔥 REMOVE FILTER (RELOAD)
|--------------------------------------------------------------------------
*/
function removeFilter(key) {

    const url = new URL(window.location.href);

    url.searchParams.delete(key);

    if (key === 'product') {
        url.searchParams.delete('product_text');
    }

    window.location.href = url.toString(); // 🔥 FULL RELOAD
}


/*
|--------------------------------------------------------------------------
| 🔥 HELPERS
|--------------------------------------------------------------------------
*/
function badge(label, value, key) {
    return `
        <span class="badge bg-dark mr-2">
            ${label}: ${value}
            <span style="cursor:pointer;margin-left:6px;"
                onclick="removeFilter('${key}')">✕</span>
        </span>
    `;
}

function showAlert(msg) {
    document.getElementById('filterAlert').innerHTML =
        `<div class="alert alert-warning">${msg}</div>`;
}

function formatDate(dateStr){
    if(!dateStr) return '';
    const d = new Date(dateStr);
    if (isNaN(d)) return '';
    return d.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
}

function diff(id,v1,v2){
    let change = v2 - v1;
    let percent = v1 ? ((change/v1)*100).toFixed(1) : 0;
    let cls = change>=0 ? 'green':'red';

    document.getElementById(id).innerHTML =
        `<span class="${cls}">
            ${change>=0?'+':''}${change.toFixed(2)} (${percent}%)
        </span>`;
}

function calcText(v1,v2){
    let change = v2 - v1;
    let percent = v1 ? ((change/v1)*100).toFixed(1) : 0;
    return `${change>=0?'+':''}${change.toFixed(2)} (${percent}%)`;
}


/*
|--------------------------------------------------------------------------
| 🔥 INITIAL LOAD
|--------------------------------------------------------------------------
*/
window.onload = function () {
    loadFromURL();
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