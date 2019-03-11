var resizeList = function () {
  var winW = 630, winH = 460;
  if (document.body && document.body.offsetWidth) {
    winW = document.body.offsetWidth;
    winH = document.body.offsetHeight;
  }
  if (document.compatMode=='CSS1Compat' && document.documentElement && document.documentElement.offsetWidth ) {
    winW = document.documentElement.offsetWidth;
    winH = document.documentElement.offsetHeight;
  }
  if (window.innerWidth && window.innerHeight) {
    winW = window.innerWidth;
    winH = window.innerHeight;
  }
  winH -= 140;
  document.getElementById('list').style.height = winH + 'px';
}

var doError = function () {
  alert(langStrings['saveerror']);
}

var saveAllRows = function (e) {
  $('.save-row').each(function() {
    var logID = $(this).data('logid');
    var newMark = $('input[name=mark_' + logID + ']:checked').val();
    var reason = $('#reason_' + logID).val();
    var logType = $('#log_type_' + logID).val();
    var userID = $('#user_id_' + logID).val();
    if (typeof newMark == 'undefined') {
      alert(langStrings['nomarkmsg']);
    } else {
      var row = $(this).parents('tr');
      $.post('../ajax/reports/save_enhancedcalc_override.php',
        {
          log_id: logID,
          user_id: userID,
          q_id: $('#q_id').val(),
          paper_id: $('#paper_id').val(),
          marker_id: $('#marker_id').val(),
          mark_type: newMark,
          reason: reason,
          log: logType
        },
        function (data) {
          if (data != 'OK') {
            alert(langStrings['saveerror']);
            return false;
          }
          row.addClass('overridden').effect("highlight", {}, 1500);
        }
      ).fail(doError);
    }
  });
}

$(function () {
  resizeList();
  $(window).resize(resizeList);

   $.ajaxSetup({ timeout: 10000 });
   $('#list').ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
     doError();
   });

  $('.save-all').click(saveAllRows);
})
