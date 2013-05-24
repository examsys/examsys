function dateCopy() {
  var highlight = '';
  switch($(this).attr('id')) {
    case "fday":
      $("#tday").val($("#fday").val());
      highlight = 'tday';
      break;
    case "fmonth":
      $("#tmonth").val($("#fmonth").val());
      highlight = 'tmonth';
      break;
    case "fyear":
      $("#tyear").val($("#fyear").val());
      highlight = 'tyear';
      break;
    case "ftime":
      var from = $("#ftime").val().replace(':', '');
      var to = $("#ttime").val().replace(':', '');
      if (from > to) {
        $("#ttime").val($("#ftime").val());
        highlight = 'ttime';
      }
      break;
    case "tday":
      $("#fday").val($("#tday").val());
      highlight = 'fday';
    break;
    case "tmonth":
      $("#fmonth").val($("#tmonth").val());
      highlight = 'fmonth';
      break;
    case "tyear":
      $("#fyear").val($("#tyear").val());
      highlight = 'fyear';
      break;
    case "ttime":
      var to = $("#ttime").val().replace(':', '');
      var from = $("#ftime").val().replace(':', '');
      if (to < from) {
        $("#ftime").val($("#ttime").val());
        highlight = 'ftime';
      }
      break;
  }
  if (highlight != '') {
    $('#' + highlight).effect("highlight", {}, 1500);
  }
}
