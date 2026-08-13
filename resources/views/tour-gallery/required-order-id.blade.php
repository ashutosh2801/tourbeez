<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <link rel="apple-touch-icon" sizes="180x180" href="/admin/dist/img/fav.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/admin/dist/img/fav.png">
    <title>Verify Order ID || TourBeez</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #f4f6f9;
            font-family: Arial, Helvetica, sans-serif;
            color: #172033;
        }
        .verification-card {
            width: 100%;
            max-width: 480px;
            overflow: hidden;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 8px 28px rgba(18, 34, 76, 0.12);
        }
        .verification-header {
            padding: 26px;
            background: #01228c;
            color: #fff;
            text-align: center;
        }
        .verification-logo {
            display: block;
            width: 170px;
            max-width: 70%;
            height: auto;
            margin: 0 auto 18px;
        }
        .verification-header h1 { margin: 0 0 8px; font-size: 25px; }
        .verification-header p { margin: 0; font-size: 14px; line-height: 1.5; }
        .verification-body { padding: 28px; }
        label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 700; }
        input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #bdc5d5;
            border-radius: 8px;
            font-size: 16px;
            text-transform: uppercase;
            outline: none;
        }
        input:focus { border-color: #01228c; box-shadow: 0 0 0 3px rgba(1, 34, 140, 0.12); }
        button {
            width: 100%;
            margin-top: 18px;
            padding: 13px;
            border: 0;
            border-radius: 999px;
            background: #01228c;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
        }
        button:hover { background: #001969; }
        .help { margin: 8px 0 0; color: #667085; font-size: 13px; line-height: 1.45; }
        .error {
            margin-top: 8px;
            padding: 10px 12px;
            border-radius: 7px;
            background: #fff1f1;
            color: #b42318;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <main class="verification-card">
        <header class="verification-header">
            <img
                class="verification-logo"
                src="{{ asset('admin/dist/img/tourbeez-logo-white.png') }}"
                alt="TourBeez"
            >
            <h1>Verify Your Order</h1>
            <p>Enter your Order ID to continue to the tour photo upload page.</p>
        </header>

        <section class="verification-body">
            <form method="POST" action="{{ $verificationUrl }}">
                @csrf
                <label for="order_id">Order ID</label>
                <input
                    id="order_id"
                    name="order_id"
                    type="text"
                    value="{{ old('order_id') }}"
                    placeholder="Example: TED3FBG"
                    autocomplete="off"
                    required
                    autofocus
                >
                <p class="help">You can find the Order ID in your booking confirmation.</p>

                @error('order_id')
                    <div class="error">{{ $message }}</div>
                @enderror

                <button type="submit">Continue to Upload</button>
            </form>
        </section>
    </main>
</body>
</html>
