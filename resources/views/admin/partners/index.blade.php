<x-admin>
@section('title','Partners')

<div class="row">
    <div class="col-lg-12">
        <div class="card-primary mb-3 partners-header">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8 col-6">
                        <h3 class="card-title">All Partners</h3>
                    </div>
                    <div class="col-md-4 col-6 text-right">
                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addModal">
                            + Add New
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-primary bg-white border rounded-lg-custom">
            <div class="card-body p-0">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Logo</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th class="text-right">Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($partners as $key => $partner)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>
                                    @if($partner->upload_id)
                                        <img src="{{ uploaded_asset($partner->upload_id) }}" height="45">
                                    @elseif($partner->logo_url)
                                        <img src="{{ $partner->logo_url }}" height="45">
                                    @endif
                                </td>
                                <td>{{ $partner->name }}</td>
                                <td>{{ $partner->slug }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.partners.edit',$partner->id) }}"
                                       class="btn btn-circle btn-sm text-black text-lg">
                                        <i class="las la-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-soft-danger btn-icon btn-circle btn-sm delete-partner"
                                            data-id="{{ $partner->id }}">
                                        <i class="las la-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="aiz-pagination">
                    {{ $partners->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade add-modal" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8 col-9">
                            <h6 class="m-0">Add New Partner</h6>
                        </div>
                        <div class="col-md-4 col-3">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.partners.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" id="name"  class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Slug</label>
                            <input type="text"
                                   name="slug"
                                   id="slug"
                                   class="form-control"
                                   placeholder="auto-generated-if-empty">
                        </div>

                        <div class="form-group">
                            <label>Logo Upload</label>
                            <div class="input-group input-group-sm"
                                 data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Browse</div>
                                </div>
                                <div class="form-control file-amount">Choose Logo</div>
                                <input type="hidden" name="upload_id"
                                       class="selected-files">
                            </div>
                            <div class="file-preview box"></div>
                        </div>

                        <div class="form-group">
                            <label>OR Logo URL</label>
                            <input type="text" name="logo_url"
                                   class="form-control"
                                   placeholder="https://example.com/logo.png">
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).on('click', '.delete-partner', function () {

    let partnerId = $(this).data('id');
    let url = "{{ route('admin.partners.destroy', ':id') }}";
    url = url.replace(':id', partnerId);

    Swal.fire({
        title: 'Are you sure?',
        text: "This partner will be soft deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    Swal.fire(
                        'Deleted!',
                        response.message,
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                },
                error: function () {
                    Swal.fire(
                        'Error!',
                        'Something went wrong.',
                        'error'
                    );
                }
            });

        }
    });
});
</script>

<script>
    $('#name').on('keyup', function () {
        let slug = $(this).val()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');

        $('#slug').val(slug);
    });
</script>
@endsection

</x-admin>
