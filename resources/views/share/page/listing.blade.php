<!-- Listing Page -->
 <!-- Swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
 <style>
  .swiper-button-next,
  .swiper-button-prev {
    width: 32px;
    height: 32px;
    background: rgba(255,255,255,0.9);
    border-radius: 9999px;
  }

  .swiper-button-next::after,
  .swiper-button-prev::after {
    font-size: 14px;
    color: #111;
  }
  .swiper-pagination-bullet-active {
    background: #FFF;
  }
</style>
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

<div id="destinations">
    <div class="container mx-auto px-2">
        <!-- Heading -->
        <div class="grid grid-cols-1 md:grid-cols-2">
            <div class="w-full mt-7">
                <h1 class="w-full text-black !text-xl lg:!text-3xl font-semibold">
                    {{ $heading }}
                </h1>

                <ul class="w-full breadcrumb flex mt-2 space-x-2">
                    <li class="text-xs lg:text-sm text-gray-600 hover:text-blue-900">
                        <a href="{{ config('app.site_url') }}">Home</a>
                    </li>
                    <li class="text-xs lg:text-sm text-gray-600 hover:text-blue-900">
                        <a href="{{ config('app.site_url') }}/destinations">Destinations</a>
                    </li>
                    <li class="text-xs lg:text-sm text-black">{{ $heading }}</li>
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
                        <div id="bodySearchResults"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid -->
    <div class="container mx-auto px-2">
        <div class="listing grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-5 py-5">
            <!-- CARD START -->
            @foreach($items as $index => $item)
            <div class="bg-white rounded-lg overflow-hidden relative">
                <!-- SWIPER -->
                <div class="swiper tourSwiper rounded-xl">
                    <div class="swiper-wrapper">
                        @foreach($item['all_images'] as $img)
                        <div class="swiper-slide">
                            <a href="{{ url('/tour/' . $item['slug']) }}" title="{{ $item['title'] }}">
                            <img 
                                src="{{ $img['thumb_image'] }}"
                                srcset="{{ $img['thumb_image'] }} 300w, {{ $img['medium_image'] }} 900w, {{ $img['original_image'] }} 1200w"
                                sizes="(max-width: 640px) 33vw, (max-width: 1024px) 50vw, 100vw"
                                alt="{{ $item['title'] }}"
                                class="w-full h-[200px] object-cover"
                                loading="lazy"
                            />
                            </a>
                        </div>
                        @endforeach           
                    </div>

                    <!-- Arrows -->
                    <div class="swiper-button-next text-white"></div>
                    <div class="swiper-button-prev text-white"></div>

                    <!-- Pagination dots -->
                    <div class="swiper-pagination"></div>
                </div>

                <!-- BOOK NOW BADGE -->
                <div class="absolute top-1 left-1 z-10">
                    <span class="rounded bg-pink-200 text-red-900 text-xs px-2 py-1">
                    Book Now & Pay Later
                    </span>
                </div>

                <!-- HEART -->
                <button class="absolute top-1 right-1 z-10 bg-white rounded-full p-[4px]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor"
                    class="w-5 h-5 text-gray-600 hover:text-yellow-400">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21.752 7.243c0-2.485-2.099-4.5-4.686-4.5-1.935 0-3.597 1.126-4.066 2.733-.47-1.607-2.131-2.733-4.066-2.733-2.587 0-4.686 2.015-4.686 4.5 0 7.45 8.752 12.207 8.752 12.207s8.752-4.757 8.752-12.207z" />
                    </svg>
                </button>

                <!-- CONTENT -->
                <div class="px-2 pt-4 pb-2 text-black">

                    <!-- Rating + Timer -->
                    <!-- <div class="flex justify-between items-center">
                        <p class="flex items-center text-sm">
                            <div
                                className="trustpilot-widget"
                                data-locale="en-US"
                                data-template-id="577258fb31f02306e4e3aaf9"
                                data-businessunit-id="68d2e213324bc49c44f037c0"
                                data-style-height="24px"
                                data-style-width="auto"
                                data-token="822ac554-79c3-4341-b0a5-7476031f1ce4"
                                data-sku={{ $item['unique_code'] }}
                                data-no-reviews="show"
                                data-scroll-to-list="true"
                                data-style-alignment="left"
                                >
                                <a
                                    href="https://www.trustpilot.com/review/tourbeez.com"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    Trustpilot
                                </a>
                            </div>
                        </p>
                        <small class="text-xs">
                            Offer Ends in <b>12h 45m 20s</b>
                        </small>
                    </div> -->

                    <!-- TITLE -->
                    <a href="{{ url('/tour/' . $item['slug']) }}" title="{{ $item['title'] }}" class="block mt-2 mb-3">
                        <p class="text-sm font-bold line-clamp-2 hover:text-blue-900">
                            {{ $item['title'] }}
                        </p>
                    </a>

                    <!-- FEATURES + PRICE -->
                    <div class="flex justify-between">

                        <!-- LEFT -->
                        <div class="space-y-1">
                            <small class="flex items-center text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" data-slot="icon" class="h-4 w-4 text-gray-500"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"></path></svg> <span class="ml-1">Free Cancellation</span>
                            </small>
                            <small class="flex items-center text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" data-slot="icon" class="h-4 w-4 text-gray-500"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd"></path></svg> <span class="ml-1">{{ $item['duration'] }}</span>
                            </small>
                        </div>

                        <!-- RIGHT -->
                        <div class="text-right space-y-1">
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
        </div>
    </div>  
</div>

<script>
  new Swiper('.tourSwiper', {
    loop: true,
    autoplay: {
      delay: 10000,
    },
    pagination: {
      el: '.swiper-pagination',
      clickable: true,
    },
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
  });
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  /* ================= ICONS ================= */
  if (window.lucide) lucide.createIcons();

  /* ================= ELEMENTS ================= */
  const dropdownBtn = document.getElementById("dropdownBtn");
  const dropdownMenu = document.getElementById("dropdownMenu");

  const currencyBtn = document.getElementById("currencyBtn");
  const currencyMenu = document.getElementById("currencyMenu");
  const currencyValue = document.getElementById("currencyValue");

  const mobileBtn = document.getElementById("mobileBtn");
  const mobileMenu = document.getElementById("mobileMenu");

  const searchBtn = document.getElementById("searchBtn");
  const searchOverlay = document.getElementById("searchOverlay");

  const heroWrapper = document.querySelector(".hero-search");
  const heroResults = document.getElementById("heroSearchResults");
  const heroInput = document.getElementById("searchInput");
  const heroInputMobile = document.getElementById("searchInputMobile");

  const topSearchInput = document.getElementById("destinationSearch");
  const topSearchResults = document.getElementById("searchResults");

  const bodySearchInput = document.getElementById("bodyDestinationSearch");
  const bodySearchResults = document.getElementById("bodySearchResults");

  /* ================= HERO SEARCH ================= */
  [heroInput, heroInputMobile].forEach(input => {
    input?.addEventListener("focus", () => {
      heroResults.classList.remove("hidden");
    });

    input?.addEventListener("input", () => {
      heroResults.classList.remove("hidden");
    });
  });

  /* ================= TOP SEARCH ================= */
  topSearchInput?.addEventListener("focus", () => {
    topSearchResults.classList.remove("hidden");
  });

  /* ================= BODY SEARCH ================= */
  bodySearchInput?.addEventListener("focus", () => {
    bodySearchResults.classList.remove("hidden");
  });

  /* ================= BUTTON TOGGLES ================= */
  dropdownBtn?.addEventListener("click", e => {
    e.stopPropagation();
    dropdownMenu.classList.toggle("hidden");
  });

  currencyBtn?.addEventListener("click", e => {
    e.stopPropagation();
    currencyMenu.classList.toggle("hidden");
  });

  mobileBtn?.addEventListener("click", e => {
    e.stopPropagation();
    mobileMenu.classList.toggle("hidden");
  });

  searchBtn?.addEventListener("click", e => {
    e.stopPropagation();

    const isHidden = searchOverlay.classList.toggle("hidden");
    searchBtn.innerHTML = isHidden
      ? `<i data-lucide="search" class="h-6 w-6 text-gray-600"></i>`
      : `<i data-lucide="x" class="h-6 w-6 text-gray-600"></i>`;

    if (window.lucide) lucide.createIcons();
  });

  /* ================= CURRENCY SELECT ================= */
  currencyMenu?.querySelectorAll("div").forEach(item => {
    item.addEventListener("click", () => {
      currencyValue.textContent = item.textContent;
      currencyMenu.classList.add("hidden");
    });
  });

  /* ================= ONE OUTSIDE CLICK HANDLER ================= */
  document.addEventListener("mousedown", e => {

    if (dropdownMenu && !dropdownMenu.contains(e.target) && !dropdownBtn.contains(e.target)) {
      dropdownMenu.classList.add("hidden");
    }

    if (currencyMenu && !currencyMenu.contains(e.target) && !currencyBtn.contains(e.target)) {
      currencyMenu.classList.add("hidden");
    }

    if (searchOverlay && !searchOverlay.contains(e.target) && !searchBtn.contains(e.target)) {
      searchOverlay.classList.add("hidden");
      searchBtn.innerHTML = `<i data-lucide="search" class="h-6 w-6 text-gray-600"></i>`;
      if (window.lucide) lucide.createIcons();
    }

    if (heroWrapper && heroResults && !heroWrapper.contains(e.target)) {
      heroResults.classList.add("hidden");
    }

    if (topSearchResults && !topSearchResults.contains(e.target) && !topSearchInput.contains(e.target)) {
      topSearchResults.classList.add("hidden");
    }

    if (bodySearchResults && !bodySearchResults.contains(e.target) && !bodySearchInput.contains(e.target)) {
      bodySearchResults.classList.add("hidden");
    }

    if (mobileMenu && !mobileMenu.contains(e.target) && !mobileBtn.contains(e.target)) {
      mobileMenu.classList.add("hidden");
    }
  });

  /* ================= ESC CLOSE ALL ================= */
  document.addEventListener("keydown", e => {
    if (e.key === "Escape") {
      dropdownMenu?.classList.add("hidden");
      currencyMenu?.classList.add("hidden");
      searchOverlay?.classList.add("hidden");
      heroResults?.classList.add("hidden");
      topSearchResults?.classList.add("hidden");
      bodySearchResults?.classList.add("hidden");
      mobileMenu?.classList.add("hidden");

      searchBtn.innerHTML = `<i data-lucide="search" class="h-6 w-6 text-gray-600"></i>`;
      if (window.lucide) lucide.createIcons();
    }
  });
});
</script>
