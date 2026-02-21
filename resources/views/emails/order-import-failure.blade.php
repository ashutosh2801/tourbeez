<h2>Order Import Failed Rows</h2>

<table border="1" cellpadding="5">
    <tr>
        <th>Order Number</th>
        <th>Reason</th>
    </tr>

    @foreach($failedRows as $row)
        <tr>
            <td>{{ $row['order_number'] }}</td>
            <td>{{ $row['reason'] }}</td>
        </tr>
    @endforeach
</table>
