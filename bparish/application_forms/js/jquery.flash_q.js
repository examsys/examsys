function sendTextToAS3(lang, q_no, mode, image, correct, user){
  var toSend = lang + ';' + q_no + ';' + mode + ';' + image + ';';
  if (typeof 'correct' != 'undefined' && correct != '' && correct != undefined) {
    toSend += correct + ';';
  }
  if (typeof 'user' != 'undefined' && user != '' && user != undefined) {
    toSend += user + ';';
  }

  // Add small delay to get around race condition that was evident in Firefox
  setTimeout(function() {
    doSend(q_no, toSend);
  }, 500)
}

function doSend(q_no, toSend) {
  try
  {
    var flash1 = document.getElementById("externalinterface" + q_no + "_1");
    if (flash1) {
      flash1.sendTextFromJS(toSend);
    }
  }
  catch(error)
  {
    var flash2 = document.getElementById("externalinterface" + q_no + "_2");
    if (flash2) {
      flash2.sendTextFromJS(toSend);
    }
  }
}

function receiveTextFromAS3(txt) {
  parts = txt.split(";");
  
  flashTarget = parts.shift();
  data = parts.join(";");

  $('#' + flashTarget).val(data);
}
