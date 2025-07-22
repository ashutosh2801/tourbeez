<div class="card">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Booking</h3>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="list-unstyled">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form class="needs-validation" novalidate action="{{ route('admin.tour.booking_update', $data->id) }}" method="POST" enctype="multipart/form-data" autocomplete="off">
            @csrf
            <div class="card-body">
                <div class="row">                   

                    <div class="col-lg-8">
                        <div class="form-group">
                            <label class="form-label">Select Booking Type</label><br>
                            <input type="radio" name="booking_type" value="rezdy" {{ $data->detail?->booking_type == 'rezdy' ? 'checked' : '' }} >
                            <label for="rezdy_radio">Rezdy</label>
                            
                            <input type="radio" name="booking_type" value="TourBeez" {{ $data->detail?->booking_type == 'TourBeez' || $data->detail?->booking_type == '' ? 'checked' : '' }} style="margin-left: 20px;">
                            <label for="other_radio">TourBeez</label>

                            <input type="radio" name="booking_type" value="other" {{ $data->detail?->booking_type == 'other' ? 'checked' : '' }} style="margin-left: 20px;">
                            <label for="other_radio">Other Link</label>

                        </div>
                    </div>

                    <div class="col-lg-8" id="rezdy_input">
                        <div class="form-group">
                            <label for="booking_link" class="form-label">Rezdy code/link</label>
                            <input type="text" name="booking_link" id="booking_link"
                                value="{{ old('booking_link') ?? $data->detail?->booking_link }}"
                                class="form-control" placeholder="Ex: 987654" autocomplete="off">

                            @error('booking_link')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-8" id="other_input" style="display: none;">
                        <div class="form-group">
                            <label for="other_link" class="form-label">Other Link (Like GetYourGuide, etc)</label>
                            <input type="text" name="other_link" id="other_link"
                                value="{{ old('other_link') ?? $data->detail?->other_link }}"
                                class="form-control"
                                placeholder="Ex: https://www.getyourguide.com/..." autocomplete="off">

                            @error('other_link')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
 

                </div>
            </div>
            <div class="card-footer" style="display:block">
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.scheduling', encrypt($data->id)) }}" class="btn btn-secondary">Back</a>
                <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success">Save</button>
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.pickups', encrypt($data->id)) }}" class="btn btn-primary">Next</a>
            </div>
            </form>
        </div>
    </div>
</div>

@section('js')
@parent
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const rezdyRadio = document.getElementById("rezdy_radio");
        const otherRadio = document.getElementById("other_radio");
        const rezdyInput = document.getElementById("rezdy_input");
        const otherInput = document.getElementById("other_input");

        function toggleInputs() {
            if (rezdyRadio.checked) {
                rezdyInput.style.display = "block";
                otherInput.style.display = "none";
            } else {
                rezdyInput.style.display = "none";
                otherInput.style.display = "block";
            }
        }

        // Attach event listeners
        rezdyRadio.addEventListener("change", toggleInputs);
        otherRadio.addEventListener("change", toggleInputs);

        // Initial toggle on load
        toggleInputs();
    });
</script>

@endsection
