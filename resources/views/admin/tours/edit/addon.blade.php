<style>
tr:hover{cursor: pointer;}    
tr.dragging {opacity: 1;}
tr.drag-over-top {border-top: 3px solid blue;}
tr.drag-over-bottom {border-bottom: 3px solid blue;}
</style>
<div class="card">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Extra</h3>
            <div class="card-tools">
                <a href="{{ route('admin.addon.create') }}" class="btn btn-sm btn-info">Create New</a>
            </div>
        </div>
        <form class="needs-validation" novalidate action="{{ route('admin.tour.addon_update', $data->id) }}" method="POST"
    enctype="multipart/form-data">
        <div class="card-body">
            
            @csrf
            <table class="table table-striped align-middle" id="myTable">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="check_all" style="width: 20px;height: 20px;" /></th>
                        <th>Image</th>
                        <th width="200">Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th width="150">Customer choise</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $i=1;
                    $existing_addons = $data->addons->pluck('id')->toArray();
                    @endphp
                    @foreach ($addons as $item)
                        <tr draggable="true" data-id="{{ $item->id }}" data-order="{{ $item->order ? $item->order : $i++  }}">
                            <th><input {{ (is_array($existing_addons) && in_array($item->id, $existing_addons)) ? 'checked' : '' }} type="checkbox" class="check_all" name="addons[]" value="{{ $item->id }}" style="width: 20px;height: 20px;" /></th>
                            <td>
                                <img class="img-md" src="{{ uploaded_asset($item->image) }}" height="150"  alt="{{translate('photo')}}">
                            </td>
                            <td><a href="{{ route('admin.addon.edit', encrypt($item->id)) }}" class="text-info">{{ $item->name }}</a></td>
                            <td>{{ substr($item->description,0,150) }}...</td>
                            <td>{{ price_format($item->price) }}</td>
                            <td>{{ $item->customer_choice }}</td>
                                                  
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer" style="display:block">
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit', encrypt($data->id)) }}" class="btn btn-secondary">Back</a>
                <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success">Save</button>
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.scheduling', encrypt($data->id)) }}" class="btn btn-primary">Next</a>
            </div>
            </form>
    </div>
</div>

@section('js')
@parent
<script>
document.addEventListener("DOMContentLoaded", function() {
    const checkAllBox = document.getElementById('check_all');
    const checkboxes = document.querySelectorAll('.check_all');

    checkAllBox.addEventListener('change', function() {
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = checkAllBox.checked;
        });
    });
});
</script>
<script>
  let draggedRow;
  const tbody = document.querySelector("#myTable tbody");

  function updateOrderAndSend() {
    const rows = tbody.querySelectorAll("tr");
    const data = [];

    rows.forEach((row, index) => {
      const id = row.getAttribute("data-id");
      const newOrder = index + 1;
      row.setAttribute("data-order", newOrder); // update DOM attribute
      data.push({ id, order: newOrder });
    });

    console.log("Updated Order:", data); // You can remove this in production

    // Optional: Send data to server via fetch
    fetch("{{ route('admin.addon.order') }}", {
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

  document.querySelectorAll("#myTable tbody tr").forEach(row => {
    row.addEventListener("dragstart", (e) => {
      draggedRow = row;
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
      //const tbody = document.querySelector("#myTable tbody");
      const bounding = row.getBoundingClientRect();
      const offset = e.clientY - bounding.top;

      row.classList.remove("drag-over-top", "drag-over-bottom");

      if (draggedRow === row) return;

      if (offset < bounding.height / 2) {
        tbody.insertBefore(draggedRow, row);
      } else {
        tbody.insertBefore(draggedRow, row.nextSibling);
      }

      updateOrderAndSend();
    });

    row.addEventListener("dragend", () => {
      row.classList.remove("dragging");
      document.querySelectorAll("#myTable tbody tr").forEach(r => {
        r.classList.remove("drag-over-top", "drag-over-bottom");
      });
    });
  });
</script>
@endsection
