<x-admin>
    @section('title','Update Pickup')
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card">
                <div class="card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Update Pickup</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.pickups.index') }}" class="btn btn-info btn-sm">Back</a>
                        </div>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="list-unstyled">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form class="needs-validation" novalidate action="{{ route('admin.pickups.store') }}" 
                    method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="form-group mb-10">
                                <label for="name">Pickup name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Enter pickup name" required value="{{ old('name') ?? $data->name }}">
                            </div>
                            @error('name')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                            
                            <div id="pickupLocationContainer">
                            @php
                            $pickupLocations = old('PickupLocations', $data->locations->map(function ($item) {
                                                return [
                                                    'id'         => $item->id,
                                                    'location'   => $item->location,
                                                    'address'    => $item->address,
                                                    'time'       => $item->time,
                                                    'additional' => $item->additional,
                                                ];
                                            })->toArray());
                            
                            $count = count($pickupLocations);
                            if($count == 0){
                                $pickupLocations = [ ['id' => '', 'location' => '', 'address' => '', 'time' => '', 'additional' => ''] ];
                                $count = 1;
                            }
                            @endphp
                            @foreach ($pickupLocations as $index => $option)

                            <input type="hidden" name="PickupLocations[{{ $index }}][id]" id="PickupLocations_id" 
                            value="{{ old("PickupLocations.$index.id", $option['id']) }}" class="form-control" />

                            <div style="background:#f5f5f5; border:1px solid #ccc; margin-bottom:10px; padding: 10px;">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <label for="pickup_location">Pickup location</label>
                                            <input type="text" class="form-control" id="pickup_location" name="PickupLocations[{{ $index }}][location]"
                                                placeholder="Enter pickup location" required value="{{ old("PickupLocations.$index.location", $option['location']) }}">
                                            @error("PickupLocations.$index.location")
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-md-5">
                                            <label for="pickup_address">Pickup address</label>
                                            <input type="text"  class="form-control autocomplete" id="pickup_address" name="PickupLocations[{{ $index }}][address]"
                                                placeholder="Enter pickup address" required value="{{ old("PickupLocations.$index.address", $option['address']) }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="pickup_time">Pickup time</label>
                                            <select class="form-control aiz-selectpicker" data-live-search="true" id="pickup_time" name="PickupLocations[{{ $index }}][time]">
                                                <option value="">Select one</option>
                                                @php
                                                $old_time = old("PickupLocations.$index.time", $option['time'])
                                                @endphp
                                                @for ($hour = 0; $hour <= 12; $hour++)
                                                    @foreach ([0, 30] as $minute)
                                                    @php
                                                        $time = \Carbon\Carbon::createFromTime($hour == 12 ? 12 : $hour, $minute);
                                                        $formatted = $time->format('h:i A');
                                                    @endphp
                                                    <option {{  (strtolower($old_time)==strtolower($formatted)) ? 'selected' : '' }} value="{{ $formatted }}">{{ $formatted }}</option>
                                                    @endforeach
                                                @endfor
                                            </select>
                                            @error('pickup_time')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="additional_information">Additional information</label>
                                    <textarea type="text" class="form-control" rows="3" id="additional_information" name="PickupLocations[{{ $index }}][additional]"
                                        placeholder="Enter additional information">{{ old("PickupLocations.$index.additional", $option['additional']) }}</textarea>
                                </div>
                                @error('additional_information')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>                                
                            @endforeach 

                            <div class="text-right form-group">
                                <button type="button" onclick="addPickupLocation()" class="btn border-t-indigo-100 btn-outline">Add pickup location</button>
                            </div>
                            
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary float-right">Save pickup location</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@section('js')
<script>
let pickupLocationCount = {{ old('PickupLocations') ? count(old('PickupLocations')) : 1 }}

function addPickupLocation() {
    const container = document.getElementById('pickupLocationContainer');

    const newRow = document.createElement('div');
    newRow.classList.add('align-items-end', 'mb-2');
    newRow.setAttribute('id', `pickupLocationRow_${pickupLocationCount}`);

    newRow.innerHTML = `
        <div style="background:#f5f5f5; border:1px solid #ccc; margin-bottom:10px; padding: 10px;">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-5">
                        <label for="pickup_location">Pickup location</label>
                        <input type="text" class="form-control" id="pickup_location" name="PickupLocations[${pickupLocationCount}][location]"
                            placeholder="Enter pickup location" required value="">
                    </div>
                    <div class="col-md-5">
                        <label for="pickup_address">Pickup address</label>
                        <input type="text"  class="form-control autocomplete" id="pickup_address" name="PickupLocations[${pickupLocationCount}][address]"
                            placeholder="Enter pickup address" required value="">
                    </div>
                    <div class="col-md-2">
                        <label for="pickup_time">Pickup time</label>
                        <select class="form-control aiz-selectpicker" data-live-search="true" name="pPickup[${pickupLocationCount}][time]" id="pickup_time">
                            <option value="">Select one</option>
                            @for ($hour = 0; $hour <= 12; $hour++)
                                @foreach ([0, 30] as $minute)
                                @php
                                    $time = \Carbon\Carbon::createFromTime($hour == 12 ? 12 : $hour, $minute);
                                    $formatted = $time->format('h:i A'); // 'h' = 12-hour with leading zero
                                @endphp
                                <option value="{{ $formatted }}">{{ $formatted }}</option>
                                @endforeach
                            @endfor
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="additional_information">Additional information</label>
                <textarea type="text" class="form-control" rows="3" id="additional_information" name="PickupLocations[${pickupLocationCount}][additional]"
                    placeholder="Enter additional information">{{ old('additional_information') }}</textarea>
            </div>
            <button type="button" class="btn btn-sm btn-danger text-right" onclick="removePickupLocation(${pickupLocationCount})"><i class="fa fa-minus"></i></button>
            </div>  
        </div> `;

    container.appendChild(newRow);
    pickupLocationCount++;
}

function removePickupLocation(id) {
    const row = document.getElementById(`pickupLocationRow_${id}`);
    if (row) {
        row.remove();
        pickupLocationCount--;
    }
}

</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_API_KEY') }}&libraries=places"></script>
<script>
    function initAllAutocompletes() {
        const inputs = document.querySelectorAll('.autocomplete');

        inputs.forEach((input) => {
            const autocomplete = new google.maps.places.Autocomplete(input, {
                types: ['geocode'],
                componentRestrictions: { country: "ca" }, // optional
            });

            autocomplete.addListener("place_changed", function () {
                const place = autocomplete.getPlace();
                console.log("Selected address:", place.formatted_address);
            });
        });
    }

    window.onload = initAllAutocompletes;
</script>
@endsection
</x-admin>
