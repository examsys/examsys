function showTab(tabID) {
  var mapTab = document.getElementById('mappingtab');
  if (tabID == 'editortab') {
    document.getElementById('editortab').style.display = 'block';
    document.getElementById('changestab').style.display = 'none';
    document.getElementById('commentstab').style.display = 'none';
    if (mapTab != null) {
      document.getElementById('mappingtab').style.display = 'none';
    }
  } else if (tabID == 'changestab') {
    document.getElementById('editortab').style.display = 'none';
    document.getElementById('changestab').style.display = 'block';
    document.getElementById('commentstab').style.display = 'none';
    if (mapTab != null) {
      document.getElementById('mappingtab').style.display = 'none';
    }
  } else if (tabID == 'commentstab') {
    document.getElementById('editortab').style.display = 'none';
    document.getElementById('changestab').style.display = 'none';
    document.getElementById('commentstab').style.display = 'block';
    if (mapTab != null) {
      document.getElementById('mappingtab').style.display = 'none';
    }
  } else {
    document.getElementById('editortab').style.display = 'none';
    document.getElementById('changestab').style.display = 'none';
    document.getElementById('commentstab').style.display = 'none';
    document.getElementById('mappingtab').style.display = 'block';
  }
}
