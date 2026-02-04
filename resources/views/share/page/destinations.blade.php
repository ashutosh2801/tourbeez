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
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "ItemList",
        "name": "Discover all cities worldwide",
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

    <div id="destinations">

        <div class="container mx-auto px-2">
            <!-- Heading -->
            <div class="grid grid-cols-1 md:grid-cols-2">
                <div class="w-full mt-7">
                    <h1 class="w-full text-black !text-xl lg:!text-3xl font-semibold">
                        Discover all cities worldwide
                    </h1>

                    <ul class="w-full breadcrumb flex mt-2 space-x-2">
                        <li class="text-xs lg:text-sm text-gray-600 hover:text-blue-900">
                            <a href="{{ config('app.site_url') }}">Home</a>
                        </li>
                        <li class="text-xs lg:text-sm text-black">Destinations</li>
                    </ul>
                </div>

                <!-- body Search -->
                <div class="w-full mt-7">
                    <div class="search-wrapper body-search w-full flex justify-end relative">
                        <form class="flex w-full lg:w-[450px] bg-white rounded-full shadow-md overflow-hidden mb-1 md:mb-4 px-1 py-1 border border-gray-300 z-45">
                            <input
                            id="bodyDestinationSearch"
                            type="text"
                            placeholder="Search for a Place or Activity"
                            class="flex-grow px-4 py-1 text-gray-700 font-semibold bg-transparent focus:outline-none text-sm lg:text-base"
                            />

                            <button type="button" class="p-3 webButton text-white rounded-full cursor-pointer">
                            <i data-lucide="search" class="h-5 w-5 text-white"></i>    
                            </button>

                            <!-- Search Results -->
                            <div id="bodySearchResults" class="hidden absolute z-11 lg:top-[50px] w-full lg:w-[450px]">
                            <div class="bg-white rounded-lg text-black shadow-lg text-left mt-2 mr-1 border border-gray-100">
                                <ul>
                                <!-- RESULT ITEM (repeated exactly as React) -->
                                <li class="px-4 pt-4 hover:bg-gray-100">
                                    <a href="#" class="flex border-b border-gray-300 pb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 lg:w-9 lg:h-9 text-blue-900 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path d="M19.5 10.5c0 7.5-7.5 11.25-7.5 11.25S4.5 18 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-sm lg:text-base">Niagara Falls</p>
                                        <small class="text-xs">Canada</small>
                                    </div>
                                    </a>
                                </li>

                                <li class="px-4 pt-4 hover:bg-gray-100">
                                    <a href="#" class="flex border-b border-gray-300 pb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 lg:w-9 lg:h-9 text-blue-900 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path d="M19.5 10.5c0 7.5-7.5 11.25-7.5 11.25S4.5 18 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-sm lg:text-base">Nile River</p>
                                        <small class="text-xs">Egypt</small>
                                    </div>
                                    </a>
                                </li>

                                </ul>
                            </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid -->
        <div class="container mx-auto px-2">
            <ul class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5 mt-5 py-5">
                @foreach($cities as $city)
                <li>
                    <a href="{{ $city['url'] }}" title="Things to do in  {{ $city['name'] }}" class="text-black">
                        <img src="{{ $city['image'] }}" alt="{{ $city['name'] }}" loading="lazy" class="w-full h-40 object-cover rounded-lg" />
                        <h2 class="font-md text-black">Things to do in {{ $city['name'] }}</h2>
                        <p class="font-sm text-gray-600">{{ $city['extra'] }}</p>
                    </a>
                </li>
                @endforeach
            </ul>  
        </div>
    </div>