#!/usr/bin/php
<?php
  /*
   * tools
   * tools/ylicenseExpiration.php
   * YeAPF 0.8.54-48 built on 2017-03-03 06:26 (-3 DST)
   * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
   * 2017-03-03 06:25:16 (-3 DST)
   */

  function dataSQL($data, $hora='',$forceInternalFormat=false)
  {
    global $usingMySql;

    $data=ereg_replace("[^0-9]", "", $data);
    $hora=ereg_replace("[^0-9]", "", $hora);
    if (($usingMySql) || ($forceInternalFormat)) {
      $data=substr($data,4,4).substr($data,2,2).substr($data,0,2);
      while (strlen($data)<8)
        $data.='0';
      if (strlen($hora)>4)
        $hora=substr($hora,0,2).substr($hora,2,2).substr($hora,4,2);
      else
        $hora=substr($hora,0,2).substr($hora,2,2);
      while (strlen($hora)<6)
        $hora.='0';
      return $data.$hora;
    } else {
      $aa=substr($data,4,4);
      $am=substr($data,2,2);
      $ad=substr($data,0,2);

      $bh=substr($hora,0,2);
      $bm=substr($hora,2,2);
      $bs=substr($hora,4,2);

      $data=sprintf("%02d-%02d-%04d %02d:%02d:%02d",$am,$ad,$aa,$bh,$bm,$bs);
      // $data=sprintf("%04d-%02d-%02d %02d:%02d:%02d",$aa,$am,$ad,$bh,$bm,$bs);
      return $data;
    }
  }

  function encodeLicenseExpirationDate($UDate) {
    $ret='';
    $UDate=substr($UDate.str_repeat('0',14),0,14);
    for($i=0; $i<14; $i++) {
      $ch=ord(substr($UDate,$i,1));
      $ch = $ch ^ (7+$i);
      $ret.=str_pad($ch,2,'0');
    }
    return $ret;
  }

  function decodeLicenseExpirationDate($codedExpirationDate) {
    $ret='';
    for($i=0; $i<strlen($codedExpirationDate) / 2; $i++) {
      $ch=substr($codedExpirationDate,$i*2,2);
      $ch = $ch ^ (7+$i);
      $ret.=chr($ch);
    }
    return $ret;
  }

  if ($argc<=1) {
    echo "Exemplos:\n\tliberacao para uma data especifica: 18/06/2014 13:53\n";
    echo "\tLiberacao para daqui a mais 20 dias: +20\n";
    die();
  }

  if (substr($argv[1],0,1)=='+') {
    $auxDate=date('U');
    $incDays=intval($argv[1]);
    $auxDate+=$incDays * ( 24 * 60 * 60);
    $licenseExpiration=date("YmdHis", $auxDate);
  } else {
    $licenseExpiration=dataSQL($argv[1], $argv[2], true);
  }

  $aux1=encodeLicenseExpirationDate($licenseExpiration);
  $aux2=decodeLicenseExpirationDate($aux1);

  echo "Data desejada: \n\t$licenseExpiration\nCodigo: \n\t$aux1\nVerificacao (data desejada)\n\t$aux2\n";
  echo "x58e1d9ca63ef85abef352d3306a6fac3 = $aux1\n";
?>
