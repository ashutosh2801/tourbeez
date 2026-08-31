<x-admin>
    @section('title','Business Expenses')

    <!-- HEADER -->
    <div class="extra-header card card-primary mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-md-8">
                    <h3 class="card-title">Business Expenses</h3>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('admin.business-expenses.create') }}" class="btn btn-success btn-sm">
                        + Create Expense
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- SEARCH -->
    <div class="extra-addon-body mb-3">
        <div class="card card-primary bg-white border rounded-lg-custom p-3">

            <form action="{{ route('admin.business-expenses.index') }}" method="GET">

                <div class="row">

                    <div class="col-md-2">
                        <label>Date</label>
                        <input type="date"
                               name="expense_date"
                               class="form-control"
                               value="{{ request('expense_date') }}">
                    </div>

                    <div class="col-md-2">
                        <label>Category</label>

                        <select name="category" class="form-control">
                            <option value="">All</option>

                            @foreach($categories as $slug => $label)

                                <option value="{{ $slug }}"
                                    {{ request('category') == $slug ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>

                            @endforeach

                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Partner</label>

                        <select name="partner_id" class="form-control">
                            <option value="">All</option>

                            @foreach($partners as $partner)
                                <option value="{{ $partner->id }}"
                                    {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                                    {{ $partner->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Tour</label>

                        <select name="tour_id" class="form-control">
                            <option value="">All</option>

                            @foreach($tours as $tour)
                                <option value="{{ $tour->id }}"
                                    {{ request('tour_id') == $tour->id ? 'selected' : '' }}>
                                    {{ $tour->title }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Vendor</label>

                        <input type="text"
                               name="vendor"
                               class="form-control"
                               placeholder="Vendor"
                               value="{{ request('vendor') }}">
                    </div>

                    <div class="col-md-2">
                        <label>&nbsp;</label>

                        <div class="d-flex column-gap-10">
                            <button class="btn btn-search flex-fill">
                                Search
                            </button>

                            <a href="{{ route('admin.business-expenses.index') }}"
                               class="btn btn-secondary">
                                Reset
                            </a>
                        </div>
                    </div>

                </div>

            </form>

        </div>
    </div>

    <!-- TABLE -->

    <div class="card card-primary bg-white border rounded-lg-custom">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-striped table-bordered">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Date</th>

                            <th>Category</th>

                            <th>Amount</th>

                            <th>Partner</th>

                            <th>Tour</th>

                            <th>Vendor</th>

                            <th>Description</th>

                            <th width="150">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($data as $expense)

                        <tr>

                            <td>{{ $expense->id }}</td>

                            <td>
                                {{ date('d M Y', strtotime($expense->expense_date)) }}
                            </td>

                            <td>
                                {{ ucwords($expense->category) }}
                            </td>

                            <td>
                                ${{ number_format($expense->amount,2) }}
                            </td>

                            <td>
                                {{ ucwords(optional($expense->partner)->name) }}
                            </td>

                            <td>
                                {{ optional($expense->tour)->title }}
                            </td>

                            <td>
                                {{ $expense->vendor }}
                            </td>

                            <td>
                                {{ Str::limit($expense->description,40) }}
                            </td>

                            <td>

                                <a href="{{ route('admin.business-expenses.edit', encrypt($expense->id)) }}"
                                   class="btn btn-primary btn-sm">
                                    Edit
                                </a>

                                <form action="{{ route('admin.business-expenses.destroy', encrypt($expense->id)) }}"
                                      method="POST"
                                      style="display:inline-block">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        onclick="return confirm('Delete this expense?')"
                                        class="btn btn-danger btn-sm">
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="9" class="text-center">

                                No business expenses found.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-3">

                {{ $data->withQueryString()->links() }}

            </div>

        </div>

    </div>

</x-admin>