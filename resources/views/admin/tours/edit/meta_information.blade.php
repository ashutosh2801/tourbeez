<!-- Load jQuery and Bootstrap (must be placed before </body>) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Tab Pills -->
<div class="card">
    <div class="card-body">
        <!-- Nav pills -->
        <div class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <a class="nav-link active" id="seo-tab" data-toggle="pill" href="#seo" role="tab" aria-controls="seo" aria-selected="true">
                SEO
            </a>
            <a class="nav-link" id="seoScore-tab" data-toggle="pill" href="#seoScore" role="tab" aria-controls="seoScore" aria-selected="false">
                SEO Score
            </a>
        </div>

        <!-- Tab content -->
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                <div class="card">
                        @include('admin.tours.edit.seo')
                    </div>
            </div>
            <div class="tab-pane fade" id="seoScore" role="tabpanel" aria-labelledby="seoScore-tab">
                  <div class="card">
                        @include('admin.tours.edit.seo_score')
                    </div>
            </div>
        </div>
    </div>
</div>

@section('modal')

@endsection
@section('js')
<script>
$(document).ready(function () {
    $('#seo-tab').click(function (e) {
        e.preventDefault();
        $('#seo').addClass('show active');
        $('#seoScore').removeClass('show active');
        $(this).addClass('active');
        $('#seoScore-tab').removeClass('active');
    });

    $('#seoScore-tab').click(function (e) {
        e.preventDefault();
        $('#seoScore').addClass('show active');
        $('#seo').removeClass('show active');
        $(this).addClass('active');
        $('#seo-tab').removeClass('active');
    });
});
</script>

@endsection