<?php
/*
    skel/webApp/sse.php
    YeAPF 0.8.53-1 built on 2017-01-09 08:40 (-2 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2017-01-09 08:38:53 (-2 DST)

    skel/webApp / sse.php
    This file cannot be modified within skel/webApp
    folder, but it can be copied and changed outside it.
*/
  header("Content-Type: text/event-stream\n\n");
  (@include_once "yeapf.php") or die("yeapf not configured");

  while (1) {
    // Every second, sent a "ping" event.

    if (file_exists("coifa.txt")) {
      $valor=file_get_contents("coifa.txt");
    } else
      $valor=0;
    $valor++;

    echo "event: ping\n";
    $curDate = date(DATE_ISO8601);
    $pongData = array(
      "msg"  => "normal",
      "time" => $curDate,
      "valor"=> $valor,
      "count" => $count
    );

    file_put_contents("coifa.txt", $valor);

    echo 'data: '.json_encode($pongData);
    echo "\n\n";

    // Send a simple message at random intervals.

    $count--;

    if (!$count) {
      $pongData = array(
        "msg"  => "abnormal",
        "time" => $curDate,
        "count" => $count
      );

      ob_end_flush();
      flush();

      echo "event: ping\ndata: ".json_encode($pongData);
      echo "\n\n";

      $count = rand(1, 10);
    }

    ob_end_flush();
    flush();
    sleep(1);
  }

?>