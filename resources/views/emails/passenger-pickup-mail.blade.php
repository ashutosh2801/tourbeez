<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title>
        Niagara Falls Tour Pickup Reminder
    </title>
</head>

<body style="
    margin:0;
    padding:0;
    background:#f4f6f9;
    font-family:Arial, Helvetica, sans-serif;
">

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    role="presentation"
>
    <tr>
        <td
            align="center"
            style="padding:20px;"
        >
            <table
                width="100%"
                cellpadding="0"
                cellspacing="0"
                role="presentation"
                style="
                    max-width:650px;
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

                        <!-- <h2 style="margin:0;">
                            Niagara Falls Tour
                        </h2> -->

                        <p style="margin:6px 0 0;">
                            Pickup Reminder
                        </p>

                        <p style="margin:6px 0 0;">
                            {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                        </p>

                    </td>
                </tr>

                {{-- Greeting --}}
                <tr>
                    <td style="padding:20px;">

                        <p style="margin-top:0;">
                            Hello

                            <strong>
                                {{ $order->customer?->name ?? 'Guest' }}
                            </strong>,
                        </p>

                        <p style="
                            margin-bottom:0;
                            line-height:1.6;
                        ">
                            This is a reminder for your upcoming
                            Niagara Falls Tour. Please review your
                            pickup, driver and vehicle details below.
                        </p>

                    </td>
                </tr>

                {{-- Driver Details --}}
                <tr>
                    <td style="padding:15px 20px">
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#eef3ff;border-left:5px solid #01228c">
                            <tbody><tr>
                                <td style="padding:16px">
                                    <h2 style="margin:0 0 12px;font-size:20px;color:#01228c">
                                        Tour Guide Information
                                    </h2>

                                    @if($drivers->isNotEmpty())

                                        @foreach($drivers as $driver)

                                            <div style="
                                                padding:10px 0;
                                                border-bottom:
                                                    {{ $loop->last ? '0' : '1px solid #d6def3' }};
                                            ">

                                                <p style="margin:0 0 6px;">
                                                    <strong>Name:</strong>
                                                    {{ $driver->name }}
                                                </p>

                                                <p style="margin:0;">
                                                    <strong>Phone:</strong>

                                                    @if(!empty($driver->phone))

                                                        <a
                                                            href="tel:{{ $driver->phone }}"
                                                            style="color:#01228c;"
                                                        >
                                                            {{ $driver->phone }}
                                                        </a>

                                                    @else

                                                        Not available

                                                    @endif
                                                </p>

                                            </div>

                                        @endforeach

                                    @else

                                        <p style="margin:0;">
                                            Driver information will be
                                            confirmed shortly.
                                        </p>

                                    @endif
                                    
                                </td>
                                <td>
                                    <a
                                        href="{{ $galleryUploadUrl }}"
                                        target="_blank"
                                        style="text-decoration:none;"
                                    >
                                        <img
                                            src="{{ $galleryQrUrl }}"
                                            width="125"
                                            height="125"
                                            alt="Upload Tour Photos"
                                            style="
                                                width:125px;
                                                height:125px;
                                                display:block;
                                                margin:0 auto;
                                                padding:5px;
                                                border:0;
                                                background:#ffffff;
                                            "
                                        >
                                    </a>
                                </td>
                            </tr>
                        </tbody></table>
                    </td>
                </tr>

                {{-- Vehicle --}}
                <tr>
                    <td style="padding:15px 20px">
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#eef3ff;border-left:5px solid #01228c">
                            <tbody><tr>
                                <td style="padding:16px">
                                    <h2 style="margin:0 0 12px;font-size:20px;color:#01228c">
                                        Vehicle Information
                                    </h2>

                                    @if($vehicle)

                                    <p style="margin:0;">
                                        <strong>
                                            {{ $vehicle->name }}
                                        </strong>                                        
                                    </p>

                                @else

                                    <p style="margin:0;">
                                        Vehicle information will be
                                        confirmed shortly.
                                    </p>

                                @endif

                                    
                                </td>
                            </tr>
                        </tbody></table>
                    </td>
                </tr> 

                {{-- Pickup Details --}}
                <tr>
                    <td style="padding:10px 20px">
                        <h2 style="margin:0 0 12px;color:#01228c;font-size:20px">
                            Pickup Information
                        </h2>

                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border:1px solid #d7dce3">
                            <tbody><tr>
                                <td style="padding:16px;background:#ffffff">

                                @php
                                $location = getSinglePickupLocation($order) ?: [];
                                @endphp

                                    <p style="margin:0 0 8px;font-size:17px;color:#01228c">
                                        {{ $location['location'] ?? 'To be confirmed' }}
                                    </p>

                                    <p style="margin:0 0 8px;">
                                        {{ $location['address'] ?? 'To be confirmed' }}
                                    </p>

                                    <table cellpadding="0" cellspacing="0" border="0" style="margin-top:14px;background:#eaf8ee;border-radius:5px">
                                        <tbody><tr>
                                            <td style="padding:10px 14px;color:#146c43;font-size:17px">
                                                <strong>
                                                    Pickup Time: 
                                                    @if($pickupTime)
                                                        {{ \Carbon\Carbon::createFromFormat('H:i', $pickupTime)->format('h:i A') }}
                                                    @else
                                                        To be confirmed
                                                    @endif
                                                </strong>
                                            </td>
                                        </tr>
                                    </tbody></table>
                                </td>
                            </tr>
                        </tbody></table>
                    </td>
                </tr>

                {{-- Booking Details --}}
                <tr>
                    <td style="padding:0 20px 20px;">

                        <h2 style="margin:0 0 12px;color:#01228c;font-size:20px">
                            Booking Details
                        </h2>

                        <table
                            width="100%"
                            cellpadding="10"
                            cellspacing="0"
                            role="presentation"
                            style="
                                border-collapse:collapse;
                                border:1px solid #dddddd;
                            "
                        >

                            <tr>
                                <td
                                    width="40%"
                                    style="
                                        border:1px solid #dddddd;
                                    "
                                >
                                    <strong>
                                        Passenger Name
                                    </strong>
                                </td>

                                <td style="
                                    border:1px solid #dddddd;
                                ">
                                    {{ $order->customer?->name ?? 'Guest' }}
                                </td>
                            </tr>

                            <tr>
                                <td style="
                                    border:1px solid #dddddd;
                                ">
                                    <strong>
                                        Phone
                                    </strong>
                                </td>

                                <td style="
                                    border:1px solid #dddddd;
                                ">
                                    {{ $order->customer?->phone ?? 'NA' }}
                                </td>
                            </tr>

                            <tr>
                                <td style="
                                    border:1px solid #dddddd;
                                ">
                                    <strong>
                                        Email
                                    </strong>
                                </td>

                                <td style="
                                    border:1px solid #dddddd;
                                ">
                                    {{ $order->customer?->email ?? 'NA' }}
                                </td>
                            </tr>

                            <tr>
                                <td style="
                                    border:1px solid #dddddd;
                                ">
                                    <strong>
                                        Booking Number
                                    </strong>
                                </td>

                                <td style="
                                    border:1px solid #dddddd;
                                ">
                                    <strong>
                                        #{{ $order->order_number }}
                                    </strong>
                                </td>
                            </tr>

                            <tr>
                                <td style="
                                    border:1px solid #dddddd;
                                ">
                                    <strong>
                                        Passengers
                                    </strong>
                                </td>

                                <td style="
                                    border:1px solid #dddddd;
                                ">
                                    {{ getManifestOrderGuestCount($order, $date) }}
                                </td>
                            </tr>

                        </table>

                    </td>
                </tr>

                {{-- Itinerary --}}
                @if(!blank($customMessage))

                    <tr>
                        <td style="
                            padding:20px;
                            background:#eef3ff;
                        ">

                            <h2 style="margin:0 0 12px;color:#01228c;font-size:20px">
                                Tour Itinerary
                            </h2>

                            <div style="
                                margin:0;
                                line-height:1.8;
                            ">
                                {!! $customMessage !!}
                            </div>

                        </td>
                    </tr>

                @endif

                {{-- Notes --}}
                <tr>
                    <td style="
                        padding:20px;
                        background:#fff4e5;
                    ">

                        <h3 style="margin:0 0 10px;">
                            Important Notes
                        </h3>

                        <ul style="
                            padding-left:20px;
                            margin:0;
                            line-height:1.7;
                        ">

                            <li>
                                Please be ready at least
                                <strong>
                                    10 minutes before pickup time
                                </strong>.
                            </li>

                            <li>
                                Pickup timing may vary slightly
                                due to traffic conditions.
                            </li>

                            <li>
                                If you cannot locate your driver,
                                please contact us immediately.
                            </li>

                        </ul>

                    </td>
                </tr>

                {{-- Footer --}}
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