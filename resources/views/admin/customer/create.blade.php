<x-admin>
    @section('title', 'Create User')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create User</h3>
            <div class="card-tools"><a href="{{ route('admin.user.index') }}" class="btn btn-sm btn-dark">Back</a></div>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.user.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    {{-- NAME --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">Name:*</label>
                            <input type="text" class="form-control" name="name" required value="{{ old('name') }}">
                            <x-error>name</x-error>
                        </div>
                    </div>

                    {{-- EMAIL --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">Email:*</label>
                            <input type="email" class="form-control" name="email" required value="{{ old('email') }}">
                            <x-error>email</x-error>
                        </div>
                    </div>

                    {{-- PASSWORD --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">Password:*</label>
                            <input type="password" class="form-control" name="password" required>
                            <x-error>password</x-error>
                        </div>
                    </div>

                    {{-- ROLE --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">Role:*</label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="" disabled selected>Select role</option>
                                @foreach ($roles as $role)
                                    @if($role->name !== 'Super Admin')
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <x-error>role</x-error>
                        </div>
                    </div>

                    {{-- EMAIL NOTIFICATION --}}
                    <div class="col-lg-6 mt-3">
                        <div class="form-group">
                            <label class="form-label d-block mb-2">Email Notification:</label>
                            <label class="toggle-switch">
                                <input type="checkbox" name="email_notification" value="1">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    {{-- TEXT NOTIFICATION --}}
                    <div class="col-lg-6 mt-3">
                        <div class="form-group">
                            <label class="form-label d-block mb-2">Text Notification:</label>
                            <label class="toggle-switch">
                                <input type="checkbox" name="text_notification" value="1">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                </div>

                {{-- SUPPLIER SECTION --}}
                <div id="supplier-section" class="col-12 mt-4" style="display:none;">
                    <h4 class="mb-3">Supplier Information</h4>

                    <div class="row">
                        <div class="col-lg-6">
                            <label>Business Name*</label>
                            <input type="text" name="business_name" class="form-control">
                        </div>

                        <div class="col-lg-6">
                            <label>Supplier Type*</label>
                            <select name="supplier_type" class="form-control">
                                <option value="" disabled selected>Select Type</option>
                                @foreach (['Tour Operator','Transportation','Hotel','Attraction','Restaurant','Other'] as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-6">
                            <label>Business Registration Number</label>
                            <input type="text" name="business_registration_number" class="form-control">
                        </div>

                        <div class="col-lg-6">
                            <label>Year Established</label>
                            <input type="number" name="year_established" class="form-control">
                        </div>

                        <div class="col-lg-6">
                            <label>Website URL</label>
                            <input type="url" name="website_url" class="form-control">
                        </div>

                        <div class="col-lg-6">
                            <label>Social Media Links</label>
                            <input type="text" name="social_links" class="form-control">
                        </div>

                        <div class="col-lg-6">
                            <label>Designation</label>
                            <input type="text" name="designation" class="form-control">
                        </div>

                        <div class="col-lg-6">
                            <label>Secondary Contact</label>
                            <input type="text" name="secondary_contact" class="form-control">
                        </div>

                        <div class="col-lg-12">
                            <label>Address</label>
                            <textarea name="address" class="form-control"></textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>Operating Locations</label>
                            <textarea name="operating_locations" class="form-control"></textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>Insurance Details</label>
                            <textarea name="insurance_details" class="form-control"></textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>License File</label>
                            <input type="file" name="license_file" class="form-control">
                        </div>

                        <div class="col-lg-6">
                            <label>Certifications</label>
                            <textarea name="certifications" class="form-control"></textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>Payment Method</label>
                            <select name="payment_method" class="form-control">
                                <option value="">Select Payment Method</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="PayPal">PayPal</option>
                                <option value="Stripe">Stripe</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="col-lg-6">
                            <label>Bank Details</label>
                            <textarea name="bank_details" class="form-control"></textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>Currency</label>
                            <input type="text" name="currency" class="form-control" placeholder="e.g. USD, INR">
                        </div>

                        <div class="col-lg-6">
                            <label>Company Logo</label>
                            <input type="file" name="company_logo" class="form-control">
                        </div>

                        <div class="col-lg-6">
                            <label>Service Images</label>
                            <input type="file" name="service_images[]" class="form-control" multiple>
                        </div>

                        <div class="col-lg-6">
                            <label>Promotional Offers</label>
                            <textarea name="promotional_offers" class="form-control"></textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>Digital Signature / Name</label>
                            <input type="text" name="digital_signature" class="form-control">
                        </div>

                        <div class="col-lg-6">
                            <label>Date</label>
                            <input type="date" name="submitted_date" class="form-control">
                        </div>

                        <div class="col-lg-3 mt-3">
                            <label>
                                <input type="checkbox" name="consent_info" value="1">
                                Confirm information is accurate
                            </label>
                        </div>

                        <div class="col-lg-3 mt-3">
                            <label>
                                <input type="checkbox" name="consent_terms" value="1">
                                Agree to Terms & Conditions
                            </label>
                        </div>

                    </div>
                </div>

                {{-- SUBMIT --}}
                <div class="col-lg-12 mt-4">
                    <div class="float-right">
                        <button class="btn btn-primary" type="submit">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-admin>

{{-- REQUIRED SCRIPT --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log("CREATE PAGE JS LOADED");

    const role = document.getElementById('role');
    const supplier = document.getElementById('supplier-section');

    if (role) {
        role.addEventListener('change', function() {
            console.log("ROLE CHANGED TO:", this.value);

            if (this.value === 'Supplier') {
                supplier.style.display = 'block';
            } else {
                supplier.style.display = 'none';
            }
        });
    }
});
</script>
@endpush
