<x-admin>
    @section('title','Promo Codes')

    <!-- HEADER -->
    <div class="extra-header card card-primary">
        <div class="card-header">
            <div class="row">
                <div class="col-md-8">
                    <h3 class="card-title">Promo Codes</h3>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('admin.promos.create') }}" class="btn btn-sm btn-success"> + Create New</a>
                </div>
            </div>            
        </div>
    </div>

    <!-- BODY -->
    <div class="extra-addon-body">
        <div class="card card-primary bg-white border rounded-lg-custom">
            <div class="card-body p-3">

                <!-- SEARCH BOX -->
                <div class="mb-3">
                    <input type="text" id="promoSearch" class="form-control" placeholder="Search code...">
                </div>

                <!-- TABLE -->
                <table class="table table-striped" id="promoTable">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Value</th>
                            <th>Redemptions</th>
                            <th>Validity Date</th>
                            <th>Travel Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($promos as $promo)
                            <tr>
                                <td>{{ $promo->code }}</td>
                                <td>{{ $promo->status }}</td>
                                <td>
                                    @if(str_contains($promo->value_type, 'VALUE'))
                                        ${{ $promo->voucher_value }}
                                    @elseif(str_contains($promo->value_type, 'PERCENT'))
                                        {{ $promo->value_percent }}%
                                    @endif
                                </td>
                                <td>
                                    @if($promo->redemption_limit == 'UNLIMITED')
                                        Unlimited
                                    @else
                                        {{ $promo->max_uses }}
                                    @endif
                                </td>
                                <td>
                                    {{ $promo->issue_date }} - {{ $promo->expiry_date }}
                                </td>
                                <td>
                                    @if($promo->travel_from_date && $promo->travel_to_date)
                                        {{ $promo->travel_from_date }} - {{ $promo->travel_to_date }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.promos.edit', $promo->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('admin.promos.destroy', $promo->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger btn-delete"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    @section('js')
        <script>
            $(document).ready(function() {
                var table = $('#promoTable').DataTable({
                    "paging": true,
                    "ordering": true,
                    "responsive": true,
                    "info": true,
                    "lengthChange": false
                });

                // Search box
                $('#promoSearch').on('keyup', function() {
                    table.search(this.value).draw();
                });
            });
        </script>
    @endsection
</x-admin>
