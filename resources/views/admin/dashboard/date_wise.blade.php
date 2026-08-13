<x-admin>
    @section('title','Date-wise Comparison Report')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    .container{max-width:1400px;margin:auto;padding:20px}
    .header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px}
    .filters{display:flex;gap:10px;flex-wrap:wrap}
    input,select,button{padding:10px 12px;border:1px solid #d1d5db;border-radius:8px}
    button{background:#2563eb;color:#fff;cursor:pointer}
    .cards{display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-top:20px}
    .card,.panel{background:#fff;border-radius:12px;padding:18px;box-shadow:0 1px 4px rgba(0,0,0,.08)}
    .metric{display:flex;justify-content:space-between}
    .value{font-size:30px;font-weight:700}
    .green{color:#16a34a}.red{color:#dc2626}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-top:15px}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eee}
    .insight{padding:10px;background:#f8fafc;border-radius:8px;margin-bottom:8px}
    @media(max-width:1000px){.cards,.row{grid-template-columns:1fr}}
    </style>
    <div class="container">
    <div class="header">
    <div class="filters">
    <input type="date" id="date1" value="2025-05-30">
    <input type="date" id="date2" value="2025-05-31">
    <select id="product"><option value="all">All Products</option></select>
    <select id="channel"><option>All Channels</option><option>Website</option><option>Agent</option></select>
    <button id="applyBtn">Apply</button>
    <button id="exportBtn">Export CSV</button>
    </div>
    </div>

    <div class="cards">
    <div class="card"><h4>Total Revenue</h4><div class="metric"><div><small>Yesterday</small><div id="revY" class="value">$0</div></div><div><small>Today</small><div id="revT" class="value green">$0</div></div></div></div>
    <div class="card"><h4>Passenger Count</h4><div class="metric"><div><small>Yesterday</small><div id="passY" class="value">0</div></div><div><small>Today</small><div id="passT" class="value green">0</div></div></div></div>
    <div class="card"><h4>Total Bookings</h4><div class="metric"><div><small>Yesterday</small><div id="bookY" class="value">0</div></div><div><small>Today</small><div id="bookT" class="value green">0</div></div></div></div>
    <div class="card"><h4>Avg Revenue / Passenger</h4><div class="metric"><div><small>Yesterday</small><div id="avgY" class="value">$0</div></div><div><small>Today</small><div id="avgT" class="value">$0</div></div></div></div>
    </div>

    <div class="row">
    <div class="panel"><h3>Revenue Comparison</h3><canvas id="revenueChart"></canvas></div>
    <div class="panel"><h3>Passenger Comparison</h3><canvas id="passengerChart"></canvas></div>
    </div>

    <div class="row">
    <div class="panel">
    <h3>Product-wise Comparison</h3>
    <table>
    <thead><tr><th>Product</th><th>Yesterday Revenue</th><th>Today Revenue</th><th>Change %</th></tr></thead>
    <tbody id="tableBody"></tbody>
    </table>
    </div>
    <div class="panel">
    <h3>Key Insights</h3>
    <div id="insights"></div>
    </div>
    </div>
    </div>

    <script>
    const products=[
    {name:"Niagara Falls Day Tour",yRevenue:3200,tRevenue:3850,yPass:15,tPass:18},
    {name:"Whirlpool Tour",yRevenue:2100,tRevenue:2250,yPass:11,tPass:12},
    {name:"Night Tour",yRevenue:1500,tRevenue:1800,yPass:8,tPass:10},
    {name:"Adventure Tour",yRevenue:1050,tRevenue:1200,yPass:5,tPass:6},
    {name:"Helicopter Tour",yRevenue:600,tRevenue:750,yPass:2,tPass:2}
    ];

    // Laravel:
    // const products = @json($tourAnalytics);

    const productSelect=document.getElementById('product');
    products.forEach(p=>productSelect.innerHTML+=`<option value="${p.name}">${p.name}</option>`);

    let revenueChart, passengerChart;

    function render(data){
    const yRev=data.reduce((a,b)=>a+b.yRevenue,0);
    const tRev=data.reduce((a,b)=>a+b.tRevenue,0);
    const yPass=data.reduce((a,b)=>a+b.yPass,0);
    const tPass=data.reduce((a,b)=>a+b.tPass,0);

    revY.innerText='$'+yRev.toLocaleString();
    revT.innerText='$'+tRev.toLocaleString();
    passY.innerText=yPass;
    passT.innerText=tPass;
    bookY.innerText=Math.max(yPass-4,0);
    bookT.innerText=Math.max(tPass-4,0);
    avgY.innerText='$'+(yRev/yPass).toFixed(2);
    avgT.innerText='$'+(tRev/tPass).toFixed(2);

    if(revenueChart) revenueChart.destroy();
    if(passengerChart) passengerChart.destroy();

    revenueChart=new Chart(document.getElementById('revenueChart'),{
    type:'bar',
    data:{labels:['Yesterday','Today'],datasets:[{label:'Revenue',data:[yRev,tRev]}]}
    });

    passengerChart=new Chart(document.getElementById('passengerChart'),{
    type:'bar',
    data:{labels:['Yesterday','Today'],datasets:[{label:'Passengers',data:[yPass,tPass]}]}
    });

    let rows='';
    data.forEach(p=>{
    let ch=((p.tRevenue-p.yRevenue)/p.yRevenue*100).toFixed(1);
    rows+=`<tr><td>${p.name}</td><td>$${p.yRevenue}</td><td>$${p.tRevenue}</td><td class="${ch>=0?'green':'red'}">${ch}%</td></tr>`;
    });
    tableBody.innerHTML=rows;

    const best=[...data].sort((a,b)=>((b.tRevenue-b.yRevenue)/b.yRevenue)-((a.tRevenue-a.yRevenue)/a.yRevenue))[0];
    insights.innerHTML=`
    <div class="insight">Revenue Change: <b>${(((tRev-yRev)/yRev)*100).toFixed(1)}%</b></div>
    <div class="insight">Passenger Change: <b>${(((tPass-yPass)/yPass)*100).toFixed(1)}%</b></div>
    <div class="insight">Highest Growth: <b>${best.name}</b></div>
    <div class="insight">Revenue Difference: <b>$${(tRev-yRev).toLocaleString()}</b></div>`;
    }

    render(products);

    applyBtn.onclick=()=>{
    const val=productSelect.value;
    render(val==='all'?products:products.filter(x=>x.name===val));
    };

    exportBtn.onclick=()=>{
    let csv='Product,Yesterday Revenue,Today Revenue\n';
    products.forEach(p=>csv+=`${p.name},${p.yRevenue},${p.tRevenue}\n`);
    const a=document.createElement('a');
    a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv'}));
    a.download='comparison-report.csv';
    a.click();
    };

    // Laravel API Example:
    // fetch('/admin/date-comparison?date1='+date1.value+'&date2='+date2.value)
    // .then(r=>r.json()).then(data=>render(data));
    </script>
</x-admin>
