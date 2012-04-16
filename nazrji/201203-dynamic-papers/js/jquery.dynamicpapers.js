// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

$(function () {
  $.ajaxSetup({ timeout: 6000 });
  $('#content').ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
    showAJAXError(lang['ajaxerror']);
  });

  var module = $('#module').val();
  var session = $('#session').val();

  deactivateLink('map_qns');
  deactivateLink('map_sess');
  deactivateLink('unmap');

  $('#session').change(function () { $('#year-form').submit(); });

  $('.map-objective').click(function (e) { e.stopPropagation() });
  $('.map-question').click(function (e) { e.stopPropagation() });
  $('.sel-objective').click(selUnselObjective);
  $('.sel-question').click(selUnselQuestion);

  $('.mapped-item').click(selMapTarget);

  $('.q-link a').dblclick(function (e) {
    e.preventDefault();
    var data = $(this).attr('rel').split('_');
    if (data.length == 2) {
      location.href = '../question/edit/index.php?q_id=' + data[1] + '&module=' + module + '&session=' + session + '&calling=dynamic';
    }
  });

  $('#add_qns a').click(function (e) {
    e.preventDefault();
    launchMappingWindow(cfgRootPath + '/question/add/add_questions_frame.php?module=' + module + '&session=' + session);
  });

  $('html').click(clearAllSelections);

function checkObjectives(e) {
   e.preventDefault();
   var ids = '';
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
   deactivateLink('unmap', e);

   if (confirm(lang['ajaxconfirm'])) {
     if ($(this).data('objective') != '' && $(this).data('question') != '') {
       var oID = $(this).data('objective');
       var qID= $(this).data('question');
       var li = $('#map' + oID + '_' + qID);

       setTimeout(function() {
         if (li.is(':visible')) {
           $('#unmap').addClass('loading');
         }
       }, 1000);

       $.post('../ajax/dynamic_paper/remove_mapping.php',
         {
           module: module,
           objective: oID,
           question: qID,
           session: session
         },
         function(data) {
           $('#unmap').removeClass('loading');
           if (data == 'INVALID INPUT') {
             showAJAXError(lang['ajaxerror']);
           } else {
             li.remove();
         }
       });
     }
   }
 }

 function selUnselObjective(e) {
   clearMappedSelections();

   $(this).next().toggleClass('selected');

   var count = $('.sel-objective:checked').length;
   if (count == 0) {
     deactivateLink('map_qns');
   } else {
     activateMapQns();
   }
   e.stopPropagation();
   e.stopImmediatePropagation();
 }

 function selUnselQuestion(e) {
   clearMappedSelections();

   $(this).next().toggleClass('selected');

   var count = $('.sel-question:checked').length;
   if (count == 0) {
     deactivateLink('map_sess');
   } else {
     activateMapSess();
   }
   e.stopPropagation();
 }

 function selMapTarget(e) {
   e.stopPropagation();
   e.preventDefault();

   clearMappableSelections();

   var data = $(this).attr('rel').split('_');
   $('.mapped-item').parent().removeClass('selected');
   $(this).parent().addClass('selected');
   if (data.length == 2) {
     activateUnmap(data);
   }
 }

 function activateMapQns() {
   if ($('#map_qns').hasClass('greymenuitem')) {
     $('#map_qns').removeClass('greymenuitem');
     $('#map_qns').addClass('menuitem');
     $('#map_qns a').bind('click', checkObjectives);
   }
 }

 function deactivateLink(id) {
   $('#' + id).addClass('greymenuitem');
   $('#' + id).removeClass('menuitem');
   $('#' + id).removeData();
   $('#' + id + ' a').unbind('click');
   $('#' + id + ' a').click(function(e) { e.preventDefault(); });
 }

 function activateMapSess() {
   if ($('#map_sess').hasClass('greymenuitem')) {
     $('#map_sess').removeClass('greymenuitem');
     $('#map_sess').addClass('menuitem');
     $('#map_sess' + ' a').bind('click', checkQuestions);
   }
 }

 function activateUnmap(data) {
   if ($('#unmap').hasClass('greymenuitem')) {
     $('#unmap').removeClass('greymenuitem');
     $('#unmap').addClass('menuitem');
     $('#unmap a').data('objective', data[0]);
     $('#unmap a').data('question', data[1]);
     $('#unmap a').bind('click', unMapQuestion);
   }
 }

 function showAJAXError(message) {
   alert(message);
   clearAllSelections();
 }

 function clearAllSelections() {
   clearMappableSelections();
   clearMappedSelections();
 }

 function clearMappableSelections() {
   $('.sel-question:checked').attr('checked', false);
   $('.sel-objective:checked').attr('checked', false);
   $('.map-item').removeClass('selected');
   deactivateLink('map_qns');
   deactivateLink('map_sess');
 }

 function clearMappedSelections() {
   $('.mapped-item').parent().removeClass('selected');
   deactivateLink('unmap');
   $('#unmap').removeClass('loading');
 }
});