<x-admin>
    @section('title', 'Orders list')
    <div class="card">

        <!-- Search Form (GET) -->
        <form method="GET" action="{{ route('admin.orders.index') }}">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <h3 class="card-title mb-0"></h3>
                    <div class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search Order or Customer" value="{{ request('search') }}" />
                        <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    </div>
                </div>
            </div>
        </form>

        <form id="bulkDeleteForm" method="POST" action="{{ route('admin.order.bulkDelete') }}">
        @csrf
        @method('DELETE')

        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center w-100">
                <h3 class="card-title mb-0">Orders</h3>
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure to delete selected orders?')">
                    Delete Selected
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped" id="OrderTable">
                <thead>
                    <tr>
                        <th><input style="width:15px; height:15px;" type="checkbox" id="checkAll" /></th>
                        <th width="150">Order Number</th>
                        <th>Status</th>
                        <th>Tour</th>
                        <th width="200">Customer</th>
                        <th>Amount</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td><input style="width:15px; height:15px;" type="checkbox" name="ids[]" value="{{ $order->id }}"></td>
                            <td><a href="{{ route('admin.orders.edit', encrypt($order->id) ) }}" class="alink">{{ $order->order_number }}</a></td>
                            <td>{!!  order_status($order->order_status) !!}</td>
                            <td>
                                @foreach ($order->orderTours as $order_tour)
                                <p><a href="{{ route('admin.tour.edit', encrypt($order_tour->tour_id) ) }}" target="_blank" class="alink">{{ $order_tour->tour?->title }}</a></p>
                                @endforeach
                            </td>
                            <td><a href="{{ route('admin.user.edit', encrypt($order->user_id) ) }}" class="alink" target="_blank">{{ $order->customer?->name }}</a> <br />{{ $order->customer?->phone }}</td>
                            <td>{{ $order->currency }}&nbsp;{{ price_format( $order->total_amount) }}</td>
                            <td>{{ date__format($order->created_at) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $orders->links() }}
        </div>
    </div>
    @section('js')
    <script>
        document.getElementById('checkAll').addEventListener('click', function () {
            const checkboxes = document.querySelectorAll('input[name="ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
    </script>
        <script>
            // $(function() {
            //     $('#OrderTable').DataTable({
            //         //"paging": true,
            //         "searching": true,
            //         //"ordering": true,
            //         "responsive": true,
            //     });
            // });
        </script>
    @endsection
</x-admin>
