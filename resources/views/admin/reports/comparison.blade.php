<x-admin>
@section('title','Date Comparison Report')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
.container{max-width:1400px;margin:auto}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px}
.cards{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.card{background:#fff;padding:20px;border-radius:10px;box-shadow:0 1px 6px rgba(0,0,0,.1)}
.card h3{font-size:16px;margin-bottom:10px}
.metric{display:flex;justify-content:space-between;margin-top:10px}
.green{color:#28a745}
.red{color:#dc3545}
.row{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px}
.panel{background:#fff;padding:20px;border-radius:10px}
</style>

<div class="container">

    <!-- 🔥 TOP FILTER -->
    <div class="topbar">

        <h3>Date-wise Comparison Report</h3>

        <div style="display:flex;gap:10px;">

            <input type="date" id="date1" class="form-control">
            <input type="date" id="date2" class="form-control">

            <select id="productFilter" class="form-control">
                <option value="">All Products</option>
            </select>

            <select id="partner" class="form-control">
                <option value="">All Channels</option>
                <option value="Tourbeez">Tourbeez</option>
                <option value="Internal">Internal</option>
            </select>

            <button id="applyBtn" class="btn btn-primary">Apply</button>
        </div>

    </div>

    <!-- 🔥 CARDS -->
    <div class="cards">

        <div class="card">
            <h3>Total Revenue</h3>
            <div class="metric">
                <span id="rev1"></span>
                <span id="rev2"></span>
            </div>
            <div id="revDiff"></div>
        </div>

        <div class="card">
            <h3>Passenger Count</h3>
            <div class="metric">
                <span id="pass1"></span>
                <span id="pass2"></span>
            </div>
            <div id="passDiff"></div>
        </div>

        <div class="card">
            <h3>Total Bookings</h3>
            <div class="metric">
                <span id="book1"></span>
                <span id="book2"></span>
            </div>
            <div id="bookDiff"></div>
        </div>

        <div class="card">
            <h3>Avg Revenue / Passenger</h3>
            <div class="metric">
                <span id="avg1"></span>
                <span id="avg2"></span>
            </div>
            <div id="avgDiff"></div>
        </div>

    </div>

    <!-- 🔥 CHARTS -->
    <div class="row">

        <div class="panel">
            <h4>Revenue Comparison</h4>
            <div id="revChart"></div>
        </div>

        <div class="panel">
            <h4>Passenger Comparison</h4>
            <div id="passChart"></div>
        </div>

    </div>

</div>

<script>
let revChart, passChart;

document.getElementById('applyBtn').onclick = async function() {

    const params = new URLSearchParams({
        date1: document.getElementById('date1').value,
        date2: document.getElementById('date2').value,
        product: document.getElementById('productFilter').value,
        partner: document.getElementById('partner').value,
    });

    const res = await fetch(`/admin/reports/comparison-data?${params}`);
    const data = await res.json();

    render(data);
};

function render(data) {

    const d1 = data.date1;
    const d2 = data.date2;

    // 🔥 VALUES
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

    // 🔥 CHARTS
    if(revChart) revChart.destroy();
    if(passChart) passChart.destroy();

    revChart = new ApexCharts(document.querySelector("#revChart"), {
        chart: { type: 'bar' },
        series: [{
            data: [d1.revenue, d2.revenue]
        }],
        xaxis: {
            categories: ['Date 1', 'Date 2']
        }
    });
    revChart.render();

    passChart = new ApexCharts(document.querySelector("#passChart"), {
        chart: { type: 'bar' },
        series: [{
            data: [d1.passengers, d2.passengers]
        }],
        xaxis: {
            categories: ['Date 1', 'Date 2']
        }
    });
    passChart.render();
}

function set(id, val) {
    document.getElementById(id).innerHTML = '$' + val;
}

function diff(id, v1, v2) {

    let change = v2 - v1;
    let percent = v1 ? ((change / v1) * 100).toFixed(1) : 0;

    let cls = change >= 0 ? 'green' : 'red';

    document.getElementById(id).innerHTML =
        `<span class="${cls}">
            ${change >= 0 ? '+' : ''}${change.toFixed(2)} (${percent}%)
        </span>`;
}
</script>

</x-admin>