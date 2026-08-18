<x-admin>
    @section('title','Vouchers List')

    <!-- HEADER -->
    <div class="extra-header card card-primary mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-md-8">
                    <h3 class="card-title">Vouchers List</h3>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('admin.vouchers.create') }}" class="btn btn-sm btn-success"> + Create New</a>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card card-primary bg-white border rounded-lg-custom">
        <div class="card-body p-3">
            @include('admin.partials.table-search', ['tableId' => 'voucherTable', 'title' => 'vouchers', 'placeholder' => 'Code, status, agent or reference'])
            <div class="table-responsive">
                <table class="table table-striped" id="voucherTable">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Value</th>
                            <th>Validity Date</th>
                            <th>Travel Date</th>
                            <th>Agent</th>
                            <th>Internal Reference</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vouchers as $voucher)
                            <tr>
                                <td>{{ $voucher->code }}</td>
                                <td>{{ $voucher->status }}</td>
                                <td>
                                    @if(str_contains($voucher->value_type, 'VALUE'))
                                        ${{ $voucher->voucherValue }}
                                    @elseif(str_contains($voucher->value_type, 'PERCENT'))
                                        {{ $voucher->value_percent }}%
                                    @endif
                                </td>
                                <td>{{ $voucher->issueDate }} - {{ $voucher->expiryDate }}</td>
                                <td>
                                    @if($voucher->travelFromDate && $voucher->travelToDate)
                                        {{ $voucher->travelFromDate }} - {{ $voucher->travelToDate }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $voucher->agent_name }}</td>
                                <td>{{ $voucher->internalReference }}</td>
                                <td class="text-center text-nowrap admin-table-actions">
                                    <a href="{{ route('admin.vouchers.edit', $voucher->id) }}" class="btn btn-sm btn-edit admin-table-action"><i class="far fa-edit"></i></a>
                                    <form action="{{ route('admin.vouchers.destroy', $voucher->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger admin-table-action" onclick="return confirm('Are you sure?')"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No vouchers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    @section('js')
        <script>
            $(document).ready(function() {
                var table = $('#voucherTable').DataTable({
                    "paging": false,
                    "ordering": true,
                    "responsive": true,
                    "info": false,
                    "lengthChange": false,
                    "dom": "rt"
                });

                // Optional: search box for table
                $('[data-table-search="voucherTable"]').on('input', 'input', function() {
                    table.search(this.value).draw();
                }).on('submit', function(e) { e.preventDefault(); });
            });
        </script>
    @endsection
</x-admin>
