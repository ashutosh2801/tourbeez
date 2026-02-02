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
</head>
<body class="relative">
@extends('share.layout.navbar')

<main>
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
        <div class="absolute left-1/2 transform -translate-x-1/2 -translate-y-60 md:-translate-y-60 lg:-translate-y-90 z-30 md:pt-[130px] pt-[170px] w-full top-[-140px] lg:top-[-260px]">
            <div class="w-full sm:w-150 lg:w-[650px] px-2 mx-auto text-center">

            <h1 class="text-black mb-3 font-bold text-search-shadow">
                Bee-yond Experiences
            </h1>

            <p class="mb-3 text-white font-bold text-shadow-black text-sm sm:text-md">
                Plan Better with 300,000+ Travel Experiences
            </p>

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
            </div>
        </div>
    </div>

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
                    <a href="/">Home</a>
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
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5 mt-5 py-5">
            <!-- CARD (repeat preserved) -->
            <div>
                <a href="#">
                <img src="assets/tours/8.jpg" class="w-full h-40 object-cover rounded-lg">
                <b class="text-sm font-bold text-black hover:text-blue-900 mt-3 line-clamp-2">Things to do in London</b>
                <small class="text-black">United Kingdom</small>
                </a>
            </div>

            <div>
                <a href="#">
                <img src="assets/tours/9.jpg" class="w-full h-40 object-cover rounded-lg">
                <b class="text-sm font-bold text-black hover:text-blue-900 mt-3 line-clamp-2">Things to do in Dubai</b>
                <small class="text-black">United Arab Emirates</small>
                </a>
            </div>

            <div>
                <a href="#">
                <img src="assets/tours/10.jpg" class="w-full h-40 object-cover rounded-lg">
                <b class="text-sm font-bold text-black hover:text-blue-900 mt-3 line-clamp-2">Things to do in New York</b>
                <small class="text-black">United States</small>
                </a>
            </div>

            <div>
                <a href="#">
                <img src="assets/tours/11.jpg" class="w-full h-40 object-cover rounded-lg">
                <b class="text-sm font-bold text-black hover:text-blue-900 mt-3 line-clamp-2">Things to do in Orlando</b>
                <small class="text-black">United States</small>
                </a>
            </div>

            <div>
                <a href="#">
                <img src="assets/tours/12.jpg" class="w-full h-40 object-cover rounded-lg">
                <b class="text-sm font-bold text-black hover:text-blue-900 mt-3 line-clamp-2">Things to do in Niagara Falls</b>
                <small class="text-black">Canada</small>
                </a>
            </div>
            </div>
        </div>

    </div>
</main>

@extends('share.layout.footer')

</body>
</html>
