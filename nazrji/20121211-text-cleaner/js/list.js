$(function () {
  $('body').click(deselLine);
});
    
function selLine(lineID, evt) {
  $('.highlight').removeClass('highlight');

  document.getElementById('menu1a').style.display = 'none';
  document.getElementById('menu1b').style.display = 'block';
  document.getElementById('lineID').value = lineID;
     
  $('#' + lineID).addClass('highlight');
  evt.cancelBubble = true;
}

function deselLine() {
  $('.highlight').removeClass('highlight');
  
  document.getElementById('lineID').value = '';
  document.getElementById('menu1b').style.display = 'none';
  document.getElementById('menu1a').style.display = 'block';
}
