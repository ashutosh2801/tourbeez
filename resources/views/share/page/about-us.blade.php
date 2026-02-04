<!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
    div { color: #000;}
    /* Image cards */
    .img-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
      height: 600px;
    }
    .img-grid img {
      width: 100%;
      border-radius: 12px;
      object-fit: cover;
      box-shadow: 0 4px 10px rgba(0,0,0,.08);
    }

    /* Operating principle cards */
    .principle-card {
      display: flex;
      flex-direction: column;
      justify-content: center;
      text-align: center;
      min-height: 210px;
      max-width: 320px;
      margin: 40px auto;
      padding: 24px;
      background: #f9fafb;
      border-radius: 12px;
      border: 1px solid #f1f1f1;
      box-shadow: 0 4px 10px rgba(0,0,0,0.04);
    }
    .principle-card i {
      font-size: 36px;
      color: #1e3a8a;
      margin-bottom: 16px;
    }
  </style>

<!-- HERO SECTION -->
<div class="container mx-auto px-2">
  <div class="flex gap-4" style="align-items:center">
    <div class="w-full lg:w-1/2 pt-20 lg:pt-0 pb-10">
      <h1 class='text-black'>Travel Beyond Expectations - <span class='text-blue-900'>Smooth, Soulful, Spectacular</span></h1>
            <p class='text-gray-700 mt-5 leading-relaxed text-sm lg:text-base'>We believe travel should feel natural and exciting, not stressful. That’s why Tourbeez curates smooth, joy-filled journeys that reflect real life, real people, and real wonder. we’re your journey partner. From planning to exploring, we’re with you every step of the way, making sure every moment feels right.</p>
    </div>

    <div class="w-full lg:w-1/2">
      <div class="img-grid">
        <div class="swiper imgSwiper1">
          <div class="swiper-wrapper">
            <div class="swiper-slide"><img src="{{ asset('share/about/1.jpg') }}"></div>
            <div class="swiper-slide"><img src="{{ asset('share/about/2.jpg') }}"></div>
            <div class="swiper-slide"><img src="{{ asset('share/about/3.jpg') }}"></div>
            <div class="swiper-slide"><img src="{{ asset('share/about/4.jpg') }}"></div>
          </div>
        </div>

        <div class="swiper imgSwiper2">
          <div class="swiper-wrapper">
            <div class="swiper-slide"><img src="{{ asset('share/about/5.jpg') }}"></div>
            <div class="swiper-slide"><img src="{{ asset('share/about/6.jpg') }}"></div>
            <div class="swiper-slide"><img src="{{ asset('share/about/7.jpg') }}"></div>
            <div class="swiper-slide"><img src="{{ asset('share/about/8.jpg') }}"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ABOUT TEXT -->
  <div class='mt-10 text-center'>
          <b class='text-sm text-blue-900'>About Us</b>
          <h2 class='text-black text-xl lg:text-3xl mt-1'>Travel The World With TourBeez!</h2>
          <p class='text-gray-700 mt-3 leading-relaxed text-sm lg:text-base'>The world is full of breathtaking destinations, rich cultures, and unforgettable experiences. There's so much to explore, and TourBeez is here to make your journey fresh, fun, and enriching.
 
          TourBeez is an Online Travel platform that partners with top-rated restaurants, tour operators, and attractions worldwide, connecting travellers with unique and memorable travel experiences at great prices.
          
          We are committed to providing our customers with exceptional travel experiences—whether it's a cultural city tour, a scenic nature escape, or a fully customized adventure.
          
          TourBeez offers Day Trips, Evening Excursions, and Custom Made Tours with convenient pickup and meeting points in major cities worldwide, helping you enjoy a stress-free travel experience. Our secure and user-friendly booking portal also provides flexible payment options.<br></br>
          
          TourBeez is proud to be associated with leading tourism boards and global travel networks, ensuring quality and trust in every journey you take.</p>
        </div> 
</div>

<!-- OPERATING PRINCIPLES -->
<div class="container mx-auto px-2" style="margin-top:80px">
  <div style="text-align:center">
    <b class="text-blue">Life at TourBeez</b>
    <h2>Operating Principles</h2>
    <p style="margin-top:16px">
      We execute fast, collaborate deeply, and take ownership in everything we do.
    </p>
  </div>

  <div class="swiper principleSwiper">
    <div class="swiper-wrapper">

      <div class="swiper-slide">
        <div class="principle-card">
          <i class="fa-solid fa-crown"></i>
          <h3>High Standards</h3>
          <p>We strive to be the best in everything we do.</p>
        </div>
      </div>

      <div class="swiper-slide">
        <div class="principle-card">
          <i class="fa-solid fa-handshake"></i>
          <h3>Together is Better</h3>
          <p>We trust, care, and grow together.</p>
        </div>
      </div>

      <div class="swiper-slide">
        <div class="principle-card">
          <i class="fa-solid fa-rocket"></i>
          <h3>Move Fast</h3>
          <p>We learn quickly and adapt continuously.</p>
        </div>
      </div>

      <div class="swiper-slide">
        <div class="principle-card">
          <i class="fa-solid fa-users"></i>
          <h3>TourBeez</h3>
          <p>Our mission is to help people experience the world.</p>
        </div>
      </div>

      <div class="swiper-slide">
        <div class="principle-card">
          <i class="fa-solid fa-key"></i>
          <h3>Ownership</h3>
          <p>We take initiative and responsibility.</p>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
  const isMobile = window.innerWidth < 768;

  new Swiper(".imgSwiper1", {
    direction: isMobile ? "horizontal" : "vertical",
    slidesPerView: "auto",
    spaceBetween: 20,
    loop: true,
    speed: 2000,
    autoplay: { delay: 1, disableOnInteraction: false },
    allowTouchMove: false
  });

  new Swiper(".imgSwiper2", {
    direction: isMobile ? "horizontal" : "vertical",
    slidesPerView: "auto",
    spaceBetween: 20,
    loop: true,
    speed: 2000,
    autoplay: { delay: 1, disableOnInteraction: false, reverseDirection: true },
    allowTouchMove: false
  });

  new Swiper(".principleSwiper", {
    loop: true,
    spaceBetween: 20,
    autoplay: { delay: 3000, disableOnInteraction: false },
    speed: 800,
    breakpoints: {
      640: { slidesPerView: 1 },
      768: { slidesPerView: 3 },
      1024: { slidesPerView: 4 }
    }
  });
</script>