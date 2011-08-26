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
  
  addVariableLinks();
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
  var hiddenOptions = $('.option.hide');
  if(hiddenOptions.length > 0) {
    if(hiddenOptions.length == 1) {
      $('#add-option-holder').fadeOut('fast');
    }
    hiddenOptions.eq(0).removeClass('hide');
  }
}

function addVariableLinks() {
  $('.variable-link').each(function () {
    if ($(this).attr('rel') != undefined) {
      var target = $(this).attr('rel');
      var icon = $(this).children(':first-child').attr('id');
      $(this).bind('click', { elementID: target, iconID: icon }, variableLink);
    }
  });
}

function variableLink(event) {
  var questionID = $('#question_id').val();
  var paperID = $('#paper_id').val();
  window.open("variable_link.php?paperID=" + paperID + "&elementID=" + event.data.elementID + "&q_id=" + questionID + "&iconID=" + event.data.iconID + "","paper","width=600,height=400,left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
  return false;
}
