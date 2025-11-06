<div class="card">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Itinerary</h3>
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
            <form class="needs-validation" novalidate action="{{ route('admin.tour.itinerary_update', $data->id) }}" method="POST"
            enctype="multipart/form-data">
                @method('PUT')
                @csrf
                @php
                $ItineraryOptions = old('ItineraryOptions') ?? $data->itineraries?->map(function ($item) {
                    return [
                        'id'          => $item->id,
                        'title'       => $item->title,
                        'datetime'    => $item->datetime,
                        'address'     => $item->address,
                        'description' => $item->description,
                    ];
                })->toArray() ?? [];
                            
                $count = count($ItineraryOptions);
                if($count == 0){
                    $ItineraryOptions = old('ItineraryOptions', [ ['id' => '', 'title' => '', 'datetime' => '', 'address' => '', 'description' => ''] ]);
                    $count = 1;
                }
                @endphp                

                <div class="card-body" id="ItineraryContainer">
                    @foreach ($ItineraryOptions as $index => $option) 

                    <div id="ItineraryRow_{{ $index }}"> 
                    <input type="hidden" name="ItineraryOptions[{{ $index }}][id]" id="ItineraryOptions_id" 
                    value="{{ old("ItineraryOptions.$index.id", $option['id']) }}" class="form-control" />

                    <div class="row">                    
                        <div class="col-lg-12">
                            <div class="form-group" style="background:#f5f5f5; border:1px solid #ccc; margin-bottom:10px; padding: 10px;">
                                <label for="itinerary_title" class="form-label">Itinerary</label>
                                <select class="form-control" data-live-search="true" id="itinerary" onchange="fetchItinerary(this.value, {{ $index }})">
                                    <option value="">Select one</option>
                                    @foreach ($data->itineraryAll() as $item)
                                    <option value="{{ $item->id }}">{{ $item->title . ' - ' . $item->datetime }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label for="itinerary_title" class="form-label">Title</label>
                                <input type="text" name="ItineraryOptions[{{ $index }}][title]" id="itinerary_title_{{ $index }}" value="{{ old("ItineraryOptions.$index.title", $option['title']) }}"
                                    class="form-control" placeholder="Enter itinerary title">
                                @error('itinerary_title')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="itinerary_time" class="form-label">Time</label>
                                <input type="text" name="ItineraryOptions[{{ $index }}][datetime]" id="itinerary_time_{{ $index }}" value="{{ old("ItineraryOptions.$index.datetime", $option['datetime']) }}"
                                    class="form-control" placeholder="Enter itinerary datetime">
                                @error('itinerary_time')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="itinerary_address" class="form-label">Address</label>
                                <input type="text" name="ItineraryOptions[{{ $index }}][address]" id="itinerary_address_{{ $index }}" value="{{ old("ItineraryOptions.$index.address", $option['address']) }}"
                                    class="form-control" placeholder="Enter itinerary address">
                                @error('itinerary_address')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="itinerary_description" class="form-label"> Description</label>
                                <textarea type="text" name="ItineraryOptions[{{ $index }}][description]" id="itinerary_description_{{ $index }}"
                                    class="form-control"  placeholder="Enter description" rows="4">{{ old("ItineraryOptions.$index.description", $option['description']) }}</textarea>
                                @error('itinerary_description')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <hr />
                    </div>
                    @endforeach
                </div>               
            
            </form>
        </div>
    </div>
</div>

@section('js')
@parent
<script>
let itineraryCount = {{ ($count > 1) ? $count : 1 }}

function addItinerary() {

    const container = document.getElementById('ItineraryContainer');

    const newRow = document.createElement('div');
    newRow.classList.add('align-items-end', 'mb-2');
    newRow.setAttribute('id', `ItineraryRow_${itineraryCount}`);

    newRow.innerHTML = `<div class="row">                    
        <div class="col-lg-12">
            <div class="form-group" style="background:#f5f5f5; border:1px solid #ccc; margin-bottom:10px; padding: 10px;">
                <label for="itinerary_title" class="form-label">Itinerary</label>
                <select class="form-control" data-live-search="true" id="itinerary"  onchange="fetchItinerary(this.value, ${itineraryCount})">
                    <option value="">Select one</option>
                    @foreach ($data->itineraryAll() as $item)
                    <option value="{{ $item->id }}">{{ $item->title . ' - ' . $item->datetime }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="form-group">
                <label for="itinerary_title_${itineraryCount}" class="form-label">Title</label>
                <input type="text" name="ItineraryOptions[${itineraryCount}][title]" id="itinerary_title_${itineraryCount}" value="" class="form-control" placeholder="Enter itinerary title">
            </div>
        </div>
        <div class="col-lg-4">
            <div class="form-group">
                <label for="itinerary_datetime_${itineraryCount}" class="form-label">Time</label>
                <input type="text" name="ItineraryOptions[${itineraryCount}][datetime]" id="itinerary_datetime_${itineraryCount}" value="" class="form-control" placeholder="Enter itinerary datetime">
            </div>
        </div>
        <div class="col-lg-12">
            <div class="form-group">
                <label for="itinerary_address_${itineraryCount}" class="form-label">Address</label>
                <input type="text" name="ItineraryOptions[${itineraryCount}][address]" id="itinerary_address_${itineraryCount}" value="" class="form-control" placeholder="Enter itinerary address">
            </div>
        </div>
        <div class="col-lg-12">
            <div class="form-group">
                <label for="itinerary_description_${itineraryCount}" class="form-label"> Description</label>
                <textarea type="text" name="ItineraryOptions[${itineraryCount}][description]" id="itinerary_description_${itineraryCount}" class="form-control" placeholder="Enter description" rows="4"></textarea>
            </div>
        </div>
    </div><button type="button" class="btn btn-sm btn-danger" onclick="removeItinerary(${itineraryCount})"><i class="fa fa-minus"></i></button><hr>`;

    container.appendChild(newRow);
    itineraryCount++;
}

function removeItinerary(id) {
    const row = document.getElementById(`ItineraryRow_${id}`);
    if (row) {
        row.remove();
        itineraryCount--;
    }
}

function fetchItinerary( selectedValue, num ) {
    let itinerary_id = selectedValue;
    $.post('{{ route('admin.itinerary.single') }}', {
        _token: '{{ csrf_token() }}',
        itinerary_id: itinerary_id
    }, function(data) {
        console.log(num, data);
        $(`#itinerary_title_${num}`).val(data.title);
        $(`#itinerary_datetime_${num}`).val(data.datetime);
        $(`#itinerary_address_${num}`).val(data.address);
        $(`#itinerary_description_${num}`).val(data.description);
    });
}
</script>
@endsection
