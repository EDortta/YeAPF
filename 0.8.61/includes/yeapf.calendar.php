<?php
/*
    includes/yeapf.calendar.php
    YeAPF 0.8.61-105 built on 2018-10-16 08:01 (-3 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2018-05-30 11:21:05 (-3 DST)
*/

  function negaCor($cor)
  {
    if (substr($cor,1,1)=='#')
      $cor=substr($cor,1,6);
    $r=hexdec(substr($cor,0,2));
    $g=hexdec(substr($cor,2,2));
    $b=hexdec(substr($cor,4,2));
    $r=255-$r;
    $g=255-$g;
    $b=255-$b;

    $cor=dechex($r).dechex($g).dechex($b);

    return $cor;
  }

  function montarCalendario($aDate,$aCond='')
  {
    global $sysDate;

    $mNames = array("","<b>J</b>aneiro", "<b>F</b>evereiro","<b>M</b>arço","<b>A</b>bril","<b>M</b>aio","<b>J</b>unho","<b>J</b>ulho","<b>A</b>gosto","<b>S</b>etembro","<b>O</b>utubro","<b>N</b>ovembro","dezembro");

    $aDate=substr($aDate,0,8);

    $auxDate=dateSQL2timestamp(substr($aDate,0,6).'01');
    $firstDay = date('w',$auxDate);
    $aDay   = date('j',$auxDate);
    $aMonth = date('n',$auxDate);
    $aYear  = date('Y',$auxDate);
    $aMonthName=$mNames[$aMonth];

    $daysInMonth = date('t',$auxDate);
    $nextMonth = getDate(mktime(0, 0, 0, $aMonth + 1, 1, $aYear));

    if ($aMonth<10)
      $fMonth="0$aMonth";
    else
      $fMonth=$aMonth;

    $r="<tr><th colspan=7>$aMonthName</th></tr><tr>";
    $q=0;
    for ($n=0; $n<$firstDay; $n++) {
      $r.="<td class='calendarioVazio'></td>";
      $q++;
    }

    for ($d=0; $d<$daysInMonth; $d++)
    {
      $dd=$d+1;
      if ($q==0) {
        $r.=' </tr>';
        if ($d<$daysInMonth)
          $r.='<tr>';
      }

      if ($dd<10)
        $df=$aYear.$fMonth."0$dd";
      else
        $df="$aYear$fMonth$dd";

      $bg='';
      $class='calendarioDia';
      $sql="select cor from calendario where (inicio<='$df' and fim>='$df') $aCond";
      $bg=valorSQL($sql);
      $fc='#000000';

      //echo "bg=$bg<br>";
      if ($df==substr($sysDate,0,8)) {
        $dd="<B>$dd</b>";
        $bg='#aaaaaa';
      } else
        if ($bg=='')
          $bg='#ffffff';
        else {
          $fc=negacor($bg);
          $bg="#$bg";
          $class='calendarioDiaMarcado';
        }

      if ($q==0) {
        $fc='ff0000';
        $dd="<em><b>$dd</b></em>";
      }

      $r.="<td class='$class' bgcolor='$bg' $bgImg><font color='#$fc'>$dd</font></td>";
      $q=($q+1) % 7;
    }

    while ($q!=0) {
      $r.="<td width=20 class='calendarioVazio'></td>";
      $q=($q+1) % 7;
    }
    $r="<table>$r</table>";
    return $r;
  }

  function relatarCalendario($aDate,$aCond='')
  {
    $aDate=substr($aDate,0,8);

    $auxDate=dateSQL2timestamp(substr($aDate,0,6).'01');
    $firstDay = date('w',$auxDate);
    $aDay   = date('j',$auxDate);
    $aMonth = date('n',$auxDate);
    $aYear  = date('Y',$auxDate);
    $aMonthName=$mNames[$aMonth];

    $daysInMonth = date('t',$auxDate);
    if ($aMonth<10)
      $fMonth="0$aMonth";
    else
      $fMonth=$aMonth;

    $inicioMes="$aYear$fMonth".'01';
    $finalMes="$aYear$fMonth$daysInMonth";

    $valor='';

    $sql="select * from calendario where (inicio>='$inicioMes' and fim<='$finalMes') $aCond";
    $q=fazerSQL($sql);
    while ($r=mysql_fetch_array($q)) {
      $cor=$r['cor'];
      $descricao=$r['descricao'];
      $inicio=dataFormatada($r['inicio']);
      $fim=dataFormatada($r['fim']);
      if ($fim==$inicio)
        $fim='';
      else {
        $fim="a $fim";
        $inicio="de $inicio";
      }

      $valor.='<tr><td width=30></td>';
      $valor.="<td class='calendarioDia' bgcolor='#$cor' width=20></td>";
      $valor.="<td class=calendarioDescricao>$descricao<br>$inicio $fim</td>";
      $valor.='</tr>';
    }
    if ($valor>'')
      $valor="<table class=calendarioDescricao>$valor</table>";

    return $valor;
  }
?>
