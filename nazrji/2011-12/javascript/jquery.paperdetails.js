$(function () {
  resetLinks();
  highlightQn();

  var sourceIndex;

  $('#sortable tbody').sortable( {
    items: '.qline:not(#link_break1)',
    axis: 'y',
    cancel: '#link_break1',
    helper: 'clone',
    appendTo: 'body',
    start: function(event, ui) {
      var keepWidth = 0;
      if (ui.item.hasClass('breakline')) {
        keepWidth = $('tr.details-head:first').width();
        ui.helper.find('td:first').width(keepWidth);
      } else {
        keepWidth = $('td.q-cell:first').width();
        ui.helper.find('td.l').width(keepWidth);
      }
    },
    beforeStop: function(event, ui) {
      if (!ui.item.hasClass('qline')) {
        var text = ui.helper.text();
        var breaks = $('.breakline').size();
        var row = $(document.createElement('tr'));
        row.addClass('breakline');
        row.addClass('qline');
        row.attr('id', 'link_break' + (breaks + 1));
        row.attr('data-order', 'link_break' + (breaks + 1));
        row.html('<td colspan="6"><h4><span class="opaque screen_no">Screen 1</span></h4></td>');
        row.mouseover(function () { $(this).find('img.handle').show(); });
        row.mouseout(function () { $(this).find('img.handle').hide(); });
        row.triggerHandler('click');
        $(ui.item).replaceWith(row);
      }
    },
    update: function (event, ui) {
      $('.qline').css('background-color', '#fff');
      var order = $('#sortable tbody').sortable('serialize', { attribute: 'data-order' });
      var newpos = $(ui.item).parent().children('.qline:not(.breakline)').index(ui.item) + 1;
      $('#response').load('../ajax/paper/order-questions.php?paperID=' + paperID + '&' + order);
//      window.location.href = [location.protocol, '//', location.host, location.pathname].join('') + '?' + $.rQuerySstring.setValue('selected', newpos);

//      $('td.q_no').each(function(index) { $(this).html((index + 1) + '.')});
//      $('span.screen_no').each(function(index) { $(this).html('Screen ' + (index + 1))});
//      if (!ui.item.hasClass('breakline')) {
//        ui.item.css('background-color', '#b3c8e8');
//        ui.item.effect("highlight", { color: '#e6f0ff'}, 1000);
//      }
//      resetLinks();
    }
  });
  $('#draggable').draggable({
    helper: 'clone',
    appendTo: 'body',
    connectToSortable: '#sortable tbody'
  });
});

function resetLinks() {
  var breaks = 0;
  $('.qline').each(function (index) {
    if ($(this).hasClass('breakline')) {
      breaks++;
      $(this).attr('data-order', 'link_break' + breaks);
    } else {
      $(this).attr('data-order', 'link_' + (index - breaks + 1));
    }
  });
}

function highlightQn() {
  var selected = $.rQuerySstring.getValue('selected');

  if (selected != '') {
    var row = $('#link_' + selected);
    row.css('background-color', '#b3c8e8');
    row.effect("highlight", { color: '#e6f0ff'}, 1000, function() { row.triggerHandler('click') });
  }
}
