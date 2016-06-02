<?php
/*
    skel/webApp/tasks.php
    YeAPF 0.8.49-6 built on 2016-06-02 11:41 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-06-02 10:28:54 (-3 DST)
*/

  require_once "yeapf.php";

  /* you need to create a CROND entry calling this script as in the next example:
      http://example.com/tasks.php?s=yeapf&a=tick
  */
  if ($s=='yeapf') {
    switch($a) {
      case 'tick':
        $ytasker=new YTaskManager();
        $taskId=$ytasker->getNextIdleTask();
        if ($taskId>0) {
          $taskContext=$ytasker->getTaskContext(true);
          if ($taskContext['s'].'.'.$taskContext['a']!='yeapf.tick') {
            try {
              /* if the task can be played, play it */
              if ($ytasker->playTask())
                implementation($taskContext['s'], $taskContext['a'], 't');
            } catch (Exception $e) {

            }
            /* if the task will survive, enable it for the next pool */
            if ($ytasker->taskCanRun())
              $ytasker->enableTask();
          }
        }
        break;
    }
  }

?>