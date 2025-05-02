@role('admin')
<div class="mb-5 mx-2">
    <h2 class="text-lg">Performance</h2>
    <p>Last 7 Days</p>
    <div class="py-2 d-flex bd-highlight text-center border-bottom">
        <div class="pt-3 flex-fill bd-highlight border bg-white">
            <p>Number of ordes</p>
            <h2>0</h2>
            <p>0</p>
        </div>
        <div class="pt-3 flex-fill bd-highlight border bg-white">
            <p>Value of ordes</p>
            <h2>0</h2>
            <p>$0.00</p>
        </div>
        <div class="pt-3 flex-fill bd-highlight border bg-white">
            <p>Total paid</p>
            <h2>0</h2>
            <p>$0.00</p>
        </div>
        <div class="pt-3 flex-fill bd-highlight border bg-white">
            <p>Total refund</p>
            <h2>0</h2>
            <p>$0.00</p>
        </div>
        <div class="pt-3 flex-fill bd-highlight border bg-white">
            <p>Total discount</p>
            <h2>0</h2>
            <p>$0.00</p>
        </div>
        <div class="pt-3 flex-fill bd-highlight border bg-white">
            <p>Total owed</p>
            <h2>0</h2>
            <p>$0.00</p>
        </div>
    </div>
</div>
<div class="row mx-0">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $user }}</h3>
                <p>Total Tours</p>
            </div>
            <div class="icon">
                <i class="fa fa-users"></i>
            </div>
            <a href="{{ route('admin.tour.index') }}" class="small-box-footer">View <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $category }}</h3>
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
                <h3>{{ $tour }}</h3>
                <p>Total Addons</p>
            </div>
            <div class="icon">
                <i class="fas fas fa-th"></i>
            </div>
            <a href="{{ route('admin.addon.index') }}" class="small-box-footer">View <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $collection }}</h3>
                <p>Total Pickups</p>
            </div>
            <div class="icon">
                <i class="fas fas fa-file-pdf"></i>
            </div>
            <a href="{{ route('admin.pickups.index') }}" class="small-box-footer">View <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>
@endrole
