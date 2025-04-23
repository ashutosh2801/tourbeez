<div class="card">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Scheduling</h3>
            <div class="card-tools">
                <a href="{{ route('admin.addon.create') }}" class="btn btn-sm btn-info">Create New</a>
            </div>
        </div>
        <div class="card-body">
            <form class="needs-validation" novalidate action="{{ route('admin.tour.addon_update', $data->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            
            <button type="submit" id="submit" class="btn btn-primary">Save tour</button>
            </form>
        </div>
    </div>
</div>

@section('js')
@parent

@endsection
