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
    <meta property="og:image" content="{{ $image ?? asset('public/512x512.jpg') }}">
    <meta property="og:url" content="{{ $url ?? url()->current() }}">
    <meta property="og:type" content="website">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? '' }}">
    <meta name="twitter:description" content="{{ $description ?? '' }}">
    <meta name="twitter:image" content="{{ $image ?? asset('public/512x512.jpg') }}">

    <link rel="canonical" href="{{ $url ?? url()->current() }}">
</head>
<body>
@if(isset($page) && ($page === 'home'))
    <!-- Home Page -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "TourBeez",
        "url": "{{ url()->current() }}",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "{{ url('/search?query={search_term_string}') }}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    <h1>{{ $title ?? 'TourBeez - Going Beeyond' }}</h1>
    <p>{{ $description ?? 'Discover and book amazing travel experiences with TourBeez. Plan your next adventure with ease and confidence.' }}</p>

    <h2 className="text-2xl font-bold text-black tour-font">Popular tours</h2>
    <script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "ItemList",
    "name": "Popular Tours",
    "description": "List of amazing travel experiences with TourBeez",
    "url": "{{ url()->current() }}",
    "numberOfItems": {{ count($tours ?? []) }},
    "itemListElement": [
        @foreach($tours as $index => $item)
        {
        "@type": "ListItem",
        "position": {{ $index + 1 }},
        "url": "https://tourbeez.com{{ $item['url'] }}",
        "item": {
            "@type": "TouristTrip",
            "name": "{{ $item['name'] ?? "" }}",
            "image": "{{ $item['image'] }}",
            "offers": {
                "@type": "Offer",
                "price": "{{ $item['price'] ?? 0 }}",
                "priceCurrency": "USD",
                "url": "https://tourbeez.com{{ $item['url'] }}",
                "availability": "https://schema.org/InStock"
            }
        }
        }@if(!$loop->last),@endif
        @endforeach
    ]
    }
    </script>

    <ul>
    @foreach($tours as $index => $item)  
        <li class="bg-white rounded-lg relative">
            <a href="{{ $item['url'] }}" 
                @if(!empty($external) && $external) target="_blank" @endif 
                title="{{ $item['name'] }}">
                <img 
                    src="{{ $item['image'] }}"
                    alt="{{ $item['name'] }}"
                    class="w-full h-40 md:h-40 lg:h-50 object-cover rounded-xl"
                    loading="lazy"
                />
            </a>       

            <div class="flex flex-wrap py-4 text-left">          

            <a href="{{ $item['url'] }}" 
                @if(!empty($external) && $external) target="_blank" @endif 
                title="{{ $item['name'] }}">
                <b class="text-sm font-semibold text-black h-10 mb-2 line-clamp-2">
                {{ $item['name'] }}
                </b>

                @if(!empty($item['price']))
                <p class="text-semibold text-black mt-4 text-sm lg:text-base">
                    from 
                    <b class="font-3xl">
                    {{-- Example: Include a price component --}}
                    Price: USD {{ $item['price'] }}
                    </b>
                </p>
                @endif

                @if(!empty($item['date']))
                <p class="text-semibold text-black mt-4">{{ $item['date'] }}</p>
                @endif
                
            </a>
            </div>
        </li>
    @endforeach
    </ul>

    <h2 className="text-2xl font-bold text-black tour-font">Top destinations</h2>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "ItemList",
        "name": "Popular Cities to Visit",
        "description": "Explore top travel destinations and tourist cities with amazing attractions, tours, and experiences.",
        "url": "{{ url()->current() }}",
        "numberOfItems": {{ count($cities ?? []) }},
        "itemListOrder": "https://schema.org/ItemListOrderAscending",
        "itemListElement": [
            @foreach($cities as $index => $item)
            {
                "@type": "City",
                "name": "{{ $item['name'] }}",
                "description": "{{ $item['description'] ?? 'Explore the beautiful city of ' . $item['name'] . ' with its rich culture, attractions, and experiences.' }}",
                "url": "{{ $item['url'] }}",
                "image": "{{ $item['image'] }}"
                           
            }@if(!$loop->last),@endif
            @endforeach
        ]
    }
    </script>
    <ul>
        @foreach($cities as $city)
            <li>
                <a href="{{ $city['url'] }}" title="{{ $city['name'] }}">
                    <h2>{{ $city['name'] }}</h2>
                    <img src="{{ $city['image'] }}" alt="{{ $city['name'] }}" loading="lazy" />
                </a>
            </li>
        @endforeach
    </ul>

    <h2 className="text-2xl font-bold text-black tour-font">Trending now</h2>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Blog",
        "name": "Travel & Technology Insights",
        "description": "Explore expert-written blogs about travel tips, destination guides, and technology trends for developers.",
        "url": "https://tourbeez.com/blog",
        "publisher": {
            "@type": "Organization",
            "name": "TourBeez",
            "logo": {
            "@type": "ImageObject",
            "url": "https://tourbeez.com/public/public/512x512.jpg"
            }
        },
        "blogPost": [
            @foreach($blogs as $blog)
            {
                "@type": "BlogPosting",
                "headline": "{{ $blog['title'] }}",
                "description": "{{ $blog['title'] }}",
                "url": "{{ $blog['url'] }}",
                "image": "{{ $blog['image'] }}",
                "datePublished": "{{ $blog['date'] }}",
                "dateModified": "{{ $blog['date'] }}",
                "author": {
                    "@type": "Person",
                    "name": "TourBeez Team"
                }
            }@if(!$loop->last),@endif
            @endforeach
        ]
    }
    </script>
    <ul>
        @foreach($blogs as $blog)
            <li>
                <a href="{{ $blog['url'] }}" title="{{ $blog['title'] }}">
                    <h2>{{ $blog['title'] }}</h2>
                    <img src="{{ $blog['image'] }}" alt="{{ $blog['title'] }}" loading="lazy" />
                    <p>{{ $blog['date'] }}</p>
                </a>
            </li>
        @endforeach
    </ul>
@else
    <!-- Other Pages -->
    <h1>{{ $title ?? 'TourBeez - Going Beeyond' }}</h1>
    <p>{{ $description ?? '' }}</p>
@endif
</body>
</html>
