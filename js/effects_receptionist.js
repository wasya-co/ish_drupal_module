
function update_parallax() {
  var scroll_y = $(window).scrollTop()
  var receptionist_offset = scroll_y * 0.35
  var slogan_offset = scroll_y * 0.5
  var background_offset = scroll_y * 0.15

  $('.receptionist2W .receptionist').css('transform', 'translate(-50%, -' + receptionist_offset + 'px)')
  $('.receptionist2W .card-row').css('transform', 'translateY(-' + receptionist_offset + 'px)')
  $('.receptionist2W .slogan').css('transform', 'translateY(-' + slogan_offset + 'px)')
  $('.receptionist2W .hero').css('background-position', 'center calc(50% + ' + background_offset + 'px)')

}

$(function() {
  $(window).on('scroll', function() {
    window.requestAnimationFrame(update_parallax)
  })
  $(window).on('resize', function() {
    update_parallax()
  })
  update_parallax()
})


console.log('+++ loaded effects_receptionist.js');
