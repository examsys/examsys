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
  var module = $('#module').val();
  var session = $('#session').val();

  $(".tip-right").tipTip({ defaultPosition: 'right' });

  $('.map-objective').click(function (e) {
    e.stopPropagation()
  });
  $('.sel-objective').click(selUnselObjective);

  $('#clear_all').click(function(e) {
    e.preventDefault();
    clearAllSelections();
  });

  $('#obj_form').submit(handleFormSubmission);

  $('input.session').change(selSession);

//  $('#q').keyup(highlightObjectives);
//  $('#q').keyup(highlightObjectives);
  $('#search_filter').click(filterObjectives);
  $('#search_select').click(searchObjectives);
  $('#search_show').click(unfilterObjectives);

  function selUnselObjective(e) {
    $(this).parent().toggleClass('selected');

    e.stopPropagation();
    e.stopImmediatePropagation();
  }

  function clearAllSelections() {
    clearMappableSelections();
  }

  function clearMappableSelections() {
    $('.session:checked').attr('checked', false);
    $('.sel-objective:checked').attr('checked', false);
    $('.map-item').removeClass('selected');
    $('.map-item').removeClass('search-result');
//    highlightObjectives();
  }

  function handleFormSubmission(e) {
    e.preventDefault();

    var objCount = $('.sel-objective:checked').length;

    if (objCount > 0) {
      var objectives = new Array();
      $('.sel-objective:checked').each(function() {
        objectives.push($(this).val());
      });
      window.open("./launch_dynamic_paper.php?module=" + module + "&session=" + session + "&objectives=" + objectives.join(',') + "&count=" + $('#num_qns').val(),"paper","fullscreen=yes,width="+(screen.width-80)+",height="+(screen.height-80)+",left=20,top=10,scrollbars=yes,menubar=no,titlebar=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable=yes");
    } else {
      alert(lang['mustselectobjectives']);
    }
  }

  function selSession() {
    var checked = ($(this).attr('checked') == 'checked');
    var id_parts = $(this).attr('id').split('_');
    var searchID = id_parts[1];
    var targetID;

    $('.sel-objective').each(function () {
      if ($(this).is(':visible')) {
        id_parts = $(this).val().split('_');
        targetID = id_parts[0];
        if (searchID == targetID) {
          if (checked) {
            $(this).attr('checked', 'checked');
            $(this).parent().addClass('selected');
          } else {
            $(this).attr('checked', false);
            $(this).parent().removeClass('selected');
          }
        }
      }
    });
  }

  function filterObjectives() {
    var term = $('#q').val();
    var haveResult = false;

    if (term.length > 2) {
      $('.map-session').addClass('filtered').hide();

      var usableterms = new Array();
      term = term.split(' ');
      for (var i = 0; i < term.length; i++) {
        if (term[i].length > 2) {
          usableterms.push(term[i]);
        }
      }
      term = usableterms.join('|')

      var reg = new RegExp(term, 'i');

      $('.map-item').each(function () {
        if ($(this).text().match(reg)) {
          $(this).parent().removeClass('filtered').show();
          $(this).parent().parent().prev().removeClass('filtered').show();
          haveResult = true;
        }
        else {
          $(this).parent().addClass('filtered');
        }
      });
      $('.filtered').hide();
    } else {
      unfilterObjectives(false);
    }

    if (haveResult) {
      $('#search_select').attr('disabled', false);
    }
  }

  function unfilterObjectives(clearterm) {
    if (clearterm !== false) $('#q').val('');
    $('.map-session').removeClass('filtered').show();
    $('.mapped').removeClass('filtered').show();
    $('#search_select').attr('disabled', 'disabled');
  }

  function searchObjectives() {
    $('.sel-objective').each(function () {
      if ($(this).is(':visible')) {
        $(this).attr('checked', 'checked');
        $(this).parent().addClass('selected');
      }
    });
  }
});
