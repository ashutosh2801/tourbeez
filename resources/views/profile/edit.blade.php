<x-admin>
    @section('title')
        {{ 'Edit Your Profile' }}
    @endsection
    <div class="container">
        <div class="p-3 mb-3">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-sm-12">
                    <div class="p-4 sm:p-8 bg-{{Auth::user()->mode}} shadow sm:rounded-lg mb-3">
                        <div class="max-w-xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-sm-12">
                    <div class="p-4 sm:p-8 bg-{{Auth::user()->mode}} shadow sm:rounded-lg mb-3">
                        <div class="max-w-xl">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>
                {{-- <div class="col-lg-12">
                    <div class="p-4 sm:p-8 bg-{{Auth::user()->mode}} shadow sm:rounded-lg mb-3">
                        <div class="max-w-xl">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </div>  --}}
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="p-4 sm:p-8 bg-{{Auth::user()->mode}} shadow sm:rounded-lg mb-3">
                        <div class="max-w-xl">
            {{-- ===== SUPPLIER INFO ===== --}}

            <form method="post" action="{{ route('admin.profile.suplier_update') }}" class="mt-6 space-y-6">
            @csrf
            @method('patch')
                @php
                    $supplier = $user->supplier ?? null;

                @endphp
                @if($user->role == 'Supplier')

                    <input type="text" name="user_id" class="form-control" value="{{ $user->id }}">
                    <h4 class="mt-4 mb-3">Supplier Information</h4>
                    <div class="row">
                        <div class="col-lg-6">
                            <label>Business Name</label>
                            <input type="text" name="business_name" class="form-control" value="{{ $supplier->business_name ?? '' }}">
                        </div>

                        <div class="col-lg-6">
                            <label>Supplier Type</label>
                            <!-- <input type="text" name="supplier_type" class="form-control" value="{{ $supplier->supplier_type ?? '' }}"> -->


                            <select name="supplier_type" id="supplier_type" class="form-control" required>
                                <option value="" disabled>Supplier Type</option>
                                    @foreach (['Tour Operator','Transportation', 'Hotel', 'Attraction','Restaurant', 'Other' ] as $type)
                                        @if($user->role != 'Super Admin')
                                            <option value="{{ $type }}" {{ $type == $supplier->supplier_type ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>


                        <div class="col-lg-6">
                            <label>Business Registration Number</label>
                            <input type="text" name="business_registration_number" class="form-control" value="{{ $supplier->business_registration_number ?? '' }}">
                        </div>

                        <div class="col-lg-6">
                            <label>Year Established</label>
                            <input type="number" name="year_established" class="form-control" value="{{ $supplier->year_established ?? '' }}">
                        </div>

                        <div class="col-lg-6">
                            <label>Website URL</label>
                            <input type="url" name="website_url" class="form-control" value="{{ $supplier->website_url ?? '' }}">
                        </div>

                        <div class="col-lg-6">
                            <label>Social Links</label>
                            <input type="text" name="social_links" class="form-control" value="{{ $supplier->social_links ?? '' }}">
                        </div>

                        <div class="col-lg-6">
                            <label>Designation</label>
                            <input type="text" name="designation" class="form-control" value="{{ $supplier->designation ?? '' }}">
                        </div>

                        <div class="col-lg-6">
                            <label>Secondary Contact</label>
                            <input type="text" name="secondary_contact" class="form-control" value="{{ $supplier->secondary_contact ?? '' }}">
                        </div>

                        <div class="col-lg-12">
                            <label>Address</label>
                            <textarea name="address" class="form-control">{{ $supplier->address ?? '' }}</textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>Operating Locations</label>
                            <textarea name="operating_locations" class="form-control">{{ $supplier->operating_locations ?? '' }}</textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>Insurance Details</label>
                            <textarea name="insurance_details" class="form-control">{{ $supplier->insurance_details ?? '' }}</textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>License File</label>
                            <input type="file" name="license_file" class="form-control">
                            @if(!empty($supplier->license_file))
                                <small class="text-muted">Current: {{ $supplier->license_file }}</small>
                            @endif
                        </div>

                        <div class="col-lg-6">
                            <label>Certifications</label>
                            <textarea name="certifications" class="form-control">{{ $supplier->certifications ?? '' }}</textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>Payment Method</label>
                            <input type="text" name="payment_method" class="form-control" value="{{ $supplier->payment_method ?? '' }}">
                        </div>

                        <div class="col-lg-6">
                            <label>Bank Details</label>
                            <textarea name="bank_details" class="form-control">{{ $supplier->bank_details ?? '' }}</textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>Currency</label>
                            <input type="text" name="currency" class="form-control" value="{{ $supplier->currency ?? '' }}">
                        </div>

                        <div class="col-lg-6">
                            <label>Company Logo</label>
                            <input type="file" name="company_logo" class="form-control">
                            @if(!empty($supplier->company_logo))
                                <small class="text-muted">Current: {{ $supplier->company_logo }}</small>
                            @endif
                        </div>

                        <div class="col-lg-6">
                            <label>Service Images</label>
                            <textarea name="service_images" class="form-control">{{ $supplier->service_images ?? '' }}</textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>Promotional Offers</label>
                            <textarea name="promotional_offers" class="form-control">{{ $supplier->promotional_offers ?? '' }}</textarea>
                        </div>

                        

                        <div class="col-lg-6">
                            <label>Digital Signature</label>
                            <input type="text" name="digital_signature" class="form-control" value="{{ $supplier->digital_signature ?? '' }}">
                        </div>

                        <div class="col-lg-6">
                            <label>Submitted Date</label>
                            <input type="date" name="submitted_date" class="form-control" value="{{ $supplier->submitted_date ?? '' }}">
                        </div>
                        <div class="col-lg-3 mt-3">
                            <label>Consent Info</label>
                            <input type="checkbox" name="consent_info" value="1" {{ !empty($supplier->consent_info) ? 'checked' : '' }}>
                        </div>

                        <div class="col-lg-3 mt-3">
                            <label>Consent Terms</label>
                            <input type="checkbox" name="consent_terms" value="1" {{ !empty($supplier->consent_terms) ? 'checked' : '' }}>
                        </div>
                    </div>
                
                    <div class="flex items-center gap-4">
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('Save') }}</button>
                        @if (session('status') === 'profile-updated')
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ 'Saved' }}
                                <button type="button" class="btn btn-sm float-end float-right" data-bs-dismiss="alert"
                                    aria-label="Close">&times;</button>
                            </div>
                        @endif
                    </div>
            </form>
            </div>
            </div>
        </div>
                @endif

        </div>
    </div>
</x-admin>
