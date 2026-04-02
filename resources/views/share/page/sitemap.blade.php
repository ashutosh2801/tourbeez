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
                <li class="text-xs lg:text-sm text-black">Sitemap</li>
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
                    
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto px-2 my-10">
    <div class="border-l-2 border-gray-300 pl-6 space-y-8">

        @foreach($sitemapData as $section)
            <div>
                <div class="relative mb-3">
                    <span class="absolute -left-[30px] top-1 w-3 h-3 webButton rounded-full"></span>
                    <h2 class="text-blue-900 text-lg font-semibold">
                        {{ $section['title'] }}
                    </h2>
                </div>

                <div class="border-l-2 border-gray-300 ml-5 pl-6 space-y-3">
                    @foreach($section['children'] as $item)
                        <div class="relative">
                            <span class="absolute -left-[31px] top-2 w-3 h-3 bg-blue-700 rounded-full"></span>

                            {{-- Destination --}}
                            @if(isset($section['is_destination']))
                                <a target="_blank"
                                    href="{{ url('/' . $item->slug) }}"
                                    class="text-black hover:underline">
                                    {{ $item->name }}
                                </a>

                            {{-- External --}}
                            @elseif(!empty($item['external']))
                                <a target="_blank"
                                    rel="noopener noreferrer"
                                    href="{{ $item['href'] }}"
                                    class="text-black hover:underline">
                                    {{ $item['title'] }}
                                </a>

                            {{-- Internal --}}
                            @else
                                <a target="_blank"
                                    href="{{ $item['href'] }}"
                                    class="text-black hover:underline">
                                    {{ $item['title'] }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

    </div>
</div>
