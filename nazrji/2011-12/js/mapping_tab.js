if (typeof cfgRootPath === 'undefined') var cfgRootPath = '';
minusImg = new Image;
minusImg.src = cfgRootPath + '/artwork/minus.png';
plusImg = new Image;
plusImg.src = cfgRootPath + '/artwork/plus.png';

function showHide(id) {
  var imgid = 'img_' + id;
  var divid = 'div_' + id;
  current = (document.getElementById(divid).style.display == 'block') ? 'none' : 'block';
  document.getElementById(divid).style.display = current;
  if(imgid != '') {
    icon = (document.getElementById(imgid).getAttribute('src') == minusImg.src) ? plusImg.src : minusImg.src;
    document.getElementById(imgid).setAttribute('src',icon);
  }
}

function getElementsByClassName(node, classname)
  {
      var a = [];
      var re = new RegExp('\\b' + classname + '\\b');
      var els = node.getElementsByTagName("*");
      for(var i=0,j=els.length; i<j; i++)
	  if(re.test(els[i].className))a.push(els[i]);
      return a;
}

function showNextOption(showNum) {

  var options = getElementsByClassName(document.body,'option');
  var i=0;
  while(options[i].style.display == '') {
	i = i +  showNum;
  }
  for(j = 0; j < showNum; j++) {
    options[i + j].style.display = '';
  }  
  if(options.length == (i+showNum)) {
    document.getElementById('nextOption').disabled = true;
  }

}

var newwin;
function eqnEdit(editor) {
  editor.focus();
  newwin = window.open(cfgRootPath + "/editor/DragMath/dragMath_popup.html","","width=565,height=400,resizable")
}
