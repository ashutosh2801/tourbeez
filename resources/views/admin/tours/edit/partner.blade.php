<div class="card">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Partner</h3>
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
            <form class="needs-validation" novalidate action="{{ route('admin.tour.partner_update', $data->id) }}" method="POST" enctype="multipart/form-data" autocomplete="off">
            @csrf
            <div class="card-body">
                <div class="row">                   
                <input type="hidden" name="tour_id" value="{{ $data->id }}" /> 

                    <div class="col-lg-12">
                        <div class="form-group">
                            @foreach (\App\Models\Partner::all() as $partner)  
                            <div style="background:#f5f5f5;border-radius:10px;border:1px solid #ccc;padding:10px;margin-bottom:20px">
                                <input id="{{ $partner->slug }}" type="hidden" name="partner_id[]" value="{{ $partner->id }}" /> 
                                <h2 style="font-size:25px;padding:0; margin:0 0 15px">{{ $partner->name }}</h2>

                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="title" class="form-label">Title</label>
                                        <input type="text" name="title[]" id="title"
                                            value="{{ $partner->tour?->title ?? $data->title }}"
                                            class="form-control" placeholder="Ex: Niagara Falls Day Tour" autocomplete="off">

                                        @error('title')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="link" class="form-label">Link</label>
                                        <div class="input-group">
                                            <input readonly type="text" name="link[]" id="{{'link_'.$partner->slug}}"
                                                value="https://tourbeez.com/{{ $partner->slug }}/tour/{{ $data->slug }}"
                                                class="form-control"
                                                placeholder="Ex: https://tourbeez.com/{{ $partner->slug }}/tour/{{ $data->slug }}" autocomplete="off">
                                            <button
                                                type="button"
                                                class="btn btn-outline-info"
                                                onclick="copyLink('{{'link_'.$partner->slug}}')"
                                            >
                                                Copy Link
                                            </button>    
                                        </div>
                                        @error('link')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            @endforeach                          
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
function copyLink(link) {
    const input = document.getElementById(link);

    if (!input.value) return;

    // Modern browsers
    if (navigator.clipboard) {
        navigator.clipboard.writeText(input.value).then(() => {
            alert("✅ Link copied to clipboard");
        });
    } else {
        // Fallback
        input.select();
        input.setSelectionRange(0, 99999);
        document.execCommand("copy");
        alert("✅ Link copied to clipboard");
    }
}
</script>
<script>
function generate_link(partner_slug) {
    document.getElementById('partner_tour').style.display = 'flex';
    let tour_slug = '{{ $data->slug }}';
    let tour_link = `https://tourbeez.com/${partner_slug}/tour/${tour_slug}`;
    document.getElementById('link').value = tour_link;
}   
</script>

@endsection
