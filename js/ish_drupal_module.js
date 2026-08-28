

/**
 * Usage: logg(someObject, 'label')
 *
 * This development-grade logger can be used instead of console.log() with some advantages:
 * * It encourages consistent labeling of logs. By labeling each log line, you can have dozens of log lines
 * written per action, and still know which log line comes from where.
 * The recommended label is the component name, or function name.
 * * If the label is present, the logged object is placed in the window, allowing you to inspect it in the console. The
 * label becomes the name of the object (stripped to [0-9a-zA-Z\-_] chars). If you're logging a function, you can execute it.
 * If you log more than one thing, they can interact, allowing you to validate control flow.
 * * the logger can be turned off by making this function simply return.
**/
function logg (a, b="", c=null) {
  c = "string" === typeof c ? c : b.replace(/\W/g, "");
  if (c.length > 0) {
    window[c] = a;
  }
  console.log(`+++ ${b}:`, a); // eslint-disable-line no-console
};


$ = jQuery;
$(function () {

  $('.swiper').each((idx, el) => {
    if (typeof Swiper === 'undefined' || el.swiper) {
      return;
    }
    if (!el.querySelector(':scope > .swiper-wrapper > .swiper-slide')) {
      return;
    }
    const swiper = new Swiper(el, {
      autoplay: {
        delay: 0,
        disableOnInteraction: false,
      },
      freeMode: {
        enabled: true,
        momentum: false,
      },
      loop: true,
      slidesPerView: 'auto',
      speed: 3000,

      // pagination: {
      //   el: el.querySelector('.swiper-pagination'),
      //   clickable: true,
      // },
      // navigation: {
      //   nextEl: el.querySelector('.swiper-button-next'),
      //   prevEl: el.querySelector('.swiper-button-prev'),
      // },

    });

    // Swiper eases every transition by default, which stutters at each slide boundary.
    swiper.wrapperEl.style.transitionTimingFunction = 'linear';
    console.log('+++ initialized swiper once.')
  });

  $('.tns-slider').each((idx, el) => {
    const classes = [...el.parentElement.classList]
    logg(classes, 'classes')

    var config = {
      autoplay: true,
      autoplayTimeout: 5000,
      autoWidth: true,
      center: true,
      container: el,
      loop: true,
      nav: false,
      speed: 1500,
    }
    if (classes.indexOf('continuous') != -1) {
      config = {
        autoplay: true,
        autoplayTimeout: 3000,
        autoWidth: true,
        center: true,
        container: el,
        controls: false,
        loop: true,
        mouseDrag: true,
        nav: false,
        speed: 3000,
      }
    }
    // if (classes.indexOf('autowidth') != -1) { config.autoWidth = true }
    // logg(config, 'config')
    tns(config)
  })


  window.sr = ScrollReveal();
  ScrollReveal().reveal('.fade-up', {
    distance: '40px',
    origin: 'bottom',
    duration: 800,
    easing: 'ease',
    interval: 100,
    reset: false,
  })
  ScrollReveal().reveal('.slide-right', {
    distance: '40px',
    opacity: 1,
    origin: 'left',
    duration: 800,
    easing: 'ease',
    interval: 100,
    reset: false,
  })

  console.log('+++ loaded ish_drupal_module.js', $)
})

