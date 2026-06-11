<x-admin>
    @section('title','Dashboard')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
    .container{max-width:1400px;margin:auto}
    .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px}
    .cards,.row{display:grid;grid-template-columns:repeat(2,1fr);gap:20px;margin-bottom:20px}
    .cards{grid-template-columns:repeat(4,1fr)}
    .card{display:flex;justify-content:space-between;align-items:center;flex-wrap:nowrap;}
    .info-icon{font-size:30px;color:#4a90e2}
    .card,.panel{background:#fff;padding:20px;border-radius:10px;box-shadow:0 1px 6px rgba(0,0,0,.1)}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eee}
    .text-right{text-align:right}
    @media(max-width:900px){.cards,.row{grid-template-columns:1fr}}
    </style>
    <div class="container">
        <div class="header">
            <div>
                <p><b>Tour-wise overview</b></p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:nowrap;">
                <input type="date" id="fromDate" class="form-control" value="2025-05-01">
                <input type="date" id="toDate" class="form-control" value="2025-05-31">
                <select id="tourFilter" class="form-control">
                    <option value="">All Tours</option>
                    
                </select>
                <button type="button" id="applyFilter" class="btn btn-success">Apply</button>
                <!-- <button id="downloadBtn" class="btn">Download CSV</button> -->
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <div class="info-icon">
                    <i class="fas fa-map-signs"></i>
                </div>
                <div><h4>Total Bookings</h4><h2 id="totalBookings"></h2></div>
            </div>
            <div class="card">
                <div class="info-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div><h4>Total Revenue</h4><h2 id="totalRevenue"></h2></div>
            </div>
            <div class="card">
                <div class="info-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div><h4>Avg Order Value</h4><h2 id="avgOrderValue"></h2></div>
            </div>
            <div class="card">
                <div class="info-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div><h4>Total Tours</h4><h2 id="totalTours"></h2></div>
            </div>
        </div>

        <div class="row">
            <div class="panel"><h3>Revenue by Tour</h3><div id="revenueChart"></div></div>
            <div class="panel"><h3>Bookings by Tour</h3><div id="bookingChart"></div></div>
        </div>

        <div class="row">
            <div class="panel">
                <h3>Tour Performance</h3>
                <table>
                    <thead><tr><th>Tour</th><th class="text-right">Bookings</th><th class="text-right">Revenue</th></tr></thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>
            <div class="panel">
                <h3>Revenue Trend</h3>
                <div id="trendChart"></div>
            </div>
        </div>
    </div>

    <script>
    const tours = [
        {
            name: "Best Value Niagara Falls Day Tour From Toronto",
            revenue: 85420,
            bookings: 387,
            trend: [6200, 7100, 6900, 7600, 8400, 7900, 8800, 9200]
        },
        {
            name: "Toronto To Niagara Falls Day Tour",
            revenue: 63780,
            bookings: 294,
            trend: [4200, 4800, 5300, 5100, 6200, 5900, 6800, 7200]
        },
        {
            name: "Small Group Toronto To Niagara Falls Day Tour",
            revenue: 49250,
            bookings: 228,
            trend: [3100, 3400, 3900, 4200, 4500, 4700, 5200, 5600]
        },
        {
            name: "Niagara Falls Tour From Toronto Airport",
            revenue: 71360,
            bookings: 321,
            trend: [5100, 5500, 6100, 6500, 7000, 7200, 7600, 8100]
        },
        {
            name: "Toronto To Niagara Falls Day and Evening Tour",
            revenue: 58640,
            bookings: 267,
            trend: [3900, 4300, 4700, 5200, 5600, 6000, 6400, 6900]
        },
        {
            name: "Toronto to Niagara Falls Half Day Private Tour",
            revenue: 44890,
            bookings: 186,
            trend: [2800, 3000, 3400, 3700, 4100, 4400, 4700, 5100]
        },
        {
            name: "Toronto To Niagara Falls Private Tour",
            revenue: 78930,
            bookings: 344,
            trend: [5600, 6100, 6500, 7000, 7600, 8100, 8500, 9200]
        },
        {
            name: "Whirlpool Tour",
            revenue: 35870,
            bookings: 173,
            trend: [2200, 2500, 2800, 3000, 3400, 3600, 3900, 4300]
        },
        {
            name: "Night Tour",
            revenue: 29760,
            bookings: 142,
            trend: [1800, 2100, 2300, 2600, 2900, 3200, 3500, 3900]
        },
        {
            name: "Niagara Falls Day Tour",
            revenue: 92650,
            bookings: 418,
            trend: [6800, 7300, 7900, 8500, 9100, 9700, 10200, 10800]
        }
    ];

    const sel=document.getElementById('tourFilter');
    tours.forEach(t=>sel.innerHTML+=`<option value="${t.name}">${t.name}</option>`);

    let revenueChart,bookingChart,trendChart;

    function renderDashboard(data){
        const tr=data.reduce((s,x)=>s+x.revenue,0);
        const tb=data.reduce((s,x)=>s+x.bookings,0);

        document.getElementById('totalRevenue').innerHTML='$'+tr.toLocaleString();
        document.getElementById('totalBookings').innerHTML=tb;
        document.getElementById('avgOrderValue').innerHTML='$'+(tr/tb).toFixed(2);
        document.getElementById('totalTours').innerHTML=data.length;

        if(revenueChart) revenueChart.destroy();
        if(bookingChart) bookingChart.destroy();
        if(trendChart) trendChart.destroy();

        revenueChart=new ApexCharts(document.querySelector("#revenueChart"),{
            chart:{type:'donut',height:320},
            series:data.map(x=>x.revenue),
            labels:data.map(x=>x.name)
        }); 
        revenueChart.render();

        bookingChart=new ApexCharts(document.querySelector("#bookingChart"),{
            chart:{type:'donut',height:320},
            series:data.map(x=>x.bookings),
            labels:data.map(x=>x.name)
        }); 
        bookingChart.render();

        trendChart=new ApexCharts(document.querySelector("#trendChart"),{
            chart:{type:'line',height:450},
            series:data.map(t=>({name:t.name,data:t.trend})),
            xaxis:{categories:['May1','May5','May10','May15','May20','May25','May30','May31']},
            legend: {show: false},
        }); 
        trendChart.render();

        let html='';
        data.forEach(t=>{
            html+=`<tr><td>${t.name}</td><td class="text-right">${t.bookings}</td><td class="text-right">$${t.revenue.toLocaleString()}</td></tr>`;
        });
        document.getElementById('tableBody').innerHTML=html;
    }

    renderDashboard(tours);

    document.getElementById('applyFilter').onclick=()=>{
        const val=sel.value;
        renderDashboard(val==='all'?tours:tours.filter(t=>t.name===val));
    };

    document.getElementById('downloadBtn').onclick=()=>{
        let csv='Tour,Bookings,Revenue\n';
        tours.forEach(t=>csv+=`${t.name},${t.bookings},${t.revenue}\n`);
        const blob=new Blob([csv],{type:'text/csv'});
        const a=document.createElement('a');
        a.href=URL.createObjectURL(blob);
        a.download='tour-report.csv';
        a.click();
    };
    </script>
</x-admin>
