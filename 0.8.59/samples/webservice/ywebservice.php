<?php
  /*****************************************************************
   * webservice sample
   *  (C) 2008-2017 Esteban Daniel Dortta
   *  dortta@yahoo.com
   *****************************************************************/
  if (file_exists('flag.debug')) {
    ini_set('display_errors','1');
    error_reporting (E_ALL);
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
  
  $webServiceName="%(webServiceName)";
  
  if ($webServiceName=='%('.'webServiceName)')
    die("Please, change '\$webServiceName' variable value");
    
  if (strtolower(basename(__FILE__))=='ywebservice.php') 
    die("Please, rename this file to something more appropriate");
    
  if (basename(__FILE__)!="$webServiceName.php") {
	$pathInfo=pathinfo(__FILE__);
    die("Please, change '\$webServiceName' variable to ".$pathInfo['filename']);
  }

  $yWebService = new nusoap_server;
  (@include_once "ywebservice_def.php") or die('');

  $logRequest=false;
  $dbConnect='no';
  $_LOG_SYS_REQUEST=false;
  $logEnabled=true;
  $logOutput=-1;

  function logAction($action)
  {
    global $logEnabled, $webServiceName;
    if ($logEnabled) {
      $f=fopen("logs/webServiceName.log","a");
      if ($f) {
        fwrite($f, "$action\n");
        fclose($f);
      }
    }
  }
  
  // logAction("{$server['REMOTE_ADDR']} $action");


  $sysTimeStamp=timestamp($sysDate);

  logAction("Carregando");

  (@include_once("ywebservice_imp.php")) or die("Err loading ywebservice_imp.php");

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
