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

    <!-- SEARCH BOX -->
    <div class="extra-addon-body mb-3">
        <div class="card card-primary bg-white border rounded-lg-custom p-3">
            <form class="row g-2 align-items-center" action="{{ route('admin.vouchers.index') }}" method="get">
                <div class="col-md-2">
                    <input type="text" name="Voucher[searchString]" class="form-control" placeholder="Voucher code..." value="{{ request('Voucher.searchString') }}">
                </div>
                <div class="col-md-2">
                    <input type="text" name="Voucher[internalReference]" class="form-control" placeholder="Internal Reference..." value="{{ request('Voucher.internalReference') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="Voucher[agentId]">
                        <option value="">All agents</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ request('Voucher.agentId') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control" name="Voucher[status]">
                        <option value="">All vouchers</option>
                        <option value="ISSUED" {{ request('Voucher.status') == 'ISSUED' ? 'selected' : '' }}>Issued</option>
                        <option value="REDEEMED" {{ request('Voucher.status') == 'REDEEMED' ? 'selected' : '' }}>Redeemed</option>
                        <option value="PARTIALLY_REDEEMED" {{ request('Voucher.status') == 'PARTIALLY_REDEEMED' ? 'selected' : '' }}>Partially redeemed</option>
                        <option value="EXPIRED" {{ request('Voucher.status') == 'EXPIRED' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex">
                    <button type="submit" class="btn btn-primary me-2">Search</button>
                    <a href="#" class="btn btn-white me-2">Export to CSV</a>
                    <a href="#" class="btn btn-danger" onclick="return confirm('Are you sure to delete all?')">Delete All</a>
                </div>
            </form>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card card-primary bg-white border rounded-lg-custom">
        <div class="card-body p-3">
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
                            <th>Actions</th>
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
                                <td>
                                    <a href="{{ route('admin.vouchers.edit', $voucher->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('admin.vouchers.destroy', $voucher->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
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

            <!-- Pagination -->
            <div class="mt-3">
                {{ $vouchers->withQueryString()->links() }}
            </div>
        </div>
    </div>

    @section('js')
        <script>
            $(document).ready(function() {
                var table = $('#voucherTable').DataTable({
                    "paging": true,
                    "ordering": true,
                    "responsive": true,
                    "info": true,
                    "lengthChange": false
                });

                // Optional: search box for table
                $('#voucherSearch').on('keyup', function() {
                    table.search(this.value).draw();
                });
            });
        </script>
    @endsection
</x-admin>
