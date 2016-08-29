<?php
  /*
    skel/webApp/rest.php
    YeAPF 0.8.50-10 built on 2016-08-29 09:16 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-08-23 06:47:28 (-3 DST)

    skel/webApp / rest.php
    This file cannot be modified within skel/webApp
    folder, but it can be copied and changed outside it.
   */

  header('Content-Type: text/javascript; charset=UTF-8',true);

  (@include_once "yeapf.php") or die("yeapf not configured");
  $developBase=$yeapfConfig['yeapfPath']."/../develop";
  (@include_once "$developBase/yeapf.develop.php") or die ("Error loading 'yeapf.develop.php'");


  try {
    yeapfStage("beforeAuthentication");

    $userContext=new xUserContext($u);
    if ($userContext->isValidUser($appFolderRights)) {

      $userContext->loadUserVars('devSession');
      if (isset($devSession)) {
        _dumpY(256,0,"my devSession is '$devSession'");
        $devMsgQueue=new xDevelopMSG($devSession, file_exists('flags/flag.nosharedmem'));
        $devMsgQueue->sendStagedMessage('busy');
      }

      yeapfStage("beforeOutput");
      implementation($s, $a, 'r');
      yeapfStage("afterOutput");
    } else
      yeapfStage("afterWrongAuthentication");

  } catch(Exception $e) {
    _dump("EXCEPTION: ".$e->getMessage());
    if ($devMsgQueue)
      $devMsgQueue->sendStagedMessage('exception',$e->getMessage());
  }
  if ($devMsgQueue)
    $devMsgQueue->sendStagedMessage('idle');

  _recordWastedTime("Good bye rest");
?>
