<x-admin>
@section('title','Date Comparison Report')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
.container{max-width:1400px;margin:auto}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px}
.topbar-right{display:flex;gap:10px;align-items:center}

.cards{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.card{background:#fff;padding:20px;border-radius:10px;box-shadow:0 1px 6px rgba(0,0,0,.1)}
.card h3{font-size:16px;margin-bottom:15px}

.metric-row{display:flex;justify-content:space-between;margin-bottom:10px}
.metric-label{font-size:12px;color:#777}
.metric-value{font-weight:bold;font-size:18px}

.diff{margin-top:10px;font-size:14px}
.green{color:#28a745}
.red{color:#dc3545}

.row{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px}
.panel{background:#fff;padding:20px;border-radius:10px}

.table{width:100%;border-collapse:collapse}
.table th,.table td{padding:8px;border-bottom:1px solid #eee;text-align:center}

.badge-green{color:#28a745;font-weight:bold}
.badge-red{color:#dc3545;font-weight:bold}

.insights div{padding:10px;border-radius:8px;margin-bottom:10px;font-size:14px}
.insight-green{background:#eaf7ef}
.insight-purple{background:#f3ecff}
.insight-blue{background:#eef5ff}
.insight-orange{background:#fff4ea}
.insight-red{background:#fdeeee}
</style>

<div class="container">

    <!-- 🔥 HEADER -->
    <div class="topbar">
        <div>
            <h3>Date-wise Comparison Report</h3>
            <small>Compare performance between two selected dates</small>
        </div>

        <div class="topbar-right">
            <input type="date" id="date1" class="form-control">
            <input type="date" id="date2" class="form-control">

            <!-- <select id="productFilter" class="form-control">
                <option value="">All Products</option>
                @foreach($products ?? [] as $p)
                    <option value="{{ $p->id }}">{{ $p->title }}</option>
                @endforeach
            </select> -->

            <select id="partner" class="form-control">
                <option value="">All Channels</option>
                @foreach($partners as $p)
                    <option value="{{ $p->name }}">{{ $p->name }}</option>
                @endforeach
            </select>

            <button id="applyBtn" class="btn btn-primary">Apply</button>
        </div>
    </div>

    <!-- 🔥 CARDS -->
    <div class="cards">

        <div class="card">
            <h3>Total Revenue</h3>
            <div class="metric-row">
                <div>
                    <div class="metric-label">Date 1</div>
                    <div class="metric-value" id="rev1"></div>
                </div>
                <div>
                    <div class="metric-label">Date 2</div>
                    <div class="metric-value green" id="rev2"></div>
                </div>
            </div>
            <div class="diff" id="revDiff"></div>
        </div>

        <div class="card">
            <h3>Passenger Count</h3>
            <div class="metric-row">
                <div>
                    <div class="metric-label">Date 1</div>
                    <div class="metric-value" id="pass1"></div>
                </div>
                <div>
                    <div class="metric-label">Date 2</div>
                    <div class="metric-value green" id="pass2"></div>
                </div>
            </div>
            <div class="diff" id="passDiff"></div>
        </div>

        <div class="card">
            <h3>Total Bookings</h3>
            <div class="metric-row">
                <div>
                    <div class="metric-label">Date 1</div>
                    <div class="metric-value" id="book1"></div>
                </div>
                <div>
                    <div class="metric-label">Date 2</div>
                    <div class="metric-value green" id="book2"></div>
                </div>
            </div>
            <div class="diff" id="bookDiff"></div>
        </div>

        <div class="card">
            <h3>Avg Revenue / Passenger</h3>
            <div class="metric-row">
                <div>
                    <div class="metric-label">Date 1</div>
                    <div class="metric-value" id="avg1"></div>
                </div>
                <div>
                    <div class="metric-label">Date 2</div>
                    <div class="metric-value red" id="avg2"></div>
                </div>
            </div>
            <div class="diff" id="avgDiff"></div>
        </div>

    </div>

    <!-- 🔥 CHARTS -->
    <div class="row">
        <div class="panel">
            <h4>Revenue Comparison</h4>
            <div id="revChart"></div>
        </div>

        <div class="panel">
            <h4>Passenger Count Comparison</h4>
            <div id="passChart"></div>
        </div>
    </div>

</div>

<script>
let revChart, passChart;

// 🔥 APPLY FILTER
document.getElementById('applyBtn').onclick = function () {

    const params = new URLSearchParams({
        date1: document.getElementById('date1').value,
        date2: document.getElementById('date2').value,
        // product: document.getElementById('productFilter').value,
        partner: document.getElementById('partner').value,
    });

    const url = `{{ route('admin.report.comparison') }}?${params.toString()}`;
    window.history.pushState({}, '', url);

    fetchData(params);
};

// 🔥 FETCH
async function fetchData(params) {

    const res = await fetch(`{{ route('admin.report.comparison.data') }}?${params}`);
    const data = await res.json();

    render(data);
}

// 🔥 RENDER
function render(data) {

    const d1 = data.date1;
    const d2 = data.date2;

    setMoney('rev1', d1.revenue);
    setMoney('rev2', d2.revenue);

    setNumber('pass1', d1.passengers);
    setNumber('pass2', d2.passengers);

    setNumber('book1', d1.bookings);
    setNumber('book2', d2.bookings);

    setMoney('avg1', d1.avg);
    setMoney('avg2', d2.avg);

    diff('revDiff', d1.revenue, d2.revenue);
    diff('passDiff', d1.passengers, d2.passengers);
    diff('bookDiff', d1.bookings, d2.bookings);
    diff('avgDiff', d1.avg, d2.avg);

    if(revChart) revChart.destroy();
    if(passChart) passChart.destroy();

    revChart = new ApexCharts(document.querySelector("#revChart"), {
        chart: { type: 'bar', height: 300 },
        series: [{ data: [d1.revenue, d2.revenue] }],
        colors: ['#007bff','#28a745'],
        xaxis: { categories: ['Date 1','Date 2'] }
    });
    revChart.render();

    passChart = new ApexCharts(document.querySelector("#passChart"), {
        chart: { type: 'bar', height: 300 },
        series: [{ data: [d1.passengers, d2.passengers] }],
        colors: ['#007bff','#28a745'],
        xaxis: { categories: ['Date 1','Date 2'] }
    });
    passChart.render();
}

// 🔥 HELPERS
function setMoney(id,val){
    document.getElementById(id).innerHTML = '$' + Number(val).toLocaleString();
}

function setNumber(id,val){
    document.getElementById(id).innerHTML = Number(val).toLocaleString();
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

// 🔥 INITIAL LOAD FROM URL
window.onload = function () {

    const params = new URLSearchParams(window.location.search);

    document.getElementById('date1').value = params.get('date1') || '';
    document.getElementById('date2').value = params.get('date2') || '';
    // document.getElementById('productFilter').value = params.get('product') || '';
    document.getElementById('partner').value = params.get('partner') || '';

    if (params.get('date1') && params.get('date2')) {
        fetchData(params);
    }
};
</script>


</x-admin>