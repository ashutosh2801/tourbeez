<x-admin>
    @section('title','Categories')
    @section('css')
    <style>
        .alink{color: #27bcf1; }
        .alink:hover{text-decoration: underline;}
    </style>
    @endsection


    <div class="card-primary mb-3">
        <div class="card-header categories-header">
            <div class="row">
                <div class="col-md-8 col-6">
                    <h3 class="card-title">Categories</h3>
                </div>
                @can('add_category')
                    <div class="col-md-4 col-6">
                        <div class="card-tools">
                            <a href="{{ route('admin.category.create') }}" class="btn btn-sm btn-success"> + Create New</a>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </div>
    <div class="p-3 border-bottom">
        <form method="GET" action="{{ route('admin.category.index') }}">
            <div class="row">

                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="form-control"
                           placeholder="Search category name...">
                </div>

                <div class="col-md-3">
                    <select name="has_tours" class="form-control">
                        <option value="">-- Filter By Tours --</option>
                        <option value="1" {{ request('has_tours') == '1' ? 'selected' : '' }}>
                            Has Tours
                        </option>
                        <option value="0" {{ request('has_tours') == '0' ? 'selected' : '' }}>
                            No Tours
                        </option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        Filter
                    </button>

                    <a href="{{ route('admin.category.index') }}"
                       class="btn btn-secondary">
                        Reset
                    </a>
                </div>

            </div>
        </form>
    </div>
    <div class="card-primary bg-white border rounded-lg-custom category-main-body">
        <div class="card-body p-0">
            <div class="table-viewport">
                <table class="table table-striped" id="categoryTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Tours</th>
                            <th>Action</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $cat)
                            <tr>
                                <td>
                                @can('edit_category')
                                <a href="{{ route('admin.category.edit', encrypt($cat->id)) }}"
                                    class="btn btn-name">{{ $cat->name }}</a>
                                <a href="{{ $cat->canonical_url }}"
                                    class="btn alink">{{ $cat->canonical_url }}</a>
                                @else
                                {{ $cat->name }} 

                                @endcan
                                </td>
                                <td>
                                @foreach($cat->tours as $tour)
                                    <div><a class="alink" href="{{ $tour->slug }}" title="{{ $tour->title }}"> <i class="fas fa-chevron-right"></i> {{ $tour->title }}</a></div>
                                @endforeach
                                </td>
                                <td width="5">
                                    @can('edit_category')
                                    <a href="{{ route('admin.category.edit', encrypt($cat->id)) }}"
                                        class="btn btn-sm btn-edit"> <i class="far fa-edit"></i> </a>
                                    @endcan
                                </td>
                                <td width="5">
                                    <button 
                                        class="btn btn-sm btn-warning clone-btn"
                                        data-id="{{ encrypt($cat->id) }}">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                                <td width="5">
                                    @can('destroy_category')
                                    <form action="{{ route('admin.category.destroy', encrypt($cat->id)) }}" 
                                          method="POST" 
                                          class="delete-form d-inline">
                                        @method('DELETE')
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-danger delete-btn">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <form id="cloneForm" method="POST" style="display:none;">
                    @csrf
                </form>
                <div class="mt-3 p-3">
                    {{ $data->links() }}
                </div>
            </div>
        </div>
    </div>
    @section('js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.clone-btn').forEach(button => {

                button.addEventListener('click', function () {

                    let id = this.dataset.id;

                    Swal.fire({
                        title: 'Clone Category?',
                        text: "A copy will be created with tours attached.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, clone it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {

                        if (result.isConfirmed) {

                            let form = document.getElementById('cloneForm');
                            form.action = `/admin/category/${id}/clone`;
                            form.submit();
                        }

                    });

                });

            });

        });
        </script>


        <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.delete-btn').forEach(button => {

                button.addEventListener('click', function () {

                    let form = this.closest('.delete-form');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This category will be permanently deleted.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {

                        if (result.isConfirmed) {
                            form.submit();
                        }

                    });

                });

            });

        });
        </script>
    @endsection
</x-admin>
