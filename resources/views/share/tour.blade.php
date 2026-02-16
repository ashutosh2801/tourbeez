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
    <meta property="og:title" content="{{ $title ?? 'TourBeez - Going Beeyond' }}">
    <meta property="og:description" content="{{ $description ?? '' }}">
    <meta property="og:image" content="{{ $image ?? asset('public/tourbeez-logo.jpg') }}">
    <meta property="og:url" content="{{ $url ?? url()->current() }}">
    <meta property="og:type" content="tours">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'TourBeez - Going Beeyond' }}">
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
     <style>
.gallery-img {
  width: 100%;
  object-fit: cover;
  border-radius: 0.75rem;
  cursor: pointer;
}
</style>
</head>
<body class="relative">
@extends('share.layout.navbar')

<main>
@if(isset($tour) )
    <!-- Description Page -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "TouristTrip",
        "name": "{{ $title }}",
        "description": "{{ $description }}",
        "image": [
            @if(!empty($tour['galleries']))
            @foreach($tour['galleries'] as $g)
                "{{ $g['original_url'] }}"@if(!$loop->last),@endif
            @endforeach
            @else
            "{{ $image ?? asset('public/tourbeez-logo.jpg') }}"
            @endif
        ],
        "offers": {
            "@type": "Offer",
            "price": "{{ $tour['price'] }}",
            "priceCurrency": "USD",
            "availability": "https://schema.org/InStock",
            "url": "{{ $url ?? url()->current() }}"
        }
        @if(!empty($tour['location']))
        , "touristType": "Sightseeing",
        "location": {
            "@type": "Place",
            "name": "{{ $tour['location']['name'] ?? '' }}",
            "address": "{{ $tour['location']['address'] ?? '' }}",
            "geo": {
            "@type": "GeoCoordinates",
            "latitude": "{{ $tour['location']['latitude'] ?? '' }}",
            "longitude": "{{ $tour['location']['longitude'] ?? '' }}"
            }
        }
        @endif
        @if(!empty($tour['itineraries']))
        , "itinerary": [
            @foreach($tour['itineraries'] as $item)
            {
                "@type": "TouristAttraction",
                "name": "{{ $item['title'] ?? '' }}",
                "description": "{{ $item['description'] ?? '' }}",
                @if(!empty($item['address']))
                "address": "{{ $item['address'] ?? '' }}"
                @endif
                }@if(!$loop->last),@endif
            @endforeach
        ]
        @endif
    }
    </script>

    <div>
        <div class="container mx-auto px-2">
        <div class="detail-header flex flex-col lg:flex-row justify-between flex-wrap">

            <div class="w-full mt-5 md:mt-10">
                <h1 class="w-full text-black !text-xl lg:!text-3xl font-semibold">
                    {{ $tour['title'] ?? 'N/A' }}
                </h1>
            </div>

            <div class="w-full">
                <div>
                    @if(!empty($tour['breadcrumbs']))
                    <ul class="w-full breadcrumb flex flex-nowrap xl:flex-wrap space-y-1 md:space-y-0 mt-2 pb-2 xl:pb-0 overflow-x-auto whitespace-nowrap xl:overflow-visible xl:whitespace-normal">
                    
                    @foreach ($tour['breadcrumbs'] as $b)
                        <li class="text-black text-xs lg:text-sm text-gray-600 hover:text-blue-900">
                            <a href="{{ $b['url'] }}" title="{{ $b['label'] }}">{{ $b['label'] }}</a>
                        </li>
                    @endforeach
                    </ul>
                    @endif                   
                </div>
            </div>

            <div class="w-full flex flex-col md:flex-row mt-2">

            <!-- Mobile actions -->
            <div class="w-full md:w-1/4 [display:ruby] md:hidden md:text-right mb-2">
                <div class="relative inline-block text-left">
                <a href="#" class="block w-max text-black font-medium text-xs lg:text-base md:px-3 py-2 mr-2 underline hover:text-black hover:bg-gray-100 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4 mr-1 inline">
                    <path fill-rule="evenodd" d="M11.47 2.47a.75.75 0 0 1 1.06 0l4.5 4.5a.75.75 0 0 1-1.06 1.06l-3.22-3.22V16.5a.75.75 0 0 1-1.5 0V4.81L8.03 8.03a.75.75 0 0 1-1.06-1.06l4.5-4.5ZM3 15.75a.75.75 0 0 1 .75.75v2.25a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V16.5a.75.75 0 0 1 1.5 0v2.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V16.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"></path>
                    </svg>
                    Share
                </a>
                </div>

                <a class="block w-max text-black font-medium text-xs lg:text-base px-3 py-2 underline hover:bg-gray-100 rounded-full cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 lg:h-5 lg:w-5 text-gray-800 inline mr-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"></path>
                </svg>
                Save
                </a>
            </div>

            <!-- Trustpilot -->
            <div class="w-full md:w-3/4 flex flex-wrap md:flex-nowrap flex-col md:flex-row md:items-center space-y-3 md:space-y-0">

            </div>

            <!-- Desktop actions -->
            <div class="w-full md:w-1/4 hidden md:[display:ruby] md:text-right mt-1 md:mt-0">
                <div class="relative inline-block text-left">
                <a href="#" class="block w-max text-black font-medium text-xs lg:text-base md:px-3 py-2 mr-2 underline hover:text-black hover:bg-gray-100 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4 mr-1 inline">
                    <path fill-rule="evenodd" d="M11.47 2.47a.75.75 0 0 1 1.06 0l4.5 4.5a.75.75 0 0 1-1.06 1.06l-3.22-3.22V16.5a.75.75 0 0 1-1.5 0V4.81L8.03 8.03a.75.75 0 0 1-1.06-1.06l4.5-4.5ZM3 15.75a.75.75 0 0 1 .75.75v2.25a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V16.5a.75.75 0 0 1 1.5 0v2.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V16.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"></path>
                    </svg>
                    Share
                </a>
                </div>

                <a class="block w-max text-black font-medium text-xs lg:text-base px-3 py-2 underline hover:bg-gray-100 rounded-full cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 lg:h-5 lg:w-5 text-gray-800 inline mr-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"></path>
                </svg>
                Save
                </a>
            </div>

            </div>
        </div>
        </div>
    </div>

    <!-- GALLERY SECTION -->
  <section class="container mx-auto px-2 mt-4">

    <!-- View More Button -->
    <div class="text-right mt-3 relative">
      <button
        id="openGalleryBtn"
        class="absolute z-10 right-2 top-1 border shadow-sm border-gray-200 bg-white rounded-md px-3 py-2 text-xs md:text-xs lg:text-sm text-black cursor-pointer">
        View More Photos
      </button>
    </div>

    <!-- MOBILE VIEW (Swiper) -->
    <div class="block md:hidden mt-3">
      <div class="swiper gallerySwiper">
        <div class="swiper-wrapper">
            @if(!empty($tour['galleries']))
                <!-- Main Image --> @php $i=0; @endphp
                @foreach ($tour['galleries'] as $g) 
                <!-- Side Images -->
                <div class="swiper-slide"><img src="{{ $g['original_url'] }}" alt="{{ $tour['title'] ?? 'Tour Image' }}" class="gallery-img" /></div>
                @endforeach
            @endif
        </div>
        <div class="swiper-pagination"></div>
      </div>
    </div>    

    <!-- DESKTOP VIEW (Grid) -->
    <div class="hidden md:grid grid-cols-5 gap-2 mt-3">

        @if(!empty($tour['galleries']))
            <!-- Main Image --> @php $i=0; @endphp
            @foreach ($tour['galleries'] as $g) 
                @if($i++ == 0)
                    <div class="col-span-3 row-span-2">
                        <img src="{{ $g['original_url'] }}" alt="{{ $tour['title'] ?? 'Tour Image' }}"
                        class="gallery-img h-[270px] lg:h-[400px]" />
                    </div>
                @else
                    <!-- Side Images -->
                    <img src="{{ $g['original_url'] }}" alt="{{ $tour['title'] ?? 'Tour Image' }}" class="gallery-img h-[130px] lg:h-[195px]" />
                @endif
                @if($i == 5) @php break;  @endphp @endif
            @endforeach
        @endif
    </div>
    </section>

    <!-- LIGHTBOX -->
    <div id="lightbox" class="fixed inset-0 bg-black/90 z-[999] hidden flex items-center justify-center">
        <button id="closeLightbox" class="absolute top-5 right-5 text-white text-3xl">&times;</button>
        <button id="prevImg" class="absolute left-5 text-white text-4xl">&#10094;</button>
        <img id="lightboxImg" class="max-h-[90vh] max-w-[90vw] rounded-lg" />
        <button id="nextImg" class="absolute right-5 text-white text-4xl">&#10095;</button>
    </div>


    <div class="container mx-auto px-2">
        <div class='flex flex-col-reverse md:flex-row md:space-x-4 justify-between mt-6 md:mt-8 space-y-6 lg:space-y-0'>
            <div class='w-full md:w-3/5 lg:w-2/3'>
            <h2 class="text-black text-xl lg:text-2xl font-bold mb-3">Overview</h2>
            <div>
                {!! $tour['description'] ?? '' !!}
            </div>
            <div>
                {!! $tour['long_description'] ?? '' !!}
            </div>    
        
            <!-- <h2 class='text-black text-xl lg:text-2xl mb-3'>Price: USD {{ $tour['price'] ?? 'N/A' }}</h2> -->
            
            <div class="flex flex-col md:flex-row flex-wrap gap-4 text-sm lg:text-base">
                @if(!empty($tour['duration']))
                    <div>
                        <p class="flex items-center text-black">
                            {{-- Clock Icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 lg:h-6 lg:w-6 text-blue-700 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $tour['duration'] }}
                        </p>
                    </div>
                @endif        
            </div>  
            
            <h2 class='text-black text-xl lg:text-2xl font-bold mb-3'>What's Included</h2>
            <div class="flex">
                {{-- Inclusions --}}
                <div class="w-1/2">
                <ul class="text-black leading-loose text-sm md:text-xs lg:text-base">
                    @if(!empty($tour['inclusions']))
                        @foreach($tour['inclusions'] as $item)
                            <li class="flex items-center">
                                {{-- Check Icon (Blue) --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-900 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $item['name'] ?? '' }}
                            </li>
                        @endforeach
                    @endif
                </ul>
                </div>

                {{-- Exclusions --}}
                <div class="w-1/2">
                <ul class="text-black leading-loose text-sm md:text-xs lg:text-base">
                    @if(!empty($tour['exclusions']))
                        @foreach($tour['exclusions'] as $item)
                            <li class="flex items-center">
                                {{-- X Icon (Red) --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-900 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                {{ $item['name'] ?? '' }}
                            </li>
                        @endforeach
                    @endif
                </ul>
                </div>
            </div>

            @if(!empty($tour['optionals']))
            <h2 class='text-black text-xl lg:text-2xl font-bold mt-3'>Optionals</h2>
            {{-- Optionals --}}
            <ul class="grid grid-cols-1 lg:grid-cols-2 py-5 text-black leading-loose text-sm md:text-xs lg:text-base">
                @foreach($tour['optionals'] as $item)
                    <li class="flex items-center">
                        {{-- Plus Icon (Blue) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-900 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ $item['name'] ?? '' }}
                    </li>
                @endforeach
            </ul>
            @endif

            @if(!empty($tour['itineraries']))
            <h2 class='text-black text-xl lg:text-2xl font-bold mb-3'>Tour Itinerary</h2>
            {{-- Itinerary --}}
            <ul class="mt-5 tour-itinerary">
                    @foreach($tour['itineraries'] as $index => $item)
                        <li class="pl-12 pb-8 relative">
                            {{-- Number Badge --}}
                            <span class="absolute left-0 text-white rounded-full bg-blue-900 px-3 pb-1 pt-px text-base">
                                {{ $index + 1 }}.
                            </span>

                            {{-- Title --}}
                            <b class="text-black w-full text-sm lg:text-base mb-2 block">
                                {{ $item['title'] ?? '' }}
                            </b>

                            {{-- Description --}}
                            <p class="text-black text-sm md:text-xs lg:text-sm mb-2 leading-relaxed">
                                {{ $item['description'] ?? '' }}
                            </p>

                            {{-- Address --}}
                            @if(!empty($item['address']))
                                <small class="text-gray-800 text-xs lg:text-sm flex items-center">
                                    {{-- Map Pin Icon --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-900 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5s-3 1.343-3 3 1.343 3 3 3zm0 0c-4.418 0-8 2.239-8 5v2h16v-2c0-2.761-3.582-5-8-5z" />
                                    </svg>
                                    {{ $item['address'] }}
                                </small>
                            @endif
                        </li>
                    @endforeach
                @endif
            </ul>

            @if(!empty($tour['location']['address']))
                <h2 class='text-black text-xl lg:text-2xl font-bold mt-3'>Where?</h2>
                <a 
                    href="https://www.google.com/maps/place/{{ urlencode($tour['location']['address']) }}" 
                    target="_blank" 
                    class="webFontColor text-sm md:text-xs lg:text-sm my-5 flex"
                >
                    {{-- Map Icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 md:h-4 lg:h-5 w-5 md:w-4 lg:w-5 mr-2 flex-shrink-0 text-blue-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5s-3 1.343-3 3 1.343 3 3 3zm0 0c-4.418 0-8 2.239-8 5v2h16v-2c0-2.761-3.582-5-8-5z" />
                    </svg>
                    {{ $tour['location']['address'] }}
                </a>
            @endif

            @if(!empty($tour['refund']['terms_and_conditions']))
                <h2 class='text-black text-xl lg:text-2xl font-bold mt-3'>Cancellation & Refund Policy</h2>
                <div class="overview">
                    {!! $tour['refund']['terms_and_conditions'] !!}
                </div>
            @endif

            @if(!empty($tour['faqs']))
            <h2 class='text-black text-xl lg:text-2xl font-bold mb-3'>Frequently Asked Questions</h2>
            {{-- FAQs --}}
            <div class="space-y-4">
                @foreach($tour['faqs'] as $index => $faq)
                    <div>                    
                        {{-- Question Button --}}
                        <h2 class="text-black font-semibold">
                            {{ $faq['question'] ?? '' }}
                        </h2>

                        {{-- Answer Content --}}
                        <div class="text-black">
                            {!! $faq['answer'] ?? '' !!}
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            @if(!empty($tour['other_description']))
            <h2 class='text-black text-xl lg:text-2xl font-bold mt-3'>Additionals</h2>
            <div>
            {{ $tour['other_description'] ?? '' }}
            </div>
            @endif
            </div>


            
            
            


<div class="w-full md:w-2/5 lg:w-1/3 mx-auto mt-10">
  <div class="sticky top-44 booking-wrap">
    <div class="w-full border border-gray-200 shadow-lg rounded-md bg-white">

      <div class="bg-pink-200 py-2 text-xs lg:text-sm text-center rounded-t-md text-black flex justify-center items-center" bis_skin_checked="1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alarm-clock w-5 h-5 text-red-800 mr-1" aria-hidden="true"><circle cx="12" cy="13" r="8"></circle><path d="M12 9v4l2 2"></path><path d="M5 3 2 6"></path><path d="m22 6-3-3"></path><path d="M6.38 18.7 4 21"></path><path d="M17.64 18.67 20 21"></path></svg> <small class="text-xs md:text-sm">Offer Ends in <b>18h 10m 06s</b></small></div>

      <div class="p-5">

        <!-- Price -->
        <h2 class="text-black text-xl font-semibold mb-3">
          <span class="text-gray-600">From</span>
          <span id="price">${{ $tour['price'] }}</span>
          <small class="text-sm text-gray-500">{{ $tour['price_type'] }}</small>
        </h2>

        <!-- FORM -->
        <form id="bookingForm">

          <b class="text-black">Select Date and Time</b>

          <!-- Date & Time -->
          <div class="flex flex-col md:flex-row gap-3 my-3">

            <!-- Date -->
            <input
              type="date"
              id="tourDate"
              class="w-full border rounded-lg px-3 py-3 text-sm text-black"
            />

            <!-- Time -->
            <select
              id="tourTime"
              class="w-full border rounded-lg px-3 py-3 text-sm bg-white text-black"
            >
              <option value="">Select Time</option>
              <option value="09:00 AM">09:00 AM</option>
              <option value="12:00 PM">12:00 PM</option>
              <option value="03:00 PM">03:00 PM</option>
            </select>

          </div>

          <!-- Guests -->
          <div class="relative my-3">
            <input
              type="text"
              id="guestDisplay"
              readonly
              class="w-full border rounded-lg px-3 py-3 cursor-pointer text-black"
              value="Select Guests"
            />

            <!-- Dropdown -->
            <div id="guestDropdown"
                 class="hidden absolute z-10 mt-2 w-full bg-white border rounded-lg shadow-md p-4 text-black">

              <!-- Adult -->
              <div class="flex justify-between items-center mb-4">
                <span>Adult</span>
                <div class="flex items-center space-x-4">
                  <button type="button" onclick="changeCount('adult',-1)"
                    class="w-6 h-6 rounded-full bg-gray-200">–</button>
                  <span id="adultCount">1</span>
                  <button type="button" onclick="changeCount('adult',1)"
                    class="w-6 h-6 rounded-full bg-gray-200">+</button>
                </div>
              </div>

              <!-- Child -->
              <div class="flex justify-between items-center mb-4">
                <span>Child</span>
                <div class="flex items-center space-x-4">
                  <button type="button" onclick="changeCount('child',-1)"
                    class="w-6 h-6 rounded-full bg-gray-200">–</button>
                  <span id="childCount">0</span>
                  <button type="button" onclick="changeCount('child',1)"
                    class="w-6 h-6 rounded-full bg-gray-200">+</button>
                </div>
              </div>

              <button type="button"
                onclick="applyGuests()"
                class="w-full bg-blue-600 text-white py-2 rounded">
                Apply
              </button>
            </div>
          </div>

          <!-- Error -->
          <p id="errorBox"
             class="hidden text-sm bg-red-100 text-red-600 border border-red-600 p-3 rounded mb-3">
          </p>

          <!-- Submit -->
          <button
            type="submit"
            class="w-full bg-blue-600 text-white py-3 rounded-md font-semibold">
            Add to Cart
          </button>

        </form>

      </div>
    </div>

    <!-- Info -->
    <div class="w-full border border-gray-200 shadow-lg p-4 rounded-md mt-5 bg-white">
      <p class="font-medium text-black">
        🔥 Book ahead!
        <small class="block text-gray-600 text-xs">
          On average, booked 43 days in advance
        </small>
      </p>
    </div>
  </div>
</div>

<script>
/* ---------------- STATE ---------------- */
const counts = {
  adult: 1,
  child: 0
};

/* ---------------- GUEST DROPDOWN ---------------- */
const guestInput = document.getElementById('guestDisplay');
const dropdown = document.getElementById('guestDropdown');

guestInput.addEventListener('click', () => {
  dropdown.classList.toggle('hidden');
});

/* ---------------- COUNTS ---------------- */
function changeCount(type, delta) {
  counts[type] = Math.max(0, counts[type] + delta);
  document.getElementById(type + 'Count').innerText = counts[type];
}

/* ---------------- APPLY GUESTS ---------------- */
function applyGuests() {
  let text = [];
  if (counts.adult > 0) text.push(`${counts.adult} Adult`);
  if (counts.child > 0) text.push(`${counts.child} Child`);

  guestInput.value = text.length ? text.join(', ') : 'Select Guests';
  dropdown.classList.add('hidden');
}

/* ---------------- FORM SUBMIT ---------------- */
document.getElementById('bookingForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const date = document.getElementById('tourDate').value;
  const time = document.getElementById('tourTime').value;
  const errorBox = document.getElementById('errorBox');

  if (!date) {
    showError('Please select a date.');
    return;
  }

  if (!guestInput.value || guestInput.value === 'Select Guests') {
    showError('Please select at least one guest.');
    return;
  }

  errorBox.classList.add('hidden');

  const bookingData = {
    date,
    time,
    guests: counts
  };

  console.log('Booking Data:', bookingData);
  alert('Added to cart (check console)');
});

/* ---------------- ERROR ---------------- */
function showError(msg) {
  const box = document.getElementById('errorBox');
  box.innerText = msg;
  box.classList.remove('hidden');
}
</script>









        </div>       
    </div>

@else
    <h1>{{ $title ?? 'TourBeez - Going Beeyond' }}</h1>
    <p>{{ $description ?? 'Discover and book amazing travel experiences with TourBeez. Plan your next adventure with ease and confidence.' }}</p>

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "TouristTrip",
            "name": "{{ $title ?? 'TourBeez - Going Beeyond' }}",
            "description": "{{ $description ?? 'Discover and book amazing travel experiences with TourBeez. Plan your next adventure with ease and confidence.' }}",
            "image": "{{ $image ?? '' }}"            
        }
    </script>
@endif
 
</main>
@extends('share.layout.footer')

</body>
</html>
