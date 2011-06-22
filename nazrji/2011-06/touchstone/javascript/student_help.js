function launchHelp(pageID) {
  helpwin = window.open("/touchstone/help/student/index.php?id=" + pageID + "","help","width=" + (screen.width-100) + ",height=" + (screen.height-100) + ",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
  helpwin.moveTo(10,10);
  if (window.focus) {
    helpwin.focus();
  }
  return false;
}
