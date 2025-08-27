<table>
    <thead>
        <tr>
            <th>Tour</th>
            <th>Slot Time</th>
            <th>Order #</th>
            <th>Customer</th>
            <th>Phone</th>
            <th>Guests</th>
            <th>Extras</th>
            <th>Balance</th>
            <th>Total</th>
            <th>Paid</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sessions as $session)
            @foreach ($session['orders'] as $order)
                <tr>
                    <td>{{ $session['title'] }}</td>
                    <td>{{ $session['slot_time'] }}</td>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->customer?->name }}</td>
                    <td>{{ $order->customer?->phone }}</td>
                    <td>{{ $order->guest_summary }}</td>
                    <td>{{ $order->extras_summary }}</td>
                    <td>{{ number_format($order->balance_amount, 2) }}</td>
                    <td>{{ number_format($order->total_amount, 2) }}</td>
                    <td>{{ number_format($order->paid_amount, 2) }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
