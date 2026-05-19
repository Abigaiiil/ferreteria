const swiper = new Swiper(".mySwiper", {
  slidesPerView: 6, // Cuántos iconos ver a la vez
  spaceBetween: 20,
  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },
  breakpoints: {
    // Para celulares
    320: { slidesPerView: 2 },
    768: { slidesPerView: 4 },
    1024: { slidesPerView: 6 }
  }
});