<?php
/*
    skel/webApp/templates/bs4-backup/yeapf_ticker.php
    YeAPF 0.8.61-105 built on 2018-10-16 08:01 (-3 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2018-08-27 06:24:01 (-3 DST)
*/

  require_once "yeapf.php";

  $ytasker=new YTaskManager();
  $taskId=$ytasker->getNextIdleTask();
  if ($taskId>0) {
    $taskContext=$ytasker->getTaskContext(true);
    if ($taskContext['s'].'.'.$taskContext['a']!='yeapf.tick') {
      try {
        /* if the task can be played, play it */
        if ($ytasker->playTask()) {
          set_time_limit(0);
          yeapfStage("beforeImplementation");
          implementation($taskContext['s'], $taskContext['a'], 't'); 
          yeapfStage("afterImplementation");         
        }
      } catch (Exception $e) {

      }
      /* if the task will survive, enable it for the next pool */
      if ($ytasker->taskCanRun())
        $ytasker->enableTask();
    }
  }
?>