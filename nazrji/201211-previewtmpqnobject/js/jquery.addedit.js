$(function () {
  if (typeof postExam != 'undefined' && postExam) {
    if (otherSummatives == 0) {
      $('#option_marks_correct').focus(function() {
          prev_val = $(this).val();
      }).change(function () { $(this).blur(); return showMarksWarning($(this)); });
      $('#option_marks_incorrect').focus(function() {
          prev_val = $(this).val();
      }).change(function () { $(this).blur(); return showMarksWarning($(this)); });
      $('#option_marks_partial').focus(function() {
          prev_val = $(this).val();
      }).change(function () { $(this).blur(); return showMarksWarning($(this)); });
    } else {
      $('#option_marks_correct').attr('disabled', 'disabled');
      $('#option_marks_incorrect').attr('disabled', 'disabled');
      $('<input type="hidden" id="option_marks_correct_hidden" name="option_marks_correct" value="' + $('#option_marks_correct').val() + '" />').insertAfter('#option_marks_correct');
      $('<input type="hidden" id="option_marks_incorrect_hidden" name="option_marks_incorrect" value="' + $('#option_marks_incorrect').val() + '" />').insertAfter('#option_marks_incorrect');
    }
  }

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
    $('#existing_media' + id).val('');
    return false;
  });
  
  $('.extmatch-option').blur(updateExtMatchOptions);
  
  $('.sct-type').change(updateSctType);
  
  $('#scale_type').change(checkShowLikertCustom);
  
  $('.dichotomous-display').change(updateDichotomousLabels);
  $('.blank-display').change(updateBlankInstructions);
  $('#score_method').change(showPartialMarks);

  addVariableLinks();
  $('.sct-type').trigger('change');
  
  trimLongChanges();
  
  $('#addbank').click(checkMapping);
  
  $('#media-labels-link').click(function() { $('#media-label-upload').slideToggle(); return false;})
  
  $('#addquestion').click(addQuestion);

  $(".tiptop").tipTip({defaultPosition: 'top'});
});

function changeTab() {
  if (!$(this).parent().hasClass('disabled')) {
    if (!$(this).parent().hasClass('on')) {
      $('.tab-area').hide();
      $('.tabs li').each(function () {
        $(this).removeClass('on');
      });
      $(this).parent().addClass('on');
      
      var id = $(this).attr('rel');
      $('#' + id).fadeIn();
    }
  }

  return false;
}

function showNextOption() {
  var hiddenOptions = $('.option.hide');
  if (hiddenOptions.length > 0) {
    if (hiddenOptions.length == 1) {
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
      if (!$(this).hasClass('disabled')) {
        $(this).bind('click', { elementID: target, iconID: icon }, variableLink);
      } else {
        $(this).bind('click', function (e) { e.preventDefault(); });
      }
    }
  });
}

function variableLink(event) {
  var questionID = $('#question_id').val();
  var paperID = $('#paperID').val();
  window.open("variable_link.php?paperID=" + paperID + "&elementID=" + event.data.elementID + "&q_id=" + questionID + "&iconID=" + event.data.iconID + "","paper","width=600,height=400,left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
  return false;
}

function updateExtMatchOptions() {
  var index = $(this).attr('rel');
  var raw_text = $(this).val();
  var text = parseInt(index) + '. ' + raw_text;
  var opt_text = '';

  if(index != undefined) {
    $('.extmatch-correct').each(function () {
      options = $(this).children('option');
      if (index > options.length) {
        if (raw_text != '') {
          for (i = options.length + 1; i <= index; i++) {
            opt_text = (i == index) ? text : i + '.';
            $(this).append('<option value="' + i + '">' + opt_text + '</option>');
          }
        }
      } else {
        options.get(index - 1).text = text;
      }
    });
  }
}

function updateSctType() {
  var type_index = $(this).val() - 1;
  $('#sct-hypothesis').text(sct_types[type_index][0]);
  
  $('.sct-option').each(function (i) {
    $(this).val(sct_types[type_index][i + 1]);
  })
}

function checkShowLikertCustom() {
  if ($(this).val() == 'custom') {
    $('#extended-option-list').slideDown();
  } else {
    $('#extended-option-list').slideUp();
  }
}

function updateDichotomousLabels() {
  var positive = 'T';
  var negative = 'F';
  
  if ($(this).val().substr(0, 2) == 'YN') {
    positive = 'Y';
    negative = 'N';
  }

  $('.dichotomous-true').html(positive);
  $('.dichotomous-false').html(negative);
}

function updateBlankInstructions() {
  var visible = $(this)[0].selectedIndex + 1;
  var hidden = ((visible % 2) + 1);
  $('#instructions' + hidden).fadeOut('fast', function () { $('#instructions' + visible).fadeIn('fast'); }); 
}

function showPartialMarks () {
  if ($('#score_method :selected').text() == lang['allowpartial']) {
    $('.marks-partial').fadeIn('fast');
  } else {
    $('.marks-partial').fadeOut('fast');
  }
}

function trimLongChanges() {
  $('a.more').click(function (e, i) {
    $(this).prev().prev().prev().toggle();
    $(this).prev().prev().slideToggle();
    if ($(this).text() == lang['showmore']) {
      $(this).text(lang['hidemore'])
    } else {
      $(this).text(lang['showmore'])
    }
  });
}

function checkMapping() {
  var checked = $('.objectives input:checked');
  if (checked.length > 0) {
    return confirm(lang['mappingwarning']);
  }
  
  return true;
}

function addQuestion() {
  winH = screen.height - 80
  winW = screen.width - 80
  notice=window.open("../add/add_random_questions_frame.php?q_no=1&questionlist=questionlist&question_no=question_no","notice","width=" + winW + ",height=" + winH + ",left=40,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
  if (window.focus) {
    notice.focus();
  }
}

function addQuestionsToList(questions) {
  var current_qns = $('#questionlist li');
  var num_options = current_qns.length + 1;
  
  for (i = 0; i < questions.length; i++) {
    $('#questionlist').append('<li><label for="option_text' + (num_options + i) + '" class="fullwidth"><input id="option_text' + (num_options + i) + '" name="option_text' + (num_options + i) + '" value="' + questions[i][0] + '" type="checkbox" checked="checked" class="random-q" /> ' + questions[i][1] + '</label><input name="optionid' + (num_options + i) + '" value="-1" type="hidden" /></li>');
  }

  $('#questioncheck').valid();
}

function showMarksWarning(element) {
  var rval = false;
  if (!postExamWarningShown) {
    rval = confirm(lang['markchangewarning']);
    if (!rval) {
      element.val(prev_val);
    } else {
      postExamWarningShown = true;
    }
  }
  return rval;
}
