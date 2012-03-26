// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

$(function () {
  $.ajaxSetup({ timeout: 6000 });
  $('#content').ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
    showAJAXError(lang['ajaxerror']);
  });

  // TODO: handle 'by question' display
  deactivateLink('map_qns');
  deactivateLink('map_sess');
  $('.unmap').click(unMapQuestion);

  $('#session').change(function () { $('#year-form').submit(); });

  $('.map-objective').click(selUnselObjective);
  $('.map-question').click(selUnselQuestion);
});

function checkObjectives(e) {
  e.preventDefault();
  var ids = '';
  var module = $('#module').val();
  var session = $('#session').val();
  $('.sel-objective:checked').each(function() { ids += $(this).val() + ','; });
  if (ids.length > 0) {
    launchMappingWindow(cfgRootPath + '/question/add/add_questions_frame.php?module=' + module + '&objectives=' + ids.replace(/,$/, '') + '&session=' + session);
  } else {
    alert(lang['mustselectobjectives'])
  }
}

function checkQuestions(e) {
  e.preventDefault();
  var ids = '';
  var module = $('#module').val();
  var session = $('#session').val();
  $('.sel-question:checked').each(function() { ids += $(this).val() + ','; });
  if (ids.length > 0) {
    launchMappingWindow(cfgRootPath + '/mapping/map_question.php?module=' + module + '&questions=' + ids.replace(/,$/, '') + '&session=' + session);
  } else {
    alert(lang['mustselectquestions'])
  }
}

function launchMappingWindow(url) {
  var winH = screen.height - 80;
  var winW = screen.width - 80;
  var notice=window.open(url, "notice", "width=" + winW + ", height=" + winH + ",left=40,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
  if (window.focus) {
    notice.focus();
  }
}

function unMapQuestion(e) {
  e.preventDefault();
  if (confirm(lang['ajaxconfirm'])) {
    var data = $(this).attr('rel').split('_');
    var module, qID, oID, session;
    var li = $(this).parent();

    if (data.length == 2) {
      module = $('#module').val();
      oID = data[0];
      qID = data[1];
      session = $('#session').val();
      $.post('../ajax/dynamic_paper/remove_mapping.php',
         {
           module: module,
           objective: oID,
           question: qID,
           session: session
         },
         function(data) {
          if (data == 'INVALID INPUT') {
            showAJAXError(lang['ajaxerror']);
          } else {
            li.remove();
          }
      });
    }
  }
}

function selUnselObjective() {
  $(this).toggleClass('selected');

  var count = $('.sel-objective:checked').length;
  if (count == 1 && !$(this).hasClass('selected')) {
    deactivateLink('map_qns');
  } else {
    activateMapQns();
  }
}

function selUnselQuestion() {
  $(this).toggleClass('selected');

  var count = $('.sel-question:checked').length;
  if (count == 1 && !$(this).hasClass('selected')) {
    deactivateLink('map_sess');
  } else {
    activateMapSess();
  }
}

function activateMapQns() {
  if ($('#map_qns').hasClass('greymenuitem')) {
    $('#map_qns').removeClass('greymenuitem');
    $('#map_qns').addClass('menuitem');
    $('#map_qns').click(checkObjectives);
  }
}

function deactivateLink(id) {
  $('#' + id).addClass('greymenuitem');
  $('#' + id).removeClass('menuitem');
  $('#' + id).unbind('click');
  $('#' + id).click(function(e) { e.preventDefault(); });
}

function activateMapSess() {
  if ($('#map_sess').hasClass('greymenuitem')) {
    $('#map_sess').removeClass('greymenuitem');
    $('#map_sess').addClass('menuitem');
    $('#map_sess').click(checkQuestions);
  }
}

function showAJAXError(message) {
  alert(message);
}
