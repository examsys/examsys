  var scrollLine = 0;
  var scrollDown = 0;
  var myUpInterval = 0;
  var myDownInterval = 0;
  
  function scrollUpStart(submenuID,arrayID,urlID,arrayName) {
    myUpInterval = window.setInterval(function () {
      if (scrollLine > 0) {
        scrollLine--;
        var limit = (scrollLine + 19);
        if (limit >= arrayID.length) {
          limit = arrayID.length-1;
        }
        var line = 0;
        for (i=scrollLine;i<=limit;i++) {
          submenuItemID = submenuID.substr(5,1) + '_' + line;
          if (urlID[i].substr(0,1) == '-') {
            if (navigator.appName == 'Microsoft Internet Explorer') {
              document.getElementById(submenuItemID).outerHTML =  '<div class="popupitem" id="' + submenuItemID + '"><hr nonshade="nonshade" style="height:1px; border:none; background-color:#C0C0C0; color:#C0C0C0" /></div>';
            } else {
              document.getElementById(submenuItemID).innerHTML = '<hr nonshade="nonshade" style="height:1px; border:none; background-color:#C0C0C0; color:#C0C0C0" />';
              document.getElementById(submenuItemID).setAttribute('onclick',"window.location=''");
            }
          } else if (urlID[i].substr(0,1) == '#') {
            if (navigator.appName == 'Microsoft Internet Explorer') {
              document.getElementById(submenuItemID).outerHTML =  '<div class="popupitembold" id="' + submenuItemID + '">' + urlID[i].substr(1) + '</div>';
            } else {
              document.getElementById(submenuItemID).innerHTML = urlID[i].substr(1);
            }
          } else {
            if (navigator.appName == 'Microsoft Internet Explorer') {
              document.getElementById(submenuItemID).outerHTML =  '<div class="popupitem" id="' + submenuItemID + '" onclick="window.location=\'' + urlID[i] + '\'" onmouseover="menuRowOn(\'' + submenuItemID + '\');" onmouseout="menuRowOff(\'' + submenuItemID + '\');">' + arrayID[i] + '</div>';
            } else {
              document.getElementById(submenuItemID).innerHTML = arrayID[i];
              document.getElementById(submenuItemID).setAttribute('onclick',"window.location='" + urlID[i] + "'");
            }
          }
          line++;
        }
        downID = submenuID.substr(5,1) + '_down';
        document.getElementById(downID).innerHTML = '<img src="/touchstone/artwork/submenu_down_on.png" width="9" height="5" alt="down" border="0" />&nbsp;';
      } else {
        upID = submenuID.substr(5,1) + '_up';
        document.getElementById(upID).innerHTML = '<img src="/touchstone/artwork/submenu_up_off.png" width="9" height="5" alt="down" border="0" />&nbsp;';
        clearInterval(myDownInterval);
      }
    },50);
  }
  
  function scrollUpEnd() {
    clearInterval(myUpInterval);
  }
  
  function scrollDownStart(submenuID,arrayID,urlID,arrayName) {
    myDownInterval = window.setInterval(function () {
      if (scrollLine < (arrayID.length-20)) {
        if (scrollLine == 0) {
          upID = submenuID.substr(5,1) + '_up';
          document.getElementById(upID).innerHTML = '<img src="/touchstone/artwork/submenu_up_on.png" width="9" height="5" alt="down" border="0" />&nbsp;';
        }
        scrollLine++;
        var limit = (scrollLine + 19);
        if (limit >= arrayID.length) {
          limit = arrayID.length-1;
        }
        var line = 0;
        for (i=scrollLine;i<=limit;i++) {
          submenuItemID = submenuID.substr(5,1) + '_' + line;
          if (urlID[i].substr(0,1) == '-') {
            if (navigator.appName == 'Microsoft Internet Explorer') {
              document.getElementById(submenuItemID).outerHTML =  '<div class="popupitem" id="' + submenuItemID + '"><hr nonshade="nonshade" style="height:1px; border:none; background-color:#C0C0C0; color:#C0C0C0" /></div>';
            } else {
              document.getElementById(submenuItemID).innerHTML = '<hr nonshade="nonshade" style="height:1px; border:none; background-color:#C0C0C0; color:#C0C0C0" />';
              document.getElementById(submenuItemID).setAttribute('onclick',"window.location=''");
            }
          } else if (urlID[i].substr(0,1) == '#') {
          if (navigator.appName == 'Microsoft Internet Explorer') {
            document.getElementById(submenuItemID).outerHTML =  '<div class="popupitembold" id="' + submenuItemID + '">' + urlID[i].substr(1) + '</div>';
          } else {
            document.getElementById(submenuItemID).innerHTML = urlID[i].substr(1);
          }
          } else {
            if (navigator.appName == 'Microsoft Internet Explorer') {
              document.getElementById(submenuItemID).outerHTML =  '<div class="popupitem" id="' + submenuItemID + '" onclick="window.location=\'' + urlID[i] + '\'" onmouseover="menuRowOn(\'' + submenuItemID + '\');" onmouseout="menuRowOff(\'' + submenuItemID + '\');">' + arrayID[i] + '</div>';
            } else {
              document.getElementById(submenuItemID).innerHTML = arrayID[i];
              document.getElementById(submenuItemID).setAttribute('onclick',"window.location='" + urlID[i] + "'");
            }
          }
          line++;
        }
      } else {
        downID = submenuID.substr(5,1) + '_down';
        document.getElementById(downID).innerHTML = '<img src="/touchstone/artwork/submenu_down_off.png" width="9" height="5" alt="down" border="0" />&nbsp;';
        clearInterval(myDownInterval);
      }
    },50);
  }
  
  function scrollDownEnd() {
    clearInterval(myDownInterval);
  }
  
  function menuRowOn(rowID) {
    document.getElementById(rowID).style.backgroundColor='#316AC5';
    document.getElementById(rowID).style.color='white';
  }

  function menuRowOff(rowID) {
    document.getElementById(rowID).style.backgroundColor='white';
    document.getElementById(rowID).style.color='black';
  }

  function showMenu(submenuID,menuID,callingID,arrayID,urlID,e) {    
    scrollLine = 0;
  
    var limit = (scrollLine + 19);
    if (limit >= arrayID.length) {
      limit = arrayID.length-1;
    }
    if (arrayID.length > 20) {
      upID = submenuID.substr(5,1) + '_up';
      document.getElementById(upID).innerHTML = '<img src="/touchstone/artwork/submenu_up_off.png" width="9" height="5" alt="down" border="0" />&nbsp;';
      downID = submenuID.substr(5,1) + '_down';
      document.getElementById(downID).innerHTML = '<img src="/touchstone/artwork/submenu_down_on.png" width="9" height="5" alt="down" border="0" />&nbsp;';
    }
    var line = 0;
    for (i=scrollLine;i<=limit;i++) {
      submenuItemID = submenuID.substr(5,1) + '_' + line;
      if (urlID[i].substr(0,1) == '-') {
        if (navigator.appName == 'Microsoft Internet Explorer') {
          document.getElementById(submenuItemID).outerHTML =  '<div class="popupitem" id="' + submenuItemID + '"><hr nonshade="nonshade" style="height:1px; border:none; background-color:#C0C0C0; color:#C0C0C0" /></div>';
        } else {
          document.getElementById(submenuItemID).innerHTML = '<hr nonshade="nonshade" style="height:1px; border:none; background-color:#C0C0C0; color:#C0C0C0" />';
          document.getElementById(submenuItemID).setAttribute('onclick',"window.location=''");
        }
      } else if (urlID[i].substr(0,1) == '#') {
        if (navigator.appName == 'Microsoft Internet Explorer') {
          document.getElementById(submenuItemID).outerHTML =  '<div class="popupitembold" id="' + submenuItemID + '">' + urlID[i].substr(1) + '</div>';
        } else {
          document.getElementById(submenuItemID).innerHTML = urlID[i].substr(1);
        }
      } else {
        if (navigator.appName == 'Microsoft Internet Explorer') {
          document.getElementById(submenuItemID).outerHTML =  '<div class="popupitem" id="' + submenuItemID + '" onclick="window.location=\'' + urlID[i] + '\'" onmouseover="menuRowOn(\'' + submenuItemID + '\');" onmouseout="menuRowOff(\'' + submenuItemID + '\');">' + arrayID[i] + '</div>';
        } else {
          document.getElementById(submenuItemID).innerHTML = arrayID[i];
          document.getElementById(submenuItemID).setAttribute('onclick',"window.location='" + urlID[i] + "'");
        }
      }
      line++;
    }

    if (!e) var e = window.event;
    hideMenus(e);
    
    document.getElementById(submenuID).style.display = 'block';
    popupHeight = document.getElementById(submenuID).offsetHeight;
    
    sidebarHeight = document.getElementById('left-sidebar').offsetHeight;
    
    mytop = document.getElementById(callingID).offsetParent.offsetTop + document.getElementById(menuID).offsetTop + 12;
    if ((mytop + popupHeight) > sidebarHeight) {
      mytop = sidebarHeight - popupHeight - 5;
    }
    document.getElementById(submenuID).style.top = mytop + 'px';
    
    e.cancelBubble = true;
    
    return false;
  }
