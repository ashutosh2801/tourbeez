<x-admin>
@section('title','Activity Timeline')

<style>
.timeline-item {
    padding: 15px;
    border-left: 4px solid #28a745;
    background: #f8f9fa;
    border-radius: 6px;
    margin-bottom: 12px;
}

.timeline-text {
    font-size: 14px;
}

.timeline-time {
    font-size: 12px;
    color: #777;
}
</style>

<div class="card">
    <div class="card-header">
        <h5>Activity Timeline (Readable)</h5>
    </div>

    <div class="card-body">

        @forelse($logs as $log)

            <div class="timeline-item">

                <!-- 🔥 Sentence -->
                <div class="timeline-text">
                    {{ activity_description($log) }}
                </div>

                <!-- 🔥 Meta -->
                <div class="timeline-time">
                    🕒 {{ $log->created_at->diffForHumans() }} 
                    ({{ $log->created_at->format('d M Y, h:i A') }})
                </div>

                <!-- 🔥 Order Info -->
                @if(optional($log->subject)->order_number)
                    <div class="timeline-time">
                        📦 Order: {{ $log->subject->order_number }}
                    </div>
                @endif

            </div>

        @empty
            <p>No activity found</p>
        @endforelse

        {{ $logs->links() }}

    </div>
</div>

</x-admin>