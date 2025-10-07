<x-admin>
@section('title','All uploaded files')
<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{translate('All uploaded files')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('admin.uploaded-files.create') }}" class="btn btn-primary">
				<span>{{translate('Upload New File')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
    <form id="sort_uploads" action="">
        <div class="card-header row gutters-5">
            <div class="col-md-3">
                <h5 class="mb-0 h6">{{translate('All files')}}</h5>
            </div>
            <div class="col-md-3 ml-auto mr-0">
                <select class="form-control form-control-xs aiz-selectpicker" name="sort" onchange="sort_uploads()">
                    <option value="newest" @if($sort_by == 'newest') selected="" @endif>{{ translate('Sort by newest') }}</option>
                    <option value="oldest" @if($sort_by == 'oldest') selected="" @endif>{{ translate('Sort by oldest') }}</option>
                    <option value="smallest" @if($sort_by == 'smallest') selected="" @endif>{{ translate('Sort by smallest') }}</option>
                    <option value="largest" @if($sort_by == 'largest') selected="" @endif>{{ translate('Sort by largest') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control form-control-xs" name="search" placeholder="{{ translate('Search your files') }}" value="{{ $search }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">{{ translate('Search') }}</button>
            </div>
        </div>
    </form>
    <div class="card-body">
    	<div class="row gutters-5">
			
    		@foreach($all_uploads as $key => $file)
    			@php
    				if($file->file_original_name == null){
    				    $file_name = translate('Unknown');
    				}else{
    					$file_name = $file->file_original_name;
						
	    			}
					
    			@endphp
		
    			<div class="col-auto w-140px w-lg-220px">
    				<div class="aiz-file-box">
    					<div class="dropdown-file" >
    						<a class="dropdown-link" data-toggle="dropdown">
    							<i class="la la-ellipsis-v"></i>
    						</a>
    						<div class="dropdown-menu dropdown-menu-right">
    							@if($file->type == 'youtube')
									<a href="javascript:void(0)" class="dropdown-item" onclick="imagedetailsInfo(this)" data-id="{{ $file->id }}" 
									data-name="{{ $file_name }}" data-url="{{ $file->file_original_name }}"  data-caption="{{ $file->caption }}" data-description="{{ $file->description }}">
	    								<i class="las la-info-circle mr-2"></i>
	    								<span>{{ translate('Image Info') }}</span>
	    							</a>

    							@else
	    							<a href="javascript:void(0)" class="dropdown-item" onclick="imagedetailsInfo(this)" data-id="{{ $file->id }}" 
									data-name="{{ $file_name }}" data-url="{{ url('/') . '/public/'.$file->file_name }}"  data-caption="{{ $file->caption }}" data-description="{{ $file->description }}">
	    								<i class="las la-info-circle mr-2"></i>
	    								<span>{{ translate('Image Info') }}</span>
	    							</a>

    							@endif
    							<a href="javascript:void(0)" class="dropdown-item" onclick="detailsInfo(this)" data-id="{{ $file->id }}">
    								<i class="las la-info-circle mr-2"></i>
    								<span>{{ translate('Details Info') }}</span>
    							</a>
    							<a href="{{ static_asset($file->file_name) }}" target="_blank" download="{{ $file_name }}.{{ $file->extension }}" class="dropdown-item">
    								<i class="la la-download mr-2"></i>
    								<span>{{ translate('Download') }}</span>
    							</a>
    						
    							@if($file->type == 'youtube')
    								<a href="javascript:void(0)" class="dropdown-item" onclick="copyUrl(this)" data-url="{{ $file->file_original_name }}">
    								<i class="las la-clipboard mr-2"></i>
    								<span>{{ translate('Copy Link') }}</span>
    							</a>
    							@else
    								<a href="javascript:void(0)" class="dropdown-item" onclick="copyUrl(this)" data-url="{{ static_asset($file->file_name) }}">
    								<i class="las la-clipboard mr-2"></i>
    								<span>{{ translate('Copy Link') }}</span>
    							</a>
    							@endif
    							<a href="javascript:void(0)" class="dropdown-item confirm-alert" data-href="{{ route('admin.uploaded-files.destroy', $file->id ) }}" data-target="#delete-modal">
    								<i class="las la-trash mr-2"></i>
    								<span>{{ translate('Delete') }}</span>
    							</a>
    						</div>
    					</div>
    					<div class="card card-file aiz-uploader-select c-default" title="{{ $file_name }}.{{ $file->extension }}">
    						<div class="card-file-thumb">
    							@if($file->type == 'image')
    								<img src="{{ static_asset($file->file_name) }}" class="img-fit">
    							@elseif($file->type == 'video')
    								<i class="las la-file-video"></i>
    							@elseif($file->type == 'youtube')
    								<img src="{{ $file->thumb_name }}" class="img-fit">
    							@else
    								<i class="las la-file"></i>
    							@endif
    						</div>
    						<div class="card-body">
    							<h6 class="d-flex">
    								<span class="text-truncate title">{{ $file_name }}</span>
    								<span class="ext">.{{ $file->extension }}</span>
    							</h6>
    							<p>{{ formatBytes($file->file_size) }}</p>
    						</div>
    					</div>
    				</div>
    			</div>
    		@endforeach
    	</div>
		<div class="aiz-pagination mt-3">
			{{ $all_uploads->appends(request()->input())->links() }}
		</div>
    </div>
</div>

<div id="delete-modal" class="modal fade">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Delete Confirmation') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mt-1">{{ translate('Are you sure to delete this file?') }}</p>
                <button type="button" class="btn btn-link mt-2" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <a href="" class="btn btn-primary mt-2 comfirm-link">{{ translate('Delete') }}</a>
            </div>
        </div>
    </div>
</div>
<div id="info-modal" class="modal fade">
	<div class="modal-dialog modal-dialog-right">
			<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title h6">{{ translate('File Info') }}</h5>
				<button type="button" class="close" data-dismiss="modal">
				</button>
			</div>
			<div class="modal-body c-scrollbar-light position-relative" id="info-modal-content">
				<div class="c-preloader text-center absolute-center">
            <i class="las la-spinner la-spin la-3x opacity-70"></i>
        </div>
			</div>
		</div>
	</div>
</div>
<div id="image-info-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-right" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.uploaded-files.add_image_info') }}" method="POST">
                @csrf
			
                <div class="modal-header">
                    <h5 class="modal-title h6">{{ translate('Image Info') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
				<input type="hidden" class="form-control" id="image_id" name="image_id">
                <div class="modal-body c-scrollbar-light position-relative" id="info-modal-content">
                    <div class="form-group">
                        <label for="image-title">Title</label>
                        <input type="text" class="form-control" id="image-title" name="image_title">
                    </div>

                    <div class="form-group">
                        <label for="caption">Caption</label>
                        <input type="text" class="form-control" id="caption" name="caption">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <!-- Image URL and Copy Button -->
                    <div class="form-group">
                        <label for="image-url">Image URL</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="image-url" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="copyImageUrl()">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Info</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('js')
	<script type="text/javascript">
			function imagedetailsInfo(e){
				var id = $(e).data('id')
				var name = $(e).data('name')
				var caption = $(e).data('caption')
				var description = $(e).data('description')
				var url = $(e).data('url')
				$('#image-info-modal').modal('show');
				$('#image_id').val(id);
				$('#image-title').val(name);
				$('#image-url').val(url);
				$('#caption').val(caption);
				$('#description').val(description);
		}
		function detailsInfo(e){
      $('#info-modal-content').html('<div class="c-preloader text-center absolute-center"><i class="las la-spinner la-spin la-3x opacity-70"></i></div>');
			var id = $(e).data('id')
			$('#info-modal').modal('show');
			$.post('{{ route('admin.uploaded-files.info') }}', {_token: TB.data.csrf, id:id}, function(data){
      	$('#info-modal-content').html(data);
				console.log(data);
			});
		}
		function copyUrl(e) {
			var url = $(e).data('url');
			var $temp = $("<input>");
		    $("body").append($temp);
		    $temp.val(url).select();
		    try {
			    document.execCommand("copy");
			    TB.plugins.notify('success', '{{ translate('Link copied to clipboard') }}');
			} catch (err) {
			    TB.plugins.notify('danger', '{{ translate('Oops, unable to copy') }}');
			}
		    $temp.remove();
		}
        function sort_uploads(el){
            $('#sort_uploads').submit();
        }
		function copyImageUrl() {
			var copyText = document.getElementById("image-url");
			copyText.select();
			copyText.setSelectionRange(0, 99999); // For mobile devices
			document.execCommand("copy");

			// Optional: alert or toast
			alert("Image URL copied to clipboard");
		}
	</script>
@endsection
</x-admin>

