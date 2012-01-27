$(function () {
  $.ajaxSetup({ timeout: 3000 });
  $('#content').ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
    showAJAXError();
  });

  $(".has_tip").tipTip();

  resetLinks();
  highlightQn();

  $('.menu_list a').click(function (e) { e.preventDefault(); })

  var deleteLink = $('#delete_break');
  $('html').click(function () { deActivateDelete(deleteLink); });

  $('.breakline:gt(0)').click(function (e) {
    e.stopPropagation();
    qOff();   // WARNING: comes from main /paper/details.php file
    deActivateDelete(deleteLink);
    $(this).addClass('line-selected');
    if (deleteLink.hasClass('greymenuitem')) {
      activateDelete(deleteLink, $(this).attr('id'));
    }
  });

  $('#sortable tbody').sortable( {
    items: '.qline:not(#link_break1)',
    axis: 'y',
    cancel: '#link_break1',
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
//      $('#response').load('../ajax/paper/order-questions.php?paperID=' + paperID + '&' + order);

      $.get('../ajax/paper/order-questions.php?paperID=' + paperID + '&' + order, function(data) {
        if (data == 'ERROR') {
          showAJAXError();
        } else {
          window.location.href = [location.protocol, '//', location.host, location.pathname].join('') + '?' + $.rQuerySstring.setValue('selected', newpos);
        }
      });
    }
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
//    row.css('background-color', '#eee');
    row.effect("highlight", { color: '#b3c8e8'}, 1500);
  }
}

function activateDelete(element, sid) {
  element.removeClass('greymenuitem');
  element.addClass('menuitem');
  element.click(deleteScreenBreak);
  element.data('screenID', sid);
}

function deActivateDelete(element) {
  $('.breakline').removeClass('line-selected');
  element.addClass('greymenuitem');
  element.removeClass('menuitem');
  element.unbind('click');
}

function activateAddBreak(element) {
  element.removeClass('greymenuitem');
  element.addClass('menuitem');
  element.click(incScreen);
}

function deActivateAddBreak(element) {
  element.addClass('greymenuitem');
  element.removeClass('menuitem');
  element.unbind('click');
}

function deleteScreenBreak() {
  var screenNo = $(this).data('screenID').substring(10);
  $.get('../ajax/paper/delete-screen-break.php?paperID=' + paperID + '&screen=' + screenNo)
  .success(function (data) {
    if (data == 'SUCCESS') {
      window.location.reload();
    } else {
      alert('Invalid screen break selected. Screen break was not deleted');
    }
  });
}

function showAJAXError() {
  alert('There was a problem carrying out your action. Please refresh the page and try again');
}
