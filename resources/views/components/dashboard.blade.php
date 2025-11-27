@php
$performance['total_refund'] = 1500;
$performance['total_discount'] = 2500;
$performance['total_owed'] = 6544.01;
@endphp
<div class="mb-3">
    <div class="dash-perform">
        <div class="row">
            <div class="col-md-6 col-6">
                <h2 class="text-sm m-0">Performance</h2>
                <form method="GET">
                    <select name="days" onchange="this.form.submit()" class="form-control-sm">
                        <option value="7" {{ request('days') == 7 ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="15" {{ request('days') == 15 ? 'selected' : '' }}>Last 15 Days</option>
                        <option value="30" {{ request('days') == 30 ? 'selected' : '' }}>Last 30 Days</option>
                    </select>
                </form>
            </div>
            <div class="col-md-6 col-6">
                <button class="btn float-right">View Sales Reports</button>
            </div>
        </div>
    </div>
</div>

<div class="total-record">
    <div class="row">
        <div class="col-md-4">
            <div class="order-number bg-white border rounded-lg-custom p-3 mb-3">
                <div class="row">
                    <div class="col-md-12">
                        <p>Number of Orders</p>
                    </div>
                    <div class="col-md-12">
                        <div class="circle-chart">
                            <svg>
                                <circle class="circle-bg" cx="75" cy="75" r="70"></circle>
                                <circle class="circle-progress" cx="75" cy="75" r="70"></circle>
                            </svg>
                            <div class="circle-text" id="order-number">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="order-number bg-white border rounded-lg-custom p-3 mb-3">
                <div class="row">
                    <div class="col-md-12">
                        <p>Value of Orders</p>
                    </div>
                    <div class="col-md-12">
                        <div class="circle-chart">
                            <svg>
                                <circle class="circle-bg" cx="75" cy="75" r="70"></circle>
                                <circle class="circle-progress" cx="75" cy="75" r="70"></circle>
                            </svg>
                            <div class="circle-text" id="order-value">$ 0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="order-number bg-white border rounded-lg-custom p-3 mb-3">
                <div class="row">
                    <div class="col-md-12">
                        <p>Total Paid</p>
                    </div>
                    <div class="col-md-12">
                        <div class="circle-chart">
                            <svg>
                                <circle class="circle-bg" cx="75" cy="75" r="70"></circle>
                                <circle class="circle-progress" cx="75" cy="75" r="70"></circle>
                            </svg>
                            <div class="circle-text" id="total-paid">$ 0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="bg-white border rounded-lg-custom p-3 mb-3">
                <div id="mountainChart"></div>
            </div>
        </div>
    </div>
</div>

<div>
    <div class="row">
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
            <a href="{{ route('admin.customers.index') }}" class="info-stats4">
                <div class="info-icon">
                    <i class="fa fa-users"></i>
                </div>
                <div class="sale-num">
                    <h3>{{ $user_count }}</h3>
                    <p>Total Customers</p>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
            <a href="{{ route('admin.category.index') }}" class="info-stats4">
                <div class="info-icon">
                    <i class="fas fas fa-th"></i>
                </div>
                <div class="sale-num">
                    <h3>{{ $category_count }}</h3>
                    <p>Total Categories</p>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
            <a href="{{ route('admin.tour.index') }}" class="info-stats4">
                <div class="info-icon">
                    <i class="fas fa-map-signs"></i>
                </div>
                <div class="sale-num">
                    <h3>{{ $tour_count }}</h3>
                    <p>Total Tours</p>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
            <a href="{{ route('admin.user.index') }}" class="info-stats4">
                <div class="info-icon">
                    <i class="fas fa-user-cog"></i>
                </div>
                <div class="sale-num">
                    <h3>{{ $staff_count }}</h3>
                    <p>Total Staff</p>
                </div>
            </a>
        </div>
    </div>
</div>

<div>
    <div class="row">
        <div class="col-md-6 order-panel">
            <div class="bg-white rounded-lg-custom border p-3">
                <div class="row">
                    <div class="col-md-6 col-6">
                        <b class="text-black">Orders</b>
                        <form method="GET">
                            <select name="days" onchange="this.form.submit()" class="form-control-sm">
                                <option value="7" {{ request('days') == 7 ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="15" {{ request('days') == 15 ? 'selected' : '' }}>Last 15 Days</option>
                                <option value="30" {{ request('days') == 30 ? 'selected' : '' }}>Last 30 Days</option>
                            </select>
                        </form>
                    </div>
                    <div class="col-md-6 col-6">
                        <button class="btn float-right">View All Orders</button>
                    </div>
                    <div class="col-md-12">
                        <div class="table-viewport">
                            <table class="table table-bordered table-striped" id="OrderTable">
                                <thead>
                                    <tr>
                                        <th style="white-space: nowrap;">Order No.</th>
                                        <th style="white-space: nowrap;">Tour</th>
                                        <th style="white-space: nowrap;">Amount</th>
                                        <th style="white-space: nowrap;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <a href="" class="">TEZBJOH</a>
                                        </td>
                                        <td>
                                            <a href="" class="">
                                                Amsterdam: Van Gogh Museum Ticket
                                            </a>
                                            <small>Nov 15, 2025 9:30 AM</small>
                                        </td>
                                        <td>
                                            <p class="m-0">
                                                CAD 750.00
                                                <small>Card</small>
                                            </p>
                                        </td>
                                        <td>
                                            <b class="order-confimed">Confirmed</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href="" class="">TEZBJOH</a>
                                        </td>
                                        <td>
                                            <a href="" class="">
                                                Amsterdam: Van Gogh Museum Ticket
                                            </a>
                                            <small>Nov 15, 2025 9:30 AM</small>
                                        </td>
                                        <td>
                                            <p class="m-0">
                                                CAD 750.00
                                                <small>Card</small>
                                            </p>
                                        </td>
                                        <td>
                                            <b class="order-confimed">Confirmed</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href="" class="">TEZBJOH</a>
                                        </td>
                                        <td>
                                            <a href="" class="">
                                                Amsterdam: Van Gogh Museum  Ticket
                                            </a>
                                            <small>Nov 15, 2025 9:30 AM</small>
                                        </td>
                                        <td>
                                            <p class="m-0">
                                                CAD 750.00
                                                <small>Card</small>
                                            </p>
                                        </td>
                                        <td>
                                            <b class="order-pending">Pending</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href="" class="">TEZBJOH</a>
                                        </td>
                                        <td>
                                            <a href="" class="">
                                                Amsterdam: Van Gogh Museum Ticket
                                            </a>
                                            <small>Nov 15, 2025 9:30 AM</small>
                                        </td>
                                        <td>
                                            <p class="m-0">
                                                CAD 750.00
                                                <small>Card</small>
                                            </p>
                                        </td>
                                        <td>
                                            <b class="order-cancelled">Cancelled</b>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        <div class="col-md-6 order-panel">
            <div class="bg-white rounded-lg-custom border p-3">
                <div class="row">
                    <div class="col-md-6 col-6">
                        <b class="text-black">Manifest</b>
                        <form method="GET">
                            <select name="days" onchange="this.form.submit()" class="form-control-sm">
                                <option value="7" {{ request('days') == 7 ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="15" {{ request('days') == 15 ? 'selected' : '' }}>Last 15 Days</option>
                                <option value="30" {{ request('days') == 30 ? 'selected' : '' }}>Last 30 Days</option>
                            </select>
                        </form>
                    </div>
                    <div class="col-md-6 col-6">
                        <button class="btn float-right">View All Manifest</button>
                    </div>
                    <div class="col-md-12">
                        <div class="table-viewport">
                            <table class="table table-bordered table-striped" id="OrderTable">
                                <thead>
                                    <tr>
                                        <th style="white-space: nowrap;">Date</th>
                                        <th style="white-space: nowrap;">Tour</th>
                                        <th style="white-space: nowrap;">Guests</th>
                                        <th style="white-space: nowrap;">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr onclick="window.location='/';" style="cursor:pointer;">
                                        <td class="text-center">
                                            <small>
                                                07<br>
                                                <b class="text-black">NOV</b><br>
                                                2025
                                            </small>
                                        </td>
                                        <td>
                                            <b class="tag">TCDKWCG</b>
                                            <a href="" class="">
                                                Diamonds Tour - Inspection in Diamond District New York
                                            </a>
                                            <small>1:30 AM - 2:00 AM</small>
                                        </td>
                                        <td>
                                            <p class="m-0">2 Adult<br>1 Child</p>
                                        </td>
                                        <td>
                                            <p class="m-0">
                                                CAD25.00
                                                <small>1 Order</small>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr onclick="window.location='/';" style="cursor:pointer;">
                                        <td class="text-center">
                                            <small>
                                                20<br>
                                                <b class="text-black">SEP</b><br>
                                                2025
                                            </small>
                                        </td>
                                        <td>
                                            <b class="tag">TCDKWCG</b>
                                            <a href="" class="">
                                                Diamonds Tour - Inspection in Diamond District New York
                                            </a>
                                            <small>1:30 AM - 2:00 AM</small>
                                        </td>
                                        <td>
                                            <p class="m-0">2 Adult<br>1 Child</p>
                                        </td>
                                        <td>
                                            <p class="m-0">
                                                CAD25.00
                                                <small>1 Order</small>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr onclick="window.location='/';" style="cursor:pointer;">
                                        <td class="text-center">
                                            <small>
                                                01<br>
                                                <b class="text-black">OCT</b><br>
                                                2025
                                            </small>
                                        </td>
                                        <td>
                                            <b class="tag">TCDKWCG</b>
                                            <a href="" class="">
                                                Diamonds Tour - Inspection in Diamond District New York
                                            </a>
                                            <small>1:30 AM - 2:00 AM</small>
                                        </td>
                                        <td>
                                            <p class="m-0">2 Adult<br>1 Child</p>
                                        </td>
                                        <td>
                                            <p class="m-0">
                                                CAD25.00
                                                <small>1 Order</small>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr onclick="window.location='/';" style="cursor:pointer;">
                                        <td class="text-center">
                                            <small>
                                                15<br>
                                                <b class="text-black">DEC</b><br>
                                                2025
                                            </small>
                                        </td>
                                        <td>
                                            <b class="tag">TCDKWCG</b>
                                            <a href="" class="">
                                                Diamonds Tour - Inspection in Diamond District New York
                                            </a>
                                            <small>1:30 AM - 2:00 AM</small>
                                        </td>
                                        <td>
                                            <p class="m-0">2 Adult<br>1 Child</p>
                                        </td>
                                        <td>
                                            <p class="m-0">
                                                CAD25.00
                                                <small>1 Order</small>
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<script>
  // Select all circle progress elements first
  const circles = document.querySelectorAll('.circle-progress');

  // Number of Orders
  const orders = {{ $performance['number_of_orders'] ?? 0 }};
  const maxOrders = 1000;
  const progressOrders = circles[0];
  const orderNumber = document.getElementById('order-number');
  const ordersOffset = 440 - (440 * (orders / maxOrders));

  setTimeout(() => {
    progressOrders.style.strokeDashoffset = ordersOffset;
  }, 500);

  let countOrders = 0;
  const stepOrders = Math.ceil(orders / 50);
  const intervalOrders = setInterval(() => {
    countOrders += stepOrders;
    if(countOrders >= orders) {
      countOrders = orders;
      clearInterval(intervalOrders);
    }
    orderNumber.textContent = countOrders;
  }, 20);

  // Value of Orders
  const orderValueAmount = {{ $performance['value_of_orders'] ?? 0 }};
  const maxValue = 500000;
  const progressValue = circles[1];
  const orderValueText = document.getElementById('order-value');
  const valueOffset = 440 - (440 * (orderValueAmount / maxValue));

  setTimeout(() => {
    progressValue.style.strokeDashoffset = valueOffset;
  }, 500);

  let countValue = 0;
  const stepValue = Math.ceil(orderValueAmount / 100);
  const intervalValue = setInterval(() => {
    countValue += stepValue;
    if(countValue >= orderValueAmount) {
      countValue = orderValueAmount;
      clearInterval(intervalValue);
    }
    orderValueText.textContent = '$' + countValue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
  }, 15);

  // Total Paid
  const totalPaidAmount = {{ $performance['total_paid'] ?? 0 }};
  const maxPaid = 500000;
  const progressPaid = circles[2];
  const totalPaidText = document.getElementById('total-paid');
  const paidOffset = 440 - (440 * (totalPaidAmount / maxPaid));

  setTimeout(() => {
    progressPaid.style.strokeDashoffset = paidOffset;
  }, 500);

  let countPaid = 0;
  const stepPaid = Math.ceil(totalPaidAmount / 100);
  const intervalPaid = setInterval(() => {
    countPaid += stepPaid;
    if(countPaid >= totalPaidAmount) {
      countPaid = totalPaidAmount;
      clearInterval(intervalPaid);
    }
    totalPaidText.textContent = '$' + countPaid.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
  }, 15);
</script>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var options = {
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false },
                zoom: { enabled: false },
                events: {
                    mouseMove: function() {}, // allow scroll
                }
            },

            colors: ['#ff4d4d', '#ffaa00', '#3b82f6'], // Refund, Discount, Owed

            series: [{
                name: 'Total Refund',
                data: [0, {{ $performance['total_refund'] }}]
            },
            {
                name: 'Total Discount',
                data: [0, {{ $performance['total_discount'] }}]
            },
            {
                name: 'Total Owed',
                data: [0, {{ $performance['total_owed'] }}]
            }],

            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },

            stroke: {
                curve: 'smooth',
                width: 4
            },

            xaxis: {
                categories: ['Start', 'Now'],
                labels: { style: { fontSize: '14px' } }
            },

            yaxis: {
                labels: { formatter: val => "$ " + val.toLocaleString() }
            },

            tooltip: {
                y: {
                    formatter: val => "$ " + Number(val).toLocaleString()
                }
            },

            legend: {
                position: 'top'
            }
        };

        var chart = new ApexCharts(document.querySelector("#mountainChart"), options);
        chart.render();
    });
</script>