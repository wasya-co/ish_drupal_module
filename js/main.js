
$ = jQuery;
$(function () {

  $(".collapse-expand").each(function() {
    const self = this
    const thisId = $(self).attr('id')
    const state = localStorage.getItem("collapse-expand#"+thisId)
    if (null === state) {
      if ($(self).attr('class').split(/\s+/).indexOf('collapsed') != -1) {
        localStorage.setItem("collapse-expand#"+thisId, "collapsed")
        $(self).next().slideToggle()
      }
    }
    if (state === 'collapsed') {
      $(self).next().slideToggle()
      $(self).addClass('collapsed')
    }
  })
  $(".collapse-expand").click(function (_e) {
    const thisId = $(this).attr('id')
    const state = localStorage.getItem("collapse-expand#"+thisId)
    if (state === 'collapsed') {
      localStorage.setItem("collapse-expand#"+thisId, "expanded")
      $(this).removeClass('collapsed')
    } else {
      localStorage.setItem("collapse-expand#"+thisId, "collapsed")
      $(this).addClass('collapsed')
    }
    $(this).next().slideToggle();
  }).children().click(function (e) {
    e.stopPropagation()
  })


  $('.my-slider').each((idx, el) => {
    var slider = tns({
      center: true,
      container: el,
      items: 2.6,
      loop: true,
      autoplay: true,
      nav: false,
    });
  })


  window.sr = ScrollReveal();
  ScrollReveal().reveal('.fade-up', {
    distance: '40px',
    origin: 'bottom',
    duration: 800,
    easing: 'ease',
    interval: 100,
    reset: false
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

  console.log('+++ loaded ish_drupal_module main.js')
})

