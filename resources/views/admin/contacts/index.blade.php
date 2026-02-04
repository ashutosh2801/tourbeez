<x-admin>
    @section('title', 'Contacts')
    <div class="card-primary mb-3">
        <div class="card-header banner-head">
            <div class="row">
                <div class="col-md-12 col-12">
                    <h5 class="card-title">Contacts</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="card-primary bg-white border rounded-lg-custom contacts-main-body">
        <div class="card-body p-0">
            <table class="table table-striped" id="contactTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $contact)
                        <tr>
                            <td>{{ $contact->id }}</td>
                            <td>{{ $contact->name }}</td>
                            <td>{{ $contact->email }}</td>
                            <td>{{ $contact->phone }}</td>
                            <td>{{ Str::limit($contact->message, 50) }}</td>
                            <td>{{ $contact->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.contacts.show', $contact->id) }}" class="btn btn-sm btn-info">View</a>
                            </td>
                            <!-- <td>
                                <form action="{{ route('admin.contacts.destroy', encrypt($contact->id)) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this contact?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td> -->
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @section('js')
        <script>
            $(function() {
                $('#contactTable').DataTable({
                    "paging": true,
                    "searching": true,
                    "ordering": true,
                    "responsive": true,
                });
            });
        </script>
    @endsection
</x-admin>
