<?php
/*
    skel/webApp/develop_msg.php
    YeAPF 0.8.62-18 built on 2019-04-04 23:38 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
    2018-05-30 11:21:05 (-3 DST)

    skel/webApp / develop_msg.php
    This file cannot be modified within skel/webApp
    folder, but it can be copied and changed outside it.

    develop_msg.php implements a server sent events in order
    to be consumed by develop frame
    The required files are under YeAPF tree and not under the application tree.
    All theses files are under YeAPF/develop/ branch.
*/

  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
  header("Content-Type: text/event-stream\n\n");

  function say($eventName, $data) {
    global $devSession;

    $JSONData=json_encode($data);
    _dumpY(256,0,"$devSession - event: $eventName - data: $JSONData");
    echo "event: $eventName\ndata: $JSONData\n\n";
  }


  (@include_once "yeapf.php") or die("yeapf not configured");
  $developBase=$yeapfConfig['yeapfPath']."/../develop";
  (@include_once "$developBase/yeapf.develop.php") or die ("Error loading 'yeapf.develop.php'");

  $devMsgQueue=new xDevelopMSG($devSession, file_exists('flags/flag.nosharedmem'));

  $tick=0;
  while (1) {
    $messages=$devMsgQueue->getMessages();
    if (count($messages)>0) {
      foreach($messages as $msg) {
        $tick=date('U');
        say('developMSG',array('tick'=>$tick, 'msg'=>$msg));
      }
    }

    $now=date('U');
    if ($now-$tick>5) {
      say('ping', array('tick' => $now));
      $tick=$now;
    }


    ob_flush();
    flush();
    usleep(1000000);
  }
?>
