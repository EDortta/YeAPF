<?php
/*
    skel/webApp/graph.php
    YeAPF 0.8.49-10 built on 2016-06-03 13:09 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2014-10-23 14:35:55 (-3 DST)

    skel/webApp / graph.php
    This file cannot be modified within skel/webApp
    folder, but it can be copied and changed outside it.
*/

  if (file_exists('flags/flag.dbgphp')) {
    ini_set('display_errors','1');
    error_reporting (E_ALL);
  }

  (@include_once "yeapf.php") or die("yeapf not configured");

  if (file_exists('flags/flag.pausedAfterClick')) {
    _dumpY(1,1,"webApp paused");
    die();
  }

  yeapfStage("beforeAuthentication");

  $userContext=new xUserContext($u);
  if ($userContext->isValidUser($appFolderRights)) {
    yeapfStage("afterAuthentication");

    $__scriptName=getAppScript();
    if ($__scriptName>'') {
      _dumpY(1,1,"Loading appBody ($__scriptName)");
      (@include_once "$__scriptName") or die("Error loading $__scriptName");
    }
    unset($__scriptName);

    yeapfStage("beforeImplementation");
    $yImplementedAction=implementation($s, $a, 'g');
    yeapfStage("afterImplementation");

  } else {

    if (($s=='getCache') && ($cacheID>'')) {
      _dumpY(1,1,"processing graphic cache");
      processFile("cached/$cacheID");
    } else {
      yeapfStage("afterWrongAuthentication");
      _dumpY(1,1,"s=$s cacheID=$cacheID (NOT VALID USER!)");
    }

  }

  if ($yeapfPauseAfterClickFlag)
    touch('flags/flag.pausedAfterClick');

?>
