<div>
    <!-- SLIDER -->
    <div class="bg-black">
        <div class="slider-opacity">
            <div class="swiper mySwiper w-full h-[300px] md:h-[350px] lg:h-[580px]">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                <div class="w-full h-full bg-cover bg-center" style="background-image:url('{{ asset('share/assets/slider/01.jpg') }}')"></div>
                </div>
                <div class="swiper-slide">
                <div class="w-full h-full bg-cover bg-center" style="background-image:url('{{ asset('share/assets/slider/02.jpg') }}')"></div>
                </div>
                <div class="swiper-slide">
                <div class="w-full h-full bg-cover bg-center" style="background-image:url('{{ asset('share/assets/slider/03.jpg') }}')"></div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>
    <!-- SEARCH OVER SLIDER -->
    <div class="relative">
        <div class="absolute left-1/2 transform -translate-x-1/2 -translate-y-60 md:-translate-y-60 lg:-translate-y-90 z-30 md:pt-[0px] pt-[170px] w-full top-[-140px] lg:top-[-285px]">
            <div class="w-full sm:w-150 lg:w-[650px] px-2 mx-auto text-center">

                <div class="text-center px-4" bis_skin_checked="1"><div class="inline-block mb-4" bis_skin_checked="1"><span class="bg-gradient-to-r from-pink-500 to-red-500 text-white text-xs md:text-sm font-semibold px-5 py-2 rounded-full shadow-md tracking-wide uppercase">CANADA'S #1 RATED TOUR OPERATOR</span></div><h1 class="text-white font-extrabold leading-tight 
                            text-2xl sm:text-3xl md:text-5xl lg:text-6xl 
                            drop-shadow-lg">Discover More. <br><span class="bg-gradient-to-r from-yellow-300 to-orange-400 bg-clip-text text-transparent">Travel Smarter.</span></h1><p class="mt-4 text-white text-sm sm:text-base md:text-lg max-w-2xl mx-auto leading-relaxed text-shadow-black">Skip the crowds and unlock curated experiences with expert local guides.</p><div class="flex flex-wrap justify-center items-center gap-4 my-4 text-white text-xs sm:text-sm" bis_skin_checked="1"><div class="bg-white/10 backdrop-blur-md px-3 py-1 rounded-full border border-white/30" bis_skin_checked="1">⭐ 4.9/5 (7000+ Reviews)</div><div class="bg-white/10 backdrop-blur-md px-3 py-1 rounded-full border border-white/30" bis_skin_checked="1">$ Best Price Guaranteed</div><div class="bg-white/10 backdrop-blur-md px-3 py-1 rounded-full border border-white/30" bis_skin_checked="1">🌎 1M+ Travelers</div></div></div>

                <!-- SEARCH BOX -->
                <div class="search-wrapper hero-search relative">

                    <!-- Mobile Search -->
                <div class="flex sm:hidden bg-white p-2 rounded-full shadow-lg">
                    <input
                        id="searchInputMobile"
                        type="text"
                        placeholder="Search for a place or activity"
                        class="w-full outline-none px-4 text-black"
                    />
                    <button class="p-3 webButton text-white rounded-full"><i data-lucide="search" class="h-5 w-5 text-white"></i></button>
                    </div>

                    <!-- Desktop Search -->
                    <div class="hidden sm:flex justify-between bg-white sm:p-3 lg:p-2 md:p-1 rounded-full text-black shadow-lg mx-auto w-[600px]">
                    <div class="flex flex-col pl-7 w-full text-left">
                        <label class="font-bold text-black">Where to?</label>
                        <input
                        id="searchInput"
                        type="text"
                        placeholder="Search for a place or activity"
                        class="outline-none text-black"
                        />
                    </div>
                    <button class="p-4 webButton text-white rounded-full"><i data-lucide="search" class="h-5 w-5 text-white"></i> </button>
                    </div>

                    <!-- SEARCH RESULTS -->
                    <div id="heroSearchResults" class="hidden bg-white text-black rounded-lg shadow-lg text-left mt-2 mx-auto md:w-[600px]">
                    <ul>
                        <li class="px-4 pt-4 hover:bg-gray-100 rounded-lg">
                        <a href="" class="flex border-b pb-4">
                            <svg class="w-9 h-9 text-blue-900 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-width="1.5" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-width="1.5" d="M19.5 10.5c0 7.5-7.5 11.25-7.5 11.25S4.5 18 4.5 10.5a7.5 7.5 0 1115 0z"/>
                            </svg>
                            <div>
                            <p class="font-semibold">Mississauga</p>
                            <small>Canada</small>
                            </div>
                        </a>
                        </li>

                        <li class="px-4 pt-4 hover:bg-gray-100 rounded-lg">
                        <a href="" class="flex border-b pb-4">
                            <svg class="w-9 h-9 text-blue-900 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-width="1.5" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-width="1.5" d="M19.5 10.5c0 7.5-7.5 11.25-7.5 11.25S4.5 18 4.5 10.5a7.5 7.5 0 1115 0z"/>
                            </svg>
                            <div>
                            <p class="font-semibold">Nice</p>
                            <small>France</small>
                            </div>
                        </a>
                        </li>
                    </ul>
                    </div>

                </div>

                <div class="mt-3 space-y-4 text-center" bis_skin_checked="1"><div class="inline-block" bis_skin_checked="1"><div class="text-xs text-gray-100 tracking-widest uppercase mb-4" bis_skin_checked="1">Traveler Favorite</div><div bis_skin_checked="1"><a class="mt-1 px-4 py-2 rounded-full 
                                bg-white/20 backdrop-blur-md border border-white/40 
                                text-black text-sm md:text-base font-semibold 
                                hover:bg-white/40 hover:scale-105 transition-all duration-300 cursor-pointer" href="/tour/best-value-niagara-falls-day-tour-from-toronto-pickups-from-toronto-mississauga" data-discover="true" bis_skin_checked="1">🌊 Best Value Niagara Falls Day Tour From Toronto</a></div></div><div class="text-sm sm:text-base text-white" bis_skin_checked="1"><span><a class="cursor-pointer hover:underline hover:text-yellow-500 transition-all duration-200" href="/things-to-do-in-niagara-falls/48315-c1" data-discover="true" bis_skin_checked="1">Niagara Falls</a><span class="mx-2 text-gray-400">•</span></span><span><a class="cursor-pointer hover:underline hover:text-yellow-500 transition-all duration-200" href="/things-to-do-in-toronto/10519-c1" data-discover="true" bis_skin_checked="1">Toronto</a><span class="mx-2 text-gray-400">•</span></span><span><a class="cursor-pointer hover:underline hover:text-yellow-500 transition-all duration-200" href="/things-to-do-in-brampton/10284-c1" data-discover="true" bis_skin_checked="1">Brampton</a><span class="mx-2 text-gray-400">•</span></span><span><a class="cursor-pointer hover:underline hover:text-yellow-500 transition-all duration-200" href="/things-to-do-in-mississauga/10419-c1" data-discover="true" bis_skin_checked="1">Mississauga</a></span></div><div class="flex flex-wrap justify-center gap-3 mt-4" bis_skin_checked="1"><div class="flex items-center gap-2 
                            bg-white/10 backdrop-blur-md 
                            border border-white/30 
                            px-3 py-2 rounded-full text-white text-xs sm:text-sm" bis_skin_checked="1"><span class="text-green-400">✓</span>Free Cancellation up to 24h</div><div class="flex items-center gap-2 
                            bg-white/10 backdrop-blur-md 
                            border border-white/30 
                            px-3 py-2 rounded-full text-white text-xs sm:text-sm" bis_skin_checked="1"><span class="text-green-400">✓</span>Instant Confirmation</div><div class="flex items-center gap-2 
                            bg-white/10 backdrop-blur-md 
                            border border-white/30 
                            px-3 py-2 rounded-full text-white text-xs sm:text-sm" bis_skin_checked="1"><span class="text-green-400">✓</span>24/7 Customer Support</div></div></div>

            </div>
        </div>
    </div>

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

    <div class='popular-tours'>
        <div class="container mx-auto px-2">
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
            
            <div class="grid grid-cols-1 md:grid-cols-2">
                <div class="w-full mt-7">
                    <h1 class="w-full text-black !text-xl lg:!text-3xl font-semibold">
                    Popular Tours
                    </h1>                
                </div>
            </div>
            <ul class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5 mt-5 py-5">
            @foreach($tours as $index => $item) 
                @if($loop->iteration > 5)
                    @break
                @endif 
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
            <div class="grid grid-cols-1 md:grid-cols-2">
                <div class="w-full mt-7">
                    <h1 class="w-full text-black !text-xl lg:!text-3xl font-semibold">
                    Top Destinations
                    </h1>                
                </div>
            </div>
            <ul class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5 mt-5 py-5">
                @foreach($cities as $city)
                @if($loop->iteration > 15)
                    @break
                @endif
                    <li>
                        <a href="{{ $city['url'] }}" title="{{ $city['name'] }}" class="text-black">
                            <img src="{{ $city['image'] }}" alt="{{ $city['name'] }}" loading="lazy" class="w-full h-40 object-cover rounded-lg" />
                            <h2>{{ $city['name'] }}</h2>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class='bg-gray-100 px-5 sm:px-0 py-10 md:py-10 lg:py-15 rounded-xl'>
                <h2 class='text-black text-center text-xl sm:text-2xl pb-5'>
                    Log in to Manage Bookings & Rewards
                </h2>
                <p class='text-black text-center pb-5 text-sm md:text-base lg:text-base'>Don't have an account yet? <a href="/signup" title="Sign Up" class='underline cursor-pointer'>Sign Up</a></p>
                <div class='flex item-center justify-center text-center'>
                    <a href="/login" title="Log In" class='w-80 webButton py-4 rounded-full font-bold transition duration-500 cursor-pointer'>Log In</a>
                </div>
            </div>

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
                    "url": "https://tourbeez.com/public/public/tourbeez-logo.jpg"
                    }
                },
                "blogPost": [
                    @foreach($blogs as $blog)
                    {
                        "@type": "BlogPosting",
                        "headline": "{{ $blog['name'] }}",
                        "description": "{{ $blog['name'] }}",
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
            <div class="grid grid-cols-1 md:grid-cols-2">
                <div class="w-full mt-7">
                    <h1 class="w-full text-black !text-xl lg:!text-3xl font-semibold">
                    Trending Now
                    </h1>                
                </div>
            </div>
            <ul class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5 mt-5 py-5">
                @foreach($blogs as $blog)
                @if($loop->iteration > 5)
                    @break
                @endif
                    <li>
                        <a href="{{ $blog['url'] }}" title="{{ $blog['name'] }}" class="text-black">
                            <img src="{{ $blog['image'] }}" alt="{{ $blog['name'] }}" loading="lazy" class="w-full h-40 object-cover rounded-lg" />
                            <h2>{{ $blog['name'] }}</h2>
                            <p>{{ $blog['date'] }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div>
                <div class='my-5'>
                    <div class="container mx-auto px-2">
                        <div class='flex flex-col sm:flex-row sm:space-x-5 space-y-4 sm:space-y-0'>
                            <div class="w-full sm:w-1/2 bg-blue-50 py-8 md:py-8 lg:py-15 px-4 rounded-xl text-center">
                                <h2 class='text-black font-medium text-center text-xl sm:text-2xl pb-5'>Keep things Flexible</h2>
                                <p class='px-0 md:px-0 lg:px-30 text-black text-sm lg:text-base'>Use Reserve Now & Pay Later to secure the activities you don't want to miss without being locked in.</p>
                            </div>
                            <div class="w-full sm:w-1/2 bg-blue-50 py-8 md:py-8 lg:py-15 px-4 rounded-xl text-center">
                                <h2 class='text-black font-medium text-center text-xl sm:text-2xl pb-5'>Free Cancellation</h2>
                                <p class='px-0 md:px-0 lg:px-30 text-black text-sm lg:text-base'>You'll receive a full refund if you cancel at least 24 hours in advance of most experiences.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 mb-3">
                <div class="w-full mt-7">
                    <h1 class="w-full text-black !text-xl lg:!text-3xl font-semibold">
                        Popular Cities
                    </h1>                
                </div>
            </div>
            <!-- {/* Desktop View */} -->
            <ul class="hidden md:flex flex-wrap space-y-3">
                @foreach($cities as $city)
                @if($loop->iteration > 25)
                    @break
                @endif
                <li>
                    <a href="{{ $city['url'] }}" title="{{ $city['name'] }}" class="text-gray-700 text-sm hover:text-blue-900">
                    {{ 'Things to do in '.$city['name'] }} 
                    </a>
                </li>
                @endforeach
            </ul>

            <!-- {/* Mobile View */} -->
            <ul class="grid md:hidden grid-cols-2 space-y-3">
                @foreach($cities as $city)
                @if($loop->iteration > 25)
                    @break
                @endif
                <li>
                    <a href="{{ $city['url'] }}" title="{{ $city['name'] }}" class="text-gray-700 text-sm hover:text-blue-900">
                    {{ 'Things to do in ' }}  <br /> <b>{{ $city['name'] }} </b>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>    