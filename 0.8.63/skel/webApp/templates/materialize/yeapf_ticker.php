<?php
/*
    skel/webApp/templates/materialize/yeapf_ticker.php
    YeAPF 0.8.63-242 built on 2019-11-29 09:22 (-2 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    2019-11-29 09:22:24 (-2 DST)
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