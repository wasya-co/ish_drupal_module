function cache_flying() {
  $('.flying').each(function() {
    var $el = $(this)
    $el.css({ transform: 'none', opacity: 1 })
    $el.data('rest_top', $el.offset().top)
  })
}

function update_flying() {
  var window_h = $(window).height()
  var scroll_y = $(window).scrollTop()

  $('.flying').each(function() {
    var $el = $(this)
    var rest_top = $el.data('rest_top')
    if (rest_top == null) {
      return
    }

    var view_y = rest_top - scroll_y
    var settle_y = window_h * 0.42
    var delta = view_y - settle_y
    var translate_y = 0
    var opacity = 1

    if (delta > 0) {
      var inbound = Math.min(delta / (window_h * 0.75), 1)
      translate_y = inbound * inbound * 90
      opacity = 1 - inbound * 0.3
    } else {
      var outbound = Math.min(-delta / (window_h * 0.5), 1)
      translate_y = -outbound * outbound * window_h
      opacity = 1 - outbound
    }

    $el.css({
      transform: 'translateY(' + translate_y + 'px)',
      opacity: opacity
    })
  })
}

$(function() {
  cache_flying()
  $(window).on('scroll', function() {
    window.requestAnimationFrame(update_flying)
  })
  $(window).on('resize', function() {
    cache_flying()
    update_flying()
  })
  update_flying()
})
