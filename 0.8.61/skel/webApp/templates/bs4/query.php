<?php
/*
    skel/webApp/templates/bs4/query.php
    YeAPF 0.8.61-153 built on 2018-11-30 07:00 (-2 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com 
    2018-11-30 07:00:56 (-2 DST)

    skel/webApp / query.php
    This file cannot be modified within skel/webApp
    folder, but it can be copied and changed outside it.
*/


  header('Content-Type: application/xml; charset=UTF-8', true);
  header("Cache-Control: no-cache, must-revalidate");
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
  // logOutput = -1: Arquivo, 0: silencio, 1: tela, 2: xml
  $logOutput=2; /* XML */

  $dbConnect='no';
  (@include_once "yeapf.php") or die("<?xml version='1.0' encoding='ISO-8859-1'?>\n<root><error>yeapf not configured</error><sgug><timestamp>0</timestamp><devSession>null</devSession></sgug></root>");
  if ($s!='yeapf:develop') {
    $dbConnect=(file_exists("flags/flag.nodb"))?'no':'yes';
    db_startup();
  }

  /* @OBSOLETE 20170111
  $developBase=$yeapfConfig['yeapfPath']."/../develop";
  (@include_once "$developBase/yeapf.develop.php") or die ("Error loading 'yeapf.develop.php'");
  */

  _dumpY(1,1,"appFolderRights=$appFolderRights");

  $callBackFunction = isset($callBackFunction)?$callBackFunction:'';

  $logging=1;
  if ($logging>0) {
    $id=isset($id)?$id:'';
    $targetUser=isset($targetUser)?$targetUser:'';
    $message=isset($message)?$message:'';
    $wParam=isset($wParam)?$wParam:'';
    $lParam=isset($lParam)?$lParam:'';
    $broadcastCondition=isset($broadcastCondition)?$broadcastCondition:'';
    if (basename(getenv('SCRIPT_NAME'))=='query.php')
      _dumpY(1,1,basename(getenv('SCRIPT_NAME'))." $ts;s=$s;a=$a;id=$id&sqlID=$sqlID;targetUser=$targetUser;message=$message;wParam=$wParam;lParam=$lParam;$callBackFunction();$broadcastCondition");
    else
      _dumpY(1,1,basename(getenv('SCRIPT_NAME'))." $ts;s=$s;a=$a;id=$id&sqlID=$sqlID;$fieldName=$fieldValue;$callBackFunction();$broadcastCondition");
  }

  /*
   * Deve existir uma função que receberá os parámetros ($aSourceUserId, $aMessage, $aWParam, $aLParam)
   * Retornará um vetór associativo e processará uma mensagem por vez
   * O resultado será enviado de forma ordenada para o cliente
   * Registre sua função de serviço de mensagens com 'addMessageHandler()'
   */

  $xq_return='';
  $xq_regCount=0;
  $xq_requestedRows=20;
  $userMsg='';

  yeapfStage("beforeAuthentication");

  $userContext=new xUserContext($u, true);
  $userContext->setTimeTraking(false);
  $userContext->isValidUser();
  yeapfStage("afterAuthentication");
  $userContext->loadUserVars();
  yeapfStage("beforeImplementation");
  $__impt0=decimalMicrotime();
  implementation($s, $a, 'q');
  $__impt1=decimalMicrotime();
  $__impT=$__impt1-$__impt0;
  _recordWastedTime("Time wasted in user implementation of $s.$a: $__impT ($__impt1 - $__impt0)");
  yeapfStage("afterImplementation");

  if ($logging>1)
    _dumpY(1,1,"xq_regCount=$xq_regCount");

  $xmlData=xq_produceContext($callBackFunction,$xq_return,$xq_regCount);

  if (!file_exists("e_body.xml"))
    _dumpY(0,0,"FATAL ERROR: 'e_body.xml' could not be found");
  else
    $xResult=_file("e_body.xml");

  // dbCharset - database charset
  // appCharset - application charset
  // $xResult=mb_convert_encoding($xResult,"ISO-8859-1",mb_detect_encoding($xResult));
  $xResult=iconv(detect_encoding($xResult),"UTF-8", $xResult);
  if ($logging>2)
    _dumpY(1,1,$xResult);

  echo html_entity_decode("$xResult", ENT_NOQUOTES, "UTF-8");

  registerAPIUsageFinish();
  db_close();
  _recordWastedTime("Good bye query");
?>
