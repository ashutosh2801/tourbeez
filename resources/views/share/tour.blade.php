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
@if(isset($items) )
    <!-- Listing Page -->
    <script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "ItemList",
    "name": "TourBeez Tours",
    "description": "List of amazing travel experiences with TourBeez",
    "url": "{{ url()->current() }}",
    "numberOfItems": {{ count($tours ?? []) }},
    "itemListElement": [
        @foreach($items as $index => $tour)
        {
        "@type": "ListItem",
        "position": {{ $index + 1 }},
        "url": "{{ url('/tour/' . ($tour['slug'] ?? $tour->slug)) }}",
        "item": {
            "@type": "TouristTrip",
            "name": "{{ $tour['title'] ?? $tour->title }}",
            "description": "{{ $tour['description'] ?? $tour->description }}",
            "image": [
            @if(!empty($tour['galleries']))
                @foreach($tour['galleries'] as $g)
                "{{ $g['original_url'] }}"@if(!$loop->last),@endif
                @endforeach
            @else
                "{{ asset('512x512.jpg') }}"
            @endif
            ],
            "offers": {
            "@type": "Offer",
            "price": "{{ $tour['price'] ?? $tour->price ?? 0 }}",
            "priceCurrency": "USD",
            "url": "{{ url('/tour/' . ($tour['slug'] ?? $tour->slug)) }}",
            "availability": "https://schema.org/InStock"
            }
        }
        }@if(!$loop->last),@endif
        @endforeach
    ]
    }
    </script>

    @foreach($items as $index => $item)
    <div class="flex md:flex-col bg-white border border-gray-200 md:border-0 shadow-lg md:shadow-none rounded-xl relative">

        {{-- Tour Images --}}
        <div class="w-2/5 md:w-full relative">
            <a href="{{ url('/tour/' . $item['slug']) }}" title="{{ $item['title'] }}">
                <div class="swiper-container">
                    @foreach($item['all_images'] as $img)
                        <img 
                            src="{{ $img['thumb_image'] }}"
                            srcset="{{ $img['thumb_image'] }} 300w, {{ $img['medium_image'] }} 900w, {{ $img['original_image'] }} 1200w"
                            sizes="(max-width: 640px) 33vw, (max-width: 1024px) 50vw, 100vw"
                            alt="{{ $item['title'] }}"
                            class="w-full h-53 md:h-50 object-cover rounded-tl-xl rounded-bl-xl md:rounded-xl hover:scale-102 transition-all duration-500 cursor-pointer"
                            loading="lazy"
                        />
                    @endforeach
                </div>
            </a>

            <div class="absolute top-1 left-1 p-1 transition z-10">
                <button class="rounded bg-pink-200 text-red-900 text-xs lg:text-xs border-0 px-1 py-1">
                    Book Now & Pay Later
                </button>
            </div>        
        </div>

        {{-- Tour Info --}}
        <div class="w-3/5 md:w-full px-2 pt-4 pb-1 text-black flex flex-wrap">        

            <a href="{{ url('/tour/' . $item['slug']) }}" title="{{ $item['title'] }}" class="mt-2 mb-2 md:mt-3 md:mb-3 h-10 block">
                <b class="text-sm font-bold text-black hover:text-blue-900 line-clamp-2">{{ $item['title'] }}</b>
            </a>

            <div class="w-full md:flex md:flex-col md:flex-row justify-between">
                {{-- Left Info --}}
                <div class="w-full md:w-1/2 flex flex-wrap space-y-1">
                    <small class="w-full text-xs flex items-left">
                        <svg class="h-4 w-4 mr-2 text-gray-500" fill="currentColor"><!-- CheckCircleIcon SVG --></svg> 
                        Free Cancellation
                    </small>
                    @if(!empty($item['duration']))
                        <small class="w-full text-xs flex items-left">
                            <svg class="h-4 w-4 mr-2 text-gray-500" fill="currentColor"><!-- ClockIcon SVG --></svg>
                            {{ $item['duration'] }}
                        </small>
                    @endif
                </div>

                {{-- Right Info --}}
                <div class="w-full md:w-1/2 flex flex-wrap space-y-1 mt-2 md:mt-0">
                    @if($item['discount'] > 0 && $item['original_price'] > $item['discounted_price'])
                        <div class="w-full text-xs flex justify-end">
                            <p class="line-through ml-1 text-red-500 text-md font-bold">
                                USD {{ $item['original_price'] }}
                            </p>
                            <button class="rounded bg-green-700 ml-2 text-white border-0 text-xs px-1">
                                {{ $item['discount_type'] == "PERCENTAGE" ? $item['discount'] . '%' : ($item['discount']) }} off
                            </button>
                        </div>
                    @endif
                    <b class="w-full text-sm text-gray-900 flex justify-end font-semibold">
                        From <span class="ml-2">USD {{ $item['discounted_price'] }}</span>
                    </b>
                </div>
            </div>
        </div>

    </div>
    @endforeach   

@elseif(isset($tour) )
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
            "{{ $image ?? asset('public/512x512.jpg') }}"
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
    <h1>{{ $tour['title'] ?? 'N/A' }}</h1>

    <h2>Overview</h2>
    <div>
        {!! $tour['description'] ?? '' !!}
    </div>
    <div>
        {!! $tour['long_description'] ?? '' !!}
    </div>

    <div>
        @if(!empty($tour['breadcrumbs']))
        <ul>
            @foreach ($tour['breadcrumbs'] as $b)
                <li><a href="{{ $b['url'] }}">{{ $b['label'] }}</a></li>
            @endforeach
        </ul>
        @endif                   
    </div>
    <div>
        @if(!empty($tour['galleries']))
        <div>
            @foreach ($tour['galleries'] as $g)
                <img src="{{ $g['original_url'] }}" alt="{{ $tour['title'] ?? 'Tour Image' }}">
            @endforeach
        </div>
        @endif
    </div>
    <p>Price: USD {{ $tour['price'] ?? 'N/A' }}</p>
    
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
    
    <h2 className='text-black text-xl lg:text-2xl'>What's Included</h2>
    {{-- Inclusions --}}
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

    {{-- Exclusions --}}
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

    <h2 className='text-black text-xl lg:text-2xl font-semibold'>Optionals</h2>
    {{-- Optionals --}}
    <ul class="grid grid-cols-1 lg:grid-cols-2 py-5 text-black leading-loose text-sm md:text-xs lg:text-base">
        @if(!empty($tour['optionals']))
            @foreach($tour['optionals'] as $item)
                <li class="flex items-center">
                    {{-- Plus Icon (Blue) --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-900 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ $item['name'] ?? '' }}
                </li>
            @endforeach
        @endif
    </ul>

    <h2 className='text-black text-xl lg:text-2xl'>Tour Itinerary</h2>
    {{-- Itinerary --}}
    <ul class="mt-5 tour-itinerary">
        @if(!empty($tour['itineraries']))
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

    <h2 className='text-black text-xl lg:text-2xl'>Where?</h2>
    @if(!empty($tour['location']['address']))
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

    <h2>Cancellation & Refund Policy</h2>
    @if(!empty($tour['refund']['terms_and_conditions']))
        <div class="overview">
            {!! $tour['refund']['terms_and_conditions'] !!}
        </div>
    @endif

    <h2>Frequently Asked Questions</h2>
    {{-- FAQs --}}
    <div class="space-y-4">
        @if(!empty($tour['faqs']))
            @foreach($tour['faqs'] as $index => $faq)
                <div>                    
                    {{-- Question Button --}}
                    <h2>
                        {{ $faq['question'] ?? '' }}
                    </h2>

                    {{-- Answer Content --}}
                    <div>
                        {!! $faq['answer'] ?? '' !!}
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <h2 className='text-black text-xl lg:text-2xl'>Additionals</h2>
    <div>
        @if(!empty($tour['other_description']))
        {{ $tour['other_description'] ?? '' }}
        @endif
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
    
</body>
</html>
