<x-admin>
@section('title', 'Internal Orders Create')

<div class="card">
<form action="{{ route('admin.orders.store') }}" method="POST">
        @csrf
    <div class="card-header d-flex justify-content-between align-items-center bg-secondary p-3 mb-3 rounded text-black">
        <div class="form-group">
            <h4 class="m-0">New Order</h4>
            <small>Created by {{ auth()->user()->name }}</small>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center bg-secondary p-3 mb-3 rounded">
        <div>
            <strong id="totalDue">$0.00</strong><br>
            <small>Balance</small>
        </div>
        <div>
            <select name="Order[status]" class="form-control">
                <option value="CONFIRMED" selected>Confirmed</option>
                <option value="NEW">New</option>
                <option value="ON_HOLD">On Hold</option>
                <option value="PENDING_SUPPLIER">Pending Supplier</option>
                <option value="PENDING_CUSTOMER">Pending Customer</option>
                <option value="CANCELLED">Cancelled</option>
                <option value="ABANDONED_CART">Abandoned Cart</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary ">Create Order</button>
    </div>
    <div class="accordion" id="accordionExample">

        <!-- ================= Customer Details ================= -->
        <div class="card">
            <div class="card-header bg-secondary py-0" id="headingOne">
                <h2 class="my-0 py-0">
                    <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0" 
                        data-toggle="collapse" data-target="#collapseOne">
                        <i class="fa fa-angle-right"></i> Customer Details
                    </button>                                  
                </h2>
            </div>
            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                <div class="card-body">
                    <div class="form-group">
                        <label for="customer">Select Customer</label>
                        <select name="customer_id" id="customer" class="form-control aiz-selectpicker" data-live-search="true">
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->email }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= Tour Details ================= -->
        <div class="card">
            <div class="card-header bg-secondary py-0" id="headingTwo">
                <h2 class="my-0 py-0">
                    <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0" 
                        data-toggle="collapse" data-target="#collapseTwo">
                        <i class="fa fa-angle-down"></i> Tour Details
                    </button>
                </h2>
            </div>
            <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionExample">
                <div class="card-body">
                    <div id="tour_all"></div>
                    <div id="tourContainer"></div>
                    <button type="button" onclick="addTour()" class="btn btn-info mb-2">+ Add Tour</button>
                </div>
            </div>
        </div>

        <!-- ================= Additional Information ================= -->
        <div class="card">
            <div class="card-header bg-secondary py-0" id="headingFour">
                <h2 class="my-0 py-0">
                    <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0" 
                        data-toggle="collapse" data-target="#collapseFour">
                        <i class="fa fa-angle-right"></i> Additional Information
                    </button>
                </h2>
            </div>
            <div id="collapseFour" class="collapse show" aria-labelledby="headingFour" data-parent="#accordionExample">
                <div class="card-body">
                    <textarea class="form-control" name="additional_info" rows="4" placeholder="Additional information"></textarea>
                </div>
            </div>
        </div>

        <!-- ================= Payment Details ================= -->
        <div class="card">
            <div class="card-header bg-secondary py-0" id="headingThree">
                <h2 class="my-0 py-0">
                    <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0" 
                        data-toggle="collapse" data-target="#collapseThree">
                        <i class="fa fa-angle-right"></i> Payment Details
                    </button>                     
                </h2>
            </div>
            <div id="collapseThree" class="collapse show" aria-labelledby="headingThree" data-parent="#accordionExample">
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <td>Total:</td>
                            <td id="totalAmount">0</td>
                            <td>Balance:</td>
                            <td id="balanceAmount">0</td>
                            <td>Paid:</td>
                            <td id="paidAmount">0</td>
                        </tr>
                        <tr>
                            <td>Stored Credit Card:</td>
                            <td id="cardInfo">N/A</td>
                            <td>STRIPE Transaction ID:</td>
                            <td id="transactionId">N/A</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= Form Actions ================= -->
        <div class="card-footer" style="display:block">
            <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success">Create Order</button>
            <a style="padding:0.6rem 2rem" href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>

    </div>
 </form>
</div>


@section('js')
<script>
let tourCount = 1;

// ================= Tour Options =================
function tourOptions() {
    let options = '';
    @foreach($tours as $tour)
        options += `<option value="{{ $tour->id }}">{{ $tour->title }}</option>`;
    @endforeach
    return options;
}

// ================= Add Tour Row =================
function addTour() {
    const container = document.getElementById('tourContainer');
    const newRow = document.createElement('div');
    newRow.setAttribute('id', `row_${tourCount}`);

    newRow.innerHTML = `
    <div style="border:1px solid #ccc; margin-bottom:10px; padding:10px">
        <table class="table">
            <tr>
                <td>
                    <select onchange="loadTourDetails(this.value, ${tourCount})" name="tour_id[]" class="form-control aiz-selectpicker" data-live-search="true">
                        <option value="">Select Tour</option>` + tourOptions() + `</select>
                </td>
                <td>
                    <button type="button" onclick="removeTour('row_${tourCount}')" class="btn btn-danger">Remove</button>
                </td>
            </tr>
        </table>
        <div id="tour_details_${tourCount}"></div>
    </div>`;

    container.appendChild(newRow);
    TB.plugins.bootstrapSelect('refresh');
    tourCount++;
}

// ================= Remove Tour Row =================
function removeTour(id) {
    const row = document.getElementById(id);
    if(row) row.remove();
}

// ================= Load Single Tour Details =================
function loadTourDetails(tourId, count) {
    if(!tourId) return;

    $.ajax({
        url: '{{ route("tour.single") }}',
        type: 'POST',
        data: { id: tourId, tourCount: count, _token: '{{ csrf_token() }}' },
        success: function(response){
            $(`#tour_details_${count}`).html(response);
            TB.plugins.dateRange();
            TB.plugins.timePicker();
        },
        error: function(err){
            console.error(err);
        }
    });
}

// ================= Collapse Icons =================
$(document).ready(function(){
    $(".collapse.show").each(function(){
        $(this).prev(".card-header").find(".fa").addClass("fa-angle-down").removeClass("fa-angle-right");
    });

    $(".collapse").on('show.bs.collapse', function(){
        $(this).prev(".card-header").find(".fa").removeClass("fa-angle-right").addClass("fa-angle-down");
    }).on('hide.bs.collapse', function(){
        $(this).prev(".card-header").find(".fa").removeClass("fa-angle-down").addClass("fa-angle-right");
    });
});
</script>
@endsection

</x-admin>
