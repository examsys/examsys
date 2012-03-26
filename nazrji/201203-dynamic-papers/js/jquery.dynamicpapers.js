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
  $('.unmap').click(unMapQuestion);

  $('#session').change(function () { $('#year-form').submit(); });

  $('.objective').click(selUnselObjective);
});

function checkObjectives(e) {
  e.preventDefault();
  var ids = '';
  var module = $('#module').val();
  var session = $('#session').val();
  $('.map-objective:checked').each(function() { ids += $(this).val() + ','; });
  if (ids.length > 0) {
    launchAddQuestions(module, ids.replace(/,$/, ''), session);
  } else {
    alert(lang['mustselectobjectives'])
  }
}

function launchAddQuestions(module, objs, session) {
  var winH = screen.height - 80;
  var winW = screen.width - 80;
  var notice=window.open(cfgRootPath + "/question/add/add_questions_frame.php?module=" + module + "&objectives=" + objs + "&session=" + session, "notice", "width=" + winW + ", height=" + winH + ",left=40,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
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

  var count = $('.map-objective:checked').length;
  if (count == 1 && !$(this).hasClass('selected')) {
    deactivateLink('map_qns');
  } else {
    activateMapQns();
  }
}

function activateMapQns() {
  $('#map_qns').removeClass('greymenuitem');
  $('#map_qns').addClass('menuitem');
  $('#map_qns').click(checkObjectives);
}

function deactivateLink(id) {
  $('#' + id).addClass('greymenuitem');
  $('#' + id).removeClass('menuitem');
  $('#' + id).unbind('click');
  $('#' + id).click(function(e) { e.preventDefault(); });
}

function showAJAXError(message) {
  alert(message);
}
