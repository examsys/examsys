function goHome() {
  window.top.content.location="display_page.php?id=1";
}

function goPrint() {
  window.top.content.focus();
  window.top.content.print();
}

function roll(img_name,img_src)  {
  document[img_name].src = img_src;
}

function newPage() {
  window.top.content.location = 'new_page.php';
}

function editPage()  {
  window.top.content.location = 'edit_page.php?id=' + document.getElementById('editid').value;
}

function infoPage()  {
  window.top.content.location = 'stats.php';
}

function addPointer()  {
  window.top.content.location = 'new_pointer.php?id=' + document.getElementById('editid').value;
}

function search() {
  window.top.content.location = 'search.php?searchstring=' + document.getElementById('searchbox').value;
}

function recycleBin() {
  window.top.content.location = 'recycle_bin.php';
}
