<div class="card">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Tour Optionals</h3>
            <div class="card-tools">
                <!-- <a href="{{ route('admin.addon.create') }}" class="btn btn-sm btn-info">Create New</a> -->
            </div>
        </div>
        <div class="card-body p-0">
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
                $optionalValue = old('optionalValue', $data->optionals?->map(function ($item, $index) {
                                                return [
                                                    'id'     => $item->id,
                                                    'name'   => $item->name,
                                                    'order'  => $item->sort_by ?? ($index + 1),
                                                ];
                                        })->filter()->values()->toArray());
                            
                $count = count($optionalValue);
                if($count == 0){
                    $optionalValue = old('optionalValue', [ ['id' => '', 'name' => '', 'type' => '', 'order' => 1] ]);
                    $count = 1;
                }
                @endphp

                @foreach ($optionalValue as $index => $option) 

                <div id="FeatureRow_{{ $index }}"> 
                    <input type="hidden" name="optionalValue[{{ $index }}][id]" id="optionalValue_id_{{ $index }}" 
                    value="{{ old("optionalValue.$index.id", $option['id']) }}" class="form-control" />

                    <div class="row">
                        <?php /* @if ($count == 1)                        
                        <div class="col-lg-12">
                            <div class="form-group" style="background:#f5f5f5; border:1px solid #ccc; margin-bottom:10px; padding: 10px;">
                                <label for="optional_name" class="form-label">Tour Optionals</label>
                                <select class="form-control" data-live-search="true" onchange="fetchOptional(this.value, {{ $index }})">
                                    <option value="">Select one</option>
                                    @foreach ($data->optionals as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif */ ?>

                        <div class="col-lg-1">
                            <div class="form-group">
                                <input type="number" name="optionalValue[{{ $index }}][order]" id="exclusion_order_{{ $index }}" value="{{ old("optionalValue.$index.order", $option['order']) }}"
                                    class="form-control text-center" min="1" placeholder="Enter order">
                                @error('exclusion_order')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-11">
                            <div class="form-group mb-2">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-pencil-alt"></i></span>
                                    </div>
                                    <input type="text" name="optionalValue[{{ $index }}][name]" id="optional_name_{{ $index }}" value="{{ old("optionalValue.$index.name", $option['name']) }}"
                                        class="form-control  mr-2" placeholder="Enter name">
                                    @if ( count($optionalValue) == ($index + 1))
                                    <button type="button" class="btn btn-sm btn-success mr-2" onclick="addOptional()"><i class="fa fa-plus"></i></button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeOptional({{ $index }})"><i class="fa fa-minus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <div id="optionalsContainer"></div>

            </div>
            <div class="card-footer" style="display:block">
                <div class="row">
                    <div class="col-md-6">
                        <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                    </div>
                    <div class="col-md-6 align-buttons">
                        <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.faqs', encrypt($data->id)) }}" class="btn btn-secondary"> <i class="fas fa-chevron-left"></i> Back</a>
                        <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.exclusions', encrypt($data->id)) }}" class="btn btn-secondary">Next <i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>

@section('js')
@parent
<script>
let optionalCount = {{ ($count > 1) ? $count : 1 }}

function addOptional() {

    const container = document.getElementById('optionalsContainer');

    const newRow = document.createElement('div');
    newRow.classList.add('align-items-end', 'mb-2');
    newRow.setAttribute('id', `FeatureRow_${optionalCount}`);

    newRow.innerHTML = `<hr><div class="row">                    
        <div class="col-lg-12">
            <div class="form-group" style="background:#f5f5f5; border:1px solid #ccc; margin-bottom:10px; padding: 10px;">
                <label for="optional_name" class="form-label">Tour Optionals</label>
                <select class="form-control aiz-selectpicker" data-live-search="true" id="optional"  onchange="fetchOptional(this.value, ${optionalCount})">
                    <option value="">Select one</option>
                    @foreach ($optionals as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <input type="hidden" name="optionalValue[${optionalCount}][type]" id="optionalValue_type_${optionalCount}" 
                    value="" class="form-control" />
        <div class="col-lg-1">
            <div class="form-group">
                <input type="number" name="optionalValue[${optionalCount}][order]" id="optionalValue_order_${optionalCount}" value="" class="form-control text-center" min="1" placeholder="number">
            </div>
        </div>                    
        <div class="col-lg-11">
            <div class="form-group mb-2">
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-pencil-alt"></i></span>
                    </div>
                    <input type="text" name="optionalValue[${optionalCount}][name]" id="optional_name_${optionalCount}" value=""
                        class="form-control mr-2" placeholder="Enter name" required>
                    <button type="button" class="btn btn-sm btn-success mr-2" onclick="addOptional()"><i class="fa fa-plus"></i></button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeOptional(${optionalCount})"><i class="fa fa-minus"></i></button>
                </div>
            </div>
        </div>
    </div>`;

    container.appendChild(newRow);
    $(`#optionalValue_order_${optionalCount}`).val( optionalCount + 1 );
    optionalCount++;
    TB.plugins.bootstrapSelect();
}

function removeOptional(id) {
    const row = document.getElementById(`FeatureRow_${id}`);
    if (row) {
        row.remove();
        optionalCount--;
    }
}

function fetchOptional( selectedValue, num ) {
    $.post('{{ route('admin.optionals.single') }}', {
        _token: '{{ csrf_token() }}',
        feature_id: selectedValue,
        type: 'optionals'
    }, function(data) {
        console.log(num, data);
        $(`#optional_name_${num}`).val(data.name);
        $(`#optional_type_${num}`).val(data.type);
    });
}
</script>
@endsection
