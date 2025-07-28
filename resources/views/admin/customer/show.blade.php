<x-admin>
    @section('title', 'Show Customer')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $user->name }}</h3>
            <div class="card-tools"><a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-dark">Back</a></div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.user.update',$user) }}" method="POST">
                @method('PUT')
                @csrf
                <input type="hidden" name="id" value="{{ $user->id }}">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="name" class="form-label">Name:*</label>
                            <input type="text" class="form-control" name="name" required
                                value="{{ $user->name }}">
                                <x-error>name</x-error>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="Email" class="form-label">Email:*</label>
                            <input type="email" class="form-control" name="email" required
                                value="{{ $user->email }}">
                                <x-error>email</x-error>
                        </div>
                    </div>
                    
                    
                    <div class="col-lg-12">
                        <div class="float-right">
                            <!-- <button class="btn btn-primary" type="submit">Save</button> -->
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-admin>
