// EXTERNAL SCRIPT
// <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script> 


//FOR CAROUSEL SWIPPER
new Swiper('.card-wrapper', {  
  loop: true,  
  speed: 700,  
  spaceBetween: 30, 
  centeredSlides: true,  
  centeredSlidesBounds: true, 

  // If we need pagination  
  pagination: {  
    el: '.swiper-pagination',  
    clickable: true,  
    dynamicBullets: true,  
  },  

  // Navigation arrows  
  navigation: {  
    nextEl: '.swiper-button-next',  
    prevEl: '.swiper-button-prev',  
  },  
  
  breakpoints: { 
    0: {  
      slidesPerView: 1  
    },  
    768: {  
      slidesPerView: 2  
    },  
    1024: {  
      slidesPerView: 3  
    },  
  }  
});  