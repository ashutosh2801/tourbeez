<x-admin>
@section('title','Countries')

<div class="row">
    <div class="col-lg-12">
        <div class="card-primary mb-3">
            <div class="card-header countries-head">
                <div class="row">
                    <div class="col-md-8">
                        <h3 class="card-title">{{ translate('All Countries') }}</h3>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addModal">
                                + Add New
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-primary bg-white border rounded-lg-custom countries-body">
            <div class="card-body p-0">
                <div class="search-section">
                     <form class="" id="sort_countries" action="" method="GET">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type name & Enter') }}">
                        </div>
                    </form>
                </div>
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{translate('Name')}}</th>
                            <th>{{ translate('Code') }}</th>
                            <th class="text-right" width="20%">{{translate('Options')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($countries as $key => $country)
                            <tr>
                                <td>{{ ($key+1) + ($countries->currentPage() - 1)*$countries->perPage() }}</td>
                                <td>{{ ucfirst($country->name) }}</td>
                                <td>{{ $country->iso3 }}</td>
                                <td class="text-right">
                                    <a href="javascript:void(0);" onclick="country_modal('{{ route('admin.countries.edit', encrypt($country->id) )}}')" class="btn btn-circle btn-sm text-black text-lg" title="{{ translate('Edit') }}">
                                        <i class="las la-edit"></i>
                                    </a>
                                    <a href="javascript:void(0);" data-href="{{route('admin.countries.destroy', $country->id)}}" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" title="{{ translate('Delete') }}">
                                        <i class="las la-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    {{ $countries->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
    <!-- <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Add New Country')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.countries.store') }}" method="POST" >
                    @csrf
                    <div class="form-group mb-3">
                        <label for="name">{{translate('Name')}}</label>
                        <input type="text" value="{{ old('name') }}" id="name" name="name" placeholder="{{ translate('Country Name') }}" class="form-control" required>
                         @error('name')
                             <small class="form-text text-danger">{{ $message }}</small>
                         @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="name">{{translate('Code')}}</label>
                        <input type="text" value="{{ old('code') }}" id="code" name="code" placeholder="{{ translate('Country Code') }}" class="form-control" required>
                         @error('code')
                             <small class="form-text text-danger">{{ $message }}</small>
                         @enderror
                    </div>
                    <div class="form-group mb-3 text-right">
                        <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div> -->
</div>


<!-- Add Modal -->
<div class="modal fade add-modal" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

        <div class="card-primary">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="m-0">{{translate('Add New Country')}}</h6>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                
            </div>
            <div class="card-body">
                <form action="{{ route('admin.countries.store') }}" method="POST" >
                    @csrf
                    <div class="form-group mb-3">
                        <label for="name">{{translate('Name')}}</label>
                        <input type="text" value="{{ old('name') }}" id="name" name="name" placeholder="{{ translate('Country Name') }}" class="form-control" required>
                            @error('name')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="name">{{translate('Code')}}</label>
                        <input type="text" value="{{ old('code') }}" id="code" name="code" placeholder="{{ translate('Country Code') }}" class="form-control" required>
                            @error('code')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                    </div>
                    <div class="form-group mb-3 text-right">
                        <button type="submit" class="btn btn-success"> <i class="fas fa-save"></i> {{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
  </div>
</div>


@section('modal')
    @include('modals.create_edit_modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script>

      function sort_countries(el){
          $('#sort_countries').submit();
      }

      function update_status(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('admin.countries.status') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Country status updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

      function country_modal(url){
          $.get(url, function(data){
              $('.create_edit_modal').modal('show');
              $('.create_edit_modal_content').html(data);
          });
      }
    </script>
@endsection
</x-admin>