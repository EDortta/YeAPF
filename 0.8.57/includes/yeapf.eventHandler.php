<?php
/*
    includes/yeapf.eventHandler.php
    YeAPF 0.8.57-1 built on 2017-05-12 19:12 (-3 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2017-03-24 09:55:58 (-3 DST)
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
    global $lastImplementation, $__eventHandler, $s, $a, $flgCanContinueWorking;

    $cEvents=0;
    $auxS=($aS > '')? $aS: $s;
    $auxA=($aA > '')? $aA: $a;
    if (isset($__eventHandler)) {
      foreach($__eventHandler as $eh) {
        if (($flgCanContinueWorking) || ($auxS=='yeapf')) {
          if (function_exists($eh)) {
            _dumpY(1,1,"calling event handler '$eh'");
            _record($lastImplementation,"eventHandler::$eh($auxS,$auxA)");
            // $ret=$eh($auxS,$auxA);

            _recordWastedTime("Preparing to call $eh($auxS, $auxA)");
            $__impt0=decimalMicrotime();
            //$ret=call_user_func($eh, $auxS, $auxA);
            $ret=$eh($auxS, $auxA);
            _dumpY(1,2,"$eh('$auxS', '$auxA') returns $ret");
            $__impt0=decimalMicrotime()-$__impt0;
            _recordWastedTime("Time wasted calling $eh($a): $__impt0");
            if ((intval($ret)&2)==2) {
              $flgCanContinueWorking=false;
              _dump("flgCanContinueWorking has been dropped by '$auxS'.'$auxA' event handler ($eh)");
            }


            $cEvents++;
          }
        }
      }
    }

    return $cEvents;
  }


?>
