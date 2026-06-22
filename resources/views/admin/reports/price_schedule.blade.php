<x-admin>
@section('title', 'Price Schedule')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<style>
    .table {
        font-size: 12px;
    }

    .table th, .table td {
        white-space: nowrap;
        padding: 6px 10px;
        vertical-align: middle;
    }

    .table-wrapper {
        overflow-x: auto;
        width: 100%;
    }

    .pagination {
        justify-content: center;
    }
        /* FULL FIX FOR SELECT2 HEIGHT */
    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid #aeb0b4 !important;
        border-radius: 0.375rem !important;
        display: flex !important;
        align-items: center !important;
    }

    /* TEXT FIX */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: normal !important;
        padding-left: 10px !important;
        color: #495057 !important;
    }

    /* PLACEHOLDER COLOR */
    .select2-container--default .select2-selection__placeholder {
        color: #6c757d !important;
    }

    /* ARROW ALIGNMENT */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        right: 10px !important;
    }

    /* FIX WHEN SELECTED (THIS IS YOUR BUG) */
    .select2-container--default.select2-container--open .select2-selection--single,
    .select2-container--default.select2-container--focus .select2-selection--single {
        height: 38px !important;
    }

    /* FORCE CONSISTENT HEIGHT ALWAYS */
    .select2-container {
        width: 100% !important;
    }
</style>

<div class="card-primary mb-3">
    <div class="card-header reports-head">
        <h3 class="card-title">Price Schedule Filters</h3>
    </div>
</div>

{{-- FILTERS --}}
<div class="card card-primary bg-white border rounded-lg-custom report-filter-box">
    <form method="GET">

           <div class="row">

                {{-- BOOKING DATE --}}
                @php
                    $hasFilter = request()->hasAny([
                        'booking_date',
                        'tour_date',
                        'product',
                        'order_status',
                        'payment_status',
                        'partner',
                        'action_type'
                    ]);
                @endphp
                <div class="col-xl-4 col-md-3 col-12 position-relative">
                    <div class="form-group">
                        <label class="filter-label">Booking Date</label>

                        <input 
                            type="text" 
                            name="booking_date"
                            
                            class="form-control aiz-date-range"
                            data-advanced-range="true"
                            data-separator=" - "
                            data-show-dropdown="true"
                            placeholder="Select date range"
                            autocomplete="off"
                            value="{{ request('booking_date') }}"
                        >

                        @if(request('booking_date'))
                            <span class="clear-btn" onclick="clearBooking()">✕</span>
                        @endif
                    </div>
                </div>


                {{-- TOUR DATE --}}
                <div class="col-xl-4 col-md-3 col-12 position-relative">
                    <div class="form-group">
                        <label class="filter-label">Tour Date</label>

                        <input 
                            type="text" 
                            name="tour_date"
                            
                            class="form-control aiz-date-range"
                            data-advanced-range="true"
                            data-separator=" - "
                            data-show-dropdown="true"
                            placeholder="Select date range"
                            autocomplete="off"
                            value="{{ request('tour_date') }}"
                        >

                        @if(request('tour_date'))
                            <span class="clear-btn" onclick="clearTour()">✕</span>
                        @endif
                    </div>
                </div>

                <!-- <div class="col-xl-3 col-md-3 col-12 position-relative">
                    <label class="filter-label">Booking Date</label>

                    <input type="text" id="booking_range" class="form-control"
                        placeholder="Select date range" autocomplete="off">

                    
                        <span class="clear-btn" onclick="clearBooking()">✕</span>
                    

                    <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                </div> -->


                {{-- TOUR DATE --}}
                <!-- <div class="col-xl-3 col-md-3 col-12 position-relative">
                    <label class="filter-label">     Date</label>

                    <input type="text" id="tour_range" class="form-control"
                        placeholder="Select date range" autocomplete="off">

                   
                        <span class="clear-btn" onclick="clearTour()">✕</span>
                   

                    <input type="hidden" name="tour_start_date" id="tour_start_date" value="{{ request('tour_start_date') }}">
                    <input type="hidden" name="tour_end_date" id="tour_end_date" value="{{ request('tour_end_date') }}">
                </div> -->

                <!-- <div class="col-xl-3 col-md-3 col-12 position-relative">
                    <label class="filter-label">Products</label>
                    <select id="productFilter" name="product" class="form-control"></select>
                </div>  -->

                {{-- PRODUCTS --}}
                <div class="col-xl-4 col-md-3 col-12 position-relative">
                    <div class="form-group">
                        <label class="filter-label">Products</label>
                        <select id="productFilter" name="product" class="form-control">
                            @if(request('product') && request('product_text'))
                                <option value="{{ request('product') }}" selected>
                                    {{ request('product_text') }}
                                </option>
                            @endif
                        </select>
                        <input type="hidden" id="product_text" name="product_text" value="{{ request('product_text') }}">
                    </div>
                </div> 

                {{-- ORDER STATUS --}}
                <div class="col-xl-3 col-md-3 col-12">
                    <label class="filter-label">Order Status</label>
                    <select name="order_status" class="form-control">
                        <option value="">All</option>
                        @php
                        $status_with_code = [
                                    
                                    3 => 'Pending supplier',
                                    4 => 'Pending customer',
                                    5 => 'Confirmed',
                                    
                            ];
                        @endphp
                        @foreach($status_with_code as $key => $val)
                            <option value="{{ $key }}"
                                {{ request('order_status') == $key ? 'selected' : '' }}>
                                {{ $val }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- PAY TYPE --}}
                <div class="col-xl-3 col-md-3 col-12">
                    <label class="filter-label">Pay Type</label>
                    <select name="action_type" class="form-control">
                        <option value="">All</option>
                        <option value="pay_now" {{ request('action_type')=='pay_now'?'selected':'' }}>Pay Now</option>
                        <option value="pay_later" {{ request('action_type')=='pay_later'?'selected':'' }}>Pay Later</option>
                    </select>
                </div>

                {{-- SOURCE --}}
                <div class="col-xl-3 col-md-3 col-12">
                    <label class="filter-label">Source</label>
                    <select name="partner" class="form-control">
                        <option value="">All</option>
                        @foreach($partners as $partner)
                            <option value="{{ ucfirst($partner->slug) }}"
                                {{ request('partner') == ucfirst($partner->slug) ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                        @endforeach
                        <option value="Tourbeez" {{ request('partner') == 'Tourbeez' ? 'selected' : '' }}>Tourbeez</option>
                        <option value="Internal" {{ request('partner') == 'Internal' ? 'selected' : '' }}>Internal</option>
                    </select>
                </div>

                {{-- BUTTONS --}}
                <div class="col-xl-3 col-md-3 col-12">
                    <div class="d-flex column-gap-10">
                        <button class="btn btn-apply flex-fill">Apply</button>
                        <a href="{{ route('admin.report.invoice.details') }}" class="btn btn-secondary flex-fill">Reset</a>
                    </div>
                </div>

            </div>
    </form>
</div>
@if(!request()->hasAny(['booking_date','tour_date','product','order_status','payment_status','partner','action_type']))
        <div class="alert alert-info">
            Please apply filters to view report data.
        </div>
    @endif
<div class="active-filters mb-3">
        @if(request()->hasAny([
            'booking_date','tour_date','product',
            'order_status','action_type','partner'
        ]))



            <div class="d-flex flex-wrap gap-2">

                {{-- BOOKING DATE --}}
                @if(request('booking_date'))
                    <span class="badge bg-dark">
                        Booking: {{ request('booking_date') }}
                        <a href="{{ request()->fullUrlWithQuery(['booking_date' => null]) }}" class="remove-filter" data-filter="booking_date"><span class="ml-2">✕</span></a>
                    </span>
                @endif

                {{-- TOUR DATE --}}
                @if(request('tour_date'))
                    <span class="badge bg-dark ml-2">
                        Tour: {{ request('tour_date') }}
                        <a href="{{ request()->fullUrlWithQuery(['tour_date' => null]) }}" class="remove-filter" data-filter="tour_date"><span class="ml-2">✕</span></a>
                    </span>
                @endif

                {{-- PRODUCT --}}
                @if(request('product'))
                    <span class="badge bg-dark  ml-2">
                        Product: {{ request('product_text') ?? request('product') }}
                        <a href="{{ request()->fullUrlWithQuery(['product' => null, 'product_text' => null]) }}" class="remove-filter" data-filter="product"><span class="ml-2">✕</span></a>
                    </span>
                @endif

                {{-- STATUS --}}
                @if(request('order_status'))
                    <span class="badge bg-dark  ml-2">
                        Status: {{ $status_with_code[request('order_status')] ?? request('order_status') }}
                        <a href="{{ request()->fullUrlWithQuery(['order_status' => null]) }}" class="remove-filter" data-filter="order_status"><span class="ml-2">✕</span></a>
                    </span>
                @endif

                {{-- PAY TYPE --}}
                @if(request('action_type'))
                    <span class="badge bg-dark ml-2">
                        Pay: {{ request('action_type') }}
                        <a href="{{ request()->fullUrlWithQuery(['action_type' => null]) }}" class="remove-filter" data-filter="action_type"><span class="ml-2">✕</span></a>
                    </span>
                @endif
                 @if(request('partner'))
                    <span class="badge bg-dark ml-2">
                        Pay: {{ request('partner') }}
                        <a href="{{ request()->fullUrlWithQuery(['partner' => null]) }}" class="remove-filter" data-filter="action_type"><span class="ml-2">✕</span></a>
                    </span>
                @endif

            </div>
        @endif
    </div>
{{-- TABLE --}}

    <div class="card card-primary bg-white border rounded-lg-custom report-table">

        <div class="card-header report-table-head">
            <div class="row">
                <div class="col-md-8 col-12">
                    <h3 class="card-title">Price Schedule List</h3>
                </div>
                <div class="col-md-4 col-12">
                    <div class="card-tools">
                        <!-- <a href="{{ route('admin.report.invoice.details.export', request()->all()) }}"
                           class="btn btn-success btn-sm">
                            Download Excel
                        </a> -->
                        <a 
                            href="{{ route('admin.report.price_schedule.export', request()->query()) }}" 
                            class="btn btn-success"
                        >
                            <i class="fas fa-download"></i> Download Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-wrapper">
          <table class="table table-bordered" style="min-width: 2500px; margin: 15px 20px;">

                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th>Fulfilment</th>
                        <th>Quantity</th>
                        <th>Adult (13+)</th>
                        <th>Child (3-12)</th>
                        <th>Infant (2 and under)</th>
                        <th>Senior (60+ years)</th>
                        
                        <th>Product Price</th>
                        <th>Extra Amount</th>
                        <th>Tax Amount</th>
                        <th>Discount</th>
                        <th>Customer Total</th>
                        <th>Order Balance</th>
                        
                        
                        <th>Transport Cost - Tax</th>
                        <th>Product Price (Supplier Cost)</th>
                        <th>Tax</th>
                        <th>Other Fee</th>
                        <th>Net Total</th>
                        <th>Profit</th>
                        <th>Product</th>
                        {{-- ADDON HEADERS --}}
                        @foreach($addonKeys as $key)
                          <th colspan="6">{{ Str::headline($key) }}</th>
                        @endforeach


											                
                    </tr>

                    <tr>
                        <th colspan="23"></th>
                        @foreach($addonKeys as $key)
                          <th>Desc</th>
                          <th>Quantity</th>
                          <th>Price</th>
                          <th>Tax</th>
                          <th>Fee</th>
                          <th>Total</th>
                        @endforeach
                    </tr>   

                </thead>

                <tbody>
                    @forelse($rows as $row) 
                    
                    <tr>
                        <td>{{ $row['no'] ?? '' }}</td>
                        <td><a href="{{ route('admin.orders.edit', encrypt($row['order_id'])) }}" target="_blank">{{ $row['order_number'] ?? '' }}</a></td>
                        <td>{{ $row['customer_name'] ?? '' }}</td>
                        <td>{{ $row['order_date'] ?? '' }}</td>
                        <td>{{ $row['fulfilment_date'] ?? '' }}</td>
                        <?php /*                         
                        <td>{{ $row['payment_status'] ?? '' }}</td> 
                        */ ?>
                        
                        <td>{{ $row['adult'] + $row['child'] + $row['infant'] + $row['other'] + $row['senior'] }}</td>
                        <td>{{ $row['adult'] }}</td>
                        <td>{{ $row['child'] }}</td>
                        <td>{{ $row['infant'] }}</td>
                        <td>{{ $row['senior'] }}</td>
                        <td align="right">{{ number_format_with_currency($row['product_price'], 2) }}</td>
                        <td align="right">{{ number_format_with_currency($row['extra_amount'], 2) }}</td>
                        <td align="right">{{ number_format_with_currency($row['tax_amount'], 2) }}</td>
                        <td align="right">{{ number_format_with_currency($row['discount_amount'], 2) }}</td>
                        <td align="right">{{ number_format_with_currency($row['customer_total'], 2) }}</td>
                        <td align="right">{{ number_format_with_currency($row['balance_amount'], 2) }}</td>
                        <td align="right">{{ number_format_with_currency($row['transport_cost'], 2) }}</td>
                        
                        
                        <td align="right">{{ number_format_with_currency($row['tour_selling_price'], 2) }}</td>
                        <td align="right">{{ number_format_with_currency($row['tour_selling_tax'], 2) }}</td>
                        <td align="right">0</td>
                        <td align="right">{{ number_format_with_currency(($row['tour_selling_total']+$row['transport_cost'] ) , 2) }}</td>
                        <td align="right">{{ number_format_with_currency(($row['customer_total'] - $row['tour_selling_total'] - $row['transport_cost']), 2)  }}</td>
                        <td align="right">{{ $row['product_name'] ?? '' }}</td>
                        {{-- DYNAMIC ADDONS --}}
                        @foreach($addonKeys as $key)
                            <td align="right">
                                @if(!empty($row[$key.'_desc']))
                                    <strong>{{ $row[$key.'_desc'] }}</strong>
                                @endif
                            </td>

                            <td align="right">
                                @if(!empty($row[$key.'_quant']))
                                    <strong>{{ $row[$key.'_quant'] }}</strong>
                                @endif
                            </td>

                            <td align="right">
                                @if(!empty($row[$key.'_price']))
                                    <strong>{{ number_format_with_currency($row[$key.'_price'], 2) }}</strong>
                                @else
                                    0
                                @endif
                            </td>

                            <td align="right">
                                @if(!empty($row[$key.'_tax']))
                                    <strong>{{ number_format_with_currency($row[$key.'_tax'], 2) }}</strong>
                                @else
                                    0
                                @endif
                            </td>

                            <td align="right">
                                @if(!empty($row[$key.'_fee']))
                                    <strong>{{ number_format_with_currency($row[$key.'_fee'], 2) }}</strong>
                                @else
                                    0
                                @endif
                            </td>

                            <td>
                                @if(!empty($row[$key.'_total']))
                                    <strong>{{ number_format_with_currency($row[$key.'_total'], 2) }}</strong>
                                @else
                                    0
                                @endif
                            </td>
                        @endforeach

                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 8 + (count($addonKeys) * 5) }}" class="text-center">
                            No Data Found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
          </table>

          {{-- PAGINATION --}}
          <div class="text-center">
              {{ $orders->links() }}
          </div>

        </div>
  </div>




@section('js')
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  let today = moment();
            
    $(document).ready(function () {
        // ✅ Select2 (optimized)
        $('#productFilter').select2({
            placeholder: 'Select Tour',
            minimumInputLength: 3,
            ajax: {
                url: '{{ route("admin.tours.tours-list") }}',
                dataType: 'json',
                delay: 0,
                cache: true,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return {
                        results: data.map(tour => ({
                            id: tour.id,
                            text: `${tour.title} (${tour.unique_code ?? 'N/A'})`
                        }))
                    };
                }
            }
        });

    });
    
        
  $('#productFilter').on('select2:select', function (e) {
      let data = e.params.data;
      $('#product_text').val(data.text);
  });

  function clearBooking() {
      $('#booking_range').val('');

      // remove from URL (important UX)
      let url = new URL(window.location.href);
      url.searchParams.delete('booking_date');
      window.location.href = url.toString();
  }

function clearTour() {
    $('input[name="tour_date"]').val('');
    let url = new URL(window.location.href);
    url.searchParams.delete('tour_date');
    window.location.href = url.toString();
}

if ($('#productFilter').val() && !$('#product_text').val()) {
    let selectedText = $('#productFilter option:selected').text();
    $('#product_text').val(selectedText);
}
</script>

@endsection

</x-admin>