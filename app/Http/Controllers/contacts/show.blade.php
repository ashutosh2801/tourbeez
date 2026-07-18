<x-admin>
    @section('title', 'View Contact')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Contact Details</h3>
            <div class="card-tools">
                <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm btn-dark">Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Name:</label>
                        <input type="text" class="form-control" value="{{ $contact->name }}" readonly>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Email:</label>
                        <input type="email" class="form-control" value="{{ $contact->email }}" readonly>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Phone:</label>
                        <input type="text" class="form-control" value="{{ $contact->phone }}" readonly>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="form-label">Message:</label>
                        <textarea class="form-control" rows="5" readonly>{{ $contact->message }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin>
