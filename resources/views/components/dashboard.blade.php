
<div class="mb-5 mx-2">
    <h2 class="text-lg">Performance</h2>
    <form method="GET">
        <select name="days" onchange="this.form.submit()" class="form-control-sm">
            <option value="7" {{ request('days') == 7 ? 'selected' : '' }}>Last 7 Days</option>
            <option value="15" {{ request('days') == 15 ? 'selected' : '' }}>Last 15 Days</option>
            <option value="30" {{ request('days') == 30 ? 'selected' : '' }}>Last 30 Days</option>
        </select>
    </form>

    <div class="py-2 d-flex bd-highlight text-center border-bottom flex-wrap">
        <div class="pt-3 flex-fill bd-highlight border bg-white m-1 rounded">
            <p>Number of Orders</p>
            <h2>{{ $performance['number_of_orders'] }}</h2>
            <p>{{ $performance['number_of_orders'] }}</p>
        </div>

        <div class="pt-3 flex-fill bd-highlight border bg-white m-1 rounded">
            <p>Value of Orders</p>
            <h2>{{ number_format($performance['value_of_orders'], 2) }}</h2>
        </div>

        <div class="pt-3 flex-fill bd-highlight border bg-white m-1 rounded">
            <p>Total Paid</p>
            <h2>{{ number_format($performance['total_paid'], 2) }}</h2>
        </div>

        <div class="pt-3 flex-fill bd-highlight border bg-white m-1 rounded">
            <p>Total Refund</p>
            <h2>{{ number_format($performance['total_refund'], 2) }}</h2>
        </div>

        <div class="pt-3 flex-fill bd-highlight border bg-white m-1 rounded">
            <p>Total Discount</p>
            <h2>{{ number_format($performance['total_discount'], 2) }}</h2>
        </div>

        <div class="pt-3 flex-fill bd-highlight border bg-white m-1 rounded">
            <p>Total Owed</p>
            <h2>{{ number_format($performance['total_owed'], 2) }}</h2>
        </div>
    </div>
</div>


<div class="row mx-0">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $user_count }}</h3>
                <p>Total Customers</p>
            </div>
            <div class="icon">
                <i class="fa fa-users"></i>
            </div>
            <a href="{{ route('admin.customers.index') }}" class="small-box-footer">View <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $category_count }}</h3>
                <p>Total Categories</p>
            </div>
            <div class="icon">
                <i class="fas fa-list-alt"></i>
            </div>
            <a href="{{ route('admin.category.index') }}" class="small-box-footer">View <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $tour_count }}</h3>
                <p>Total Tours</p>
            </div>
            <div class="icon">
                <i class="fas fas fa-th"></i>
            </div>
            <a href="{{ route('admin.tour.index') }}" class="small-box-footer">View <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $staff_count }}</h3>
                <p>Total Staff</p>
            </div>
            <div class="icon">
                <i class="fas fas fa-file-pdf"></i>
            </div>
            <a href="{{ route('admin.user.index') }}" class="small-box-footer">View <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>
