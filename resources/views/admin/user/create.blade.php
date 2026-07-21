<style>
    /* Toggle Switch Style */
.toggle-switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 32px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: all 0.4s ease;
  border-radius: 34px;
}

.slider::before {
  position: absolute;
  content: "";
  height: 24px;
  width: 24px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: all 0.4s ease;
  border-radius: 50%;
  box-shadow: 0 0 4px rgba(0,0,0,0.2);
}

.toggle-switch input:checked + .slider {
  background-color: #4caf50;
}

.toggle-switch input:checked + .slider::before {
  transform: translateX(28px);
}

</style>
<x-admin>
    @section('title', 'Create User')
    <div class="card card-primary bg-white border rounded-lg-custom">
        <div class="card-header create-supplier-head">
            <div class="row">
                <div class="col-md-8 col-6">
                    <h3 class="card-title">Create User</h3>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card-tools">
                        <a href="{{ route('admin.supplier.index') }}" class="btn btn-sm btn-back">Back</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body create-role-body">
            <form class="m-0" action="{{ route('admin.user.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    {{-- ================= BASIC USER INFO ================= --}}

                    <div class="col-lg-4">
                        <label>First Name* </label>
                        <input type="text" class="form-control" name="first_name" required value="{{ old('first_name') }}" placeholder="eg: John">
                        <x-error>first_name</x-error>
                    </div>

                    <div class="col-lg-4">
                        <label>Last Name* </label>
                        <input type="text" class="form-control" name="last_name" required value="{{ old('last_name') }}" placeholder="eg: Roy">
                        <x-error>last_name</x-error>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="Email" class="form-label">Email:*</label>
                            <input type="email" class="form-control" name="email" required value="{{ old('email') }}">
                            <x-error>email</x-error>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <label>Phone*</label>
                        <input type="text" class="form-control" name="phone" required value="{{ old('phone') }}" placeholder="eg: +1 416-456-1234">
                        <x-error>phone</x-error>
                    </div>

                    <div class="col-lg-4">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password">
                        <x-error>password</x-error>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="role" class="form-label">Role:*</label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="" selected disabled>Select the role</option>
                                @foreach ($roles as $role)
                                    @if($role->name != 'Super Admin')
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <x-error>role</x-error>
                        </div>
                    </div>
                    {{-- ================= NOTIFICATION TOGGLES ================= --}}
                    

                    <div class="col-lg-6 mt-3">
                        <div class="form-group">
                            <label class="form-label d-block mb-2">Email Notification:</label>
                            <label class="toggle-switch">
                                <input type="checkbox" name="email_notification" value="1" {{ old('email_notification') ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="col-lg-6 mt-3">
                        <div class="form-group">
                            <label class="form-label d-block mb-2">Text Notification:</label>
                            <label class="toggle-switch">
                                <input type="checkbox" name="text_notification" value="1" {{ old('text_notification') ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                

                    {{-- ================= SUPPLIER INFO SECTION ================= --}}
                    <div id="supplier-section" class="col-12 mt-4" style="display: none;">
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

                    {{-- ================= DRIVER INFO SECTION ================= --}}
                    <div id="driver-section" class="col-12 mt-4" style="display:none;">
                        <h4 class="mb-3">Driver Information</h4>

                        <div class="row">

                            {{-- ===== PERSONAL INFO ===== --}}
                            <div class="col-lg-6">
                                <label>License Number*</label>
                                <input type="text" name="license_number" class="form-control" value="{{ old('license_number') }}">
                            </div>

                            <div class="col-lg-6">
                                <label>License Expiry</label>
                                <input type="date" name="license_expiry" class="form-control" value="{{ old('license_expiry') }}">
                            </div>

                            <div class="col-lg-6">
                                <label>Govt ID</label>
                                <input type="text" name="govt_id" class="form-control" value="{{ old('govt_id') }}">
                            </div>

                            {{-- ===== VEHICLE INFO ===== --}}
                            <div class="col-lg-6">
                                <label>Vehicle Type</label>
                                <select name="vehicle_type" class="form-control">
                                    <option value="" disabled selected>Select Vehicle Type</option>
                                    <option value="Car">Car</option>
                                    <option value="Tempo">Tempo</option>
                                    <option value="Bus">Bus</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="col-lg-6">
                                <label>Vehicle Number</label>
                                <input type="text" name="vehicle_number" class="form-control" value="{{ old('vehicle_number') }}">
                            </div>

                            <div class="col-lg-6">
                                <label>Vehicle Model</label>
                                <input type="text" name="vehicle_model" class="form-control" value="{{ old('vehicle_model') }}">
                            </div>

                            <div class="col-lg-6">
                                <label>Vehicle Capacity</label>
                                <input type="number" name="vehicle_capacity" class="form-control" value="{{ old('vehicle_capacity') }}">
                            </div>

                            {{-- ===== DOCUMENTS ===== --}}
                            <div class="col-lg-6">
                                <label>License File</label>
                                <input type="file" name="license_file" class="form-control">
                            </div>

                            <div class="col-lg-6">
                                <label>RC File</label>
                                <input type="file" name="rc_file" class="form-control">
                            </div>

                            <div class="col-lg-6">
                                <label>Insurance File</label>
                                <input type="file" name="insurance_file" class="form-control">
                            </div>

                            {{-- ===== WORK INFO ===== --}}
                            <div class="col-lg-6">
                                <label>Per Day Rate</label>
                                <input type="number" name="per_day_rate" class="form-control" value="{{ old('per_day_rate') }}">
                            </div>

                            <div class="col-lg-6 mt-3">
                                <div class="form-group">
                                    <label class="form-label d-block mb-2">Availability:</label>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="is_available" value="1" {{ old('is_available') ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>

                            {{-- ===== LOCATION ===== --}}
                            <div class="col-lg-6">
                                <label>City</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                            </div>

                            <div class="col-lg-12 mt-3">
                                <label>Address</label>
                                <textarea name="address" class="form-control">{{ old('address') }}</textarea>
                            </div>

                        </div>
                    </div>

                    {{-- ================= SUBMIT BUTTON ================= --}}
                    <div class="card-footer">
                        <div class="float-right">
                            <button class="btn btn-success m-0" type="submit"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        
            document.addEventListener('DOMContentLoaded', function() {

                const roleSelect = document.getElementById('role');
                const supplierSection = document.getElementById('supplier-section');
                const driverSection = document.getElementById('driver-section');

                function toggleSections() {
                    let role = roleSelect.value;

                    supplierSection.style.display = (role === 'Supplier') ? 'block' : 'none';
                    driverSection.style.display   = (role === 'Driver') ? 'block' : 'none';
                }

                // Run on change
                roleSelect.addEventListener('change', toggleSections);

                // Run on page load (IMPORTANT for edit / old values)
                toggleSections();
            });
            
    </script>
</x-admin>