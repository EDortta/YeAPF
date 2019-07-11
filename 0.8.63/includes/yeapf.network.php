<?php
/*
    includes/yeapf.network.php
    YeAPF 0.8.63-106 built on 2019-07-11 09:42 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    2019-05-24 09:36:01 (-3 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  function xinet_pton($inet)
  {
    $r='';
    $v=getNextValue($inet,'.');
    while ($v>'') {
      $r.=chr(intval($v));
      $v=getNextValue($inet,'.');
    }
    return $r;
  }

  function xip2int($hostIP)
  {
    $n=16581375;
    $ip=0;
    for ($i=0; $i<4; $i++) {
      $v=seguinteValor($hostIP,'.');
      $vn=$v*$n;
      $ip+=$vn;
      $n=$n/255;
    }
    return $ip;
  }

  function xint2ip($intIP)
  {
    $n=16581375;
    $res='';
    while ($n>=1) {
      $v=floor($intIP / $n);
      $intIP=$intIP-$v*$n;
      if ($res>'')
        $res.='.';
      $res.=intval($v);
      $n=$n/255;

    }
    return $res;
  }

  //========================================================================
?>
