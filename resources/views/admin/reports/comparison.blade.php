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
</style>

<div class="comparison-body">
    <div class="card card-primary bg-white border rounded-lg-custom mb-3 top-search-bar">
        <div class="row">
            <div class="col-sm-12">
                <b class="text-sm">Compare performance between two selected dates</b>
            </div>
            <div class="col-sm-3">
                <input type="date" id="date1" class="form-control">
            </div>
            <div class="col-sm-3">
                <input type="date" id="date2" class="form-control">
            </div>
            <div class="col-sm-2">
                <select id="productFilter" class="form-control">
                    <option value="">All Products</option>
                </select>
            </div>
            <div class="col-sm-2">
                <select id="partner" class="form-control">
                    <option value="">All Channels</option>
                    @foreach($partners as $p)
                        <option value="{{ $p->name }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <button id="applyBtn" class="btn btn-search">Apply</button>
            </div>
        </div>
    </div>

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

    set('rev1', d1.revenue);
    set('rev2', d2.revenue);

    set('pass1', d1.passengers);
    set('pass2', d2.passengers);

    set('book1', d1.bookings);
    set('book2', d2.bookings);

    set('avg1', d1.avg);
    set('avg2', d2.avg);

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