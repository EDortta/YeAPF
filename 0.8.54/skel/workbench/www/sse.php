<?php
/*
    skel/workbench/www/sse.php
    YeAPF 0.8.54-9 built on 2017-01-31 17:11 (-2 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2017-01-31 17:11:37 (-2 DST)

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

  echo "retry: 5000\n\n";
  for($n=1000; $n>0; $n--) echo "\n\n";
  while (@ob_end_flush());

  /* this message will help SSE client to recognizes as valid SSE connection */
  SSE::sendEvent("message", "welcome");

  _dumpY(8,0,"SSE - conn");

  set_time_limit(0);

  /* @params
     si - md5(sse_session_id)

     w and u comes from the client
     The folder .sse/$w and the file .sse/$w/$u/.user must exists
     in order to continue.
     That means that the client firstly connect to the application
     through index/body/query/rest -> yeapf.sse.php -> SSE:grantUserFolder()
  */

  $sse_dispatch = function($eventName, $eventData) {
    SSE::sendEvent($eventName, $eventData);
  };

  $sse_session_id=SSE::getSessionId(isset($si)?$si:"");
  if ($sse_session_id>"") {
    /* exposes $w and $u as global variables */
    $sessionInfo = SSE::getSessionInfo($sse_session_id);
    extract($sessionInfo);

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
  }

  SSE::sendEvent("close");

  _dumpY(8,0,"SSE - finish");

?>