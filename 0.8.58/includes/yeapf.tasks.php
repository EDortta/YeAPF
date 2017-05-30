<?php
/*
    includes/yeapf.tasks.php
    YeAPF 0.8.58-13 built on 2017-05-30 11:50 (-3 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2017-05-15 17:40:10 (-3 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  class YTaskManager
  {
    private $initialized  = false;
    private $task_id      = -1;
    private $taskFlagName = "yTaskManager";

    private function setTaskId($taskId)
    {
      $this->initialized=true;
      $this->task_id=$taskId;
    }

    /* Get the current task id or -1 */
    public function getTaskId()
    {
      return $this->initialized?$this->task_id:-1;
    }

    /* Create a new task entry in the task manager */
    public function createNewTask($s, $a, $j_params='', $iteraction_ttl=240, $priority=2)
    {
      $retTaskId=-1;
      $priority=min(max(1,intval($priority)), 4);
      $iteraction_ttl=max(0, intval($iteraction_ttl));
      if (lock($this->taskFlagName)) {
        try {
          $j_params=escapeString($j_params);
          $sqlTask="select max(id) from is_tasks where s='$s' and a='$a' and stage<4 and j_params='$j_params'";
          $retTaskId=intval(db_sql($sqlTask));
          if ($retTaskId<=0) {
            $ts=date('U');
            if (lock("newTaskId")) {
              $newId=intval(db_sql("select max(id) from is_tasks"));
              $newId++;

              /* all tasks starts with stage=0 (disabled) */
              $sql="insert into is_tasks(id, stage, s, a, j_params,
                                         xq_start, xq_target,
                                         mru, priority,
                                         creation_ts, iteraction_ttl)
                    values ($newId, 0, '$s', '$a', '$j_params', 0, -1, 0, $priority, $ts, $iteraction_ttl)";
              db_sql($sql);
              unlock("newTaskId");
            }
            $retTaskId=db_sql($sqlTask);
          }
          $this->setTaskId($retTaskId);
        } catch (Exception $e) {
          _dump("Error trying to create a new task: ".$e->getMessage());
        }
        unlock($this->taskFlagName);
      }
      return $retTaskId;
    }

    /* Get next idle task from task manager */
    public function getNextIdleTask()
    {
      $sql="select id from is_tasks where stage=2 order by mru desc";
      $retTaskId=intval(db_sql($sql));
      $retTaskId=$retTaskId>0?$retTaskId:-1;
      if ($retTaskId>0) {
        db_sql("update is_tasks set mru=mru+1 where id=$retTaskId");
      }
      $this->setTaskId($retTaskId);
      return $retTaskId;
    }

    private function setTaskCanRun($enable)
    {
      global $cfgMainFolder;
      $ret=false;
      if ($this->initialized) {
        $taskId=$this->getTaskId();
        $runFlag="$cfgMainFolder/flags/task-".$taskId.".run";
        if (!is_dir("$cfgMainFolder/flags"))
          mkdir("$cfgMainFolder/flags", 0777);
        if ($enable)
          $ret=touch($runFlag);
        else {
          if (file_exists($runFlag))
            $ret=unlink($runFlag);
          else
            $ret=true;
        }
      }
      return $ret;
    }

    public function taskCanRun($deep=true)
    {
      global $cfgMainFolder;
      $ret=false;
      if ($this->initialized) {
        $taskId=$this->getTaskId();
        $runFlag="$cfgMainFolder/flags/task-".$taskId.".run";
        $ret=file_exists($runFlag);
        if ($ret && $deep) {
          $task_info=db_sql("select coalesce(iteraction_ts,creation_ts) as i_ts, iteraction_ttl from is_tasks where id=$taskId",false);
          extract($task_info);
          if ($iteraction_ttl>0) {
            $flagStat=stat($runFlag);
            $mtime=$flagStat['mtime'];
            $now=date('U');
            echo "iteraction_ttl: $iteraction_ttl seg. limite: ";
            echo $mtime+$iteraction_ttl;
            echo ".. agora:".$now."\n";
            $ret=($mtime+$iteraction_ttl>$now);
          }
        }
      }
      return $ret;
    }

    private function setTaskStage($newStage, $minStage, $maxStage, $canRun)
    {
      $ret=false;
      if ($this->initialized) {
        $taskId=$this->getTaskId();
        // echo "Seting stage $newStage for task #$taskId\n";
        $currentStage=db_sql("select stage from is_tasks where id=$taskId");
        /* only can be pauses tasks in stage $minStage..$maxStage */
        if (($currentStage>=$minStage) && ($currentStage<=$maxStage)) {
          if ($this->setTaskCanRun($canRun)) {
            $now=date('U');
            db_sql("update is_tasks set stage=$newStage, iteraction_ts=$now where id=$taskId");
            $ret=true;
          }
        }
      }
      return $ret;
    }

    /* set task's stage at 0 */
    public function disableTask()
    {
      return $this->setTaskStage(0, 1, 3, false);
    }

    /* set task's stage at 1 */
    public function pauseTask()
    {
      return $this->setTaskStage(1, 2, 3, false);
    }

    /* set task's stage at 2 */
    public function enableTask()
    {
      return $this->setTaskStage(2, 0, 3, false);
    }

    /* set task's stage at 3 */
    public function playTask()
    {
      return $this->setTaskStage(3, 1, 2, true);
    }

    private function _endTask($stage)
    {
      $ret=false;
      if ($this->initialized) {
        $taskId=$this->getTaskId();
        $currentStage=db_sql("select stage from is_tasks where id=$taskId");
        if ($currentStage<4) {
          $this->setTaskCanRun(false);
          $ts=date('U');
          db_sql("update is_tasks set stage=$stage, finalization_ts=$ts where id=$taskId and stage<4");
          $ret=true;
        }
      }
      return $ret;
    }

    /* set task's stage at 4 */
    public function abortTask()
    {
      return $this->_endTask(4);
    }

    /* set task's stage at 5 */
    public function endTask()
    {
      return $this->_endTask(5);
    }

    public function getTaskContext($complete=false)
    {
      $retContext=array('s'=>'dummy', 'a'=>'void');
      if ($this->initialized) {
        $taskId=$this->getTaskId();
        if ($complete)
          $retContext=db_sql("select s, a, j_params, xq_start, xq_target from is_tasks where id=$taskId", false);
        else
          $retContext=db_sql("select j_params, xq_start, xq_target from is_tasks where id=$taskId", false);
        $j_params=json_decode($retContext['j_params']);
        unset($retContext['j_params']);
        if (isset($j_params)) {
          $reserved=explode(',','s,a,j_params,xq_start,xq_target');
          foreach($j_params as $k=>$v) {
            if (!in_array($k,$reserved)) {
              $retContext[$k]=$v;
            }
          }
        }
        foreach($retContext as $k=>$v){
          if (is_numeric($k))
            unset($retContext[$k]);
        }
      }
      return $retContext;
    }

    public function advanceTo($xq_start)
    {
      if ($this->initialized) {
        $taskId=$this->getTaskId();
        db_sql("update is_tasks set xq_start=$xq_start where id=$taskId");
      }
    }

  }

?>
