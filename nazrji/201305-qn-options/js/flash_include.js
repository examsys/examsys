function setUpFlash(num, flashId, lang, image, config, answer, extra, colour) {
  var swf;
  if (navigator.appName.indexOf("Microsoft") != -1) {
    swf = document.getElementById(flashId);
  } else {
    swf = document[flashId];
  }
  swf.imageInfo(image, num, lang);
  if (answer != undefined) {
    swf.answerInfo(answer);
  }
  if (config != undefined) {
    swf.configInfo(config);
  }
  if (extra != undefined) {
    swf.extraInfo(extra);
  }
  if (colour != undefined) {
    swf.colourInfo(colour);
  }}

function addLoadEvent(func, num, flashId, image, config, answer, extra, colour) {
  var oldonload = window.onload;
  if (typeof window.onload != 'function') {
    window.onload = func
  } else {
    window.onload = function() {
      if (oldonload) {
        oldonload();
      }
      func(num, flashId, image, config, answer, extra, colour);
    }
  }
}

function flashInfo(infoArray) {
  flashTarget = (typeof flashTarget === 'undefined' || flashTarget == '') ? 'q' : flashTarget;
  document.getElementById(flashTarget+infoArray[0]).value = infoArray[1];
}
