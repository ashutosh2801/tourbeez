<x-admin>
    @section('title', 'Create Business Expense')

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h3 class="card-title">Create Business Expense</h3>

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

            <form action="{{ route('admin.business-expenses.store') }}"
                  method="POST">

                @csrf

                <div class="row">

                    <div class="form-group col-md-4">
                        <label>
                            Expense Date
                            <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="expense_date"
                               class="form-control"
                               value="{{ old('expense_date', date('Y-m-d')) }}"
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
                               value="{{ old('amount') }}"
                               placeholder="0.00"
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
                                    {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
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
                                    {{ old('tour_id') == $tour->id ? 'selected' : '' }}>
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
                               value="{{ old('vendor') }}"
                               placeholder="Vendor Name">

                    </div>

                </div>

                <div class="row">

                    <div class="form-group col-md-12">

                        <label>Description</label>

                        <textarea name="description"
                                  rows="5"
                                  class="form-control"
                                  placeholder="Enter description...">{{ old('description') }}</textarea>

                    </div>

                </div>

                <hr>

                <button type="submit"
                        class="btn btn-success">

                    Save Business Expense

                </button>

                <a href="{{ route('admin.business-expenses.index') }}"
                   class="btn btn-secondary">

                    Cancel

                </a>

            </form>

        </div>
    </div>

</x-admin>