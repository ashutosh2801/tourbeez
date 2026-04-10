<x-admin>
    @section('title', 'Edit User')
    <form action="{{ route('admin.customers.source.update', ['id' => encrypt($user->id),'source' => $source]) }}" method="POST">
        <div class="card card-primary bg-white border rounded-lg-custom">
            <div class="card-header customer-edit-head">
                <div class="row">
                    <div class="col-md-8 col-6">
                        <h3 class="card-title">{{ $user->name }}</h3>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="card-tools">
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-back">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="card-body customer-edit-body">
                @method('PUT')
                @csrf
                <input type="hidden" name="id" value="{{ $user->id }}">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="name" class="form-label">Name *</label>
                            <input type="text" class="form-control" name="name" required value="{{ $user->name }}" readonly>
                            <x-error>name</x-error>
                        </div>
                    </div>
                </div>

                @php
                    $orderCustomer = $user->customer;

                    $orderCustomer = $orderCustomer?? $user;

                @endphp


                @if($orderCustomer ?? false)


                    <!-- <h4 class="mt-4 mb-3">Order Customer Details</h4> -->

                    <!-- <div class="row">
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
                            <input type="email" name="oc_email" class="form-control" value="{{ $orderCustomer->email }}" disabled>
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

                        <div class="col-lg-6">
                            <label>Stripe Customer ID</label>
                            <input type="text" name="oc_stripe_customer_id" class="form-control" value="{{ $orderCustomer->stripe_customer_id }}">
                        </div>
                    </div> -->

                @endif
            </div>

            @php
                $orderCustomer = $user->customer;
                $orderCustomer = $orderCustomer?? $user;
            @endphp
        </div>

        @if($orderCustomer ?? false)
            <div class="card card-primary bg-white border rounded-lg-custom customer-edit-body">
                <div class="card-header">
                    <h3 class="card-title">Order Customer Details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="oc_first_name" class="form-control" value="{{ $orderCustomer->first_name }}">
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="oc_last_name" class="form-control" value="{{ $orderCustomer->last_name }}">
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="oc_email" class="form-control" value="{{ $orderCustomer->email }}" readonly>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="oc_phone" class="form-control" value="{{ $orderCustomer->phone }}">
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Instructions</label>
                                <textarea name="oc_instructions" class="form-control">{{ $orderCustomer->instructions }}</textarea>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Pickup ID</label>
                                <select name="oc_pickup_id" class="form-control">
                                    <option value="">Select pickup</option>
                                    @foreach($pickupLocations as $pickuplocation)
                                        <option value="{{$pickuplocation->id}}" {{$orderCustomer->pickup_id == $pickuplocation->id ? 'selected' : ''}}>{{ $pickuplocation->location . " - " .  $pickuplocation->address}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Pickup Name</label>
                                <textarea name="oc_pickup_name" class="form-control">{{ $orderCustomer->pickup_name }}</textarea>
                            </div>
                        </div>
                    
                    </div>
                </div>
            </div>
        @endif


        <div class="card-footer bg-white border rounded-lg-custom">
            <div class="float-right">
                <button class="btn btn-success m-0" type="submit"><i class="fas fa-save"></i> Save</button>
            </div>
        </div>
    </form>
    

</x-admin>
