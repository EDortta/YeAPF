<?php
/*
    samples/key-admin/rest.php
    YeAPF 0.8.63-110 built on 2019-07-11 14:44 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    2019-07-11 14:44:49 (-3 DST)

    skel/webApp / rest.php
    This file cannot be modified within skel/webApp
    folder, but it can be copied and changed outside it.
*/

  header('Content-Type: text/javascript; charset=UTF-8',true);
  header("Access-Control-Allow-Origin: *");
  header("Connection: Keep-alive");


  (@include_once "yeapf.php") or die("yeapf not configured");
  /* @OBSOLETE 20170111
  $developBase=$yeapfConfig['yeapfPath']."/../develop";
  (@include_once "$developBase/yeapf.develop.php") or die ("Error loading 'yeapf.develop.php'");
  */


  try {
    yeapfStage("beforeAuthentication");

    $userContext=new xUserContext(isset($u)?$u:md5('0'));
    if ($userContext->isValidUser($appFolderRights)) {

      $userContext->loadUserVars('devSession');
      /* @OBSOLETE 20170111
      if (isset($devSession)) {
        _dumpY(256,0,"my devSession is '$devSession'");
        $devMsgQueue=new xDevelopMSG($devSession, file_exists('flags/flag.nosharedmem'));
        $devMsgQueue->sendStagedMessage('busy');
      }
      */

      yeapfStage("beforeImplementation");
      yeapfStage("beforeOutput");
      implementation($s, $a, 'r');
      yeapfStage("afterOutput");
      yeapfStage("afterImplementation");
    } else
      yeapfStage("afterWrongAuthentication");

  } catch(Exception $e) {
    _dump("EXCEPTION: ".$e->getMessage());
    /* @OBSOLETE 20170111
    if ($devMsgQueue)
      $devMsgQueue->sendStagedMessage('exception',$e->getMessage());
    */
  }
  /* @OBSOLETE 20170111
  if ($devMsgQueue)
    $devMsgQueue->sendStagedMessage('idle');
  */

  registerAPIUsageFinish();
  _recordWastedTime("Good bye rest");
?>
