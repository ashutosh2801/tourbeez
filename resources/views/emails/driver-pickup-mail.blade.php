```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Driver Pickup Manifest</title>
</head>

<body
    style="
        margin:0;
        padding:0;
        background-color:#f3f5f8;
        font-family:Arial, Helvetica, sans-serif;
        color:#222;
    "
>

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    role="presentation"
>
    <tr>
        <td
            align="center"
            style="padding:20px 10px;"
        >

            <table
                width="100%"
                cellpadding="0"
                cellspacing="0"
                role="presentation"
                style="
                    max-width:900px;
                    background:#ffffff;
                    border-radius:10px;
                    overflow:hidden;
                "
            >

                {{-- Header --}}
                <tr>
                    <td style="
                        background:#01228c;
                        color:#ffffff;
                        text-align:center;
                        padding:20px;
                    ">

                        <a
                            href="https://tourbeez.com"
                            target="_blank"
                            style="text-decoration:none;"
                        >
                            <img
                                src="https://tourbeez.com/public/admin/dist/img/logo.jpg"
                                alt="TourBeez"
                                width="200"
                                style="
                                    width:200px;
                                    max-width:100%;
                                    display:inline-block;
                                    border:0;
                                    margin-bottom:10px;
                                "
                            >
                        </a>                    

                        <h2 style="margin:6px 0 0;">
                            Pickup Reminder
                        </h2>

                        <p style="margin:6px 0 0;">
                            {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                        </p>

                    </td>
                </tr>

                {{-- GREETING --}}
                <tr>
                    <td style="padding:20px 20px 10px;">
                        <p style="margin:0 0 10px;">
                            Hello {{ $driver->name }},
                        </p>

                        <p style="margin:0;">
                            Please find your pickup schedule and tour itinerary below.
                        </p>
                    </td>
                </tr>

                {{-- SUMMARY --}}
                <tr>
                    <td style="padding:10px 20px 20px;">
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            role="presentation"
                            style="
                                background:#f8fafc;
                                border:1px solid #e2e8f0;
                                border-radius:6px;
                            "
                        >
                            <tr>
                                <td style="padding:12px;">
                                    <strong>Total Orders:</strong>
                                    {{ $orders->count() }}

                                    <br>

                                    <strong>Total Passengers:</strong>
                                    {{ $orders->sum('guest_count') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- PICKUP SCHEDULE --}}
                <tr>
                    <td style="padding:0 20px 20px;">
                        <h3 style="margin:0 0 10px;">
                            Pickup Schedule
                        </h3>

                        <table
                            width="100%"
                            border="1"
                            cellpadding="8"
                            cellspacing="0"
                            role="presentation"
                            style="
                                border-collapse:collapse;
                                border-color:#d1d5db;
                                font-size:14px;
                            "
                        >
                            <thead
                                style="
                                    background:#01228c;
                                    color:#ffffff;
                                "
                            >
                                <tr>
                                    <th
                                        align="center"
                                        style="width:45px;"
                                    >
                                        #
                                    </th>

                                    <th align="left">
                                        Pickup Location
                                    </th>

                                    <th
                                        align="center"
                                        style="width:110px;"
                                    >
                                        Time
                                    </th>

                                    <th
                                        align="center"
                                        style="width:60px;"
                                    >
                                        Pax
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach(
                                    $orders->sortBy('pickup_time')->values()
                                    as $index => $o
                                )
                                    @php
                                        $formattedPickupTime = '-';

                                        if (!empty($o['pickup_time'])) {
                                            try {
                                                $formattedPickupTime =
                                                    \Carbon\Carbon::parse(
                                                        $o['pickup_time']
                                                    )->format('h:i A');
                                            } catch (\Throwable $e) {
                                                $formattedPickupTime =
                                                    $o['pickup_time'];
                                            }
                                        }
                                    @endphp

                                    <tr
                                        style="
                                            {{ $index % 2
                                                ? 'background:#f8fafc;'
                                                : 'background:#ffffff;'
                                            }}
                                        "
                                    >
                                        <td align="center">
                                            {{ $index + 1 }}
                                        </td>

                                        <td>
                                            {{ $o['pickup_location'] ?? '-' }}
                                        </td>

                                        <td align="center">
                                            <strong>
                                                {{ $formattedPickupTime }}
                                            </strong>
                                        </td>

                                        <td align="center">
                                            {{ $o['guest_count'] ?? 0 }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>

                {{-- TOUR ITINERARY --}}
                @if(!empty($customMessage))
                    <tr>
                        <td style="padding:0 20px 20px;">
                            <h3 style="margin:0 0 10px;">
                                Tour Itinerary
                            </h3>

                            <div
                                style="
                                    width:100%;
                                    overflow-x:auto;
                                    font-size:14px;
                                    line-height:1.6;
                                "
                            >
                                {!! $customMessage !!}
                            </div>
                        </td>
                    </tr>
                @endif

                {{-- PASSENGER DETAILS --}}
                <tr>
                    <td style="padding:0 20px 20px;">
                        <h3 style="margin:0 0 10px;">
                            Passenger Details
                        </h3>

                        @foreach(
                            $orders->sortBy('pickup_time')->values()
                            as $index => $o
                        )
                            @php
                                $formattedPickupTime = '';

                                if (!empty($o['pickup_time'])) {
                                    try {
                                        $formattedPickupTime =
                                            \Carbon\Carbon::parse(
                                                $o['pickup_time']
                                            )->format('h:i A');
                                    } catch (\Throwable $e) {
                                        $formattedPickupTime =
                                            $o['pickup_time'];
                                    }
                                }

                                $vehicleName =
                                    isset($o['vehicle']) &&
                                    $o['vehicle']
                                        ? $o['vehicle']->name
                                        : null;

                                $galleryUploadUrl =
                                    $o['gallery_upload_url'] ?? null;

                                $galleryQrUrl =
                                    $o['gallery_qr_url'] ?? null;
                            @endphp

                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                role="presentation"
                                style="
                                    margin-bottom:14px;
                                    border:1px solid #dddddd;
                                    background:#ffffff;
                                    border-collapse:collapse;
                                "
                            >
                                <tr>
                                    <td
                                        style="
                                            padding:14px;
                                            border-left:4px solid #01228c;
                                        "
                                    >
                                        <strong>
                                            @if($formattedPickupTime)
                                                {{ $formattedPickupTime }} –
                                            @endif

                                            {{ $o['pickup_location'] ?? '-' }}
                                        </strong>

                                        <p style="margin:8px 0 4px;">
                                            <strong>
                                                {{ $o['customer_name'] ?? 'N/A' }}
                                            </strong>
                                        </p>

                                        @if(!empty($o['customer_phone']))
                                            <p style="margin:3px 0;">
                                                <strong>Phone:</strong>
                                                {{ $o['customer_phone'] }}
                                            </p>
                                        @endif

                                        @if(!empty($o['customer_email']))
                                            <p style="margin:3px 0;">
                                                <strong>Email:</strong>
                                                {{ $o['customer_email'] }}
                                            </p>
                                        @endif

                                        <p style="margin:3px 0;">
                                            <strong>Order:</strong>
                                            {{ $o['order_number'] ?? '-' }}
                                        </p>

                                        <p style="margin:3px 0;">
                                            <strong>Guests:</strong>
                                            {{ $o['guest_count'] ?? 0 }}
                                        </p>

                                        @if(!empty($vehicleName))
                                            <p style="margin:3px 0;">
                                                <strong>Vehicle:</strong>
                                                {{ $vehicleName }}
                                            </p>
                                        @endif

                                        @if(!empty($o['instruction']))
                                            <div
                                                style="
                                                    margin-top:10px;
                                                    padding:10px;
                                                    background:#eff6ff;
                                                    border-left:3px solid #2563eb;
                                                "
                                            >
                                                <strong>Instruction:</strong>

                                                <div style="margin-top:4px;">
                                                    {!! nl2br(e($o['instruction'])) !!}
                                                </div>
                                            </div>
                                        @endif

                                        @if(!empty($o['internal_notes']))
                                            <div
                                                style="
                                                    margin-top:10px;
                                                    padding:10px;
                                                    background:#fff7ed;
                                                    color:#9a3412;
                                                    border-left:3px solid #f97316;
                                                "
                                            >
                                                <strong>Internal Notes:</strong>

                                                <div style="margin-top:4px;">
                                                    {!! nl2br(e($o['internal_notes'])) !!}
                                                </div>
                                            </div>
                                        @endif

                                        {{-- GALLERY QR CODE --}}
                                        @if(!empty($galleryQrUrl))
                                            <table
                                                width="100%"
                                                cellpadding="0"
                                                cellspacing="0"
                                                role="presentation"
                                                style="
                                                    margin-top:15px;
                                                    background:#f8fafc;
                                                    border:1px solid #e2e8f0;
                                                    border-collapse:collapse;
                                                "
                                            >
                                                <tr>
                                                    <td
                                                        align="center"
                                                        style="padding:15px;"
                                                    >
                                                        <p
                                                            style="
                                                                margin:0 0 10px;
                                                                font-weight:bold;
                                                                color:#01228c;
                                                            "
                                                        >
                                                            Customer Tour Photo Upload
                                                        </p>

                                                        @if(!empty($galleryUploadUrl))
                                                            <a
                                                                href="{{ $galleryUploadUrl }}"
                                                                target="_blank"
                                                                style="
                                                                    text-decoration:none;
                                                                    display:inline-block;
                                                                "
                                                            >
                                                                <img
                                                                    src="{{ $galleryQrUrl }}"
                                                                    alt="Gallery Upload QR Code"
                                                                    width="170"
                                                                    style="
                                                                        display:block;
                                                                        width:170px;
                                                                        max-width:170px;
                                                                        height:auto;
                                                                        border:1px solid #dddddd;
                                                                        padding:8px;
                                                                        background:#ffffff;
                                                                    "
                                                                >
                                                            </a>
                                                        @else
                                                            <img
                                                                src="{{ $galleryQrUrl }}"
                                                                alt="Gallery Upload QR Code"
                                                                width="170"
                                                                style="
                                                                    display:block;
                                                                    width:170px;
                                                                    max-width:170px;
                                                                    height:auto;
                                                                    border:1px solid #dddddd;
                                                                    padding:8px;
                                                                    background:#ffffff;
                                                                "
                                                            >
                                                        @endif

                                                        <p
                                                            style="
                                                                margin:10px 0 5px;
                                                                font-size:13px;
                                                                color:#475569;
                                                            "
                                                        >
                                                            Scan this QR code to upload photos
                                                            for order
                                                            <strong>
                                                                {{ $o['order_number'] ?? '' }}
                                                            </strong>.
                                                        </p>

                                                        @if(!empty($galleryUploadUrl))
                                                            <a
                                                                href="{{ $galleryUploadUrl }}"
                                                                target="_blank"
                                                                style="
                                                                    display:inline-block;
                                                                    margin-top:5px;
                                                                    padding:9px 16px;
                                                                    background:#01228c;
                                                                    color:#ffffff;
                                                                    text-decoration:none;
                                                                    border-radius:5px;
                                                                    font-size:13px;
                                                                    font-weight:bold;
                                                                "
                                                            >
                                                                Open Photo Upload Page
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        @endif

                                    </td>
                                </tr>
                            </table>
                        @endforeach
                    </td>
                </tr>

                {{-- IMPORTANT NOTE --}}
                <tr>
                    <td style="padding:0 20px 20px;">
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            role="presentation"
                            style="
                                background:#fff7ed;
                                border:1px solid #fed7aa;
                                border-collapse:collapse;
                            "
                        >
                            <tr>
                                <td
                                    style="
                                        padding:12px;
                                        color:#9a3412;
                                        font-size:13px;
                                        line-height:1.5;
                                    "
                                >
                                    <strong>Important:</strong>
                                    Please verify all pickup times, locations,
                                    passenger details and assigned vehicle before
                                    beginning the tour.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- FOOTER --}}
                <tr>
                    <td style="
                        background:#01228c;
                        color:#ffffff;
                        text-align:center;
                        padding:18px;
                    ">

                        <p style="margin:0 0 4px;">
                            Best Regards,
                        </p>

                        <strong>
                            TourBeez Team
                        </strong>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
```
