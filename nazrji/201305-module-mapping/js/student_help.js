var scripts = document.getElementsByTagName('script');
var path = scripts[scripts.length-1].src.split('?')[0];      // remove any ?query
var mydir = path.split('/').slice(0, -1).join('/')+'/';  // remove last filename part of path

function launchHelp(pageID) {
  helpwin = window.open(mydir + "../help/student/index.php?id=" + pageID + "","help","width=" + (screen.width-100) + ",height=" + (screen.height-100) + ",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
  helpwin.moveTo(10,10);
  if (window.focus) {
    helpwin.focus();
  }
  return false;
}
