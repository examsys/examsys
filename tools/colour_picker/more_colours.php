<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* Displays advanced colour picking options. Called from 'colour_picker.inc'.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';

function rgb_hex($input) {
  $input = str_replace('rgb(','',$input);
  $input = str_replace(')','',$input);
  $parts = explode(',',$input);
  
  $r = dechex($parts[0]);
  if ($r == '0') $r = '00';
  
  $g = dechex($parts[1]);
  if ($g == '0') $g = '00';
  
  $b = dechex($parts[2]);
  if ($b == '0') $b = '00';
  
  return $r . $g . $b;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['colours']; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <style type="text/css">
    body {background-color:#F0F1F2; margin:6px}
  </style>
  <script>
    var currentColor;

    // function to generate the hex code
    function d2h(d) {
      var h = hD.substr(d&15,1);
      while(d>15) {
        d>>=4;h=hD.substr(d&15,1)+h;
      }
      return h;
    }

    function toHexCode(dec) {
      // array to store the hex code for each value passed 
      // to the getHex function
      var hexCode= new Array();

      // variable to define the dynamic array index
      // for hexCode array
      var i=0;

      // while loop to run until dec variable
      // is greater than 15
      while (dec > 15) {
        hexCode[i] = getHex(dec);

        // evaluate dec each time to
        // pass the while loop condition
        dec = Math.floor(dec / 16);
        i+=1;
      }
      // store the last value 
      // skiped due to loop condition
      // for dec < 15
      hexCode[i] = getHex(dec);

      // variable to store the hex Codes
      var decToHex = "";

      // reverse loop on hexCode to
      // generate the right order of hex codes
      for (i=hexCode.length-1; i>=0; i--) {
        decToHex += hexCode[i]; 
      }
    
      if (decToHex.length < 2) {
        decToHex = '0' + decToHex;
      }

      return decToHex;
    } 
   
    function updateHex() {
      hexval = toHexCode(document.getElementById('r').value) + toHexCode(document.getElementById('g').value) + toHexCode(document.getElementById('b').value) + '';
      document.getElementById('hex').value = hexval;
      document.getElementById('swatch').style.backgroundColor = '#' + hexval;
      currentColor = '#' + hexval;
    }
   
    function updateSwatch(hexval) {
      document.getElementById('r').value = parseInt(hexval.substring(0,2),16)
      document.getElementById('g').value = parseInt(hexval.substring(2,4),16)
      document.getElementById('b').value = parseInt(hexval.substring(4,6),16)
      document.getElementById('hex').value = hexval;
      document.getElementById('swatch').style.backgroundColor = '#' + hexval;
      currentColor = '#' + hexval;
    }
   
    function manualSwatch() {
      hexval = document.getElementById('hex').value;
      document.getElementById('r').value = parseInt(hexval.substring(0,2),16);
      if (hexval.length > 2) {
        document.getElementById('g').value = parseInt(hexval.substring(2,4),16);
      }
      if (hexval.length > 4) {
        document.getElementById('b').value = parseInt(hexval.substring(4,6),16);
      }
      if (hexval.length == 6) {
        document.getElementById('swatch').style.backgroundColor = '#' + document.getElementById('hex').value;
        currentColor = '#' + document.getElementById('hex').value;
      }
    }
   
    function returnColour() {
      window.opener.document.getElementById('span_<?php echo $_GET['swatch']; ?>').style.backgroundColor = currentColor;
      window.opener.document.getElementById('<?php echo $_GET['swatch']; ?>').value = currentColor;
      window.close();
    }
    
    function initialSet() {
      document.getElementById('current').style.backgroundColor = window.opener.document.getElementById('span_<?php echo $_GET['swatch']; ?>').style.backgroundColor;
      document.getElementById('swatch').style.backgroundColor = window.opener.document.getElementById('span_<?php echo $_GET['swatch']; ?>').style.backgroundColor;
    }
  </script>
</head>

<body onload="initialSet(); window.opener.document.getElementById('picker').style.display = 'none';">
<table cellspacing="1" cellpadding="0" border="0" style="font-size:90%; width:100%">
<tr><td style="vertical-align:top">

<table cellspacing="1" cellpadding="0" border="0" style="width:142px">
<tr><td>R</td><td><input type="text" size="5" id="r" name="r" onkeyup="updateHex();" /></td></tr>
<tr><td>G</td><td><input type="text" size="5" id="g" name="g" onkeyup="updateHex();" /></td></tr>
<tr><td>B</td><td><input type="text" size="5" id="b" name="b" onkeyup="updateHex();" /></td></tr>
<tr><td colspan="2">&nbsp;</td><td></tr>
<tr><td>#</td><td><input type="text" size="10" id="hex" name="hex" onkeyup="manualSwatch();" /></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2">
<div style="position:absolute; left:17px; font-size:90%"><?php echo $string['new']; ?></div>
<div id="swatch" style="width:50px; height:50px; position:relative; top:16px; left:0px; background-color:white; border:1px solid #808080"></div>
<div id="current" style="width:50px; height:50px; position:relative; top:18px; left:0px; background-color:white; border:1px solid #808080"></div>
<div style="position:relative; top:24px; left:15px; font-size:90%"><?php echo $string['old']; ?></div>
</td></tr>
</table>
</td>

<td style="text-align:right" align="right">
<table cellspacing="1" cellpadding="0" border="0">
<?php
  $colours[0] = array('EF001B','CC0017','A60012','83000E','5C000A','EF0078','CE0067','AD0057','8B0045','6A0035','E301ED','C501CE','A401AB','88018E','610066','6716EF','5913CE','4B10AF','3E0D90','2D0A6A');
  $colours[1] = array('F13449','D52437','BB1D2E','980B1A','70000C','F32A8F','D5207A','B21162','970C51','710039','E624EF','CC20D4','AD10B4','900995','6F0374','7B38ED','6C2FD2','5C27B5','471A94','391379');
  $colours[2] = array('F67684','E36875','CA5965','B34E59','933C45','F563AC','DE599B','CC5490','B24D7F','96416C','EE68F4','DB5FE1','C759CC','B255B6','964799','A779F5','976CDF','8D68CC','7F5EB7','6F539C');
  $colours[3] = array('FCC0C6','EEA8AF','DD959C','CE8C93','BC858B','FEC7E2','F4B8D6','E5A6C6','D495B4','BB85A0','FABFFD','EEAFF1','E19FE4','CF90D2','B985BB','E0C3FD','D1B1F1','C1A0E2','B192D1','A489C0');
  $colours[4] = array('FEF5F6','FDECED','F7DEE0','EACEDC','DEC1D0','FEF3F8','FBE8F1','EFD0E0','E6C7D6','D9B8C8','FEF2FE','FAE6FB','F1D3F2','E3C1E4','D8BAD9','F5EDFE','F0E5FB','E1D3EF','D9CBE7','CDBFDC');
  $colours[5] = array('028B6C','02775D','02644E','015441','013B2E','1882ED','1574D4','115EAB','0E4F90','0A3764','0040EB','0039D0','0030B1','002892','001B64','50509E','46468B','3A3A73','303060','222245');
  $colours[6] = array('279980','1C856E','15705B','0B5B49','054637','3C95EE','3283D5','286FB8','1B5997','0C3E71','2A61F3','1D4ED3','1640B2','113699','022072','6D6DB0','5D5D99','4C4C82','373763','29294D');
  $colours[7] = array('69BAA7','61A898','57998A','508B7D','47776C','7BB8F5','6EA7E0','6195C9','5684B2','4C7298','6D92F5','5F82E0','5675C9','4D68B2','495F9A','9B9BC9','8B8BB6','7E7EA5','747496','5F5F7A');
  $colours[8] = array('D0EAE4','B3D7CF','9BC4BA','8FB4AC','86A49D','C3DFFC','AACDF0','9BBDE0','97B4D1','94ACC4','BDCDFB','A8BBEF','96AAE1','8A9BCB','8393C0','D8D8EB','C7C7DC','B5B5CC','A5A5BC','9898AC');
  $colours[9] = array('F0F8F6','DEEDEA','D7E6E2','CEDDDA','C8D6D2','F1F7FE','E5F0FB','D8E5F2','CFDBE7','C3CFDA','EFF3FE','E5EAFA','DDE3F4','D2D8EA','C3CADD','F4F4F9','E5E5EF','DBDBE5','D6D6DF','D1D1D9');
  $colours[10] = array('00A000','008D00','007700','006000','004500','86D800','73BA00','629E00','528400','395C00','EDED00','CECE00','AFAF00','909000','737300','E3AB00','C79600','AA8000','856400','604800');
  $colours[11] = array('27B127','229C22','1B881B','0F6E0F','085408','96DC24','84C220','6EA515','5C8B0F','3F6600','F1F12C','D3D31B','B2B211','959509','747403','E8B827','CDA220','B18A15','8C6C0A','6E5300');
  $colours[12] = array('68C868','5CB65C','56A456','4B924B','488248','B7E768','A8D45F','97C056','86AA4D','718E41','F1F164','E1E15D','CACA58','B2B24D','979746','EECC65','DABC5E','C7AC59','B09850','948044');
  $colours[13] = array('C6ECC6','ADDEAD','96CD96','87B987','87B087','E1F6C0','D0EBA6','C1D99A','B1C88C','A4B786','FBFBAD','F1F194','E2E28E','CECE8C','B9B982','FAEABA','F2DFA7','E6D090','CBBB8B','B6A778');
  $colours[14] = array('EEF9EE','DFF1DF','D5E8D5','C6DBC6','BED1BE','F1FBE2','E9F5D5','DFEBCD','D4E1C0','C9D5B6','FEFEF0','FAFAE3','F0F0CB','E4E4C5','DADABA','FDF8EA','F9F2DE','EEE4C7','DFD7BF','D6CFB7');
  $colours[15] = array('818181','676767','494949','272727','000000','783C00','673300','562B00','472300','341A00','EB4600','CD3D00','AD3300','8F2A00','671E00','ED7700','D26900','AF5800','904800','643200');
  $colours[16] = array('989898','838383','646464','515151','2F2F2F','8C5927','7C4F23','673F19','583616','402408','EB5F26','D1521E','B34315','95330A','702303','F08C28','D47A20','B96816','954F09','713902');
  $colours[17] = array('C9C9C9','A9A9A9','919191','787878','565656','AF8B68','A28264','917458','856D55','715C49','F19068','DD8561','C97654','B47053','985D45','F5AC63','E1A05F','CA9259','B78451','966B41');
  $colours[18] = array('EFEFEF','DCDCDC','C1C1C1','9D9D9D','828282','DBCAB9','CCB8A5','BDA792','A3917F','9A8979','FBCEBC','F1BBA5','E1AA93','CE9F8B','B18B7B','FCD7B3','F3CAA2','E7B98C','C8A078','B29171');
  $colours[19] = array('FFFFFF','F7F7F7','EDEDED','DDDDDD','C9C9C9','F4EFEB','EFE8E1','E6DED6','DBD3CC','D0C9C2','FEF5F2','FAE8E1','F0DBD3','E1CBC2','D6BEB5','FEF7F0','FAECDE','F1E2D3','E3D3C3','DACABA');

  for ($row=0; $row<20; $row++) {
    echo "<tr style=\"height:14px\">\n";
    foreach ($colours[$row] as $colour) {
      echo "<td style=\"background-color:#$colour; width:14px\" onclick=\"updateSwatch('$colour')\"></td>";
    }
    echo "</tr>\n";
  }
?>
</table>

</td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2" style="text-align:center"><input type="button" name="ok" value="<?php echo $string['ok']; ?>" style="width:100px" onclick="returnColour();" />&nbsp;<input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" style="width:100px" onclick="window.close();" /></td></tr>
</table>
</body>
</html>
