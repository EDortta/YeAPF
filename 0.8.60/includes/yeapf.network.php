<?php
/*
    includes/yeapf.network.php
    YeAPF 0.8.60-119 built on 2018-06-08 05:44 (-3 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2018-05-30 11:21:05 (-3 DST)
*/
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
