<?php
/*
    skel/webApp/query.php
    YeAPF 0.8.49-100 built on 2016-07-28 17:26 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-01-23 22:00:41 (-3 DST)
*/
/*
* Sistema de mensagens entre processos do YeAPF
*/


  # header("Content-Type:text/xml;  charset=ISO-8859-1",true);
  header('Content-Type: application/xml; charset=ISO-8859-1', true);
  header("Cache-Control: no-cache, must-revalidate");
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

  $dbConnect='no';
  (@include_once "yeapf.php") or die("<?xml version='1.0' encoding='ISO-8859-1'?>\n<root><error>yeapf not configured</error><sgug><timestamp>0</timestamp><devSession>null</devSession></sgug></root>");
  if ($s!='yeapf:develop') {
    $dbConnect=(file_exists("flags/flag.nodb"))?'no':'yes';
    db_startup();
  }


  $developBase=$yeapfConfig['yeapfPath']."/../develop";
  (@include_once "$developBase/yeapf.develop.php") or die ("Error loading 'yeapf.develop.php'");

  $logOutput=2;
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
   * Deve existir uma fun��o que receber� os par�metros ($aSourceUserId, $aMessage, $aWParam, $aLParam)
   * Retornar� um vet�r associativo e processar� uma mensagem por vez
   * O resultado ser� enviado de forma ordenada para o cliente
   * Registre sua fun��o de servi�o de mensagens com 'addMessageHandler()'
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
  $xResult=iconv(detect_encoding($xResult),"ISO-8859-1", $xResult);
  if ($logging>2)
    _dumpY(1,1,$xResult);

  echo html_entity_decode("$xResult", ENT_NOQUOTES, $appCharset);

  db_close();
  _recordWastedTime("Good bye query");
?>
