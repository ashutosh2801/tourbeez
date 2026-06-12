<x-admin>
@section('title','Date Comparison Report')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
.container{max-width:1400px;margin:auto}
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
    flex-wrap:wrap;
    gap:10px
}
.topbar-right{display:flex;gap:10px;align-items:center}

.cards{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.card{
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 1px 6px rgba(0,0,0,.1)
}
.card h3{font-size:16px;margin-bottom:15px}

.metric-row{
    display:flex;
    justify-content:space-between;
    margin-bottom:10px
}

.metric-label{font-size:12px;color:#777}
.metric-value{font-weight:bold;font-size:18px}

.diff{
    margin-top:10px;
    font-size:14px
}
.green{color:#28a745}
.red{color:#dc3545}

.row{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px}
.panel{background:#fff;padding:20px;border-radius:10px}

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

            <select id="productFilter" class="form-control">
                <option value="">All Products</option>
            </select>

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

        <!-- Revenue -->
        <div class="card">
            <h3>Total Revenue</h3>

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
        <div class="card">
            <h3>Passenger Count</h3>

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
        <div class="card">
            <h3>Total Bookings</h3>

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
        <div class="card">
            <h3>Avg Revenue / Passenger</h3>

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

    <!-- 🔥 TABLE + INSIGHTS -->
    <div class="row">

        <div class="panel">
            <h4>Product-wise Comparison</h4>
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

        <div class="panel">
            <h4>Key Insights</h4>
            <div class="insights" id="insights"></div>
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

    // 🔥 CHARTS
    if(revChart) revChart.destroy();
    if(passChart) passChart.destroy();

    let tableHTML = '';

    data.products.forEach(p => {

        let cls = p.change >= 0 ? 'badge-green' : 'badge-red';

        tableHTML += `
            <tr>
                <td>${p.product}</td>
                <td>$${p.date1.toLocaleString()}</td>
                <td>$${p.date2.toLocaleString()}</td>
                <td class="${cls}">
                    ${p.change >= 0 ? '+' : ''}${p.change}%
                </td>
            </tr>
        `;
    });

document.getElementById('productTable').innerHTML = tableHTML;

    revChart = new ApexCharts(document.querySelector("#revChart"), {
        chart: { type: 'bar', height: 300 },
        series: [{ data: [d1.revenue, d2.revenue] }],
        colors: ['#007bff','#28a745'],
        xaxis: { categories: ['Yesterday','Today'] }
    });
    revChart.render();

    passChart = new ApexCharts(document.querySelector("#passChart"), {
        chart: { type: 'bar', height: 300 },
        series: [{ data: [d1.passengers, d2.passengers] }],
        colors: ['#007bff','#28a745'],
        xaxis: { categories: ['Yesterday','Today'] }
    });
    passChart.render();

    // 🔥 INSIGHTS
    document.getElementById('insights').innerHTML = `
        <div class="insight-green">Revenue change: ${calcText(d1.revenue,d2.revenue)}</div>
        <div class="insight-purple">Passenger change: ${calcText(d1.passengers,d2.passengers)}</div>
        <div class="insight-blue">Booking change: ${calcText(d1.bookings,d2.bookings)}</div>
        <div class="insight-orange">Avg change: ${calcText(d1.avg,d2.avg)}</div>
    `;
}

function set(id,val){
    document.getElementById(id).innerHTML = '$' + Number(val).toLocaleString();
}
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

function calcText(v1,v2){
    let change = v2 - v1;
    let percent = v1 ? ((change/v1)*100).toFixed(1) : 0;
    return `${change>=0?'+':''}${change.toFixed(2)} (${percent}%)`;
}
</script>

</x-admin>