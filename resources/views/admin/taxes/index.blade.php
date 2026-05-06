<x-admin>
    @section('title','Taxes and Fees')
    <div class="card-primary mb-3">
        <div class="card-header taxes-fee-head">
            <div class="row">
                <div class="col-md-8 col-12">
                    <h3 class="card-title text-white">Taxes and Fees</h3>
                </div>
                <div class="col-md-4 col-12">
                    <div class="card-tools">
                        <a href="{{ route('admin.taxes.create') }}" class="btn btn-sm btn-success">+ Create New</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-primary bg-white border rounded-lg-custom">
        <div class="card-body p-0">
            <table class="table table-striped" id="taxesTable">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)
                        <tr>
                            <td><a href="{{ route('admin.taxes.edit', encrypt($item->id)) }}" class="text-info text-sm">{{ $item->label }}</a></td>
                            <td>{{ $item->tax_fee_type }}</td>
                            <td>
                                {{
                                    ($item->fee_type == 'PERCENT' ? '' : 'CAD ') .
                                    number_format($item->tax_fee_value, 2) .
                                    ($item->fee_type == 'PERCENT' ? ' %' : '')
                                }}
                            </td>
                            <td width="60">
                                <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{ route('admin.taxes.edit', encrypt($item->id)) }}">
                                <i class="las la-edit"></i></a>
                            </td>                            
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

<!-- delete Modal -->
<div id="delete-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Delete Confirmation') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mt-1">{{ translate('Are you sure to delete this?') }}</p>
                <button type="button" class="btn btn-light mt-2" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <a id="delete-link" class="btn btn-danger mt-2">{{ translate('Delete') }}</a>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
    $(function() {
        $('#taxesTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "responsive": true,
        });
    });
</script>
@endsection
</x-admin>
