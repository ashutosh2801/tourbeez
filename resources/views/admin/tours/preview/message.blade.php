<div class="card">
    <div class="card-primary">
        <div class="card-header">
            <h3 class="card-title">Messages</h3>            
        </div>
        <div class="card-body">           
            <div class="card">
                @include('admin.tours.preview.notification')
            </div>
            <div class="card">
                @include('admin.tours.preview.reminder')
            </div>
            <div class="card">
                @include('admin.tours.preview.followup')
            </div>
            <div class="card">
                @include('admin.tours.preview.payment_request')
            </div>
        </div>        
    </div>
</div>