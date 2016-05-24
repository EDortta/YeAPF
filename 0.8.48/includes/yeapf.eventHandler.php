<?php
/*
    includes/yeapf.eventHandler.php
    YeAPF 0.8.48-103 built on 2016-05-24 18:54 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-01-23 22:00:40 (-3 DST)
*/

  _recordWastedTime("Gotcha! ".$dbgErrorCount++);
  $__eventHandler = array();

  function addEventHandler($eh)
  {
    global $__eventHandler;

    if (!in_array($eh,$__eventHandler))
      array_push($__eventHandler,$eh);
  }

  function doEventHandler($aS='', $aA='')
  {
    global $lastImplementation, $__eventHandler, $s, $a;

    $cEvents=0;
    $auxS=($aS > '')? $aS: $s;
    $auxA=($aA > '')? $aA: $a;
    if (isset($__eventHandler)) {
      foreach($__eventHandler as $eh) {
        _dumpY(1,1,"calling event handler '$eh'");
        if (function_exists($eh)) {
          _record($lastImplementation,"eventHandler::$eh($auxS,$auxA)");
          $eh($auxS,$auxA);
          $cEvents++;
        }
      }
    }

    return $cEvents;
  }


?>
