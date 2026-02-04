<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="https://tourbeez.com/public/favicon.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>{{ $title ?? 'TourBeez - Going Beeyond' }}</title>
    <meta name="description" content="{{ $description ?? 'Discover and book amazing travel experiences with TourBeez. Plan your next adventure with ease and confidence.' }}">
    <meta name="robots" content="index, follow">

    {{-- Open Graph Meta --}}
    <meta property="og:title" content="{{ $title ?? '' }}">
    <meta property="og:description" content="{{ $description ?? '' }}">
    <meta property="og:image" content="{{ $image ?? asset('public/tourbeez-logo.jpg') }}">
    <meta property="og:url" content="{{ $url ?? url()->current() }}">
    <meta property="og:type" content="website">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? '' }}">
    <meta name="twitter:description" content="{{ $description ?? '' }}">
    <meta name="twitter:image" content="{{ $image ?? asset('public/tourbeez-logo.jpg') }}">

    <link rel="canonical" href="{{ $url ?? url()->current() }}">
    <link href="{{ asset('share/css/index.css') }}" rel="stylesheet" />

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Heroicons -->
    <script src="https://unpkg.com/@heroicons/react@2.1.5/24/solid/index.js"></script>

    <!-- Swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
(function(w,d,s,l,i){
  if (window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1") {
    console.log("GTM disabled on localhost");
    return;
  }

  w[l]=w[l]||[];
  w[l].push({'gtm.start': new Date().getTime(), event:'gtm.js'});
  var f=d.getElementsByTagName(s)[0],
      j=d.createElement(s),
      dl=l!='dataLayer'?'&l='+l:'';
  j.async=true;
  j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;
  f.parentNode.insertBefore(j,f);

})(window,document,'script','dataLayer','GTM-M8WSBSM4');
</script>

<!-- TrustBox script -->
<script type="text/javascript" src="//widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js" async></script>
<!-- End TrustBox script -->

</head>
<body class="relative">
@extends('share.layout.navbar')

<!-- Local Business Structured Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "TourBeez, Inc.",
  "url": "https://tourbeez.com/",
  "image": "https://tourbeez.s3.amazonaws.com/uploads/all/dubai-dinner-cruise-or-sunset-with-open-bar-and-live-music55.jpg",
  "description": "Book curated travel experiences, tours and tickets worldwide with flexible booking, free cancellation and reserve now pay later options. Start planning today.",
  "priceRange": "$",
  "telephone": "+1-877-888-2339",
  "email": "info@tourbeez.com",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "1 Dundas Street West, Suite 2500",
    "addressLocality": "Toronto",
    "addressRegion": "Ontario",
    "postalCode": "M5G 1Z3",
    "addressCountry": "CA"
  },
  "openingHoursSpecification": [
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": "Monday",
      "opens": "09:00",
      "closes": "17:00"
    },
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": "Tuesday",
      "opens": "09:00",
      "closes": "17:00"
    },
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": "Wednesday",
      "opens": "09:00",
      "closes": "17:00"
    },
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": "Thursday",
      "opens": "09:00",
      "closes": "17:00"
    },
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": "Friday",
      "opens": "09:00",
      "closes": "17:00"
    },
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": "Saturday",
      "opens": "09:00",
      "closes": "17:00"
    },
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": "Sunday",
      "opens": "00:00",
      "closes": "00:00"
    }
  ],
  "sameAs": [
    "https://www.facebook.com/TourBeez/",
    "https://www.instagram.com/tourbeez/",
    "https://x.com/TourBeez",
    "https://ca.pinterest.com/tourbeez/",
    "https://www.youtube.com/@TourBeez",
    "https://www.tiktok.com/notfound",
    "https://www.threads.com/@tourbeez"
  ]
}
</script>
<!-- End Local Business Structured Data -->

<!-- Logo Structured Data -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "TourBeez, Inc.",
    "url": "https://tourbeez.com/",
    "logo": "https://tourbeez.com/public/assets/logo-DYnsi7vK.jpg",
    "description": "Book curated travel experiences, tours and tickets worldwide with flexible booking, free cancellation and reserve now pay later options. Start planning today.",
    "contactPoint": [
        {
            "@type": "ContactPoint",
            "telephone": "+1-(877)-888-2339",
            "contactType": "Customer Service",
            "areaServed": "CA",
            "availableLanguage": ["en"]
        }
    ],
    "sameAs": [
        "https://www.facebook.com/TourBeez/",
        "https://www.instagram.com/tourbeez/",
        "https://x.com/TourBeez",
        "https://ca.pinterest.com/tourbeez/",
        "https://www.youtube.com/@TourBeez",
        "https://www.tiktok.com/notfound",
        "https://www.threads.com/@tourbeez"
    ]
}
</script>
<!-- End Logo Structured Data -->
<main>

@if (!empty($file))
    @include("share.page.$file")
@else
    <div id="destinations">
        <div class="container mx-auto px-2">
            <h1>{{ $title }}</h1>
            <p>{{ $description }}</p>
        </div>
    </div>
@endif
    
</main>

@extends('share.layout.footer')
</body>
</html>
