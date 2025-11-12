<div class="card">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Tour Optionals</h3>
            <div class="card-tools">
                <!-- <a href="{{ route('admin.addon.create') }}" class="btn btn-sm btn-info">Create New</a> -->
            </div>
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
            <form class="needs-validation" novalidate action="{{ route('admin.tour.optional_update', $data->id) }}" method="POST"
            enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="card-body">
                
                @php
                $optionalValue = old('optionalValue', $data->optionals?->map(function ($item) {
                                                return [
                                                    'id'     => $item->id,
                                                    'name'   => $item->name,
                                                ];
                                        })->filter()->values()->toArray());
                            
                $count = count($optionalValue);
                if($count == 0){
                    $optionalValue = old('optionalValue', [ ['id' => '', 'name' => '', 'type' => ''] ]);
                    $count = 1;
                }
                @endphp

                @foreach ($optionalValue as $index => $option) 

                <div id="FeatureRow_{{ $index }}"> 
                    <input type="hidden" name="optionalValue[{{ $index }}][id]" id="optionalValue_id_{{ $index }}" 
                    value="{{ old("optionalValue.$index.id", $option['id']) }}" class="form-control" />

                    <div class="row">
                        @if ($count == 1)                        
                        <div class="col-lg-12">
                            <div class="form-group" style="background:#f5f5f5; border:1px solid #ccc; margin-bottom:10px; padding: 10px;">
                                <label for="include_name" class="form-label">Tour Inclusions</label>
                                <select class="form-control" data-live-search="true" onchange="fetchInclude(this.value, {{ $index }})">
                                    <option value="">Select one</option>
                                    @foreach ($data->optionals as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif

                        <div class="col-lg-12">
                            <div class="form-group mb-2">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-pencil-alt"></i></span>
                                    </div>
                                    <input type="text" name="optionalValue[{{ $index }}][name]" id="include_name_{{ $index }}" value="{{ old("optionalValue.$index.name", $option['name']) }}"
                                        class="form-control  mr-2" placeholder="Enter name">
                                    <button type="button" class="btn btn-sm btn-success mr-2" onclick="addInclude()"><i class="fa fa-plus"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeInclude({{ $index }})"><i class="fa fa-minus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <div id="includesContainer"></div>

            </div>
            <div class="card-footer"  style="display:block">
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.faqs', encrypt($data->id)) }}" class="btn btn-secondary">Back</a>
                <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success">Save</button>
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.exclusions', encrypt($data->id)) }}" class="btn btn-primary">Next</a>           
            </div>
            </form>
        </div>
    </div>
</div>

@section('js')
@parent
<script>
let includeCount = {{ ($count > 1) ? $count : 1 }}

function addInclude() {

    const container = document.getElementById('includesContainer');

    const newRow = document.createElement('div');
    newRow.classList.add('align-items-end', 'mb-2');
    newRow.setAttribute('id', `FeatureRow_${includeCount}`);

    newRow.innerHTML = `<hr><div class="row">                    
        <div class="col-lg-12">
            <div class="form-group" style="background:#f5f5f5; border:1px solid #ccc; margin-bottom:10px; padding: 10px;">
                <label for="include_name" class="form-label">Tour Inclusions</label>
                <select class="form-control" data-live-search="true" id="include"  onchange="fetchInclude(this.value, ${includeCount})">
                    <option value="">Select one</option>
                    @foreach ($data->optionals as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <input type="hidden" name="optionalValue[${includeCount}][type]" id="optionalValue_type_${includeCount}" 
                    value="" class="form-control" />
        <div class="col-lg-12">
            <div class="form-group mb-2">
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-pencil-alt"></i></span>
                    </div>
                    <input type="text" name="optionalValue[${includeCount}][name]" id="include_name_${includeCount}" value=""
                        class="form-control mr-2" placeholder="Enter name">
                    <button type="button" class="btn btn-sm btn-success mr-2" onclick="addInclude()"><i class="fa fa-plus"></i></button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeInclude(${includeCount})"><i class="fa fa-minus"></i></button>
                </div>
            </div>
        </div>
    </div>`;

    container.appendChild(newRow);
    includeCount++;
}

function removeInclude(id) {
    const row = document.getElementById(`FeatureRow_${id}`);
    if (row) {
        row.remove();
        includeCount--;
    }
}

function fetchInclude( selectedValue, num ) {
    $.post('{{ route('admin.optionals.single') }}', {
        _token: '{{ csrf_token() }}',
        feature_id: selectedValue,
        type: 'optionals'
    }, function(data) {
        console.log(num, data);
        $(`#include_name_${num}`).val(data.name);
        $(`#include_type_${num}`).val(data.type);
    });
}
</script>
@endsection
