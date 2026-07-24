
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



  /*
  var slider = tns({
    container: '.my-slider',
    items: 2,
    slideBy: 'page',
    autoplay: true
  });
  // */

  $('.my-slider').each((idx, el) => {
    var slider = tns({
      center: true,
      container: el,
      items: 2.1,
      loop: true,
      autoplay: true,
    });
  })

  $('.my-slider').each((idx, el) => {
    var slider = tns({
      center: true,
      container: el,
      controls: false,
      items: 2.1,
      loop: true,
      autoplay: true,
    });
  })


  console.log('+++ loaded ish_drupal_module main.js')
})

