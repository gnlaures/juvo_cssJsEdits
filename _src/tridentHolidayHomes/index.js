window.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.page_avantio_rentals')) {

      // Init
      const galleryGrid = document.querySelector('#galleryGrid');
      const galleryGrid__images = Array.from(galleryGrid.querySelectorAll('div'));
      const galleryGrid__images__array = galleryGrid__images.map(div => div);
      galleryGrid__images__array.forEach(div => {div.classList.add('swiper-slide');});
      const galleryFull = document.querySelector('#gallery_full');

      const swiperRentalsDesktop = document.createElement('div');
      swiperRentalsDesktop.classList.add('js-swiperRentals__desktop');
      galleryFull.appendChild(swiperRentalsDesktop);
      const swiperWrapperDesktop = document.createElement('div');
      swiperWrapperDesktop.classList.add('swiper-wrapper');
      swiperRentalsDesktop.appendChild(swiperWrapperDesktop);

      galleryGrid__images__array.forEach(div => {swiperWrapperDesktop.appendChild(div);});

      const swiperRentalsMobile = document.createElement('div');
      swiperRentalsMobile.classList.add('js-swiperRentals__mobile');
      galleryFull.appendChild(swiperRentalsMobile);
      const swiperWrapperMobile = document.createElement('div');
      swiperWrapperMobile.classList.add('swiper-wrapper');
      swiperRentalsMobile.appendChild(swiperWrapperMobile);

      galleryGrid__images__array.forEach(div => {
        swiperWrapperMobile.appendChild(div.cloneNode(true));
      });

      const swiperRentalsMobileThumbs = document.createElement('div');
      swiperRentalsMobileThumbs.classList.add('js-swiperRentals__mobileThumbs');
      galleryFull.appendChild(swiperRentalsMobileThumbs);
      const swiperWrapperMobileThumbs = document.createElement('div');
      swiperWrapperMobileThumbs.classList.add('swiper-wrapper');
      swiperRentalsMobileThumbs.appendChild(swiperWrapperMobileThumbs);

      galleryGrid__images__array.forEach(div => {
        swiperWrapperMobileThumbs.appendChild(div.cloneNode(true));
      });

      const photosCsectionE = document.querySelector('#photos_section_e');
      //photosCsectionE.remove();
      photosCsectionE.style.display = 'none';

      const divs = document.querySelectorAll('.js-swiperRentals__desktop > .swiper-wrapper .swiper-slide');
      divs.forEach(div => {
        if (!div.classList.contains('galleryGrid__cover')) {
          div.classList.remove('swiper-slide');
          div.classList.add('swiper-slide-sub-item');
        }
      });
      var swiperRentalsDesktopHero = document.querySelector('.js-swiperRentals__desktop');
      var swiperWrapper = swiperRentalsDesktopHero.querySelector('.swiper-wrapper');
      var subItems = swiperRentalsDesktopHero.querySelectorAll('.swiper-slide-sub-item');
      var novaDiv;
      for (var i = 0; i < subItems.length; i++) {
          if (i % 2 === 0) {
              novaDiv = document.createElement('div');
          novaDiv.classList.add('galleryGrid__group');
          novaDiv.classList.add('swiper-slide');
        }
          novaDiv.appendChild(subItems[i]);
          if (i % 2 === 1 || i === subItems.length - 1) {
          swiperWrapper.appendChild(novaDiv);
        }
      }
      var swiperRentalsMobileThumbsEl = document.querySelector('.js-swiperRentals__mobileThumbs');
      var links = swiperRentalsMobileThumbsEl.querySelectorAll('a');
      links.forEach(function(link) {
        link.removeAttribute('href');
        link.removeAttribute('data-lightbox');
      });

      // pagination
        var mobileDiv = document.querySelector('.js-swiperRentals__mobile');
        var desktopDiv = document.querySelector('.js-swiperRentals__desktop');
        var prevButton = document.createElement('div');
        var nextButton = document.createElement('div');
        prevButton.className = 'swiper-button-prev';
        nextButton.className = 'swiper-button-next';
        mobileDiv.appendChild(prevButton);
        mobileDiv.appendChild(nextButton);
        desktopDiv.appendChild(prevButton.cloneNode(true));
        desktopDiv.appendChild(nextButton.cloneNode(true));

      const swipersConfigs = {
        swiper_mobile_thumbs: {
          slidesPerView: "auto",
          spaceBetween: 0,
          autoplay: false,
          loop: false,
        },
        swiper_desktop: {
          slidesPerView: "auto",
          spaceBetween: 0,
          loop: false,
          navigation: {
            nextEl: ".js-swiperRentals__desktop .swiper-button-next",
            prevEl: ".js-swiperRentals__desktop .swiper-button-prev",
          },
        },
      }

      const swiperRentalsDesktop__initSwiper = new Swiper('.js-swiperRentals__desktop', swipersConfigs.swiper_desktop);
      const swiperRentalsMobileThumbs__initSwiper = new Swiper('.js-swiperRentals__mobileThumbs', swipersConfigs.swiper_mobile_thumbs);
      const swiperRentalsMobile__initSwiper = new Swiper('.js-swiperRentals__mobile', {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: false,
        thumbs: {
          swiper: swiperRentalsMobileThumbs__initSwiper,
        },
        navigation: {
          nextEl: ".js-swiperRentals__mobile .swiper-button-next",
          prevEl: ".js-swiperRentals__mobile .swiper-button-prev",
        },
      });

      // fix sticky sidebar
      const sliderHeight = document.getElementById('acommodationContainerTitle').offsetHeight;

    } //end if
});


// county read more/less
document.addEventListener("DOMContentLoaded", function() {
  var toggleTitle = document.querySelector('#elementor-tab-title-5721 .elementor-toggle-title');

  // Function to toggle the text content of the toggle title
  function toggleTextContent() {
    var isActive = toggleTitle.parentElement.classList.contains('elementor-active');
    toggleTitle.textContent = isActive ? 'Read Less' : 'Read More';
  }

  // Initial toggle of text content
  toggleTextContent();

  // Add event listener to monitor class changes
  var observer = new MutationObserver(function(mutationsList) {
    for(var mutation of mutationsList) {
      if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
        toggleTextContent();
      }
    }
  });

  observer.observe(toggleTitle.parentElement, { attributes: true });

  // Scroll to .elementor-element-441e09d when "read less" is clicked
  toggleTitle.addEventListener('click', function() {
    var isActive = toggleTitle.parentElement.classList.contains('elementor-active');
    if (isActive) {
      var targetElement = document.querySelector('.elementor-element-441e09d');
      if (targetElement) {
        targetElement.scrollIntoView({ behavior: 'smooth' });
      }
    }
  });
});
