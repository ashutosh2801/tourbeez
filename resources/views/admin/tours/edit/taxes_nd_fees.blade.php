<style>
tr:hover{cursor: pointer;}    
tr.dragging {opacity: 1;}
tr.drag-over-top {border-top: 3px solid blue;}
tr.drag-over-bottom {border-bottom: 3px solid blue;}
</style>
<div class="card">
    <div class="card card-primary">
    <form class="needs-validation" novalidate action="{{ route('admin.tour.taxfee_update', $data->id) }}" method="POST"
    enctype="multipart/form-data">
        <div class="card-header">
            <h3 class="card-title">Taxes and Fees</h3>
            <div class="card-tools">
                <!-- <a href="{{ route('admin.addon.create') }}" class="btn btn-sm btn-info">Create New</a> -->
            </div>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="list-unstyled">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @method('PUT')
            @csrf
            <table class="table table-striped align-middle" id="taxTable">
                <tbody>
                  
                    @php
                    $i=1;
                    $existing_taxes_fees = $data->taxes_fees->pluck('id')->toArray();
                    @endphp
                    @foreach ($taxesfees as $item)
                        <tr draggable="true" data-id="{{ $item->id }}">
                            <th width="60"><input type="checkbox" class="check_all_tax" name="taxes[]" value="{{ $item->id }}" style="width: 20px;height: 20px;"
                            {{ (is_array($existing_taxes_fees) && in_array($item->id, $existing_taxes_fees)) ? 'checked' : '' }} /></th>
                            <td>
                                <a target="_blank" href="{{ route('admin.taxes.edit', encrypt($item->id)) }}" class="text-info">{{ $item->label }}</a>  <br />                             
                                {{ ($item->tax_fee_type=='FEE' ? '$' : '') . number_format($item->tax_fee_value,1) . ($item->tax_fee_type=='TAX' ? '%' : '') }}
                            </td>                                                  
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="text-center">
                <a href="{{ route('admin.taxes.create') }}" class="btn border-t-indigo-100 btn-outline">Add New Taxes and Fees</a>
            </div>
        </div>
        <div class="card-footer" style="display:block">
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.exclusions', encrypt($data->id)) }}" class="btn btn-secondary">Back</a>
                <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success">Save</button>
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.gallery', encrypt($data->id)) }}" class="btn btn-primary">Next</a>           
            </div>
            </form>
    </div>
</div>

@section('js')
@parent
<script>
// document.addEventListener("DOMContentLoaded", function() {
//     const checkAllBox = document.getElementById('check_all_tax');
//     const checkboxes = document.querySelectorAll('.check_all_tax');

//     checkAllBox.addEventListener('change', function() {
//         checkboxes.forEach(function(checkbox) {
//             checkbox.checked = checkAllBox.checked;
//         });
//     });
// });
</script>
<script>
  let draggedTaxRow_;
  const tbodyTax_ = document.querySelector("#taxTable tbody");

  function updateTaxOrderAndSend() {
    const rows = tbodyTax_.querySelectorAll("tr");
    const data = [];

    rows.forEach((row, index) => {
      const id = row.getAttribute("data-id");
      const newOrder = index + 1;
      row.setAttribute("data-order", newOrder); // update DOM attribute
      data.push({ id, order: newOrder });
    });

    console.log("Updated Order:", data); // You can remove this in production

    // Optional: Send data to server via fetch
    fetch("{{ route('admin.taxes.order') }}", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": "{{ csrf_token() }}",
        _token: '{{ csrf_token() }}'
      },
      body: JSON.stringify({ rows: data })
    })
    .then(res => res.json())
    .then(res => {
      console.log("Order saved:", res);
    })
    .catch(err => {
      console.error("Error saving order:", err);
    });
  }

  document.querySelectorAll("#taxTable tbody tr").forEach(row => {
    row.addEventListener("dragstart", (e) => {
      draggedTaxRow_ = row;
      e.dataTransfer.effectAllowed = "move";
      row.classList.add("dragging");
    });

    row.addEventListener("dragover", (e) => {
      e.preventDefault();
      const bounding = row.getBoundingClientRect();
      const offset = e.clientY - bounding.top;
      row.classList.remove("drag-over-top", "drag-over-bottom");
      if (offset < bounding.height / 2) {
        row.classList.add("drag-over-top");
      } else {
        row.classList.add("drag-over-bottom");
      }
    });

    row.addEventListener("dragleave", () => {
      row.classList.remove("drag-over-top", "drag-over-bottom");
    });

    row.addEventListener("drop", (e) => {
      e.preventDefault();
      //const tbody = document.querySelector("#taxTable tbody");
      const bounding = row.getBoundingClientRect();
      const offset = e.clientY - bounding.top;

      row.classList.remove("drag-over-top", "drag-over-bottom");

      if (draggedTaxRow_ === row) return;

      if (offset < bounding.height / 2) {
        tbodyTax_.insertBefore(draggedTaxRow_, row);
      } else {
        tbodyTax_.insertBefore(draggedTaxRow_, row.nextSibling);
      }

      updateTaxOrderAndSend();
    });

    row.addEventListener("dragend", () => {
      row.classList.remove("dragging");
      document.querySelectorAll("#taxTable tbody tr").forEach(r => {
        r.classList.remove("drag-over-top", "drag-over-bottom");
      });
    });
  });
</script>
@endsection

