@php
    $toastMessages = collect(['success', 'warning', 'info', 'error'])
        ->filter(fn ($type) => session()->has($type))
        ->map(fn ($type) => [
            'type' => $type,
            'message' => session()->get($type),
        ])
        ->values();
@endphp

@if ($toastMessages->isNotEmpty())
    <script>
        (function () {
            const messages = @json($toastMessages);

            function showFallback(message) {
                const alert = document.createElement('div');
                const bootstrapType = message.type === 'error' ? 'danger' : message.type;

                alert.className = `alert alert-${bootstrapType} alert-dismissible fade show`;
                alert.setAttribute('role', 'alert');
                alert.style.position = 'fixed';
                alert.style.top = '70px';
                alert.style.right = '20px';
                alert.style.zIndex = '9999';
                alert.style.maxWidth = '420px';
                alert.appendChild(document.createTextNode(String(message.message ?? '')));

                const closeButton = document.createElement('button');
                closeButton.type = 'button';
                closeButton.className = 'close ml-3';
                closeButton.setAttribute('aria-label', 'Close');
                closeButton.innerHTML = '<span aria-hidden="true">&times;</span>';
                closeButton.addEventListener('click', () => alert.remove());
                alert.appendChild(closeButton);

                document.body.appendChild(alert);
                window.setTimeout(() => alert.remove(), 5000);
            }

            messages.forEach(function (message) {
                if (window.toastr && typeof window.toastr[message.type] === 'function') {
                    window.toastr[message.type](message.message);
                    return;
                }

                showFallback(message);
            });
        })();
    </script>
@endif
