<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
  <title>Calculator</title>
  <script language="javascript">
    var isNS4 = (navigator.appName == "Netscape") ? 1 : 0;
  </script>
  <link rel="stylesheet" type="text/css" href="calc.css"/>
  <link rel="stylesheet" type="text/css" href="../../css/body.css"/>
  <!--[if IE ]>
  <style type="text/css">
    .label-button {
      margin: 0 2px 0 1px;
    }
  </style>
  <![endif]-->
  <script language="javascript">
  <!-- Copyright(c) Flow Simulation Limited, 2000, 2002
  var k = 0;
  var n_ = 0;
  var p = 0;
  var H = 0;
  var K = 0;
  var j = true;
  var s = 0;
  var M = false;
  var B = 0;
  var v = 14;
  var w = 12;
  var A = 3;
  var L = 12;
  var u = false;
  var b = false;
  var J = 'degrees';
  var d = false;
  var F = new fb(L);
  var z;
  var o;
  var i_;
  var N;
  var G;
  var I;
  var E_;
  var h;
  var shifton = 0;

  function checkButton(e) {
    var bCode = window.event ? e.keyCode : e.which;
    if (bCode == 16 && shifton == 0) {
      shifton = 1;
    }
    //alert(bCode);
    if (bCode >= 48 && bCode <= 57 && shifton == 0) {
      bCode = bCode - 48;
      Z(bCode);
    } else if (bCode >= 96 && bCode <= 105) {
      bCode = bCode - 96;
      Z(bCode);
    } else if (bCode == 42 || bCode == 106) {
      cb('*');
    } else if (bCode == 43 || bCode == 107) {
      cb('+');
    } else if (bCode == 45 || bCode == 109) {
      cb('-');
    } else if (bCode == 110) {
      Cb();
    } else if (bCode == 46) {
      Mb();
    } else if (bCode == 191 || bCode == 111) {
      cb('/');
    } else if (bCode == 187) {
      if (shifton == 1) {
        cb('+');
      } else {
        xb();
      }
    } else if (bCode == 189 && shifton == 1) {
      cb('-');
    } else if (bCode == 56 && shifton == 1) {
      cb('*');
    }
  }

  function checkShift(e) {
    var bCode = window.event ? e.keyCode : e.which;
    if (bCode == 16 && shifton == 1) {
      shifton = 0;
    }
  }

  function nb() {
    var e = location.search.substring(1, location.search.length).split('&');
    for (C = 0; C < e.length; C++) {
      var y = e[C].split('=');
      var name = y[0];
      var I = y[1];
      if (name == "value") {
        Nb(I);
      }
      if (name == "bgcolor") {
        if (I.charAt(0) >= '0' && I.charAt(0) <= '9') document.bgColor = "#" + I;
        else                                          document.bgColor = I;
      }
      if (name == "form") {
        z = I;
      }
      if (name == "input") {
        o = I;
      }
    }
    if (z && o && opener) {
      Nb(opener.document[z][o].value);
    }
    Jb(p);
  }
  function Nb(sVal) {
    var I = parseFloat(sVal);
    if (I == I && !isNaN(I)) p = I;
  }
  function ab() {
    this.I = 0;
    this.op = '';
  }
  function fb(G) {
    for (C = 0; G > C; C++) this[C] = new ab();
  }
  function Ib(I, op) {
    if (K == L) return false;
    for (C = K; C > 0; C--) {
      F[C].I = F[C - 1].I;
      F[C].op = F[C - 1].op;
    }
    F[0].I = I;
    F[0].op = op;
    K++;
    return true;
  }
  function wb(i_) {
    if (0 >= K) return false;
    op = F[0].op;
    var g = F[0].I;
    if (i_ && K == 1) {
      if (op == '+')   p = g * (1.0 + p / 100);
      else if (op == '-')   p = g * (1.0 - p / 100);
      else if (op == '*')   p = g * p / 100;
      else if (op == '/')   p = g / p * 100;
    }
    else {
      if (op == '+')   p = g + p;
      else if (op == '-')   p = g - p;
      else if (op == '*')   p = g * p;
      else if (op == '/')   p = g / p;
    }
    if (op == 'pow')  p = Math.pow(g, p);
    else if (op == 'root')  p = Math.pow(g, 1 / p);
    for (C = 0; K > C; C++) {
      F[C].I = F[C + 1].I;
      F[C].op = F[C + 1].op;
    }
    K--;
    return (op != '(');
  }
  function Q() {
    var c = document.calc.display.value;
    var t = parseFloat(c);
    if (isNaN(t) || t != t)
      alert('Not a valid number: "' + c + '"');
    else
      p = t;
    Jb(p);
    return p;
  }
  function Jb(I) {
    p = I;
    var c = '' + p;
    if (c.indexOf('N') >= 0 ||
            c.indexOf('n') >= 0 ||
            p != p ||
            isNaN(p)) {
      Eb();
      return;
    }
    var C = c.indexOf('e');
    if (C >= 0) {
      var D = c.substring(C + 1, c.length);
      if (C > w) C = w;
      c = c.substring(0, C);
      c += 'e' + D;
    }
    else {
      var f = Math.abs(p);
      var m = Math.floor(f);
      var r = f - m;
      var x = v - ('' + m).length - 1;
      if (!j && n_ > 0) x = n_;
      var l = '1000000000000000000000000000000000'.substring(0, x + 1);
      if (m < 10000000000000) m = Math.floor(Math.floor(f * l + .5) / l);
      if (0 > p)  c = '-' + m;
      else      c = ' ' + m;
      var q = '0000000000000000000000000000000000' + Math.floor(0.5 + r * l);
      q = q.substring(q.length - x, q.length);
      if (j || n_ == 0) {
        for (C = q.length; C > 0; C--)
          if (q.charAt(C - 1) != '0') break;
        q = q.substring(0, C);
      }
      if (q.length > 0) c += '.' + q;
    }
    if (M) {
      if (0 > B) c += 'e' + B;
      else           c += 'e+' + B;
    }
    if (0 > c.indexOf('.') && !d) {
      if (j || s > 0) c += '.';
      else                       c += ' ';
    }
    if (z && o && opener) {
      opener.document[z][o].value = c;
    }
    document.calc.display.value = ' ' + c;
    zb(false);
    b = false;
    resetHyp(b);
  }
  function Eb() {
    d = true;
    p = Number.NaN;
    document.calc.display.value = 'Overflow Error';
    zb(false);
    b = false;
    resetHyp(b);
  }
  function Mb() {
    K = 0;
    Bb();
  }
  function Bb() {
    d = false;
    M = false;
    kb();
    Jb(0);
    resetHyp(false);
  }
  function pb() {
    kb();
    if (Ib(0, '(')) Jb(p);
    else             Eb();
  }
  function S() {
    kb();
    while (wb());
    Jb(p);
  }
  function lb(op) {
    if (op == '+' || op == '-') return 1;
    else if (op == '*' || op == '/') return 2;
    else if (op == 'pow' || op == 'root') return 3;
    else                             return 0;
  }
  function cb(op) {
    kb();
    if (K > 0 && lb(F[0].op) >= lb(op)) wb();
    if (Ib(p, op)) Jb(p);
    else Eb();
  }
  function kb() {
    if (M)
      p = p * Math.pow(10, B);
    j = true;
    M = false;
    s = 0;
    n_ = 0;
  }
  function xb() {
    kb();
    while (wb(u));
    Jb(p);
  }
  function Db(E_) {
    if (0 > B) E_ = -E_;
    if (k > A) return;
    B = B * 10 + E_;
    k++;
  }
  function Kb(E_) {
    if (0 > p) E_ = -E_;
    if (k > v - 1) return;
    if (s > 0) {
      s = s * 10;
      p = p + (E_ / s);
      n_++;
    }
    else p = p * 10 + E_;
    k++;
  }
  function Z(E_) {
    if (j) {
      p = 0;
      k = 1;
      j = false;
    }
    if (E_ == 0 && k == 0) {
      Jb(p);
      return;
    }
    if (M) Db(E_);
    else                  Kb(E_);
    Jb(p);
  }
  function bb() {
    if (d) return;
    if (M) B = -B;
    else {
      kb();
      p = -p;
    }
    Jb(p);
  }
  function Cb() {
    if (j) {
      p = 0;
      k = 1;
      j = false;
    }
    if (s == 0) s = 1;
    Jb(p);
  }
  function jb() {
    if (u) {
      Y();
      return;
    }
    if (j || M) return;
    M = true;
    B = 0;
    k = 0;
    s = 0;
    Jb(p);
  }
  function Fb(N) {
    if (J == 'radians') return N;
    else if (J == 'grads') return (Math.PI * N / 200);
    else                        return (Math.PI * N / 180);
  }
  function hb(N) {
    if (J == 'radians') return N;
    else if (J == 'grads') return (N * 200 / Math.PI);
    else                        return (N * 180 / Math.PI);
  }
  function unShifted(fn) {
    u = false;
    fn();
  }
  function shifted(fn) {
    u = true;
    fn();
  }
  function P() {
    kb();
    if (b) {
      if (u) Jb(Math.log(p + Math.sqrt(p * p + 1.0)));
      else        Jb(0.5 * (Math.exp(p) - Math.exp(-p)));
    }
    else {
      if (u) Jb(hb(Math.asin(p)));
      else        Jb(Math.sin(Fb(p)));
    }
  }
  function rb() {
    kb();
    if (b) {
      if (u) Jb(Math.log(p + Math.sqrt(p * p - 1.0)));
      else        Jb(0.5 * (Math.exp(p) + Math.exp(-p)));
    }
    else {
      if (u) Jb(hb(Math.acos(p)));
      else        Jb(Math.cos(Fb(p)));
    }
  }
  function gb() {
    kb();
    if (b) {
      if (u) Jb(0.5 * Math.log((1.0 + p) / (1.0 - p)));
      else        Jb((Math.exp(p) - Math.exp(-p)) / (Math.exp(p) + Math.exp(-p)));
    }
    else {
      if (u) Jb(hb(Math.atan(p)));
      else        Jb(Math.tan(Fb(p)));
    }
  }
  function U() {
    kb();
    if (u) Jb(Math.pow(2, p));
    else        Jb(Math.log(p) / Math.LN2);
  }
  function eb() {
    kb();
    if (u) Jb(Math.pow(10, p));
    else        Jb(Math.log(p) / Math.LN10);
  }
  function X() {
    kb();
    if (u) Jb(Math.exp(p));
    else        Jb(Math.log(p));
  }
  function zb(h) {
    u = h;
  }
  function qb(hyper) {
    b = hyper;
    if (b) {
      resetHyp(true);
    } else {
      resetHyp(false);
    }
  }
  function db() {
    kb();
    H += p;
    resetMR();
    Jb(p);
  }
  function V() {
    kb();
    Jb(H);
  }
  function Ab() {
    kb();
    H = p;
    resetMR();
    Jb(p);
  }
  function tb() {
    kb();
    var swap = p;
    Jb(F[0].I);
    F[0].I = swap;
  }
  function Y() {
    kb();
    Jb(Math.PI);
  }
  function ib() {
    kb();
    Jb(Math.random());
  }
  function sb() {
    kb();
    Jb(Math.sqrt(p));
  }
  function Hb() {
    kb();
    Jb(p * p);
  }
  function cube() {
    kb();
    Jb(p * p * p);
  }
  function ub() {
    if (u) {
      R();
      return;
    }
    kb();
    Jb(1.0 / p);
  }
  function Lb() {
    if (u)  cb('root');
    else    cb('pow');
  }
  function R() {
    kb();
    var E_ = p;
    p = 1;
    if (0 > E_ || E_ > 200 || E_ != Math.floor(E_)) Eb();
    else {
      for (C = 1; E_ >= C; C++) p *= C;
      Jb(p);
    }
  }
  function resetHyp(on) {
    var btn = document.getElementById('btn_hyp');
    if (on) {
      btn.className = 'b1 b3';
    } else {
      btn.className = 'b1';
    }
  }
  function resetMR() {
    var on = (H != 0);
    var btn = document.getElementById('btn_mr');
    if (on) {
      btn.className = 'b2 memBut b3';
    } else {
      btn.className = 'b2 memBut';
    }
  }
  function changeTrigMode(label) {
    var deg_lab = document.getElementById('lab_deg');
    var rad_lab = document.getElementById('lab_rad');
    var grad_lab = document.getElementById('lab_grad');
    deg_lab.className = 'label-button';
    rad_lab.className = 'label-button';
    grad_lab.className = 'label-button';
    if (label.id == 'lab_rad') {
      J = 'radians';
      rad_lab.className = 'label-button selected';
    } else if (label.id == 'lab_grad') {
      J = 'grads';
      grad_lab.className = 'label-button selected';
    } else {
      J = 'degrees';
      deg_lab.className = 'label-button selected';
    }
  }
  // -->
  </script>
</head>
<body style="background-color:#ECE9D8" onload="nb(); document.calc.focus();" onkeydown="return checkButton(event);" onkeyup="return checkShift(event);">

  <div align="center" style="margin-top:6px">
<?php
print <<<END
    <table>
      <tr>
          <td colspan="5">
            <form name="calc" onsubmit="return false;">
              <input id="ans" type="text" size="20" name="display" readonly />
            </form>
          </td>
      </tr>
      <tr class="spaced">
          <td colspan="5" class="trigmode">
              <input type="radio" id="trigmode_deg" name="trigmode" value="deg" checked="checked" class="offscreen" />
              <label for="trigmode_deg" id="lab_deg" class="label-button selected" onclick="changeTrigMode(this)">Deg</label>
              <input type="radio" id="trigmode_rad" name="trigmode" value="rad" class="offscreen" />
              <label for="trigmode_rad" id="lab_rad" class="label-button" onclick="changeTrigMode(this)">Rad</label>
              <input type="radio" id="trigmode_grad" name="trigmode" value="grad" class="offscreen" />
              <label for="trigmode_grad" id="lab_grad" class="label-button" onclick="changeTrigMode(this)">Grad</label>
              <label id="lab_off" class="label-button off" onclick="window.close()">Off</label>
          </td>
      </tr>
      <tr class="spaced">
          <td><div class="b"><a href="javascript: shifted(P)" class="b1">asin</a></div></td>
          <td><div class="b"><a href="javascript: shifted(rb)" class="b1">acos</a></div></td>
          <td><div class="b"><a href="javascript: shifted(gb)" class="b1">atan</a></div></td>
          <td><div class="b"><a href="javascript: unShifted(X)" class="b1">ln</a></div></td>
          <td><div class="b"><a href="javascript: unShifted(eb)" class="b1">log</a></div></td>
      </tr>

      <tr>
          <td><div class="b"><a href="javascript: unShifted(P)" class="b1">sin</a></div></td>
          <td><div class="b"><a href="javascript: unShifted(rb)" class="b1">cos</a></div></td>
          <td><div class="b"><a href="javascript: unShifted(gb)" class="b1">tan</a></div></td>
          <td><div class="b"><a id="btn_hyp" href="javascript: qb(!b)" class="b1">hyp</a></div></td>
          <td><div class="b"><a href="javascript: shifted(eb)" class="b1">&nbsp;10<sup><i><span style="font-family:'Times New Roman',serif; font-size:130%; padding-left:2px">x</span></i></sup></a></div></td>
      </tr>


      <tr>
          <td><div class="b"><a href="javascript: shifted(xb)" class="b1">%</a></div></td>
          <td><div class="b"><a href="javascript: shifted(Lb)" class="b1" style="font-family:'Times New Roman'"><sup><i>x</i></sup>&radic;</a></div></td>
          <td><div class="b"><a href="javascript: sb()" class="b1" style="font-family:'Times New Roman'">&radic;</a></div></td>
          <td><div class="b"><a href="javascript: shifted(jb)" class="b1"><span style="font-family:'Times New Roman',serif; font-size:150%">&pi;</span></a></div></td>
          <td><div class="b"><a href="javascript: shifted(X)" class="b1">&nbsp;e<sup><i><span style="font-family:'Times New Roman',serif; font-size:130%; padding-left:2px">x</span></i></sup></a></div></td>
      </tr>

      <tr>
          <td><div class="b"><a href="javascript: Hb()" class="b1"><span style="font-family:'Times New Roman'; font-size:150%"><i>x</i></span><span style="font-size:90%; padding-left:1px"><sup>2</sup></span></a></div></td>
          <td><div class="b"><a href="javascript: cube()" class="b1"><span style="font-family:'Times New Roman'; font-size:150%"><i>x</i></span><span style="font-size:90%; padding-left:1px"><sup>3</sup></span></a></div></td>
          <td><div class="b"><a href="javascript: unShifted(Lb)" class="b1">&nbsp;<span style="font-family:'Times New Roman',serif; font-size:150%"><i>x</i></span><span style="font-size:90%; padding-left:1px"><sup><i>y</i></sup></span></a></div></td>
          <td><div class="b"><a href="javascript: unShifted(ub)" class="b1">1/<span style="font-family:'Times New Roman',serif; font-size:110%"><i>x</i></span></a></div></td>
          <td><div class="b"><a href="javascript: shifted(ub)" class="b1"><span style="font-family:'Times New Roman',serif; font-size:150%"><i>x</i></span>!</a></div></td>
      </tr>

END;
?>
      <tr class="spaced">

        <td>
          <div class="b"><a href="javascript: bb()" class="b2">&plusmn;</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: pb()" class="b2">(</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: S()" class="b2">)</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: Bb()" class="ac">CE</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: Mb()" class="ac">AC</a></div>
        </td>
        </td>
      </tr>

      <tr>
        <td>
          <div class="b"><a href="javascript: Z(7)" class="b2">7</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: Z(8)" class="b2">8</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: Z(9)" class="b2">9</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: cb('/')" class="b2">&divide;</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: db()" class="b2 memBut">M+</a></div>
        </td>
      </tr>

      <tr>
        <td>
          <div class="b"><a href="javascript: Z(4)" class="b2">4</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: Z(5)" class="b2">5</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: Z(6)" class="b2">6</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: cb('*')" class="b2">&times;</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: Ab()" class="b2 memBut">Min</a></div>
        </td>
      </tr>

      <tr>
        <td>
          <div class="b"><a href="javascript: Z(1)" class="b2">1</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: Z(2)" class="b2">2</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: Z(3)" class="b2">3</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: cb('-')" class="b2">-</a></div>
        </td>
        <td>
          <div class="b"><a id="btn_mr" href="javascript: V()" class="b2 memBut">MR</a></div>
        </td>
      </tr>

      <tr>
        <td>
          <div class="b"><a href="javascript: Z(0)" class="b2">0</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: Cb('+')" class="b2">.</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: unShifted(xb)" class="b2">=</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: cb('+')" class="b2">+</a></div>
        </td>
        <td>
          <div class="b"><a href="javascript: unShifted(jb)" class="b2">EXP</a></div>
        </td>
      </tr>
      <tr class="spaced">
        <td colspan="5" style="text-align:right; font-family:Arial,sans-serif; font-size:7pt; color:black">
          www.calculator.org
        </td>
      </tr>
    </table>
  </div>
</body>
</html>

