<?php

//EDDSDFDFD

print <<<END
<HTML>

<HEAD>

  <link rel="stylesheet" type="text/css" href="scal.css" />
</HEAD>
<BODY>
<!--
comment
-->
<script language="JavaScript" src="calc.js"></script>
<script language="JavaScript">
  var arr_zn = ["7","8","9","/","C","4","5","6","*","sqr","1","2","3","-","=","0","z",".","+"],
          T = self.opener.sCal, a_img = [], i, j, l;
  function ch_img(v1, v2) {
    document.images[v1].src = 'images/'+v1+'_'+v2+'.gif';
  }
  function on_load() {
    //T.TCRmntr('C');
    T.t_load = true;
    if (T.control_obj.value == '') from_p = '0';
    else from_p = T.control_obj.value;
    document.forms[0].elements[0].value = from_p;
  }
  for (i = 0; i < document.links.length; i++) {
    l = document.links[i];
    l.onmousedown = Function("ch_img(" + i + ",0)")
    l.onmouseout = Function("ch_img(" + i + ",1)")
    l.onmouseup = l.onmouseover = Function("ch_img(" + i + ",2)")
    l.onclick = l.ondblclick = Function("T.TCRmntr('" + arr_zn[i] + "')");
  }

//var object_calc;


  var urlParams = {};
  (function () {
    var e,
            a = /\+/g,  // Regex for replacing addition symbol with a space
            r = /([^&=]+)=?([^&]*)/g,
            d = function (s) { return decodeURIComponent(s.replace(a, " ")); },
            q = window.location.search.substring(1);

    while (e = r.exec(q))
      urlParams[d(e[1])] = d(e[2]);
  })();

//alert(urlParams["id"]);
//alert(window.opener.document[urlParams["form"]][urlParams["field"]].value);

</script>

<form name="sCal">
<!-- calculator color; & layout -->
<table class="calculator" >
<!--
 Top(FSET1) drop-down SELECT menu: can change or add JScrAps ** 1 **
-->
<tr align="center">
 <td colspan=6>


<!-- 
Begin JScrAps
-->



  </td>
 </tr>
  <!--  x-text area (PPC does 19 cols max?) for MAIN INPUT/OUTUT -->
  <tr align="center">
    <td colspan=5><TextArea name="IOx" rows=4 cols=30 class="LCD"></TextArea></td>

  </tr>
  </tr>
  <tr>
    <td colspan=5>
    M:<input type="text" name="IOm" size=32 readonly="readonly" disabled="disabled" class="memory"></td>
    </td>
  </tr>


<tr align="center">

END;

  if(isset($_GET['calc']) AND $_GET['calc']!=2)
  {
print <<<END
  <td><div class="button1"><a href="#" class="calcbutton6" onclick="Xwork('x / Math.abs(x) * Math.log(Math.abs(x) + Math.sqrt(x * x + 1))')">asinh</a></div>
  </td>
  <td><div class="button1"><a href="#" class="calcbutton6" onclick="Xwork(' 2 * Math.log(Math.sqrt((x + 1) / 2) + Math.sqrt((x - 1) / 2))')">acosh</a></div>
  </td>
  <td><div class="button1"><a href="#" class="calcbutton6" onclick="Xwork(' Math.log((x - 1) / (x + 1)) / 2')">atanh</a></div>
  </td>

END;

}
else
  {
  print <<<END
 <td></td><td></td><td></td>
END;
  }
print <<<END

  <td><div class="button1"><a href="#" class="calcbutton1" onClick="BkSpace()">DEL</a></div></td>


  <td><div class="button1"><a href="#" class="calcbutton1" onClick="Clear()">AC</a></div></td>

</tr>

END;

  if(isset($_GET['calc']) AND $_GET['calc']!=2)
  {
print <<<END

  <tr align="center">
  <td><div class="button1"><a href="#" class="calcbutton6" onclick="Xwork('( Math.exp(x) - 1 / Math.exp(x) )/ 2')">sinh</a></div>
  </td>
  <td><div class="button1"><a href="#" class="calcbutton6" onclick="Xwork('( Math.exp(x) + 1 / Math.exp(x) )/ 2')">cosh</a></div>
   </td>
  <td><div class="button1"><a href="#" class="calcbutton6" onclick="Xwork('( Math.exp(x) - 1 / Math.exp(x) )/ ( Math.exp(x) + 1 / Math.exp(x) )')">tanh</a></div>
  </td>

  <td><div class="button1"><a href="#" class="calcbutton7" onclick="Xwork('Math.exp(x)')">e<sup>x</sup></a></div>
  </td>

    </td>
  <td><div class="button1"><a href="#" class="calcbutton7" onclick="Xwork('Math.pow(10,x)')">10<sup>x</sup></a></div>
  </td>

  </tr>


  <tr align="center">
  <td><div class="button1"><a href="#" class="calcbutton6" onclick="Xwork('Math.asin(x)*180/Math.PI')">asin</a></div>
    </td>
  <td><div class="button1"><a href="#" class="calcbutton6" onclick="Xwork('Math.acos(x)*180/Math.PI')">acos</a></div>
    </td>
  <td><div class="button1"><a href="#" class="calcbutton6" onclick="Xwork('Math.atan(x)*180/Math.PI')">atan</a></div>
    </td>
  <td><div class="button1"><a href="#" class="calcbutton7" onclick="Xwork('Math.log(x)')">ln</a></div>

  <td><div class="button1"><a href="#" class="calcbutton7" onclick="Xwork('Math.log(x)*Math.LOG10E')">log</a></div>

</tr>


<tr align="center">
  <td><div class="button1"><a href="#" class="calcbutton6" onclick="Xwork('Math.sin(x*Math.PI/180)')">sin</a></div>
  </td>
  <td><div class="button1"><a href="#" class="calcbutton6" onclick="Xwork('Math.cos(x*Math.PI/180)')">cos</a></div>
  </td>
  <td><div class="button1"><a href="#" class="calcbutton6" onclick="Xwork('Math.tan(x*Math.PI/180)')">tan</a></div>
  </td>
  <td><div class="button1"><a href="#" class="calcbutton7" onclick="Xwork('Math.PI')">PI</a></div>
  </td>
  <td><div class="button1"><a href="#" class="calcbutton7"  onclick="Xwork('Math.E')">e</a></div>
      </td>
</tr>



  <tr align="center">
   <td><div class="button1"><a href="#" class="calcbutton7" onclick="Xwork('Math.sqrt(x)')">&radic;</a></div>
   <td><div class="button1"><a href="#" class="calcbutton7" onclick="xSquare()">x<sup>2</sup></a></div>
   <td><div class="button1"><a href="#" class="calcbutton7" onClick="recip()">1/x</a></div></td>
   <td><div class="button1"><a href="#" class="calcbutton7" onClick="Xwork('for(j=x;j>2;j--){x*=j-1;}')">x!</a></div></td>
   <td><div class="button1"><a href="#" class="calcbutton7" onClick="xPlusEq('^')">x<sup>y</sup></a></div>



  </tr>

END;

  }
    ?>


<tr align="center">




  <td><div class="button1"><a href="#" class="calcbutton5" onClick="xMultEq('-1')">&plusmn;</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton5" onClick="xPlusEq('(')">(</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton5" onClick="xPlusEq(')')">)</a></div></td>

<td>
    <script language="javascript">
if(urlParams["form"]!="undefined") {
  document.write('<div class="button1"><a href="#" class="calcbutton" onClick="returntoform()">RET</a>')
}
else
{
  document.write('<div class="button1"><a href="#" class="calcbutton" onClick="window.close()">OFF</a>')
}

  </script>


 </div></td>
  <td><div class="button1"><a href="#" class="calcbutton2"  onClick="Mclear()">MC</a></div></td>
  </td></tr>
<!-- layout of CALCULATOR BUTTONS: -->
<tr align="center">
  <td><div class="button1"><a href="#" class="calcbutton4" onClick="xPlusEq(7)">7</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton4" onClick="xPlusEq(8)">8</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton4" onClick="xPlusEq(9)">9</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton3" onClick="xPlusEq('/')">/</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton2" onClick="Mclear(); XtoM()">MS</a></div></td>


</tr>
<tr align="center">
  <td><div class="button1"><a href="#" class="calcbutton4" onClick="xPlusEq(4)">4</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton4" onClick="xPlusEq(5)">5</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton4" onClick="xPlusEq(6)">6</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton3" onClick="xPlusEq('*')">*</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton2" onClick="MtoX()">MR</a></div></td>
</tr>
<tr align="center">
  <td><div class="button1"><a href="#" class="calcbutton4" onClick="xPlusEq(1)">1</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton4" onClick="xPlusEq(2)">2</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton4" onClick="xPlusEq(3)">3</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton3" onClick="xPlusEq('-')">-</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton2" onClick="Mminus()">M-</a></div></td>

</tr>
<tr align="center">
  <td><div class="button1"><a href="#" class="calcbutton4" onClick="xPlusEq('0')">0</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton4" onClick="xPlusEq('.')">.</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton3" onClick="xEval()">=</a></div></td>
  <td><div class="button1"><a href="#" class="calcbutton3" onClick="xPlusEq('+')">+</a></div></td>

  <td><div class="button1"><a href="#" class="calcbutton2"  onClick="Mplus()">M+</a></div></td>

</tr>
</table>
</form>
<script language="JavaScript">
if(urlParams["form"]!="undefined") {
        x=window.opener.document[urlParams["form"]][urlParams["field"]].value;
Ox();
}
</script>
</body>
</html>
