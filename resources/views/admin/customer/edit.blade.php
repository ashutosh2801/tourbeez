<x-admin>
    @section('title', 'Edit User')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $user->name }}</h3>
            <div class="card-tools"><a href="{{ route('admin.user.index') }}" class="btn btn-sm btn-dark">Back</a></div>
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
                    
                    
                    
                </div>

                @php
                    $orderCustomer = $user->customer;

                @endphp


                @if($orderCustomer ?? false)


                    <h4 class="mt-4 mb-3">Order Customer Details</h4>

                    <div class="row">
                        <div class="col-lg-6">
                            <label>First Name</label>
                            <input type="text" name="oc_first_name" class="form-control" value="{{ $orderCustomer->first_name }}">
                        </div>

                        <div class="col-lg-6">
                            <label>Last Name</label>
                            <input type="text" name="oc_last_name" class="form-control" value="{{ $orderCustomer->last_name }}">
                        </div>

                        <div class="col-lg-6">
                            <label>Email</label>
                            <input type="email" name="oc_email" class="form-control" value="{{ $orderCustomer->email }}">
                        </div>

                        <div class="col-lg-6">
                            <label>Phone</label>
                            <input type="text" name="oc_phone" class="form-control" value="{{ $orderCustomer->phone }}">
                        </div>

                        <div class="col-lg-12">
                            <label>Instructions</label>
                            <textarea name="oc_instructions" class="form-control">{{ $orderCustomer->instructions }}</textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>Pickup ID</label>
                            <textarea name="oc_pickup_id" class="form-control">{{ $orderCustomer->pickup?->location }}</textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>Pickup Name</label>
                            <textarea name="oc_pickup_name" class="form-control">{{ $orderCustomer->pickup_name }}</textarea>
                        </div>

                        <!-- <div class="col-lg-6">
                            <label>Stripe Customer ID</label>
                            <input type="text" name="oc_stripe_customer_id" class="form-control" value="{{ $orderCustomer->stripe_customer_id }}">
                        </div> -->
                    </div>
                    @endif

                    <div class="col-lg-12">
                        <div class="float-right">
                            <button class="btn btn-primary" type="submit">Save</button>
                        </div>
                    </div>
            </form>
        </div>
    </div>
</x-admin>
