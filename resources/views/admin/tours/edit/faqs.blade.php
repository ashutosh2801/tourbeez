<div class="card">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">FAQs</h3>
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
            <form class="needs-validation" novalidate action="{{ route('admin.tour.faq_update', $data->id) }}" method="POST"
            enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="card-body">

                @php
                $FaqOptions = old('FaqOptions', $data->faqs?->map(function ($item) {
                                                return [
                                                    'id'         => $item->id,
                                                    'question'   => $item->question,
                                                    'answer'     => $item->answer,
                                                ];
                                            })->toArray());
                            
                $count = count($FaqOptions);
                if($count == 0){
                    $FaqOptions = old('FaqOptions', [ ['id' => '', 'question' => '', 'answer' => ''] ]);
                    $count = 1;
                }
                @endphp

                <div id="FaqContainer">
                    @foreach ($FaqOptions as $index => $option) 

                    <div id="FaqRow_{{ $index }}"> 
                    <input type="hidden" name="FaqOptions[{{ $index }}][id]" id="FaqOptions_id" 
                    value="{{ old("FaqOptions.$index.id", $option['id']) }}" class="form-control" />

                    <div class="row">                    
                        <div class="col-lg-12">
                            <div class="form-group" style="background:#f5f5f5; border:1px solid #ccc; margin-bottom:10px; padding: 10px;">
                                <label for="faq_question" class="form-label">FAQs</label>
                                <select class="form-control" data-live-search="true" id="faq" onchange="fetchFaq(this.value, {{ $index }})">
                                    <option value="">Select one</option>
                                    @foreach ($data->faqAll() as $item)
                                    <option value="{{ $item->id }}">{{ $item->question }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label for="faq_question" class="form-label">Question</label>
                                <input type="text" name="FaqOptions[{{ $index }}][question]" id="faq_question_{{ $index }}" value="{{ old("FaqOptions.$index.question", $option['question']) }}"
                                    class="form-control" placeholder="Enter question">
                                @error('faq_question')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="faq_answer" class="form-label"> Answer</label>
                                <textarea type="text" name="FaqOptions[{{ $index }}][answer]" id="faq_answer_{{ $index }}"
                                    class="form-control"  placeholder="Enter answer" rows="4">{{ old("FaqOptions.$index.answer", $option['answer']) }}</textarea>
                                @error('faq_answer')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeFaq({{ $index }})"><i class="fa fa-minus"></i></button>
                    <hr />
                    </div>
                    @endforeach
                </div>
                <div class="text-right">
                    <button type="button" onclick="addFaq()" class="btn border-t-indigo-100 btn-outline">Add FAQ</button>
                </div>
            </div>
            <div class="card-footer" style="display:block">
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.itinerary', encrypt($data->id)) }}" class="btn btn-secondary">Back</a>
                <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success">Save</button>
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.inclusions', encrypt($data->id)) }}" class="btn btn-primary">Next</a>           
            </div>
            </form>
        </div>
    </div>
</div>

@section('js')
@parent
<script>
let faqCount = {{ ($count > 1) ? $count : 1 }}

function addFaq() {

    const container = document.getElementById('FaqContainer');

    const newRow = document.createElement('div');
    newRow.classList.add('align-items-end', 'mb-2');
    newRow.setAttribute('id', `FaqRow_${faqCount}`);

    newRow.innerHTML = `<div class="row">                    
        <div class="col-lg-12">
            <div class="form-group" style="background:#f5f5f5; border:1px solid #ccc; margin-bottom:10px; padding: 10px;">
                <label for="faq_question" class="form-label">FAQs</label>
                <select class="form-control" data-live-search="true" id="faq"  onchange="fetchFaq(this.value, ${faqCount})">
                    <option value="">Select one</option>
                    @foreach ($data->faqAll() as $item)
                    <option value="{{ $item->id }}">{{ $item->question }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="form-group">
                <label for="faq_question_${faqCount}" class="form-label">Question</label>
                <input type="text" name="FaqOptions[${faqCount}][question]" id="faq_question_${faqCount}" value="" class="form-control" placeholder="Enter question">
            </div>
        </div>
        <div class="col-lg-12">
            <div class="form-group">
                <label for="faq_answer_${faqCount}" class="form-label"> Answer</label>
                <textarea type="text" name="FaqOptions[${faqCount}][answer]" id="faq_answer_${faqCount}" class="form-control" placeholder="Enter answer" rows="4"></textarea>
            </div>
        </div>
    </div><button type="button" class="btn btn-sm btn-danger" onclick="removeFaq(${faqCount})"><i class="fa fa-minus"></i></button><hr>`;

    container.appendChild(newRow);
    faqCount++;
}

function removeFaq(id) {
    const row = document.getElementById(`FaqRow_${id}`);
    if (row) {
        row.remove();
        faqCount--;
    }
}

function fetchFaq( selectedValue, num ) {
    let faq_id = selectedValue;
    $.post('{{ route('admin.faq.single') }}', {
        _token: '{{ csrf_token() }}',
        faq_id: faq_id
    }, function(data) {
        console.log(num, data);
        $(`#faq_question_${num}`).val(data.question);
        $(`#faq_answer_${num}`).val(data.answer);
    });
}
</script>
@endsection
