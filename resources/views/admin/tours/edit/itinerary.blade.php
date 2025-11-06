<div class="card">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Itinerary</h3>
            <div class="card-tools"></div>
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
            <div class="card-body">

                @php
                    $ItineraryOptions = old('ItineraryOptions') 
                        ?? $data->itineraries?->sortBy('order')->values()->map(function ($item, $index) {
                            return [
                                'id'          => $item->id,
                                'title'       => $item->title,
                                'datetime'    => $item->datetime,
                                'address'     => $item->address,
                                'description' => $item->description,
                                'order'       => $item->order ?? ($index + 1),
                            ];
                        })->toArray() 
                        ?? [];

                    $count = count($ItineraryOptions);

                    if ($count == 0) {
                        $ItineraryOptions = old('ItineraryOptions', [[
                            'id'          => '',
                            'title'       => '',
                            'datetime'    => '',
                            'address'     => '',
                            'description' => '',
                            'order'       => 1
                        ]]);
                        $count = 1;
                    }
                @endphp

                <div class="card-body" id="ItineraryContainer">
                    @foreach ($ItineraryOptions as $index => $option) 
                    <div id="ItineraryRow_{{ $index }}" class="itinerary-row"> 
                        <input type="hidden" name="ItineraryOptions[{{ $index }}][id]" value="{{ old("ItineraryOptions.$index.id", $option['id']) }}" class="form-control" />

                        <div class="row align-items-start"> 
                            
                            <div class="col-lg-11">
                                <div class="form-group" style="background:#f5f5f5; border:1px solid #ccc; margin-bottom:10px; padding: 10px;">
                                    <label for="itinerary" class="form-label">Itinerary</label>
                                    <select class="form-control" data-live-search="true" onchange="fetchItinerary(this.value, {{ $index }})">
                                        <option value="">Select one</option>
                                        @foreach ($data->itineraryAll() as $item)
                                            <option value="{{ $item->id }}">{{ $item->title . ' - ' . $item->datetime }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-1 d-flex align-items-center justify-content-center">
                                <div class="drag-handle" style="cursor: move;">
                                    <i class="fa fa-grip-vertical fa-lg"></i>
                                </div>
                            </div>


                            <div class="col-lg-8">
                                <div class="form-group">
                                    <label for="itinerary_title_{{ $index }}" class="form-label">Title</label>
                                    <input type="text" name="ItineraryOptions[{{ $index }}][title]" id="itinerary_title_{{ $index }}" value="{{ old("ItineraryOptions.$index.title", $option['title']) }}" class="form-control" placeholder="Enter itinerary title">
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="itinerary_datetime_{{ $index }}" class="form-label">Time</label>
                                    <input type="text" name="ItineraryOptions[{{ $index }}][datetime]" id="itinerary_datetime_{{ $index }}" value="{{ old("ItineraryOptions.$index.datetime", $option['datetime']) }}" class="form-control" placeholder="Enter itinerary datetime">
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="itinerary_order_{{ $index }}" class="form-label">Order</label>
                                    <input type="number" name="ItineraryOptions[{{ $index }}][order]" id="itinerary_order_{{ $index }}" value="{{ old("ItineraryOptions.$index.order", $option['order']) }}" class="form-control itinerary-order">
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="itinerary_address_{{ $index }}" class="form-label">Address</label>
                                    <input type="text" name="ItineraryOptions[{{ $index }}][address]" id="itinerary_address_{{ $index }}" value="{{ old("ItineraryOptions.$index.address", $option['address']) }}" class="form-control" placeholder="Enter itinerary address">
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="itinerary_description_{{ $index }}" class="form-label"> Description</label>
                                    <textarea name="ItineraryOptions[{{ $index }}][description]" id="itinerary_description_{{ $index }}" class="form-control" placeholder="Enter description" rows="4">{{ old("ItineraryOptions.$index.description", $option['description']) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-sm btn-danger" onclick="removeItinerary({{ $index }})"><i class="fa fa-minus"></i></button>
                        <hr />
                    </div>
                    @endforeach
                </div>

                <div class="text-right">
                    <button type="button" onclick="addItinerary()" class="btn border-t-indigo-100 btn-outline">Add itinerary</button>
                </div>
            </div>

            <div class="card-footer" style="display:block">
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.pickups', encrypt($data->id)) }}" class="btn btn-secondary">Back</a>
                <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success">Save</button>
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.faqs', encrypt($data->id)) }}" class="btn btn-primary">Next</a>           
            </div>
            </form>
        </div>
    </div>
</div>

@section('js')
@parent
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
let itineraryCount = {{ ($count > 1) ? $count : 1 }}

function addItinerary() {
    const container = document.getElementById('ItineraryContainer');

    const newRow = document.createElement('div');
    newRow.classList.add('itinerary-row');
    newRow.setAttribute('id', `ItineraryRow_${itineraryCount}`);

    newRow.innerHTML = `
        <div class="row align-items-start"> 
            <div class="col-lg-1 d-flex align-items-center justify-content-center">
                <div class="drag-handle" style="cursor: move;">
                    <i class="fa fa-grip-vertical fa-lg"></i>
                </div>
            </div>

            <div class="col-lg-11">
                <div class="form-group" style="background:#f5f5f5; border:1px solid #ccc; margin-bottom:10px; padding: 10px;">
                    <label for="itinerary" class="form-label">Itinerary</label>
                    <select class="form-control" data-live-search="true" onchange="fetchItinerary(this.value, ${itineraryCount})">
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

            <div class="col-lg-2">
                <div class="form-group">
                    <label for="itinerary_datetime_${itineraryCount}" class="form-label">Time</label>
                    <input type="text" name="ItineraryOptions[${itineraryCount}][datetime]" id="itinerary_datetime_${itineraryCount}" value="" class="form-control" placeholder="Enter itinerary datetime">
                </div>
            </div>

            <div class="col-lg-2">
                <div class="form-group">
                    <label for="itinerary_order_${itineraryCount}" class="form-label">Order</label>
                    <input type="number" name="ItineraryOptions[${itineraryCount}][order]" id="itinerary_order_${itineraryCount}" value="${itineraryCount+1}" class="form-control itinerary-order">
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
                    <textarea name="ItineraryOptions[${itineraryCount}][description]" id="itinerary_description_${itineraryCount}" class="form-control" placeholder="Enter description" rows="4"></textarea>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-danger" onclick="removeItinerary(${itineraryCount})"><i class="fa fa-minus"></i></button>
        <hr>`;

    container.appendChild(newRow);
    itineraryCount++;

    refreshSortable();
}

function removeItinerary(id) {
    const row = document.getElementById(`ItineraryRow_${id}`);
    if (row) {
        row.remove();
        refreshOrder();
    }
}

function fetchItinerary(selectedValue, num) {
    let itinerary_id = selectedValue;
    $.post('{{ route('admin.itinerary.single') }}', {
        _token: '{{ csrf_token() }}',
        itinerary_id: itinerary_id
    }, function(data) {
        $(`#itinerary_title_${num}`).val(data.title);
        $(`#itinerary_datetime_${num}`).val(data.datetime);
        $(`#itinerary_address_${num}`).val(data.address);
        $(`#itinerary_description_${num}`).val(data.description);
    });
}

// --- Drag & Drop ---
function refreshOrder() {
    $('#ItineraryContainer .itinerary-row').each(function(index) {
        $(this).find('.itinerary-order').val(index + 1);
    });
}

function refreshSortable() {
    $("#ItineraryContainer").sortable({
        handle: ".drag-handle",
        update: function() {
            refreshOrder();
        }
    });
}

$(function() {
    refreshSortable();
    refreshOrder();
});
</script>
@endsection
