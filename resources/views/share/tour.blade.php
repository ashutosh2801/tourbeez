<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'TourBeez - Going Beeyond' }}</title>
    <meta name="description" content="{{ $description ?? 'Discover and book amazing travel experiences with TourBeez. Plan your next adventure with ease and confidence.' }}">
    <meta name="robots" content="index, follow">

    {{-- Open Graph Meta --}}
    <meta property="og:title" content="{{ $title ?? 'TourBeez - Going Beeyond' }}">
    <meta property="og:description" content="{{ $description ?? '' }}">
    <meta property="og:image" content="{{ $image ?? asset('public/512x512.jpg') }}">
    <meta property="og:url" content="{{ $url ?? url()->current() }}">
    <meta property="og:type" content="article">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'TourBeez - Going Beeyond' }}">
    <meta name="twitter:description" content="{{ $description ?? '' }}">
    <meta name="twitter:image" content="{{ $image ?? asset('public/512x512.jpg') }}">

    <link rel="canonical" href="{{ $url ?? url()->current() }}">
</head>
<body>
    <h1>{{ $title ?? 'TourBeez - Going Beeyond' }}</h1>
    <p>{{ $description ?? 'Discover and book amazing travel experiences with TourBeez. Plan your next adventure with ease and confidence.' }}</p>
</body>
</html>
