<x-admin>
@section('title','States')

<div class="row">
    <div class="col-lg-12">
        <div class="card-primary mb-3">
            <div class="card-header states-head">
                <div class="row">
                    <div class="col-md-8 col-6">
                        <h3 class="card-title">{{ translate('All States') }}</h3>
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
        <div class="card-primary bg-white border rounded-lg-custom states-body">
            <div class="card-body p-0">
                <div class="search-section">
                     <form class="" id="sort_states" action="" method="GET">
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
                            <th data-breakpoints="md">{{translate('Country')}}</th>
                            <th class="text-right" width="20%">{{translate('Options')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($states as $key => $state)
                            <tr>
                                <td>{{ ($key+1) + ($states->currentPage() - 1)*$states->perPage() }}</td>
                                <td>{{ ucwords($state->name) }}</td>
                                <td>{{ ucwords($state->country->name) }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.states.edit', encrypt($state->id)) }}" class="btn btn-circle btn-sm text-black text-lg" title="{{ translate('Edit') }}">
                                        <i class="las la-edit"></i>
                                    </a>
                                    <a href="javascript:void(0);" data-href="{{route('admin.states.destroy', $state->id)}}" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" title="{{ translate('Delete') }}">
                                        <i class="las la-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    {{ $states->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
    <!-- <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Add New State')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.states.store') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="name">{{translate('Country')}}</label>
                        <select class="form-control aiz-selectpicker" data-live-search="true" name="country_id" required>
                            @foreach($countries as $country)
                                <option value="{{$country->id}}">{{ ucwords($country->name) }}</option>
                            @endforeach
                        </select>
                        @error('country')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="name">{{translate('State Name')}}</label>
                        <input type="text" id="name" name="name" placeholder="{{ translate('State Name') }}"
                               class="form-control" required>
                       @error('name')
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
                    <div class="col-md-8 col-9">
                        <h6 class="m-0">{{translate('Add New State')}}</h6>
                    </div>
                    <div class="col-md-4 col-3">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                
            </div>
            <div class="card-body">
                <form action="{{ route('admin.states.store') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="name">{{translate('Country')}}</label>
                        <select class="form-control aiz-selectpicker" data-live-search="true" name="country_id" required>
                            @foreach($countries as $country)
                                <option value="{{$country->id}}">{{ ucwords($country->name) }}</option>
                            @endforeach
                        </select>
                        @error('country')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="name">{{translate('State Name')}}</label>
                        <input type="text" id="name" name="name" placeholder="{{ translate('State Name') }}"
                               class="form-control" required>
                       @error('name')
                           <small class="form-text text-danger">{{ $message }}</small>
                       @enderror
                    </div>
                    <div class="form-group mb-3 text-right">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> {{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
  </div>
</div>

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script>
      function sort_states(el){
          $('#sort_states').submit();
      }
    </script>
@endsection
</x-admin>