<x-admin>
@section('title', 'Internal Orders Create')
<form action="{{ route('admin.orders.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" id="order_id" value="">

    <div class="row">
        <div class="col-lg-12">
            <div class="accordion" id="accordionExample">

                {{-- Additional Information --}}
                <div class="card">
                    <div class="card-header bg-secondary py-0" id="heading4">
                        <h2 class="my-0 py-0">
                            <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0" data-toggle="collapse" data-target="#collapse4">
                                <i class="fa fa-angle-right"></i>Additional information
                            </button>
                        </h2>
                    </div>
                    <div id="collapse4" class="collapse show" aria-labelledby="heading4" data-parent="#accordionExample">
                        <div class="card-body">
                            <div id="tourContainer"></div>
                            <button type="button" onclick="addTour()" class="btn btn-outline-primary mb-2">Add Tour</button>
                        </div>
                    </div>
                </div>

                {{-- Payment Details --}}
                <div class="card">
                    <div class="card-header bg-secondary py-0" id="headingThree">
                        <h2 class="my-0 py-0">
                            <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0" data-toggle="collapse" data-target="#collapseThree">
                                <i class="fa fa-angle-right"></i> Payment Details
                            </button>                     
                        </h2>
                    </div>
                    <div id="collapseThree" class="collapse show" aria-labelledby="headingThree" data-parent="#accordionExample">
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <td>Total: <span id="totalAmount">0</span></td>
                                    <td>Balance: <span id="balanceAmount">0</span></td>
                                    <td>Paid: <span id="paidAmount">0</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Order Email History --}}
                <div class="card">
                    <div class="card-header bg-secondary py-0" id="headingEmail">
                        <h2 class="my-0 py-0">
                            <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0" data-toggle="collapse" data-target="#collapseEmail">
                                <i class="fa fa-angle-right"></i> Order Email History
                            </button>                     
                        </h2>
                    </div>
                    <div id="collapseEmail" class="collapse show" aria-labelledby="headingEmail" data-parent="#accordionExample">
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>To</th>
                                        <th>From</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5">No email history for new order</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Footer Buttons --}}
            <div class="card-footer" style="display:block">
                <button style="padding:0.6rem 2rem" type="submit" class="btn btn-success">Create Order</button>
                <a style="padding:0.6rem 2rem" href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>


@section('modal')
<!-- Order Email Modal -->
<div class="modal fade" id="order_template_modal" tabindex="1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="order_mail" method="POST">
                @csrf
                <input type="hidden" name="identifier" id="identifier">
                <div class="modal-body">
                    <div class="form-group">
                        <label>To</label>
                        <input type="text" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>CC Mail</label>
                        <input type="text" name="cc_mail" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>BCC Mail</label>
                        <input type="text" name="bcc_mail" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" id="subject" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="body" id="body" class="form-control aiz-text-editor" data-min-height="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Send Mail</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Order SMS Modal -->
<div class="modal fade" id="order_confirmation_sms" tabindex="2">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="order_sms_send" method="POST">
                @csrf
                <input type="hidden" name="identifier" id="identifier">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" name="mobile_number" id="mobile_number" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" id="message" class="form-control aiz-text-editor" data-min-height="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Send Sms</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Charge Modal -->
<div class="modal fade" id="chargeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Capture Payment</h5>
      </div>
      <div class="modal-body">
        <form id="chargeForm">
            <input type="hidden" id="chargeOrderId" name="order_id">
            <div class="mb-3">
                <label>Customer Name: <span id="customerName"></span></label>
            </div>
            <div class="mb-3">
                <label>Amount</label>
                <input type="text" id="chargeAmount" class="form-control" name="amount" required>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="submit" form="chargeForm" class="btn btn-primary">Charge</button>
      </div>
    </div>
  </div>
</div>
@endsection
@section('js')
<script>
let tourCount = 1;

function tourOptions() {
    let options = '';
    @foreach ($tours as $tour)
        options += `<option value="{{ $tour->id }}">{{ $tour->title }}</option>`;
    @endforeach
    return options;
}

function addTour() {
    const container = document.getElementById('tourContainer');
    const newRow = document.createElement('div');
    newRow.setAttribute('id', `row_${tourCount}`);
    newRow.innerHTML = `<div style="border:1px solid #ccc; margin-bottom:10px">
        <table class="table">
            <tr>
                <td>
                    <select onchange="loadOrderTour(this.value)" name="load_order_tour" 
                        class="form-control aiz-selectpicker" data-live-search="true" style="max-width: 500px">
                        <option value="">Select Tour</option>` 
                        + tourOptions() + 
                    `</select>
                </td>
            </tr>
        </table></div>`;
    container.appendChild(newRow);
    tourCount++;
}

function loadOrderTour(tour_id) {
    if(!tour_id) return;
    $.ajax({
        url: '{{ route('tour.single') }}',
        type: 'POST',
        data: { id: tour_id, tourCount: tourCount, _token: '{{ csrf_token() }}' },
        success: function(response) {
            $('#tourContainer').append(response);
        },
        error: function(xhr) {
            console.error(xhr.responseText);
        }
    });
}

// Email/SMS Modal
$(document).ready(function(){
    $('#order_mail').on('submit', function(e){
        e.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            url: "{{ route('admin.mail_send') }}",
            type: 'POST',
            data: formData,
            contentType: false, processData: false,
            success: function(response){
                $('.modal').modal('hide');
            }
        });
    });

    $('#order_sms_send').on('submit', function(e){
        e.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            url: "{{ route('admin.order_sms_send') }}",
            type: 'POST',
            data: formData,
            contentType: false, processData: false
        });
    });

    $('#chargeForm').on('submit', function(e){
        e.preventDefault();
        let orderId = $('#chargeOrderId').val();
        let amount  = $('#chargeAmount').val();
        $.ajax({
            url: "{{ route('admin.orders.charge', ['order' => '__ORDER_ID__']) }}".replace('__ORDER_ID__', orderId),
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', amount: amount },
            success: function(){
                $('#chargeModal').modal('hide');
                alert("Payment captured successfully!");
            }
        });
    });
});
</script>
@endsection
</x-admin>
