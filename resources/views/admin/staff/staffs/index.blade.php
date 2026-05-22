@extends('admin.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{translate('All Staffs')}}</h1>
		</div>
		@can('add_staffs')
			<div class="col-md-6 text-md-right">
				<a href="{{ route('staffs.create') }}" class="btn btn-circle btn-primary">
					<span>{{translate('Add New Staffs')}}</span>
				</a>
			</div>
		@endcan
	</div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Staffs')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th width="10%">#</th>
                    <th>{{translate('Name')}}</th>
                    <th data-breakpoints="md">{{translate('Email')}}</th>
					<th data-breakpoints="md">{{translate('Phone')}}</th>
                    <th>{{translate('Role')}}</th>
                    <th class="text-right">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staffs as $key => $staff)
                    @if($staff->user != null)
                        <tr>
                            <td>{{ ($key+1) + ($staffs->currentPage() - 1)*$staffs->perPage() }}</td>
                            <td>{{$staff->user->first_name.' '.$staff->user->last_name}}</td>
                            <td>{{$staff->user->email}}</td>
							<td>{{$staff->user->phone}}</td>
                            <td>
								@if ($staff->role != null)
									{{$staff->role->name}}
								@endif
							</td>
                            <td class="text-right">
								@can('edit_staffs')
		                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{route('staffs.edit', encrypt($staff->id))}}" title="{{ translate('Edit') }}">
		                                <i class="las la-edit"></i>
		                            </a>
								@endcan
								@can('delete_staffs')
		                            

			                            <form action="{{ route('staffs.destroy', $staff->id) }}"
	                                      method="POST"
	                                      class="delete-form">
	                                    @method('DELETE')
	                                    @csrf
	                                    <button type="button" class="btn btn-danger delete-btn">
	                                        <i class="fas fa-trash-alt"></i>
	                                    </button>
	                                </form>
									@endcan
	                        </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $staffs->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
           

            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.delete-btn').forEach(button => {
                    button.addEventListener('click', function () {
                        let form = this.closest('form');

                        Swal.fire({
                            title: 'Are you sure?',
                            text: "This action cannot be undone!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });
            });
        </script>
    @endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
