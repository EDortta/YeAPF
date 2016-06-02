<?php
/*
    skel/webApp/task.php
    YeAPF 0.8.49-4 built on 2016-06-02 09:37 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-06-02 09:19:30 (-3 DST)
*/

  require_once "yeapf.php";

  if ($s=='yeapf') {
    switch($a) {
      case 'tick':
        $ytasker=new YTaskManager();
        $taskId=$ytasker->getNextIdleTask();
        if ($taskId>0) {
          $taskContext=$ytasker->getTaskContext(true);
          if ($taskContext['s'].'.'.$taskContext['a']!='yeapf.tick') {
            implementation($taskContext['s'], $taskContext['a'], 't');
          }
        }
        break;
    }
  }
?>