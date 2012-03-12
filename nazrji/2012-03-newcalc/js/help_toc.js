function updateMenu(sectionID,imageID) {
  current = (document.getElementById(sectionID).style.display == 'block') ? 'none' : 'block';
  document.getElementById(sectionID).style.display = current;

  icon = (document.getElementById(imageID).getAttribute('src') == '../open_book.png') ? '../closed_book.png' : '../open_book.png';
  document.getElementById(imageID).setAttribute('src',icon);
}

function getInternetExplorerVersion() {
  // Returns the version of Windows Internet Explorer or a -1
  // (indicating the use of another browser).
  var rv = -1; // Return value assumes failure.
  if (navigator.appName == 'Microsoft Internet Explorer') {
    var ua = navigator.userAgent;
    var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
    if (re.exec(ua) != null)
       rv = parseFloat( RegExp.$1 );
  }
  return rv;
}

function resizeTOC() {
  if ((parseInt(navigator.appVersion)>3 && navigator.appName=="Netscape") || (parseInt(getInternetExplorerVersion())>8 && navigator.appName=="Microsoft Internet Explorer")) {
    winW = window.innerWidth;
    winW = winW - 8;
    document.getElementById("main").style.width = winW + 'px';

    winH = window.innerHeight;
    winH = winH - 6;
    document.getElementById("main").style.height = winH + 'px';
  }
}
