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
    @section('title', 'Edit User & Supplier Info')
    <div class="card card-primary bg-white border rounded-lg-custom">
        <div class="card-header edit-supplier-head">
            <div class="row">
                <div class="col-md-8 col-6">
                    <h3 class="card-title">Edit User & Supplier Info</h3>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card-tools">
                        <a href="{{ route('admin.supplier.index') }}" class="btn btn-sm btn-back">Back</a>
                    </div>
                </div>
            </div>
        </div>
        <form action="{{ route('admin.user.update', $user) }}" method="POST" enctype="multipart/form-data">
            <div class="card-body edit-supplier-body">
                @method('PUT')
                @csrf
                <input type="hidden" name="id" value="{{ $user->id }}">

                {{-- ===== USER BASIC INFO ===== --}}
                <div class="row">
                    <div class="col-lg-6">
                        <label>Name* </label>
                        <input type="text" class="form-control" name="name" required value="{{ $user->name }}">
                        <x-error>name</x-error>
                    </div>

                    <div class="col-lg-6">
                        <label>Email*</label>
                        <input type="email" class="form-control" name="email" required value="{{ $user->email }}">
                        <x-error>email</x-error>
                    </div>

                    <div class="col-lg-6">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password">
                        <x-error>password</x-error>
                    </div>

                    <div class="col-lg-6">
                        <label>Role*</label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="" disabled>Select role</option>
                            @foreach ($roles as $role)
                                @if($role->name != 'Super Admin')
                                    <option value="{{ $role->name }}" {{ $user->role == $role->name ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <x-error>role</x-error>
                    </div>
                    <div class="col-lg-6 mt-3">
                        <div class="form-group">
                            <label class="form-label d-block mb-2">Email Notification:</label>
                            <label class="toggle-switch">
                                <input type="checkbox" name="email_notification" value="1" {{ $user->email_notification ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="col-lg-6 mt-3">
                        <div class="form-group">
                            <label class="form-label d-block mb-2">Text Notification:</label>
                            <label class="toggle-switch">
                                <input type="checkbox" name="text_notification" value="1" {{ $user->text_notification ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                </div>

                {{-- ===== SUPPLIER INFO ===== --}}
                @php
                    $supplier = $user->supplier ?? null;
                @endphp
                <div id="supplier-section" style="display:none;">
                    <hr>
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
                                        @if($role->name != 'Super Admin')
                                            <option value="{{ $type }}" {{ $type == $supplier?->supplier_type ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                        <!-- <select class="w-full border border-gray-300 rounded-lg p-3 outline-none text-gray-700 text-base"><option value="">- Select -</option><option value="Tour Operator">Tour Operator</option><option value="Transportation">Transportation</option><option value="Hotel">Hotel</option><option value="Attraction">Attraction</option><option value="Restaurant">Restaurant</option><option value="Other">Other</option></select> -->

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
                        <select name="payment_method" class="form-control">
                            <option value="">Select Payment Method</option>

                            <option value="Bank Transfer"
                                {{ old('payment_method', $supplier?->payment_method) == 'Bank Transfer' ? 'selected' : '' }}>
                                Bank Transfer
                            </option>

                            <option value="PayPal"
                                {{ old('payment_method', $supplier?->payment_method) == 'PayPal' ? 'selected' : '' }}>
                                PayPal
                            </option>

                            <option value="Stripe"
                                {{ old('payment_method', $supplier?->payment_method) == 'Stripe' ? 'selected' : '' }}>
                                Stripe
                            </option>

                            <option value="Other"
                                {{ old('payment_method', $supplier?->payment_method) == 'Other' ? 'selected' : '' }}>
                                Other
                            </option>
                        </select>
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
                </div>
            
            @php
            $driver = $user->driver ?? null;
        @endphp

        @php
    $driver = $user->driver ?? null;
@endphp

        <div id="driver-section" class="col-12 mt-4" style="display:none;">
            <hr>
            <h4 class="mb-3">Driver Information</h4>

            <div class="row">

                <div class="col-lg-6">
                    <label>License Number</label>
                    <input type="text" name="license_number" class="form-control"
                        value="{{ old('license_number', $driver->license_number ?? '') }}">
                </div>

                <div class="col-lg-6">
                    <label>License Expiry</label>
                    <input type="date" name="license_expiry" class="form-control"
                        value="{{ old('license_expiry', $driver->license_expiry ?? '') }}">
                </div>

                <div class="col-lg-6">
                    <label>Aadhaar Number</label>
                    <input type="text" name="aadhaar_number" class="form-control"
                        value="{{ old('aadhaar_number', $driver->aadhaar_number ?? '') }}">
                </div>

                <div class="col-lg-6">
                    <label>Vehicle Type</label>
                    <select name="vehicle_type" class="form-control">
                        <option value="">Select</option>
                        <option value="Car" {{ old('vehicle_type', $driver->vehicle_type ?? '') == 'Car' ? 'selected' : '' }}>Car</option>
                        <option value="Tempo" {{ old('vehicle_type', $driver->vehicle_type ?? '') == 'Tempo' ? 'selected' : '' }}>Tempo</option>
                        <option value="Bus" {{ old('vehicle_type', $driver->vehicle_type ?? '') == 'Bus' ? 'selected' : '' }}>Bus</option>
                    </select>
                </div>

                <div class="col-lg-6">
                    <label>Vehicle Number</label>
                    <input type="text" name="vehicle_number" class="form-control"
                        value="{{ old('vehicle_number', $driver->vehicle_number ?? '') }}">
                </div>

                <div class="col-lg-6">
                    <label>Vehicle Model</label>
                    <input type="text" name="vehicle_model" class="form-control"
                        value="{{ old('vehicle_model', $driver->vehicle_model ?? '') }}">
                </div>

                <div class="col-lg-6">
                    <label>Vehicle Capacity</label>
                    <input type="number" name="vehicle_capacity" class="form-control"
                        value="{{ old('vehicle_capacity', $driver->vehicle_capacity ?? '') }}">
                </div>

                <div class="col-lg-6">
                    <label>Per Day Rate</label>
                    <input type="number" name="per_day_rate" class="form-control"
                        value="{{ old('per_day_rate', $driver->per_day_rate ?? '') }}">
                </div>

                <div class="col-lg-6 mt-3">
                    <label class="form-label d-block mb-2">Availability</label>
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_available" value="1"
                            {{ old('is_available', $driver->is_available ?? 1) ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="col-lg-6">
                    <label>City</label>
                    <input type="text" name="city" class="form-control"
                        value="{{ old('city', $driver->city ?? '') }}">
                </div>

                <div class="col-lg-12">
                    <label>Address</label>
                    <textarea name="address" class="form-control">{{ old('address', $driver->address ?? '') }}</textarea>
                </div>

            </div>
        </div>
        </div>

            <div class="card-footer bg-white justify-flex-end">
                <button class="btn btn-success m-0" type="submit"><i class="fas fa-save"></i> Save</button>
            </div>
        </form>
    </div>


<script>
document.addEventListener('DOMContentLoaded', function() {

    const roleSelect = document.getElementById('role');
    const supplierSection = document.getElementById('supplier-section');
    const driverSection = document.getElementById('driver-section');

    function toggleSections() {
        let role = roleSelect.value.toLowerCase();

        supplierSection.style.display = (role === 'supplier') ? 'block' : 'none';
        driverSection.style.display   = (role === 'driver') ? 'block' : 'none';
    }

    roleSelect.addEventListener('change', toggleSections);

    // IMPORTANT → run on load (edit case)
    toggleSections();
});
</script>

</x-admin>
