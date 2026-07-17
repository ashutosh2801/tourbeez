<x-admin>
    @section('title', 'Edit Business Expense')

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h3 class="card-title">Edit Business Expense</h3>

            <a href="{{ route('admin.business-expenses.index') }}"
               class="btn btn-dark btn-sm">
                Back
            </a>
        </div>

        <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.business-expenses.update', encrypt($businessExpense->id)) }}"
                  method="POST">

                @csrf
                @method('PUT')

                <div class="row">

                    <div class="form-group col-md-4">
                        <label>
                            Expense Date
                            <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="expense_date"
                               class="form-control"
                               value="{{ old('expense_date', $businessExpense->expense_date) }}"
                               required>
                    </div>

                    <div class="form-group col-md-4">
                        <label>
                            Category
                            <span class="text-danger">*</span>
                        </label>

                        <select name="category"
                                class="form-control"
                                required>

                            <option value="">Select Category</option>

                            @foreach(business_expense_categories() as $slug => $label)

                                <option value="{{ $slug }}"
                                    {{ old('category', $businessExpense->category ?? '') == $slug ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>

                            @endforeach

                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label>
                            Amount
                            <span class="text-danger">*</span>
                        </label>

                        <input type="number"
                               name="amount"
                               step="0.01"
                               min="0"
                               class="form-control"
                               value="{{ old('amount', $businessExpense->amount) }}"
                               required>
                    </div>

                </div>

                <div class="row">

                    <div class="form-group col-md-6">

                        <label>Partner</label>

                        <select name="partner_id"
                                class="form-control">

                            <option value="">Select Partner</option>

                            @foreach($partners as $partner)

                                <option value="{{ $partner->id }}"
                                    {{ old('partner_id', $businessExpense->partner_id) == $partner->id ? 'selected' : '' }}>
                                    {{ $partner->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="form-group col-md-6">

                        <label>Tour</label>

                        <select name="tour_id"
                                class="form-control">

                            <option value="">Select Tour</option>

                            @foreach($tours as $tour)

                                <option value="{{ $tour->id }}"
                                    {{ old('tour_id', $businessExpense->tour_id) == $tour->id ? 'selected' : '' }}>
                                    {{ $tour->title }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

                <div class="row">

                    <div class="form-group col-md-12">

                        <label>Vendor</label>

                        <input type="text"
                               name="vendor"
                               class="form-control"
                               maxlength="255"
                               value="{{ old('vendor', $businessExpense->vendor) }}"
                               placeholder="Vendor Name">

                    </div>

                </div>

                <div class="row">

                    <div class="form-group col-md-12">

                        <label>Description</label>

                        <textarea name="description"
                                  rows="5"
                                  class="form-control"
                                  placeholder="Enter description...">{{ old('description', $businessExpense->description) }}</textarea>

                    </div>

                </div>

                <hr>

                <button type="submit"
                        class="btn btn-primary">
                    Update Business Expense
                </button>

                <a href="{{ route('admin.business-expenses.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>

            </form>

        </div>
    </div>

</x-admin>