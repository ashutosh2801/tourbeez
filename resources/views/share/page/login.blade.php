<div>
  <div>
    <form class="" action="/login" method="POST">
      <div class="container mx-auto px-2">
        <div class="flex items-center justify-center py-10 mx-auto">
          <div class="bg-white p-6 w-[550px] mt-10">

            <!-- Heading -->
            <h2 class="text-2xl font-bold mb-2 text-center text-black">
              Log in and get exploring
            </h2>

            <p class="text-black text-sm md:text-base text-center mb-3">
              Log into your account with your email, or create one below. Quick and easy - promise!
            </p>

            <!-- Server Message (hidden by default) -->
            <p class="text-sm rounded border-1 p-3 mb-2 text-red-600 bg-red-100 border-red-600 hidden" id="serverMessage">
              Invalid credentials
            </p>

            <!-- Email -->
            <input
              type="email"
              placeholder="Email"
              required
              name="email"
              class="w-full mb-3 p-3 border border-gray-300 rounded-lg outline-none text-black text-xs md:text-sm"
            />

            <!-- Password -->
            <div class="relative w-full mb-3">
              <input
                type="password"
                placeholder="Password"
                class="w-full p-3 pr-10 border border-gray-300 rounded-lg outline-none text-black text-xs md:text-sm"
                required
                autoComplete="off"
                name="password"
                id="password"
              />

              <!-- Eye toggle -->
              <button
                type="button"
                class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 hover:text-black"
                onclick="togglePassword()"
              >
                <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5
                        c4.478 0 8.268 2.943 9.542 7
                        -1.274 4.057-5.064 7-9.542 7
                        -4.477 0-8.268-2.943-9.542-7z"/>
                </svg>

                <svg id="eyeClose" xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5 hidden" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112 19
                        c-4.478 0-8.268-2.943-9.543-7
                        a9.97 9.97 0 012.042-3.368M6.223 6.223
                        A9.956 9.956 0 0112 5
                        c4.478 0 8.268 2.943 9.543 7
                        a9.973 9.973 0 01-4.132 5.411M15 12a3 3 0 00-3-3"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3l18 18"/>
                </svg>
              </button>
            </div>

            <!-- Remember + Forgot -->
            <div class="flex items-center justify-between mb-4">
              <label class="flex items-center text-xs md:text-sm text-black">
                <input type="checkbox" class="mr-2 w-4 h-4" />
                Remember me
              </label>
              <button
                type="button"
                class="!text-xs md:!text-sm text-black hover:text-blue-900 hover:underline cursor-pointer"
              >
                Forgot password?
              </button>
            </div>

            <!-- Submit -->
            <button
              class="w-full webButton text-white py-2 rounded-lg transition duration-500 cursor-pointer !text-sm md:!text-base"
            >
              Login
            </button>

            <!-- Bottom Links -->
            <div class="text-center text-xs md:text-sm text-black mt-3">
              Don't have an account?
              <button class="text-blue-900 hover:underline !text-sm cursor-pointer">
                Sign Up
              </button>
            </div>

          </div>
        </div>
      </div>
    </form>
  </div>
</div>
