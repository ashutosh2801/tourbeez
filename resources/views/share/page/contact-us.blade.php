<div>
  <div>
    <div class="container mx-auto px-2">
      <div class="relative">
        <div class="absolute z-10 top-20 md:top-25 lg:top-30">
          <h1 class="text-white mb-3">Contact Us</h1>
          <p class="font-bold text-base">Get in touch and let us know how we can help.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="bg-black">
    <img src="https://tourbeez.com/public/share/slider/contact-slide.jpg" class="w-full h-70 lg:h-100 object-cover opacity-70" />
  </div>

  <div class="pt-10">
    <div class="container mx-auto px-2">
      <div class="flex flex-col-reverse md:flex-col">

        <!-- CONTACT INFO -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-4 space-y-4">

          <!-- Email -->
          <div class="flex items-center justify-start flex-col text-center">
            <svg class="w-6 h-6 lg:w-10 lg:h-10 text-blue-900 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-width="2" d="M3 8l9 6 9-6M4 6h16a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z"/>
            </svg>
            <span class="text-gray-700 text-xs lg:text-base mt-2 font-semibold">
              info@tourbeez.com
            </span>
          </div>

          <!-- Address -->
          <div class="flex items-center justify-start flex-col text-center">
            <svg class="w-6 h-6 lg:w-10 lg:h-10 text-blue-900 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5s-3 1.343-3 3 1.343 3 3 3z"/>
              <path stroke-width="2" d="M19.5 10.5C19.5 16.299 12 21 12 21s-7.5-4.701-7.5-10.5a7.5 7.5 0 1115 0z"/>
            </svg>
            <span class="text-gray-700 text-xs lg:text-base mt-2 font-semibold">
              16 Arnold St, Toronto, ON M8Z 5A6
            </span>
          </div>

          <!-- Phone -->
          <div class="flex items-center justify-start flex-col text-center">
            <svg class="w-6 h-6 lg:w-10 lg:h-10 text-blue-900 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-width="2" d="M3 5a2 2 0 012-2h3l2 5-2 1a11 11 0 005 5l1-2 5 2v3a2 2 0 01-2 2A16 16 0 013 5z"/>
            </svg>
            <span class="text-gray-700 text-xs lg:text-base mt-2 font-semibold">
              +1-(877)-888-2339
            </span>
          </div>

          <!-- Website -->
          <div class="flex items-center justify-start flex-col text-center">
            <svg class="w-6 h-6 lg:w-10 lg:h-10 text-blue-900 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-width="2" d="M12 2a10 10 0 100 20 10 10 0 000-20z"/>
              <path stroke-width="2" d="M2 12h20M12 2a15.3 15.3 0 010 20"/>
            </svg>
            <span class="text-gray-700 text-xs lg:text-base mt-2 font-semibold">
              www.tourbeez.com
            </span>
          </div>

        </div>

        <!-- FORM + MAP -->
        <div class='pt-0 pb-10 md:pt-10 md:pb-10'>
              <div class='block md:flex items-start space-x-6 '>
            <div class='w-full md:w-3/4 lg:w-3/4'>
              <div class="text-left">
                <h2 class="text-black text-xl lg:text-3xl mt-1">
                  We are always here to help you
                </h2>
                <p class="text-gray-700 mt-3 leading-relaxed text-sm lg:text-base">
                  Please feel free to email/call us with any questions, a TourBeez representative will be happy to assist you!
                </p>
              </div>

              <form action="/contact-us" method="POST" class="w-full mt-8 space-y-5 md:space-y-2 lg:space-y-5">

                <input
                  type="text"
                  name="name"
                  placeholder="Full Name"
                  class="text-black w-full border border-gray-300 p-2 rounded outline-none text-sm md:text-xs lg:text-base"
                  required
                />

                <div class="block md:flex gap-4 space-y-4 md:space-y-0">
                  <input
                    type="email"
                    name="email"
                    placeholder="Email Address"
                    class="text-black w-full border border-gray-300 p-2 rounded outline-none text-sm md:text-xs lg:text-base"
                    required
                  />

                  <!-- PhoneInput replaced with normal input -->
                  <input
                    type="tel"
                    name="phone"
                    placeholder="Ex: +1 (555) 555-5555"
                    class="text-black w-full border border-gray-300 p-2 rounded outline-none text-sm md:text-xs lg:text-base"
                    required
                  />
                </div>

                <textarea
                  name="message"
                  placeholder="Message"
                  class="text-black w-full border border-gray-300 p-2 rounded outline-none text-sm md:text-xs lg:text-base"
                  rows="4"
                ></textarea>

                <!-- reCAPTCHA placeholder -->
                <div class="g-recaptcha" data-sitekey="YOUR_SITE_KEY"></div>

                <button
                  type="submit"
                  class="w-full webButton text-white py-3 rounded transition cursor-pointer flex justify-center items-center !text-sm !md:text-xs !lg:text-base hover:bg-blue-800"
                >
                  Submit
                  <svg class="w-5 h-5 lg:w-6 lg:h-6 text-white rotate-300 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" d="M22 2L11 13"/>
                    <path stroke-width="2" d="M22 2l-7 20-4-9-9-4z"/>
                  </svg>
                </button>

              </form>
            </div>

            <!-- MAP -->
            <div class="w-full md:w-1/2 mt-10 md:mt-0">
              <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2888.454628584217!2d-79.52459202382505!3d43.617895771103846!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x882b37d69149d6b1%3A0x6bd920a34f52c3b3!2s16%20Arnold%20St%2C%20Etobicoke%2C%20ON%20M8Z%205A6%2C%20Canada!5e0!3m2!1sen!2sin!4v1781784527872!5m2!1sen!2sin" loading="lazy" class='w-full h-80 md:h-100 lg:h-130 border border-gray-200 rounded'></iframe>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
</div>
