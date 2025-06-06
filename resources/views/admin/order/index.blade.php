<x-admin>
    @section('title', 'Orders list')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Orders</h3>
        </div>
        <div class="card-body">
            <table class="table table-striped" id="OrderTable">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Status</th>
                        <th>Tour</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.edit', encrypt($order->id) ) }}" class="alink">{{ $order->order_number }}</a></td>
                            <td>{!!  order_status($order->order_status) !!}</td>
                            <td>
                                @foreach ($order->orderTours as $order_tour)
                                <p><a href="{{ route('admin.tour.edit', encrypt($order->tour_id) ) }}" target="_blank" class="alink">{{ $order_tour->tour?->title }}</a></p>
                                @endforeach
                            </td>
                            <td><a href="{{ route('admin.user.edit', encrypt($order->user_id) ) }}" class="alink" target="_blank">{{ $order->user?->name }}</a> <br />{{ $order->user?->phonenumber }}</td>
                            <td>{{ price_format( $order->total_amount) }}</td>
                            <td>{{ date__format($order->created_at) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @section('js')
        <script>
            $(function() {
                $('#OrderTable').DataTable({
                    "paging": true,
                    "searching": true,
                    "ordering": true,
                    "responsive": true,
                });
            });
        </script>
    @endsection
</x-admin>
