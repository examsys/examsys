/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1    Simon Atack
 * Date: 11/01/12
 * Time: 11:40
 * To change this template use File | Settings | File Templates.
 */

//
// sCal ver 0.9: PUBLIC DOMAIN.  Checked for Win-98 to XP & Mobile 2003 -- r.mohaupt, May, 2004
// -Source Code intended user read/modify;  set Word Wrap=off for gen'l layout, =on for details.
// v.p9u
var ps = "";  // Info. Alerts: READ!
var object_calc;
var rogo_buffer = '';

function checkButton(e) {
  var evt = e || window.event;
  var bCode = evt.keyCode || evt.charCode;
  
  if (bCode == 16 && shifton == 0) {
    shifton = 1;
  }

  if (bCode >= 48 && bCode <= 57 && shifton == 0) {
    bCode = bCode - 48;
    xPlusEq(bCode);
  } else if (bCode == 48 && shifton == 1) {
    xPlusEq(')');
  } else if (bCode == 57 && shifton == 1) {
    xPlusEq('(');
  } else if (bCode >= 96 && bCode <= 105) {
    bCode = bCode - 96;
    xPlusEq(bCode);
  } else if (bCode == 42 || bCode == 106) {
    xPlusEq('*');
  } else if (bCode == 43) {
    xPlusEq('+');
  } else if (bCode == 107) {
    xPlusEq('+');
  } else if (bCode == 61) {
    xEval();
  } else if (bCode == 45 || bCode == 109) {
    xPlusEq('-');
  } else if (bCode == 110) {
    xPlusEq('.');
  } else if (bCode == 46) {
    Clear();
  } else if (bCode == 8) {
    BkSpace();
  } else if (bCode == 191 || bCode == 111) {
    xPlusEq('/');
  } else if (bCode == 187) {
    if (shifton == 1) {
      xPlusEq('+');
    } else {
      xEval();
    }
  } else if (bCode == 189) {
    xPlusEq('-');
  } else if (bCode == 56 && shifton == 1) {
    xPlusEq('*');
  }
}

function checkShift(e) {
  var bCode = window.event ? e.keyCode : e.which;
  if (bCode == 16 && shifton == 1) {
    shifton = 0;
  }
}

function sCal() {
  this.f_out = '';
  this.calc_load = false;
  this.calc_loaded = true;

  this.t_load = false;
 /*
  this.f_out = '';
  this.calc_load = false;
  this.Mr=Mr;
  this.Ix=Ix;
  this.Oxf=Oxf();
  this.Ox=Ox;
  this.xEval=xEval;
  this.xPlusEq=xPlusEq;
  this.xMultiEq=xMultEq;
  this.Clear=Clear;
  this.BkSpace=BkSpace;
  this.recip=recip;
  this.xSquare=xSquare;
  this.xFactorial.xFactorial;
  this.xWork=xWork;
  this.DoRecip=DoRecip;
  this.Im=Im;
  this.Om=Om;
  this.XtoM=XtoM;
  this.MtoX=MtoX;
  this.Mplus=Mplus;
  this.Mminus=Mminus;
  this.Mclear=Mclear;
  this.EnterKey_EqButton=EnterKey_EqButton;
  this.runStop=runStop;
  this.upDate=upDate;
  this.d_v=d_v;
  this.scalcPopup=scalcPopup;
  this.scalcisNumber=scalcisNumber;
   */
}

function scalcisNumber(data){
  var numStr = "0123456789", thischar, counter = p_counter = err_cod = popr = 0, len=data.length, i;
  for (i = 0; i < len; i ++) {
    thischar = data.substring(i,i + 1);
    if (numStr.indexOf(thischar)!= -1) counter ++;
    if (thischar == '-'){
      if (i != 0) {err_cod = 1; break;}
      else popr ++;
    }
    if (thischar == '.') {
      if ((i == 0 || i == len - 1) || (i == 1 && data.substring(i - 1, i) == '-')) {
        err_cod = 1;
        break;
      }
      else {
        p_counter ++;
        if (p_counter > 1) {
          err_cod = 1;
          break;
        }
        else popr ++;
      }
    }
  }
  if (err_cod != 1 && counter == len - popr) return true;
  else return false;
}

function scalcPopup(obj_control,obj_control1,obj_control2) {
  var w = 186, h = 122;
  var ua = navigator.userAgent.toLowerCase();
  var v = navigator.appVersion.substring(0,1);
  var n = navigator.appName.toLowerCase();
    if (ua.indexOf("opera") > 0) {w = 320; h = 500;
    } else if (ua.indexOf("netscape") < 0 && ua.indexOf("msie") < 0
        && v >= 5 && ua.indexOf("mac") > 0) {
      w = 320; h = 500;
    } else if (n == 'netscape') {
      w = 320; h = 500;
    }

    if (screen) {
      n_left = (screen.width - w) >> 1;
      n_top = (screen.height - h) >> 1;
    }
    win_ch = window.open("sCal-09.php?calc=obj_control&form="+obj_control1+"&field="+obj_control2,"win_ch", "width=" + w + ",height=" + h + ",help=no,status=no,scrollbars=no,resizable=no,top=" + n_top + ",left=" + n_left + ",dependent=yes,alwaysRaised=yes", true);
    win_ch.focus();
}

function About_sCal() {
    ps= "sCal - Small, Scientific, Script Calculator v.0.9 for Pocket PC 2003 & higher\n** Free, Public Domain, accessable JavaScript code\n**  User must inspect code and test known values before critical use.  - - NO WARRANTEES. - -\n**  R.Mohaupt: May,04. Download latest version at http://sourceforge.net/projects/scal-2/\n**  'sCal' & 'v.-.-' link to alert boxes --like 'Quick Reference Cards'. 'Help' links sCal-8-DraftDoc.htm file, if in same directory.";
    alert(ps); }
function About_Script() {
    ps= "sCal is written in JavaScript (JS) & HTML--for easy user modification & adding functions (JScrAps) to the Select Menus;  entering code directly to x-display; or cut/paste to it using any text editor. No need of a compiler.\n**  Code in x-display, [=] to eval(x) function.  Review source code eg.\n**  sCal goals are compact display and file size.\n . . .continue?";
    if (confirm(ps)) {About_Calc()}; }
function About_Calc() {
    ps= "Simple Calculator operated by buttons or keyboard.  Drop-down Select more functions.  -{[do] does now, -{[o] does opposite, [js]}- codes to x. /*Comments*/ aid data entry. [=] evaluates.\n **  Only one-[x^] power allowed, Math.pow(x,y) is preferred JavaScript.\n[x>m][m>x][m+][mc] for memory; with stopwatch [stop][lap][pause/go][clr]\n**  EXPERIMENT --not randomly, but with a point.";
alert(ps);
}

var x = "";  // by JScript loose typing, 'x' is a string OR number: confuses '+' if adding
var m = "";  // stores Memory.  Note: x & m are GLOBAL--of concern if sCal embedded.
var xRedo=0; // if xRedo=1, restores current x; =2, output to alert, etc. see xEval.
var Timer=0; var st0p=0; // for stopwatch
var TAPE=""; // virtual adding machine tape
var NL="\n";
var sp_x=" ";
var sC_xDec=8;//decimal places for Oxf()
var sC_t=100;
var clearFlag = false;
var equalsFlag = false;
var buffer = '';
var shifton = 0;
//timer x/1000 of second; USE 10 for PC's
//
// Functions:
//
function Mr(val,place) {var d=Math.pow(10,place); // set output decimal places
    return Math.round(d*val)/d
    }
function Ix() {
  var s = document.sCal.IOx.value;
  var n = s.indexOf('/*=');
  if (n>0) {
    var InPrompt=s.substring(0,n);
    var xCode=s.substring(n);
    x=InPrompt.replace(sp_x+/\n/g,";") +xCode
  } else {
    x=s
  }
}

// IOx BLOCK: updates x-value for any keyboard inputs & puts calculated x into x-display.
// If the JScrAp is a simple function, Oxf() is used;
//  else'/*=' shows its a program. 'New Lines' are unset and set for even input prompts.
function Oxf() {
  if (isNaN(x)) {
    document.sCal.IOx.value=x;
  } else {
    document.sCal.IOx.value=Mr(x,sC_xDec);
  }
}

function Ox() {
  var n = x.indexOf('/*=');
  if (n>0) {
    InPrompt=x.substring(0,n);
    var xCode=x.substring(n);
    document.sCal.IOx.value = InPrompt.replace(/;/g,sp_x+"\n") +xCode
  } else {
    document.sCal.IOx.value = x;
  }
}

function xEval() {
  Ix(); // xEval is the backbone of the 'sCal-eton'
  xTemp=x;
  var n = x.indexOf('^');
  if (n > 0) {
    if (x.indexOf('^',n+1) > 0) {
      alert("WARNING! Only 1 [^] allowed in expression!");
    } else {  // all to left of '^' is taken as base, and all right as exponent
      document.sCal.IOx.value = Math.pow(eval(x.substring(0,n)),eval(x.substring(n+1)));
    }
  } else {      // likewise, entire x-value used as function argument, not just last term
    document.sCal.IOx.value = eval(x);
  }
  if (xRedo>0) {
    x=xTemp;
    Ox();
    Om();
    if (xRedo==2) {
      alert(InPrompt+NL+m)
    }
    xRedo=0;
  }
  Ix();
  document.getElementById('ans').innerHTML = x;
  buffer = x;
  equalsFlag = true;
}

function returntoform() {
  xEval();
  //Oxf();
  if (typeof(window.opener.document.getElementById(urlParams["field"])) != 'undefined') {
    window.opener.document.getElementById(urlParams["field"]).value=x; //.getElementById(urlParams["id"]).value=x;
  }
  window.close();
}
function xPlusEq(s) {
  if (rogo_buffer == '') {
    if (equalsFlag && s!='+' && s!='-' && s!='*' && s!='/' && s!='(' && s!=')' && s!='^') {
      document.sCal.IOx.value = '';
      buffer = '';
    }
    equalsFlag = false;
    if (clearFlag) {
      buffer = '';
    }
    Ix();
    if (s == '.' && x == '' && buffer == '') {
      x = buffer = '0';
    }
    x += s;
    buffer += s;
    if (s == ')') {
      braceEval();
      document.getElementById('ans').innerHTML = buffer;
    }

    Ox();
    if (s=='+' || s=='-' || s=='*' || s=='/' || s=='(' || s==')' || s=='^') {
      clearFlag = true;
    } else {
      clearFlag = false
    }
    if (!clearFlag) {
      document.getElementById('ans').innerHTML = buffer;
    }
  } else {
    var reStr = rogo_buffer.replace('^', '\\^') + '+$';
    var re = new RegExp(reStr);
    document.getElementById('ans').innerHTML = s;
    rogo_buffer += s;
    var buffVal = rogo_eval(rogo_buffer);
    x = x.replace(re, '') + buffVal.toString();
    buffer = buffVal;
    Oxf(); //  figure x, & substiture in function,  NOTE: Oxf()!
    rogo_buffer = '';
  }
} // --- DISPLAY-x functions ---
function xMultEq(s) {
    xEval();
    x *= s;
    Oxf();
}
function Clear() {
  x = '';
  Ox();
  buffer = '';
  document.getElementById('ans').innerHTML = '0';
}
function BkSpace() {Ix(); x = x.substring(0,x.length-1) ; Ox();}
function recip() {
  xEval();
  x = 1/(x);
  document.getElementById('ans').innerHTML = x;
  buffer = x;
  Oxf();
}


function xSquare() {
  xEval();
  x = x*x;
  document.getElementById('ans').innerHTML = x;
  buffer = x;
  Oxf();
}

function xFactorial() {
  if (x<=2) {

  }
  for(j=x;j>2;j--) {
    x*=j-1;
  }
}
function Xwork(s) {  // --- finds how to handle incoming MENU (s)-values ---
  if (s.indexOf('!')==0) {
    alert(s.replace(/~~/g,"\n"))
  } else {  // '!' is key, '~~' is newline
    if (isNaN(s)) {
      if (s.indexOf('buffer') > -1) {       //-if expression is +-
        var re = new RegExp(buffer + '+$');
        x = x.replace(re, '') + eval(s);
        buffer = eval(s);
        Oxf(); //  figure x, & substiture in function,  NOTE: Oxf()!
      } else if (s.indexOf('x')>-1) {       //-if expression is f(x), i.e.Method,
        xEval();
        x = eval(s);
        buffer = x;
        Oxf(); //  figure x, & substiture in function,  NOTE: Oxf()!
      } else {
        if (s=='Math.E') {
          x = Math.E;
          buffer = x;
        } else if (s=='Math.PI') {
          if (clearFlag) {
            x += eval(s);
            Ox();
          } else {
            x = eval(s);
            document.sCal.IOx.value = x;
          }
          buffer = eval(s);
        } else {
          x += eval(s);
          Ox();
          buffer = eval(s);
        }
      }
    } else {  //-if a Property (eg. Math.PI), add value
      xPlusEq(s);
    }
  }
  document.getElementById('ans').innerHTML = buffer;
}
function DoRecip(s) //--- does [,]: inverse [d] eg. ft>m becomes m>ft. NOT ALWAYS SENSIBLE! ---
 {Ix(); var temp=eval(s); if (s.indexOf('x')>-1) {x=x*x/temp} else {x=1/temp} Oxf();
}
function Im() { // --- MEMORY fcns: like Ix() & Ox() ---
  if (document.getElementById('memval')) {
    m = document.getElementById('memval').innerHTML;
  }
}
function Om() {
  document.getElementById('memory').innerHTML = '<span style="font-size:70%">+M</span>  <span id="memval">' + m + '</span>';
}
function XtoM()  {Ix(); Im(); m+=x; Om(); x=""; Ox(); Timer=1;} //--with stopwatch settings
function MtoX()  {Ix(); Im(); x += m; Ox();}
function Mplus() {
  if (st0p > 0) {
    if (Timer==0) {
      Timer=1
    } else {
      Timer=0
    }
    m=st0p-0.00001;
    Om();
    runStop(m*1000);
  } else {
    xEval();
    if (m=="") {
      m=0;
    }
    m = parseFloat(m) + parseFloat(x);
    Om();
    x="";
    Ox();
  }
}

function Mminus() {if (st0p>0){
    if (Timer==0) {Timer=1} else {Timer=0} m=st0p-0.00001; Om(); runStop(m*1000);}
else {xEval(); if (m==""){m=0;} m = parseFloat(m) - parseFloat(x); Om(); x=""; Ox();}}

function Mclear() {m = ""; Om(); Timer=0; st0p=0;}


var Browser = navigator.appName.substr(0,9);
if (Browser=='Netscape'){NetSp=" "} else {NetSp=""};
function EnterKey_EqButton(e) { // use 'Enter' key (k=13) as [=] button, IF CURSOR IN BOX
    var k=0;
    if (Browser=='Netscape') {k=e.which;} else {k=window.event.keyCode;}
if (k==13) {xEval();} }
document.onkeypress = EnterKey_EqButton;

function runStop(k) {
    start = new Date(); start.setTime(start.getTime() - k); upDate(); }
function upDate() {  // Timer functions
    if (st0p>0){
    if (Timer==0) {
    now= new Date(); k=Mr((now.getTime()-start.getTime())/1000, sC_timerDecimal);
    if (C>0) {m=C-k} else {m=k}
st0p=m+0.00001; m+= ", "; Om();
if (st0p<0) {st0p=0; m=0; Om(); x=temp; xRedo=1; Ox(); alert("Time Up!");}
setTimeout("upDate();",sC_thousanths);}}
}
function d_w(s) {
  return document.write(s)
}

// Convert to degrees if mode
function trigModeConvert (angle) {
  angle = parseFloat(angle);
  if (trigMode == 'radians') {
    return angle;
  } else {
    return (angle / 180) * Math.PI;
  }
}

function rogo_asinh (arg) {
  arg = trigModeConvert(arg);
  return arg / Math.abs(arg) * Math.log(Math.abs(arg) + Math.sqrt(arg * arg + 1));
}

function rogo_acosh (arg) {
  arg = trigModeConvert(arg);
  return 2 * Math.log(Math.sqrt((arg + 1) / 2) + Math.sqrt((arg - 1) / 2));
}

function rogo_atanh (arg) {
  arg = trigModeConvert(arg);
  return 0.5 * Math.log((1 + arg) / (1 - arg));
}

function rogo_sinh (arg) {
  arg = trigModeConvert(arg);
  return ( Math.exp(arg) - 1 / Math.exp(arg) )/ 2;
}

function rogo_cosh (arg) {
  arg = trigModeConvert(arg);
  return (Math.exp(arg) + 1 / Math.exp(arg) )/ 2;
}

function rogo_tanh (arg) {
  arg = trigModeConvert(arg);
  return ( Math.exp(arg) - 1 / Math.exp(arg) )/ ( Math.exp(arg) + 1 / Math.exp(arg) );
}

function rogo_asin (arg) {
  arg = trigModeConvert(arg);
  return Math.asin(arg);
}

function rogo_acos (arg) {
  arg = trigModeConvert(arg);
  return Math.acos(arg);
}

function rogo_atan (arg) {
  arg = trigModeConvert(arg);
  return Math.atan(arg);
}

function rogo_sin (arg) {
  arg = trigModeConvert(arg);
  return Math.sin(arg);
}

function rogo_cos (arg) {
  arg = trigModeConvert(arg);
  return Math.cos(arg);
}

function rogo_tan (arg) {
  arg = trigModeConvert(arg);
  return Math.tan(arg);
}

function rogo_square (arg) {
  arg = parseFloat(arg);
  return arg*arg;
}

function rogo_recip (arg) {
  arg = parseFloat(arg);
  return 1/arg;
}

function rogo_fact (arg) {
  arg = parseInt(arg);
  var rval = arg;
  for (j = arg; j > 2 ;j--) {
    rval *= j - 1;
  }
  return rval;
}

function rogo_buffered(arg, operator) {
  rogo_buffer = parseFloat(arg) + operator;
  x += operator;
}

function rogo_eval(arg) {
  var n = arg.indexOf('^');
  if (n > 0) {
    return Math.pow(arg.substring(0,n), arg.substring(n+1));
  } else {      // likewise, entire x-value used as function argument, not just last term
    return eval(arg);
  }
}

function braceEval() {
  var start = x.lastIndexOf('(') + 1;
  if (start >= 0) {
    var expr = x.substring(start, x.length - 1);
    buffer = eval(expr);
    x = x.replace('(' + expr + ')', buffer);
  }
}

function changeTrigMode() {
  trigMode = (document.getElementById('trigmode_rad').checked) ? 'radians' : 'degrees';
}
