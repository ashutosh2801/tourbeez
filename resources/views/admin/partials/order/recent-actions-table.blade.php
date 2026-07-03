<div class="table-responsive">
    <table class="table" style="border: 1px solid #dee2e6;">
        <thead>
            <tr>
                <th>Date</th>
                <th>Subject</th>
            </tr>
        </thead>
        <tbody>
            @foreach($actions as $action)
                <tr>
                    <td>{{ $action->created_at }}</td>
                    <td>{!! $action->notes !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $actions->links() }}
</div>
