<?php
/*
    skel/webApp/body.php
    YeAPF 0.8.60-83 built on 2018-06-01 09:33 (-3 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2018-06-01 09:31:47 (-3 DST)

    skel/webApp / body.php
    This file cannot be modified within skel/webApp
    folder, but it can be copied and changed outside it.
*/

  header('Content-type: text/html', true);
  header("Cache-Control: no-cache, must-revalidate");
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
  // logOutput = -1: Arquivo, 0: silencio, 1: tela, 2: xml
  $logOutput=-1;

  if (file_exists('flags/flag.dbgphp')) {
    ini_set('display_errors','1');
    error_reporting (E_ALL);
  }

  (@include_once("yeapf.php")) or die("yeapf not configured");

  /* @OBSOLETE 20170111
  $developBase=$yeapfConfig['yeapfPath']."/../develop";
  (@include_once "$developBase/yeapf.develop.php") or die ("Error loading 'yeapf.develop.php'");
  */


  if (file_exists('flags/flag.pausedAfterClick')) {
    _dump("-----------------------------");
    _dump("webApp paused");
    die();
  }

  try {
    if (!isset($withoutHeader))
      $withoutHeader=(isset($e)?($e==1):false);

    if (!isset($messagePeekerInterval))
      $messagePeekerInterval=60000;

    yeapfStage("beforeAuthentication");

    $userContext=new xUserContext($u);
    if ($userContext->isValidUser($appFolderRights)) {

      $userContext->loadUserVars('devSession');
      /* @OBSOLETE 20170111
      if (isset($devSession)) {
        _dumpY(256,0,"my devSession is '$devSession'");
        $devMsgQueue=new xDevelopMSG($devSession, file_exists('flags/flag.nosharedmem'));
        $devMsgQueue->sendStagedMessage('busy');
      }
      */

      if ($s=='')
        $s=isset($cfgInitialVerb)?$cfgInitialVerb:'';


      yeapfStage("afterAuthentication");

      if ($userContext->menuFunctions()) {
        if ((isset($m)) && ($m>0))
          $userContext->addUserVars('m');

        $__scriptName=getAppScript();
        if ($__scriptName>'') {
          _dumpY(1,1,"Loading appBody ($__scriptName)");
          (@include_once("$__scriptName")) or die("'$__scriptName' cannot be loaded");

        }
        unset($__scriptName);

        yeapfStage("beforeImplementation");
        $yImplementedAction=implementation($s, $a);
        yeapfStage("afterImplementation");
      }

      $withoutHeader=intval($withoutHeader);
      $withoutBody=intval($withoutBody);

      yeapfStage("beforeOutput");

      if (!$withoutHeader) {
        initOutput();

        /* FOR BACKWARD COMPATIBILITY
        echo "\n<script>";
        processFile("xYApp.js");
        echo "</script>";
        */

        if ((!isset($aBody)) || ($aBody=='')) {

          if ($s=='')
            $s="bemvindo_$appName";

          if (isset($prn) && $prn==1)
            $aBody="i_$s"."_prn.html";
          else {
            $aBody=bestName("i_$s");
            /*
            $aBody="i_$s.form";
            if (!file_exists($aBody))
              $aBody="i_$s.html";
            */
          }

        }

        if ($aBody=='')
          _dump("No implementation file was found for '$s' subject");
        else
          _dump("'$s' implemented as '$aBody' file");

        /*
        $cacheFlushTimeout=-1;
        if (!file_exists("$aBody.form")) {
          $aBody=getCachedVersion(bestName($aBody), $u, $s, $a);
          _dumpY(1,1,"CACHED VERSION: $aBody");
        }
        */
        echo "\n<!-- container for aBody: $aBody e_body.html start -->\n";
        processFile("e_body.html");
        echo "\n<!-- container for aBody: $aBody e_body.html finish -->\n";
      } else if ($withoutBody)
        echo $aBody;
      else {
        processFile("frame_header.html");
        echo "\n<!-- aBody: $aBody start -->\n";
        processFile("$aBody");
        echo "\n<!-- aBody: $aBody finish -->\n";
        processFile("frame_footer.html");
      }
      /* @OBSOLETE 20170111
      if (isset($devMsgQueue))
        $devMsgQueue->sendStagedMessage('aBody',$aBody);
      */

      yeapfStage("afterOutput");

      if ((isset($prn)) && ($prn==1))
        $footer=$sysDate;
      else if (!$withoutBody)
        $footer=$aBody;

      $userContext->menuFooter();

      error_log("s=$s | a=$a | u=$u | aBody=$aBody | appScript=null\n",3,sys_get_temp_dir()."/yeapf.log");
    } else {
      _dumpY(1,1,"s=$s cacheID=$cacheID (NOT VALID USER!)");
      if (($s=='getCache') && ($cacheID>'')) {
        _dumpY(1,1,"processing printing cache");
        processFile("cached/$cacheID.html");
      } else {
        yeapfStage("afterWrongAuthentication");
        $auxInsecureS=md5("$s.");
        _recordError("Security issue. [ $s ($auxInsecureS) ]  not defined in insecure events");
        _recordError("$appFolderInsecureEvents");
        processFile("f_logoff.html");
      }
    }

    if ($yeapfPauseAfterClickFlag)
      touch('flags/flag.pausedAfterClick');

    finishOutput();
    registerAPIUsageFinish();
    db_close();
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

  _recordWastedTime("Good bye");
?>

