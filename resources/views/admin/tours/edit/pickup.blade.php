<style>
tr:hover{cursor: pointer;}    
tr.dragging {opacity: 1;}
tr.drag-over-top {border-top: 3px solid blue;}
tr.drag-over-bottom {border-bottom: 3px solid blue;}
</style>
<div class="card">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Pickups</h3>
            <div class="card-tools">
                <a href="{{ route('admin.pickups.create') }}" class="btn btn-sm btn-info">Create New</a>
            </div>
        </div>
        <div class="card-body">
            <form class="needs-validation" novalidate action="{{ route('admin.tour.pickup_update', $data->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <table class="table table-striped align-middle" id="pickupTable">
                <tbody>
                    @php
                    $i=1;
                    $existing_pickups = $data->pickups->pluck('id')->toArray();
                    @endphp
                    @foreach ($pickups as $item)
                        <tr draggable="true" data-id="{{ $item->id }}">
                            <th><input {{ (is_array($existing_pickups) && in_array($item->id, $existing_pickups)) ? 'checked' : '' }} type="checkbox" class="check_all" name="pickup_id" value="{{ $item->id }}" style="width: 20px;height: 20px;" /></th>
                            <td>
                                <a href="{{ route('admin.pickups.edit', encrypt($item->id)) }}" class="text-info">{{ $item->name }}</a>
                                @foreach ($item->locations as $location)
                                    <p class="m-0 text-sm text-gray-100">{{ $location->location }}, {{ $location->address }}</p>
                                @endforeach
                            </td>
                            <td>2 {{ translate('Locations') }}</td>                                                  
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="submit" id="submit" class="btn btn-primary">Save pickup</button>
            </form>
        </div>
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
  const tbody = document.querySelector("#pickupTable tbody");

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

  document.querySelectorAll("#pickupTable tbody tr").forEach(row => {
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
      //const tbody = document.querySelector("#pickupTable tbody");
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
      document.querySelectorAll("#pickupTable tbody tr").forEach(r => {
        r.classList.remove("drag-over-top", "drag-over-bottom");
      });
    });
  });
</script>
@endsection
