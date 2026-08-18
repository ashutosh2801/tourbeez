<x-admin>
    @section('title','Categories')
    @section('css')
    <style>
        .alink{color: #27bcf1; }
        .alink:hover{text-decoration: underline;}
        .admin-table-search{margin:0 0 18px;padding:20px;border:1px solid #e5e7eb;border-radius:8px;background:linear-gradient(180deg,#f8fafc 0%,#fff 100%)}
        .admin-table-search h5{margin:0 0 3px;color:#172033;font-size:17px;font-weight:700}.admin-table-search p{margin:0 0 14px;color:#6b7280;font-size:13px}.admin-table-search label{display:block;margin:0 0 6px;color:#374151;font-size:12px;font-weight:600}
        .admin-table-search-wrap{position:relative}.admin-table-search-wrap>i{position:absolute;top:50%;left:13px;color:#9ca3af;transform:translateY(-50%)}.admin-table-search-wrap .form-control{height:40px;padding-left:37px;border-color:#d7dce3;border-radius:7px;font-size:13px}
        .admin-table-search-submit{min-width:120px;height:40px;display:inline-flex;align-items:center;justify-content:center;gap:7px;border:1px solid #4f46e5;border-radius:7px;background:#4f46e5;color:#fff;font-size:13px;font-weight:600}
        .admin-table-action{width:34px;height:34px;display:inline-flex!important;align-items:center;justify-content:center;padding:0!important;vertical-align:middle}.admin-table-actions form{vertical-align:middle}
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
    <div class="card-primary bg-white border rounded-lg-custom category-main-body">
        <div class="card-body p-0">
            <div class="p-3 pb-0">
                <form method="GET" action="{{ route('admin.category.index') }}" class="admin-table-search">
                    <h5><i class="fas fa-search mr-2 text-primary"></i>Find categories</h5>
                    <p>Search categories, filter them by attached tours, and choose rows per page.</p>
                    <div class="row">
                        <div class="col-lg-6 mb-3 mb-lg-0">
                            <label for="categorySearch">Category search</label>
                            <div class="admin-table-search-wrap">
                                <i class="fas fa-search"></i>
                                <input type="search" id="categorySearch" name="search" value="{{ request('search') }}" class="form-control" placeholder="Category name" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-3 mb-3 mb-lg-0">
                            <label for="categoryTours">Find by tours</label>
                            <select id="categoryTours" name="has_tours" class="form-control" style="height:40px">
                                <option value="">All categories</option>
                                <option value="1" {{ request('has_tours') === '1' ? 'selected' : '' }}>Has Tours</option>
                                <option value="0" {{ request('has_tours') === '0' ? 'selected' : '' }}>No Tours</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label for="categoryPerPage">Per page</label>
                            <select id="categoryPerPage" name="per_page" class="form-control" style="height:40px">
                                @foreach ([10, 20, 25, 50, 100] as $number)
                                    <option value="{{ $number }}" {{ (int) request('per_page', 20) === $number ? 'selected' : '' }}>{{ $number }} per page</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="text-right mt-3">
                        <a href="{{ route('admin.category.index') }}" class="btn btn-secondary mr-2">Reset</a>
                        <button type="submit" class="admin-table-search-submit"><i class="fas fa-search"></i> Apply filters</button>
                    </div>
                </form>
            </div>
            <div class="table-viewport">
                <table class="table table-striped" id="categoryTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Tours</th>
                            <th class="text-center">Actions</th>
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
                                <td width="400">
                                    <button class="btn btn-sm btn-info toggle-tours" data-id="{{ $cat->id }}">
                                        Show Tours
                                    </button>

                                    <div class="tour-list mt-2" id="tour-{{ $cat->id }}" style="display:none;">

                                        @if(!$cat->tours->isEmpty())
                                            @foreach($cat->tours as $tour)
                                                <div>
                                                    <a class="alink" href="{{ $tour->slug }}" title="{{ $tour->title }}">
                                                        <i class="fas fa-chevron-right"></i> {{ $tour->title }}
                                                    </a>
                                                </div>
                                            @endforeach
                                        @else
                                          No tours
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center text-nowrap admin-table-actions">
                                    @can('edit_category')
                                    <a href="{{ route('admin.category.edit', encrypt($cat->id)) }}"
                                        class="btn btn-sm btn-edit admin-table-action"> <i class="far fa-edit"></i> </a>
                                    @endcan
                                    <button 
                                        class="btn btn-sm btn-warning clone-btn admin-table-action"
                                        data-id="{{ encrypt($cat->id) }}">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    @can('destroy_category')
                                    <form action="{{ route('admin.category.destroy', encrypt($cat->id)) }}" 
                                          method="POST" 
                                          class="delete-form d-inline">
                                        @method('DELETE')
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-danger delete-btn admin-table-action">
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
                <div class="p-3">
                    {{ $data->links() }}
                </div>
            </div>
        </div>
    </div>
    @section('js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#categoryTable').DataTable({paging:false,ordering:true,responsive:true,info:false,searching:false,dom:'rt'});

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
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                document.querySelectorAll('.toggle-tours').forEach(button => {

                    button.addEventListener('click', function () {

                        let id = this.dataset.id;
                        let tourList = document.getElementById('tour-' + id);

                        if (tourList.style.display === 'none') {
                            tourList.style.display = 'block';
                            this.textContent = 'Hide Tours';
                        } else {
                            tourList.style.display = 'none';
                            this.textContent = 'Show Tours';
                        }

                    });

                });

            });
        </script>
    @endsection
</x-admin>
