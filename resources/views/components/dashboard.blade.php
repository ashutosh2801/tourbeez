
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

    <div class="total-record bg-white border rounded-lg">
        <div class="row">
            <div class="col-md-2 col-6 p-0">
                <div class="border-r py-3 text-center mb-border-b">
                    <p>Number of Orders</p>
                    <h2>{{ $performance['number_of_orders'] }}</h2>
                </div>
            </div>
            <div class="col-md-2 col-6 p-0">
                <div class="border-r py-3 text-center mb-border-b">
                    <p>Value of Orders</p>
                    <h2>$ {{ number_format($performance['value_of_orders'], 2) }}</h2>
                </div>
            </div>
            <div class="col-md-2 col-6 p-0">
                <div class="border-r py-3 text-center mb-border-b">
                    <p>Total Paid</p>
                    <h2>$ {{ number_format($performance['total_paid'], 2) }}</h2>
                </div>
            </div>
            <div class="col-md-2 col-6 p-0">
                <div class="border-r py-3 text-center mb-border-b">
                    <p>Total Refund</p>
                    <h2>$ {{ number_format($performance['total_refund'], 2) }}</h2>
                </div>
            </div>
            <div class="col-md-2 col-6 p-0">
                <div class="border-r py-3 text-center mb-border-b">
                    <p>Total Discount</p>
                    <h2>$ {{ number_format($performance['total_discount'], 2) }}</h2>
                </div>
            </div>
            <div class="col-md-2 col-6 p-0">
                <div class="py-3 text-center mb-border-b">
                    <p>Total Owed</p>
                    <h2>$ {{ number_format($performance['total_owed'], 2) }}</h2>
                </div>
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