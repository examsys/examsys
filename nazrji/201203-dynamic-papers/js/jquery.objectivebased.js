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

  function selUnselObjective(e) {
    $(this).next().toggleClass('selected');

    e.stopPropagation();
    e.stopImmediatePropagation();
  }

  function clearAllSelections() {
    clearMappableSelections();
  }

  function clearMappableSelections() {
    $('.sel-question:checked').attr('checked', false);
    $('.sel-objective:checked').attr('checked', false);
    $('.map-item').removeClass('selected');
  }
});
