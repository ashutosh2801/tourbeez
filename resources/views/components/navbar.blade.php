<nav class="main-header navbar navbar-expand navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link menu-bar" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto">

    {{-- Notifications Dropdown --}}
    @php
        use App\Models\Notification;

        $user = auth()->user();

        // Supplier sees only their own notifications
        if ($user->role === 'Supplier') {
            
            $notificationsQuery = Notification::where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user));
        } else {
            // Admins or other roles see all notifications
            $notificationsQuery = Notification::query();
        }

        $unreadNotifications = $notificationsQuery->whereNull('read_at')->latest()->take(5)->get();
        $unreadCount = $notificationsQuery->whereNull('read_at')->count();
    @endphp

    <li>
      <button id="openCurrencyModal" class="btn nav-currency"  title="Convert currency to USD">
        <i class="fas fa-calculator fa-lg"></i>
      </button>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link nav-notify" data-toggle="dropdown" href="#" role="button" aria-expanded="false">
            <i class="far fa-bell fa-lg"></i>
            @if($unreadCount > 0)
                <span class="badge badge-danger navbar-badge">{{ $unreadCount }}</span>
            @endif
        </a>

        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="width: 350px; max-height: 400px; overflow-y: auto;">
            <span class="dropdown-item dropdown-header">Unread Notifications ({{ $unreadCount }})</span>
            <div class="dropdown-divider"></div>

            @forelse($unreadNotifications as $notification)
                <a href="{{ route('admin.notifications.read', $notification->id) }}" target="_blank" class="dropdown-item unread">
                    <strong>{{ $notification->data['title'] ?? 'Notification' }}</strong><br>
                    <small class="text-wrap">{{ $notification->data['message'] ?? '' }}</small><br>
                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                </a>
            @empty
                <span class="dropdown-item text-muted">No new notifications</span>
            @endforelse

            <a href="#" data-toggle="modal" data-target="#allNotificationsModal" class="dropdown-item dropdown-footer text-center">
                View All Notifications
            </a>
        </div>
    </li>

    {{-- Clear Cache --}}
    <li class="nav-item tooltip">
        <a href="{{ route('admin.clear.cache') }}" class="nav-link nav-clear">
            <i class="fas fa-wrench fa-lg"></i>
        </a>
        <span class="tooltip-text">Clear Cache</span>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link nav-profile" data-toggle="dropdown" href="#" role="button" aria-expanded="false">
            <span class="profile-name">{{ Auth::user()->name }}</span> <img src="{{ asset('admin/dist/img/avatar4.png') }}" class="img-circle elevation-2" width="40" height="40">
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right profile-dropdown">
            <a href="{{ route('admin.profile.edit') }}" class="{{ Route::is('admin.profile.edit') ? 'active' : '' }} text-center">
                <img src="{{ asset('admin/dist/img/avatar4.png') }}" class="img-circle elevation-2" width="40" height="40">
                <b>{{ Auth::user()->name }}</b>
                Admin
            </a>
            <a href="{{ route('admin.profile.edit') }}" class="{{ Route::is('admin.profile.edit') ? 'active' : '' }} link">
                <i class="nav-icon fas fa-user"></i> My Profile
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" name="submit" class="link">
                    <i class="fas fa-sign-out-alt"></i> Sign out
                </button>    
            </form>
        </div>
    </li>
</ul>
</nav>

{{-- MODAL --}}
<div class="modal fade" id="allNotificationsModal" tabindex="-1" role="dialog" aria-labelledby="allNotificationsLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="allNotificationsLabel">All Notifications</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body" id="notificationsList" style="max-height: 500px; overflow-y: auto;">
        <p class="text-center text-muted my-4">Loading notifications...</p>
      </div>

      <div class="modal-footer">
        <button id="markAllRead" class="btn btn-sm btn-secondary">Mark All Read</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Live Currency Conversion Modal -->
<!-- Live Currency Conversion Modal -->
<div class="modal fade" id="currencyModal" tabindex="-1" aria-labelledby="currencyModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="currencyForm">
        <div class="modal-header">
          <h5 class="modal-title" id="currencyModalLabel">Convert to USD</h5>
          <!-- <button type="button" id="closeCurrencyModalTop" class="btn-close" aria-label="Close"></button> -->
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label for="currency" class="form-label">Select Currency</label>
            <select class="form-control " id="currency" name="from" required>

                @foreach(config('constants.currencies') as $sign => $country)
                  <option value="{{ $sign }}">{{ $sign . '-' . $country}}</option>
                @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label for="amount" class="form-label">Enter Price</label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount" placeholder="Enter amount" required>
          </div>

          <div class="mt-3">
            <label class="form-label">Converted (USD)</label>
            <input type="text" id="convertedUsd" class="form-control" readonly placeholder="0.00">
          </div>
          <div class="mt-2 text-end">
            <button type="button" id="copyUsdBtn" class="btn btn-outline-success btn-sm">
                Copy USD Value
            </button>
        </div>
        </div>

        <!-- <div class="modal-footer">
          <button type="button" id="closeCurrencyModalBottom" class="btn btn-secondary">Close</button>
        </div> -->
      </form>
    </div>
  </div>
</div>



{{-- SCRIPT --}}
<script>
    const readRoute = "{{ route('admin.notifications.read', ':id') }}";
document.addEventListener('DOMContentLoaded', function () {

    // Open modal and load notifications via AJAX
    $('#allNotificationsModal').on('show.bs.modal', function () {
        const list = document.getElementById('notificationsList');
        list.innerHTML = '<p class="text-center text-muted my-4">Loading notifications...</p>';

        fetch('{{ route('admin.notifications.fetchAll') }}')
            .then(res => res.json())
            .then(data => {
                list.innerHTML = '';

                if (!data.notifications.length) {
                    list.innerHTML = '<p class="text-center text-muted my-4">No notifications found.</p>';
                    return;
                }

                data.notifications.forEach(notif => {

                    const url = readRoute.replace(':id', notif.id);
                    list.innerHTML += `
                        <div class="card mb-2 ${notif.read_at ? 'bg-light' : 'border-primary'}">
                            <div class="card-body py-2 px-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <a href="${url}" target="_blank">
                                        <strong>${notif.title}</strong>
                                        <p class="mb-1">${notif.message}</p>
                                        <small class="text-muted">${notif.created_at}</small>
                                        </a>
                                    </div>
                                    ${!notif.read_at ? 
                                        `<button class="btn btn-sm btn-outline-primary mark-read" data-id="${notif.id}">Mark as Read</button>` 
                                        : ''
                                    }
                                </div>
                            </div>
                        </div>
                    `;
                });
            });
    });

    // Handle Mark as Read (inside modal)
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('mark-read')) {
            const id = e.target.dataset.id;
            fetch(`/admin/notifications/read/${id}`)
                .then(() => {
                    e.target.closest('.card').classList.remove('border-primary');
                    e.target.closest('.card').classList.add('bg-light');
                    e.target.remove();
                });
        }
    });

    // Handle Mark as Read (dropdown quick read)
    document.addEventListener('click', function (e) {
        if (e.target.closest('.mark-read-link')) {
            e.preventDefault();
            const id = e.target.closest('.mark-read-link').dataset.id;
            fetch(`/admin/notifications/read/${id}`).then(() => {
                e.target.closest('.mark-read-link').classList.add('text-muted');
                e.target.closest('.mark-read-link').querySelector('.fas').classList.remove('text-primary');
            });
        }
    });

    // Handle Mark All as Read
    document.getElementById('markAllRead').addEventListener('click', function () {
        fetch('{{ route('admin.notifications.readAll') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(() => {
            $('#allNotificationsModal').modal('hide');
            location.reload(); // Refresh navbar count
        });
    });


    document.addEventListener('DOMContentLoaded', function() {
    const list = document.getElementById('notificationList');
    const countBadge = document.getElementById('notificationCount');

    async function loadNotifications() {
        try {
            const res = await fetch('{{ route('admin.notifications.navbar') }}');
            const data = await res.json();

            // Update count
            if (data.unread_count > 0) {
                countBadge.textContent = data.unread_count;
                countBadge.classList.remove('d-none');
            } else {
                countBadge.classList.add('d-none');
            }

            // Render notifications
            if (data.notifications.length === 0) {
                list.innerHTML = `<span class="dropdown-item text-muted">No new notifications</span>`;
                return;
            }

            list.innerHTML = `
                <span class="dropdown-item dropdown-header">
                    Unread Notifications (${data.unread_count})
                </span>
                <div class="dropdown-divider"></div>
                ${data.notifications.map(n => `
                    <a href="/notifications/read/${n.id}" target="_blank" class="dropdown-item">
                        <i class="fas fa-circle text-primary mr-2"></i>
                        <strong>${n.data?.title ?? 'Notification'}</strong><br>
                        <small>${n.data?.message ?? ''}</small><br>
                        <small class="text-muted">${new Date(n.created_at).toLocaleString()}</small>
                    </a>
                    <div class="dropdown-divider"></div>
                `).join('')}
                <a href="#" data-toggle="modal" data-target="#allNotificationsModal" class="dropdown-item dropdown-footer text-center">
                    View All Notifications
                </a>
            `;
        } catch (err) {
            list.innerHTML = `<span class="dropdown-item text-danger">Failed to load notifications.</span>`;
        }
    }

    // Load notifications when dropdown is opened
    document.getElementById('notificationDropdown').addEventListener('click', loadNotifications);
});
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('currencyModal');
    const openBtn = document.getElementById('openCurrencyModal');

    // Open modal using Bootstrap JS class (no data attributes)
    openBtn.addEventListener('click', function () {
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('currencyModal');
    const openBtn = document.getElementById('openCurrencyModal');
    // const closeBtnTop = document.getElementById('closeCurrencyModalTop');
    // const closeBtnBottom = document.getElementById('closeCurrencyModalBottom');
    const copyBtn = document.getElementById('copyUsdBtn');
    const convertedUsdInput = document.getElementById('convertedUsd');

    const modalInstance = new bootstrap.Modal(modalElement, { backdrop: true, keyboard: true });

    // Open modal
    openBtn.addEventListener('click', () => modalInstance.show());

    // Copy USD value to clipboard
    copyBtn.addEventListener('click', async () => {
        const value = convertedUsdInput.value.trim();
        if (!value) {
            alert('No USD value to copy!');
            return;
        }
        try {
            await navigator.clipboard.writeText(value);
            copyBtn.innerText = 'Copied!';
            copyBtn.classList.add('btn-success');
            setTimeout(() => {
                copyBtn.innerText = 'Copy USD Value';
                copyBtn.classList.remove('btn-success');
            }, 1500);
        } catch (err) {
            alert('Failed to copy. Please copy manually.');
        }
    });
});
</script>




<script>
    function convertToUSD() {
        const amount = document.getElementById('amount').value;
        const currency = document.getElementById('currency').value;

        if (!amount || amount <= 0) return;

        fetch('{{ route('admin.currency.convert') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                amount: amount,
                from: currency
            })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('convertedUsd').value = data.usd_amount;
        })
        .catch(() => {
            document.getElementById('convertedUsd').value = 'Error';
        });
    }

    // Attach event listeners for input and currency change
    document.addEventListener('DOMContentLoaded', function () {
        const amountInput = document.getElementById('amount');
        const currencySelect = document.getElementById('currency');

        amountInput.addEventListener('input', convertToUSD);
        currencySelect.addEventListener('change', convertToUSD);
    });
</script>
