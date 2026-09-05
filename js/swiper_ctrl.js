

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


jQuery(function () {

  /*
   * this was continuous scroll I think
  **/
  $('.swiper').each((idx, el) => {
    if (typeof Swiper === 'undefined' || el.swiper) {
      return;
    }
    if (!el.querySelector(':scope > .swiper-wrapper > .swiper-slide')) {
      return;
    }


    if (el.classList.contains('one-per-page')) {
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
      });

    } else if (el.classList.contains('continuous')) {
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
    }

    console.log('+++ initialized swiper once.')
  })

  console.log('+++ loaded swiper_ctrl.js', $)
})

