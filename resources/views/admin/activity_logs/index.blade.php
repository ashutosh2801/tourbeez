<x-admin>
    @section('title','Activity Logs')
    
    <style>
        .activity-list-item {
            padding:0; margin:0; list-style:none;
        }
        .activity-list-item li {
            margin-bottom: 10px; padding:5px 0; margin:0; list-style:none; display:block; border-bottom: 1px solid #e7e7e7;
        }
        .activity-list-item li span {
            display: inline-block; width: 120px;
        }
    </style>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header row gutters-5">
                    <div class="col text-center text-md-left">
                        <h5 class="mb-md-0 h6">{{ translate('Activity Logs') }}</h5>
                    </div>
                </div>
                <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th width="300">Action</th>
                            <th>Properties</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>
                                    <ul class="activity-list-item"> 
                                        <li><span>Log name</span> {{ $log->log_name }}</li>
                                        <li><span>Action</span> {{ $log->description }}</li>
                                        <li>
                                            <span>Caused by</span>
                                            @if($log->user)
                                            {{ $log->user->first_name ?? 'User ID: ' . $log->causer_id }}
                                            @else
                                            {{ $log->causer_id }}
                                            @endif
                                        </li>
                                        <li><span>Created At</span> {{ $log->created_at->format('Y-m-d H:i:s') }}</li>
                                    </ul>
                                </td>
                                <td>
                                    <!-- <pre>{{ json_encode($log->properties->toArray(), JSON_PRETTY_PRINT) }}</pre> -->
                                    <!-- <pre style="padding:0;margin:0"> -->
                                    @php
                                        $data = json_decode($log->properties, true);
                                        $attributes = !empty($data['attributes']) ? $data['attributes'] : $data;
                                    @endphp
                                    <ul class="activity-list-item">                                    
                                    @foreach ($attributes as $key => $value)
                                        @if( !is_array($key) && !is_array($value))
                                            <li><span>{{ ucfirst($key) }}</span> = {{ strip_tags($value) }} </li>
                                        @endif
                                    @endforeach
                                    </ul>
                                    <!-- </pre> -->
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">No activity logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                <div class="aiz-pagination">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin>
