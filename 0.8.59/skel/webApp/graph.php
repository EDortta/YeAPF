<?php
/*
    skel/webApp/graph.php
    YeAPF 0.8.59-128 built on 2017-12-22 07:10 (-2 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2017-08-28 19:44:55 (-2 DST)

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
