<?php
/*
    samples/webservice/ywebservice.php
    YeAPF 0.8.63-104 built on 2019-07-10 19:52 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
    2018-08-16 06:40:47 (-3 DST)

    YeAPF/samples
    webservice sample
    (C) 2008-2018 Esteban Daniel Dortta
*/

  ini_set("always_populate_raw_post_data", 0);

  function logAction($action)
  {
    global $logEnabled, $webServiceName;
    if (isset($webServiceName) && ($webServiceName>'')) {
      if ($logEnabled) {
        $f=fopen("logs/$webServiceName.log","a");
        if ($f) {
          fwrite($f, "$action\n");
          fclose($f);
        }
      }
    }
  }

  if (file_exists('flags/flag.debug')) {
    ini_set('display_errors','1');
    error_reporting (E_ALL);

    function fatal_error_handler() {

        if (@is_array($e = @error_get_last())) {
            $code = isset($e['type']) ? $e['type'] : 0;
            $msg = isset($e['message']) ? $e['message'] : '';
            $file = isset($e['file']) ? $e['file'] : '';
            $line = isset($e['line']) ? $e['line'] : '';
            if ($code>0) error_handler($code,$msg,$file,$line);
        }
    }
  } else {
    ini_set('display_errors','0');
    error_reporting (E_NONE);
  }

  $isWebservice=true;

  (@include_once "yeapf.php") or die("Err loading yeapf.php\n");
  if (file_exists("../lib/nuSOAP/nusoap.php"))
    $nusoapLib="../lib/nuSOAP/nusoap.php";
  else if (file_exists("lib/nuSOAP/nusoap.php"))
    $nusoapLib="lib/nuSOAP/nusoap.php";
  else if (file_exists("nuSOAP/nusoap.php"))
    $nusoapLib="nuSOAP/nusoap.php";
  else
    $nusoapLib = "../../lib/nuSOAP/nusoap.php";

  (@include_once $nusoapLib) or die("Err 1 loading $nusoapLib\n");

  $webServiceName="%(appName)";

  if ($webServiceName=='%('.'appName)')
    die("Please, change '\$webServiceName' variable value");

  if (strtolower(basename(__FILE__))=='y'.'webservice.php') {
    die("Please, rename this file to something more appropriate");
  }

  if (basename(__FILE__)!="$webServiceName.php") {
    $pathInfo=pathinfo(__FILE__);
    die("Please, change '\$webServiceName' variable to ".$pathInfo['filename']);
  }

  $yWebService = new nusoap_server;
  (@include_once "%(appName)_def.php") or die('');

  $logRequest=false;
  $dbConnect='no';
  $_LOG_SYS_REQUEST=false;
  $logEnabled=true;
  $logOutput=-1;


  // logAction("{$server['REMOTE_ADDR']} $action");


  $sysTimeStamp=timestamp($sysDate);

  logAction("Carregando");

  (@include_once("%(appName)_imp.php")) or die("Err loading %(appName)_imp.php");

  $HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
  if ((!$HTTP_RAW_POST_DATA) || ($HTTP_RAW_POST_DATA==''))
    $HTTP_RAW_POST_DATA = file_get_contents("php://input");

  logAction("\n\nParametros: $HTTP_RAW_POST_DATA\n");

  $dbConnect=file_exists('flag.dbConnect')?'yes':'no';
  db_startup();
  $yWebService->service($HTTP_RAW_POST_DATA);

  logAction(str_replace("<BR>","\n",$lastCommands));

  logAction("Descarregando");
?>