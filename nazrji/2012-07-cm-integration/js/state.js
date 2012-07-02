  $(document).ready(function() {
    $('.chk').click(updateChkState);
  });

	var updateChkState = function () {
    var state_name = $(this).attr('id');
    var content = $(this).is(':checked');
    updateState(state_name, content);
  }
  
  var updateState = function (state_name, content) {
    $.post('../include/set_state.php', {state_name: state_name, content: content, page: document.URL}, function(responseText){ }, "html");
  }
