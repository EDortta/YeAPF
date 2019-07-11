<?php
/*
    skel/service/sse.php
    YeAPF 0.8.63-107 built on 2019-07-11 10:47 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    2019-07-11 10:47:52 (-3 DST)

    skel/webApp / sse.php
    This file cannot be modified within skel/webApp
    folder, but it can be copied and changed outside it.
*/

  (@include_once "yeapf.php") or die("yeapf not configured");

  // Turn off output buffering
  ini_set('output_buffering', 'off');
  ini_set('zlib.output_compression', false);

  header("Content-Type: text/event-stream\n\n", true);
  header("Cache-Control: no-cache");
  header("Connection: Keep-Alive");
  header("Access-Control-Allow-Origin: *");
  header('X-Accel-Buffering: no');

  date_default_timezone_set("America/Sao_Paulo");

  _dumpY(8,0,"SSE - conn - stage 1");

  SSE::garbageCollect();

  $sse_dispatch = function($eventName, $eventData) {
    SSE::sendEvent($eventName, $eventData);
  };

  $sse_session_id=SSE::getSessionId(isset($si)?$si:"");
  _dumpY(8,0,"SSE - session_id = $sse_session_id");
  _dumpY(8,1,"SSE - cfgSSECloseConnectionTimeout = $cfgSSECloseConnectionTimeout");
  _dumpY(8,1,"SSE - cfgSSEUserAliveInterleave    = $cfgSSEUserAliveInterleave");
  _dumpY(8,1,"SSE - cfgSSEGarbageCollectTTL      = $cfgSSEGarbageCollectTTL");

  /* @params
     si - md5(sse_session_id)

     w and u comes from the client
     The folder .sse/$w and the file .sse/$w/$u/.user must exists
     in order to continue.
     That means that the client firstly connect to the application
     through index/body/query/rest -> yeapf.sse.php -> SSE:grantUserFolder()
  */

  if ($sse_session_id>"") {
    echo "retry: 15000\ndata: welcome $sse_session_id\n\n";
    for($n=10; $n>0; $n--) echo "\n\n";
    @ob_flush();
    flush();

    /* this message will help SSE client to recognizes as valid SSE connection */
    $sse_dispatch("message", "connected");

    _dumpY(8,0,"SSE - conn - stage 2");

    set_time_limit(0);

    _dumpY(8,0,"SSE - conn - stage 3");
    /* exposes $w and $u as global variables */
    $sessionInfo = SSE::getSessionInfo($sse_session_id);
    extract($sessionInfo);

    SSE::broadcastMessage('userConnected', array('u'=>$u), $w, $u);

    _dumpY(8,0,"SSE - conn - stage 4");
    /* run the loop while this session is enabled */
    while (SSE::enabled($sse_session_id, $w, $u)) {
      _dumpY(8,0,"$sse_session_id QUEUE");
      /* process the message queue */
      SSE::processQueue($sse_dispatch);

      /* keep alive is controled by yeapf.sse.php
         usually it send a dummy packet each 30th second after no work */
      SSE::keepAlive();

      /* sleep half of a second */
      usleep(500000);

      $cTime=date("U");
    }
    _dumpY(8,0,"SSE - conn - stage 5");
    /*
    if (SSE::userAttached($w,$u)) {
      SSE::detachUser($w, $u);
    }
    */
  }

  SSE::sendEvent("finish");
  while (@ob_end_flush());

  _dumpY(8,0,"SSE - finish");

?>