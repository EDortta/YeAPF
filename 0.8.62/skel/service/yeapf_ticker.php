<?php
/*
    skel/service/yeapf_ticker.php
    YeAPF 0.8.62-2 built on 2019-03-22 10:21 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
    2019-03-22 10:21:08 (-3 DST)
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