$(function () {
  $('.tabs li a').click(changeTab);

  $('#next-option').click(showNextOption);
  
  $('label.fullwidth input').click(function (e) {
    $(this).parent().toggleClass('on');
  });
  
  $('.objectives li a').click(function () {
    $(this).nextAll('ul').slideToggle('fast');
    $(this).parent('li').toggleClass('open');
    return false;
  });
  
  $('.media-delete').click(function () {
    var id = $(this).attr('rel');
    $('#media' + id).slideUp('slow', function () {
      $(this).html('<span class="warning">Current media will be deleted on save</span>');
      $(this).fadeIn();
    });
    $('#delete_media' + id).prop('checked', true);
    return false;
  });
});

function changeTab() {
  if(!$(this).parent().hasClass('on')) {
    $('.tab-area').hide();
    $('.tabs li').each(function () {
      $(this).removeClass('on');
    });
    $(this).parent().addClass('on');
    
    // Note: only works if the ID of the div matches the tab text
    var id = $(this).text().toLowerCase();
    $('#' +id).fadeIn();
  }

  return false;
}

function showNextOption() {
  var hiddenOptions = $('tbody.option.hide');
  if(hiddenOptions.length > 0) {
    if(hiddenOptions.length == 1) {
      $('#next-option-holder').fadeOut('fast');
    }
    hiddenOptions.eq(0).removeClass('hide');
  }
}
