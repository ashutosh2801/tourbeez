<div>
  <nav class="nav-bg fixed top-0 left-0 w-full shadow z-[55]">
    <div class="container mx-auto px-2 py-3 flex items-center justify-between">

      <!-- Left: Logo -->
      <div>
        <a href="{{ config('app.site_url') }}">
          <img src="{{ asset('share/assets/logo.jpg') }}" class="w-auto h-[48px] md:h-[60px] lg:h-[68px]" loading="lazy" alt="TourBeez - Going Beeyond" />
        </a>
      </div>

      <!-- Center Menu -->
      <ul class="hidden md:flex justify-between items-center space-x-6 bg-white px-4 py-2 rounded-full md:w-[400px] lg:w-[560px]">
        <li>
          <a href="{{ config('app.site_url') }}" class="md:text-xs lg:text-base text-black font-medium hover:text-blue-900 flex items-center">
            <img src="{{ asset('share/assets/icons/icon-1.jpg') }}" class="h-10 pr-2" /> Home
          </a>
        </li>
        <li>
          <a href="{{ config('app.site_url') }}" class="md:text-xs lg:text-base text-black font-medium hover:text-blue-900 flex items-center">
            <img src="{{ asset('share/assets/icons/icon-2.jpg') }}" class="h-10 pr-2" /> Destinations
          </a>
        </li>
        <li>
          <a href="{{ config('app.site_url') }}" class="md:text-xs lg:text-base text-black font-medium hover:text-blue-900 flex items-center">
            <img src="{{ asset('share/assets/icons/icon-3.jpg') }}" class="h-10 pr-2" /> Tickets
          </a>
        </li>
      </ul>

      <!-- Right Icons -->
      <div class="hidden relative md:block">
        <div class="md:flex">

          <!-- Search Button -->
          <button id="searchBtn" class="md:text-xs lg:text-base p-3 mr-2 rounded-full bg-white flex items-center cursor-pointer">
            <i data-lucide="search" class="h-6 w-6 text-gray-600"></i>
          </button>

          <!-- Wishlist -->
          <button onclick="window.location.href='/wishlist'" class="flex items-center rounded-full p-3 mr-2 bg-white cursor-pointer wishlist-icon">
            <i data-lucide="heart" class="h-6 w-6 text-gray-600 hover:text-yellow-400"></i>
          </button>

          <!-- Menu -->
          <button id="dropdownBtn" class="flex items-center rounded-full p-3 bg-white cursor-pointer">
            <i data-lucide="menu" class="h-6 w-6 text-gray-600"></i>
          </button>
        </div>

        <!-- Dropdown -->
        <div id="dropdownMenu" class="hidden absolute right-0 mt-5 w-[200px] bg-white border rounded-md shadow-md py-2 z-50 text-black">
          <div class="absolute -top-2 right-2 w-4 h-4 bg-white rotate-45 border-l border-t border-gray-200"></div>

            <!-- Currency -->
            <div class="relative">
                <button id="currencyBtn" class="w-full flex items-center justify-between px-4 py-2 text-sm hover:bg-gray-100 cursor-pointer border-b border-gray-200">
                    <div class="flex items-center">
                        <!-- Currency Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-700 mr-2 border rounded-full p-0.5 border-gray-500" viewBox="0 0 288 512"><path d="M209.2 233.4l-108-31.6C88.7 198.2 80 186.5 80 173.5c0-16.3 13.2-29.5 29.5-29.5h66.3c12.2 0 24.2 3.7 34.2 10.5 6.1 4.1 14.3 3.1 19.5-2l34.8-34c7.1-6.9 6.1-18.4-1.8-24.5C238 74.8 207.4 64.1 176 64V16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v48h-2.5C45.8 64-5.4 118.7.5 183.6c4.2 46.1 39.4 83.6 83.8 96.6l102.5 30c12.5 3.7 21.2 15.3 21.2 28.3 0 16.3-13.2 29.5-29.5 29.5h-66.3C100 368 88 364.3 78 357.5c-6.1-4.1-14.3-3.1-19.5 2l-34.8 34c-7.1 6.9-6.1 18.4 1.8 24.5 24.5 19.2 55.1 29.9 86.5 30v48c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-48.2c46.6-.9 90.3-28.6 105.7-72.7 21.5-61.6-14.6-124.8-72.5-141.7z"/></svg>

                        <span id="currencyValue">CAD</span>
                    </div>

                    <i data-lucide="chevron-down" class="h-4 w-4 text-gray-500"></i>
                </button>

                <div id="currencyMenu" class="hidden absolute right-0 mt-1 w-full bg-gray-50 border border-gray-200">
                    <div class="px-4 py-2 text-sm hover:bg-gray-100 cursor-pointer">CAD</div>
                    <div class="px-4 py-2 text-sm hover:bg-gray-100 cursor-pointer">USD</div>
                    <div class="px-4 py-2 text-sm hover:bg-gray-100 cursor-pointer">INR</div>
                    <div class="px-4 py-2 text-sm hover:bg-gray-100 cursor-pointer">EUR</div>
                    <div class="px-4 py-2 text-sm hover:bg-gray-100 cursor-pointer">GBP</div>
                </div>
            </div>

            <!-- Login -->
            <a href="#" class="flex items-center px-4 py-2 text-sm hover:bg-gray-100">
                <!-- User Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="w-4 h-4 text-gray-700 mr-2"><path d="M313.6 304c-28.7 0-42.5 16-89.6 16-47.1 0-60.8-16-89.6-16C60.2 304 0 364.2 0 438.4V464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-25.6c0-74.2-60.2-134.4-134.4-134.4zM400 464H48v-25.6c0-47.6 38.8-86.4 86.4-86.4 14.6 0 38.3 16 89.6 16 51.7 0 74.9-16 89.6-16 47.6 0 86.4 38.8 86.4 86.4V464zM224 288c79.5 0 144-64.5 144-144S303.5 0 224 0 80 64.5 80 144s64.5 144 144 144zm0-240c52.9 0 96 43.1 96 96s-43.1 96-96 96-96-43.1-96-96 43.1-96 96-96z"/></svg>

                Log In or Sign Up
            </a>

            <!-- Contact -->
            <a href="/contact-us" class="flex items-center px-4 py-2 text-sm hover:bg-gray-100">
                <!-- Envelope Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-700 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 8h18a2 2 0 002-2V6a2 2 0 00-2-2H3a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>

                Contact Us
            </a>
        </div>
      </div>

      <!-- Mobile Hamburger -->
      <button id="mobileBtn" class="md:hidden">
        <i data-lucide="menu" class="h-8 w-8"></i>
      </button>
    </div>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="hidden md:hidden bg-white shadow-md px-4 pb-4 pt-4 space-y-3">
      <a href="/" class="block text-sm hover:text-blue-600">Home</a>
      <a href="#" class="block text-sm hover:text-blue-600">Experiences</a>
      <a href="#" class="block text-sm hover:text-blue-600">Services</a>
      <hr />
      <a href="/contact-us" class="block text-sm hover:text-blue-600">Contact Us</a>
      <a href="#" class="block text-sm hover:text-blue-600">Log In or Sign Up</a>
    </div>
  </nav>

  <!-- Top Search Overlay -->
  <div id="searchOverlay" class="fixed inset-0 z-[51] hidden flex justify-center items-start pt-[90px] transition-all duration-300 backdrop-blur-sm bg-black/40 opacity-100">
    <div class="w-[560px] md:w-[400px] lg:w-[560px] block mt-20 mx-5 md:mx-0 relative">
      <!-- Search component placeholder -->
      <form class="flex w-full bg-white rounded-full shadow-md overflow-hidden mb-1 md:mb-4 px-1 py-1 border border-gray-300">
        <input
            id="destinationSearch"
            type="text"
            placeholder="Search for a Place or Activity"
            class="flex-grow px-2 lg:px-4 py-2 text-gray-700 font-semibold bg-transparent focus:outline-none text-sm lg:text-base"
        />

        <button type="button" class="p-3 lg:p-4 webButton text-white rounded-full cursor-pointer">
            <i data-lucide="search" class="h-5 w-5 text-white"></i>    
        </button>

        <!-- Search Results -->
        <div id="searchResults" class="hidden absolute z-[11] lg:top-[60px] w-full">
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