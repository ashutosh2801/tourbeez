<!-- Load jQuery and Bootstrap (must be placed before </body>) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Tab Pills -->
<div class="card">
    <div class="card-body">
        <!-- Nav pills -->
        <div class="nav nav-pills mb-3" id="pills-tab" role="tablist">
             <a class="nav-link active" id="v-pills-tab-102" data-toggle="pill" href="#seo" role="tab" aria-controls="v-pills-profile" aria-selected="false"><i class="fas fa-caret-right"></i>{{translate('SEO')}}</a>
            <a class="nav-link " id="v-pills-tab-102" data-toggle="pill" href="#seo-score" role="tab" aria-controls="v-pills-profile" aria-selected="false"><i class="fas fa-caret-right"></i>  {{translate('SEO SCORE')}}</a>
        </div>

        <!-- Tab content -->
        <div class="tab-content" id="pills-tabContent">
             <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="v-pills-profile-tab-101">
                    <div class="card">
                        @include('admin.tours.edit.info_seo')
                    </div>
                </div>
                <div class="tab-pane fade" id="seo-score" role="tabpanel" aria-labelledby="v-pills-profile-tab-101">
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
@endsection