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
            
            <div class="container mx-auto px-2">
                <!-- Heading -->
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="w-full mt-7">
                        <h1 class="w-full text-black !text-xl lg:!text-3xl font-semibold">
                            All Tickets
                        </h1>

                        <ul class="w-full breadcrumb flex mt-2 space-x-2">
                            <li class="text-xs lg:text-sm text-gray-600 hover:text-blue-900">
                                <a href="{{ config('app.site_url') }}">Home</a>
                            </li>
                            <li class="text-xs lg:text-sm text-black">All Tickets</li>
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
                                    

                                    </ul>
                                </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5 mt-5 py-5">
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
        </div>
    </div> 