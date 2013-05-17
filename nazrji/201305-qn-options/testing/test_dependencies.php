<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>dependendies test</title>
    
    <style type="text/css">
.vert {
	width: 14px;
  /*height: 200px;*/
  margin-top: 250px;
	writing-mode: lr-tb;
  float: left;
	-webkit-transform: rotate(-90deg);
	-moz-transform: rotate(-90deg);
	-ms-transform: rotate(-90deg);
	-o-transform: rotate(-90deg);
	transform: rotate(-90deg);
}
</style>
</head>

<?php
$thispath=getcwd();
if (isset($_GET['path'])) $thispath=$_GET['path'];
//echo $thispath;
$conn_table = Array(Array());

//search for more strings
function strpos_arr($string, $array, $strtpnt) {
  $pos=mb_strlen($string,'UTF-8');
  foreach($array as $search) {
    if(($temp = mb_strpos($string, $search,$strtpnt,'UTF-8'))!==false && $temp<$pos && $temp!=0) $pos=$temp;
  }
  if ($pos==mb_strlen($string,'UTF-8')) {
    $pos=false;
  }
  return $pos;
}

//add color chanels
function coladd($c1,$c2,$c3) {
  $c4='';
  for ($cx=0;$cx<3;$cx++) {
    $c5=substr($c1,$cx*2,2);
    $c6=substr($c2,$cx*2,2);
    if ($c6=='--') 
      $c4.= dechex($c3+hexdec($c5));
    else
      $c4.= $c6;
    }
  return $c4;
 }

//read the filenames and content into an array
echo "<table border=1 cellpadding=5 cellspacing=1><tr><td>";
echo "<a href='?path=".dirname($thispath)."'>..</a><br />";
if ($handle = opendir($thispath)) {
	while (false !== ($filename = readdir($handle))) {
    //if ($filename != "." && $filename != ".." && $filename != ".svn" && is_dir($thispath.'/'.$filename)) {
      echo "<a href='?path=".$thispath."/".$filename."'>".$filename."</a><br />";
    //}
    if ($filename != "." && $filename != ".." && (strpos($filename,'.php',0)>0 || strpos($filename,'.inc',0)>0)) {
			$file_point=fopen($thispath.'/'.$filename,"r");
			$file_content=fread($file_point, filesize($thispath.'/'.$filename));
			$file_content=preg_replace('/\n/','',$file_content);
			$file_content=strrev($file_content);
			fclose($file_point);
			$conn_table[$filename][0]='';
          
			$pos1 = mb_strpos($file_content,'php.',0,'UTF-8');
      $pos1 = strpos_arr($file_content,Array('php.','cni.'),0);
      $xxx =0;
			while ($pos1!==false && $xxx<3) {
        $xxx++;
        if (mb_substr($file_content,$pos1+1,1)!="'" && mb_substr($file_content,$pos1+1,1)!="\""){ 
          $part2 = strpos_arr($file_content,Array('\\','\'','"','=','/',')','(',' '),$pos1);
          $part3 = strrev(mb_substr($file_content,$pos1,$part2-$pos1,'UTF-8'));
          $part3 = preg_replace('/-/','_',$part3);
          if ($part3!='' && $part3!='.php' && $part3!='.inc') $conn_table[$filename][$part3]=$part3;
        }
        $pos1 = strpos_arr($file_content,Array('php.','cni.'),$pos1+1);
			}
    }
	}
	closedir($handle);
}
echo "</td></tr></table>";

//count the occurences
function conn_count() {
global $count_table,$conn_table;
$count_table = Array();
  foreach ($conn_table as $conn_table_key=> $conn_table_elem) {
  if (!isset($count_table[$conn_table_key])) $count_table[$conn_table_key]=0;
    foreach ($conn_table_elem as $conn_table2_key => $conn_table2_elem) {
      if (isset($count_table[$conn_table2_key]))
        $count_table[$conn_table2_key]++;
      else
        $count_table[$conn_table2_key]=1;
    }
  }
}

function remove($lim) {
  global $count_table,$conn_table;
  foreach ($conn_table as $conn_table_key => $conn_table_elem) {
    if ($count_table[$conn_table_key]<=$lim) unset($conn_table[$conn_table_key]);
  }
}

//present results in table
function tabela($lim) {
  global $count_table,$conn_table;
  ksort($count_table);
  $ref_count_table = $count_table; //array containing refferenced file name and the count of refferences to it
  arsort($ref_count_table);

  //table key
  $desc = '<ul style="font-size: smaller;font-weight: normal;text-align: left;color: #00F;">';
  $desc .= '<li>files (with number of references) are listed in rows (those with no refferences to themselfs are on grey bckgr)</li>';
  $desc .= '<li>referrences (with number of files refferring to them) are in columns (those reffered but not found on this path are on grey bckg)</li>';
  $desc .= '<li>dark-gray cells represent connections</li>';
  $desc .= '<li>green cells marks self-consideration</li>';
  $desc .= '<li>\'#\' represents mutual connection</li>';
  $desc .= '<li>rows in red (and columns in blue) represent files with no connection to others</li>';
  $desc .= '<li>columns in red represent files that no other is connected to</li>';
  $desc .= '<li>colours are additive</li>';
  $desc .= '</ul>';

  $file_count_table = Array();  //array containing analysed file name and the count of refferences from it
  foreach ($conn_table as $ti => $tv) $file_count_table[$ti]=count($tv);
  arsort($file_count_table);

  echo '<table border=0 cellspacing=0>';
  echo '<tr height=150>';
  echo '<th></th><th>'.$desc.'</th><th></th>';
  foreach ($ref_count_table as $ti => $tv) if ($ti!='') {
    $col = '';if (!isset($file_count_table[$ti])) $col = '#DDDDDD';
    if ($ref_count_table[$ti]>=$lim) echo '<th bgcolor="#CCCCCC"></th><th class="vert"><span style="background-color:'.$col.'">'.(($tv>9)?$tv:'0'.$tv).'&nbsp;'.$ti.'</span></th>';
  }
  echo '</tr>';
  $line=0;
  $count = 0;
  $tvold=0;
  foreach ($file_count_table as $ti2 => $tv2) {
    if ($ti2!='0' && ($tv2-1)>=$lim) {
      $line=1-$line;
      echo '<tr>';
      echo '<td align=right>'.++$count.'.</td>';
      $col = 'FFFFFF';if ($ref_count_table[$ti2]==0) $col = 'DDDDDD';
      echo '<td bgcolor="#'.$col.'">'.$ti2.'</td>';
      echo '<td align=right>'.($tv2-1).'</td>';
      foreach ($ref_count_table as $ti => $tv) {
        if ($ti!='' && $ref_count_table[$ti]>=$lim) {
          if ($tvold==$tv) 
            echo '<td bgcolor="#CCCCCC"></td>';
          else
            echo '<td bgcolor="#AAAAAA"></td>';

          $chr = '&nbsp;';
          $col = 'F0F0F0';if ($line==1) $col = 'F8F8F8';
          if (in_array($ti,$conn_table[$ti2])) $col='CCCCCC';
          if (isset($conn_table[$ti]) && count($conn_table[$ti])==1) $col=coladd($col,'----FF',-20);
          if ($tv==0 || $tv2==1) $col = $col=coladd($col,'FF----',-10);
          if ($ti==$ti2) $col=coladd($col,'--FF--',-90);
          if ($tv==0 && $tv2==1 && $ti==$ti2) $col=coladd($col,'--AA--',-80);
          if (in_array($ti,$conn_table[$ti2]) && isset($conn_table[$ti]) && (count($conn_table[$ti])>1) && (in_array($ti2,$conn_table[$ti]))) 
            $chr='#';//$col='A0FFA0"';
          //if ($col == 'E0E0E0') $col = 'F0F0F0';
          //if ($col == 'E8E8E8') $col = 'F8F8F8';
          echo '<td bgcolor="#'.$col.'" align=center>'.$chr.'</td>';
          $tvold=$tv;
          } 
        }
      echo '</tr>';
      }
    }
  echo '</table>';
}

//counting the occurences
conn_count();
//presenting the results
tabela(0);

?>