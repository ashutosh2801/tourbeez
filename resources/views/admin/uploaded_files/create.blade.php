<x-admin>
@section('title','Upload New File')

<div class="card-primary mb-3">
	<div class="card-header uploaded-file-head">
		<div class="row">
			<div class="col-md-8 col-12">
				<h3 class="card-title text-white">{{translate('Upload New File')}}</h3>
			</div>
			<div class="col-md-4 col-12">
				<div class="card-tools">
					<a href="{{ route('admin.uploaded-files.index') }}" class="btn btn-sm btn-secondary"> 
						<i class="las la-angle-left"></i> {{translate('Back to uploaded files')}}
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="card">
    <div class="card-primary bg-white border rounded-lg-custom">
    	<div id="aiz-upload-files" class="h-420px" style="min-height: 65vh">

    	</div>
    </div>
</div>

@section('js')
	<script type="text/javascript">
		$(document).ready(function() {
			TB.plugins.aizUppy();
		});
	</script>
@endsection
</x-admin>
