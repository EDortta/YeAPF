<?php
/*
    includes/yeapf.locks.php
    YeAPF 0.8.53-30 built on 2017-01-12 15:16 (-2 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2016-05-30 09:45:48 (-2 DST)
*/
  if (function_exists('_recordWastedTime'))
    _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  global $LOCK_DIR, $isCLI, $LOCK_VERSION, $__GSem__, $__SMem__;

  if( !function_exists('ftok') )
  {
      function ftok($filename = "", $proj = "")
      {
          if( empty($filename) || !file_exists($filename) )
          {
              return -1;
          }
          else
          {
              $filename = $filename . (string) $proj;
              for($key = array(); sizeof($key) < strlen($filename); $key[] = ord(substr($filename, sizeof($key), 1)));
              return dechex(array_sum($key));
          }
      }
  }
  if ((isset($isCLI)) and ($isCLI))
    $LOCK_DIR=sys_get_temp_dir().'/lock';
  else {
    $LOCK_DIR=getcwd().'/lock';
    if (is_link($LOCK_DIR))
      $LOCK_DIR=readlink($LOCK_DIR);
  }

  define('LOCK_DEBUG',function_exists('_dumpY'));
  if (!is_dir($LOCK_DIR))  {
    if (is_writable(dirname($LOCK_DIR))) {
      mkdir($LOCK_DIR,0766) or die("<div>Unsustainable error <b>creating</b> system 'locks' directory '$LOCK_DIR'</div>");
      chmod($LOCK_DIR,0766) or die("<div>Unsustainable error <b>changing</b> system 'locks' directory rights'$LOCK_DIR'</div>");
    } else die("<div>Unsustainable error <b>checking</b> system 'locks' directory parent for '".dirname($LOCK_DIR)."'. It's not writeable</div>");
  }

  // by default, LOCK tries to detect if shared memory semaphores are enabled
  $LOCK_VERSION=-1;

  // programmer can chagne this behaviour via .config directory
  if (file_exists('.config/lock_version'))
    $LOCK_VERSION=join("",file('.config/lock_version'));

  /*
   * LOCK_VERSION values
   *   -1 = auto/unknown
   *    0 = on disk structure
   *    1 = shared memory semaphores
   */
  if ($LOCK_VERSION==-1) {
    if (function_exists("shm_has_var")===FALSE) {
      if (LOCK_DEBUG) _dumpY(2,0,"We're using a local implementation.");
      $LOCK_VERSION=0;
    } else
      $LOCK_VERSION=1;

    if (is_dir('.config')) {
      $auxF=fopen(".config/lock_version",'w');
      if ($auxF) {
        fwrite($auxF,intval($LOCK_VERSION));
        fclose($auxF);
        if (LOCK_DEBUG) _dumpY(2,0,"Config file writed");
      } else {
        $errMsg="Unsustainable error trying to save LOCK_VERSION config file";
        if (function_exists("_dump"))
          _dump($errMsg);
        if (function_exists("_recordError"))
          _recordError($errMsg);
      }
    }
  }


  if ($LOCK_VERSION==1) {
    $__SMemName__="$LOCK_DIR/sharedMemory.mem";
    if (LOCK_DEBUG) _dumpY(2,2,"SharedMemory file: '$__SMemName__'");
    if (!file_exists($__SMemName__))  touch($__SMemName__);

    $__SMem__=ftok("$__SMemName__",'A');
    if (LOCK_DEBUG) _dumpY(2,2,"ftok(): '$__SMem__'");

    $__GSem__=sem_get(__keyToInt__("YeAPF-SharedMemorySemaphore"));
    if (LOCK_DEBUG) _dumpY(2,2,"sem_get(): '$__GSem__'");
    if ($__GSem__===false) {
      file_put_contents(".config/lock_version", "0");
      $LOCK_VERSION=0;
    }
  }

  function _sm_open(&$sm)
  {
    global $__GSem__, $__SMem__;

    if (LOCK_DEBUG) _dumpY(2,2,"Openning shared memory handler");
    $ret=false;
    if (sem_acquire($__GSem__)) {
      $sm=shm_attach($__SMem__,2048);
      if ($sm === false) {
        if (LOCK_DEBUG) _dumpY(2,2,"Error attaching to shared memory space");
        _recordError("Error attaching to shared memory space");
        sem_release($__GSem__);
        showDebugBackTrace("sharedMemorySemaphore",true);
      } else
        $ret=true;
    } else {
      if (LOCK_DEBUG) _dumpY(2,0,"semaphore cannot be attached on '$__GSem__'");
    }

    return $ret;
  }

  function _sm_close(&$sm)
  {
    global $__GSem__;

    if (LOCK_DEBUG) _dumpY(2,2,"Closing shared memory handler");

    shm_detach($sm);
    sem_release($__GSem__);
  }

  function setGlobalLock($lockID, $blocking=false)
  {
    if (LOCK_DEBUG) _dumpY(2,2,"Setting global lock ($lockID)");

    $ret=false;
    if (_sm_open($sm)) {
      if (!shm_has_var($sm, $lockID)) {
        if (!shm_put_var($sm, $lockID, 0)) {
          if (LOCK_DEBUG) _dumpY(2,2,"Error creating shared memory variable");
          _recordError("Error creating shared memory variable");
        }
        if (LOCK_DEBUG) _dumpY(2,2,"Creating shared memory variable");
        $curLockValue=0;
      } else {
        $curLockValue=shm_get_var($sm, $lockID);
      }
      if (LOCK_DEBUG) _dumpY(2,2,"Lock value: '$curLockValue'");


      if ($curLockValue==0) {
        $curLockValue=date('U');
        if (!$ret=@shm_put_var($sm, $lockID, $curLockValue))
          $ret=false;
      } else
        $ret=false;

      if (LOCK_DEBUG) _dumpY(2,2,"Lock success: ".intval($ret));
      _sm_close($sm);
    }

    return $ret;
  }

  function touchGlobalLock($lockID)
  {
    if (LOCK_DEBUG) _dumpY(2,2,"Touching global lock ($lockID)");

    $ret=false;
    if (_sm_open($sm)) {
      if (shm_has_var($sm, $lockID)) {
        $curLockValue=date('U');
        shm_put_var($sm, $lockID, $curLockValue);
        if (LOCK_DEBUG) _dumpY(2,2,"New lock value: '$curLockValue'");
        $ret=true;
      } else
        if (LOCK_DEBUG) _dumpY(2,2,"Trying to touch inexistent lock");
      _sm_close($sm);
    }
    return $ret;
  }

  function getGlobalLockAge($lockID)
  {
    if (LOCK_DEBUG) _dumpY(2,2,"Getting global lock age ($lockID)");

    $ret=0;
    if (_sm_open($sm)) {
      if ($value=@shm_get_var($sm, $lockID)) {
        $ret=date('U') - $value;

        if (LOCK_DEBUG) _dumpY(2,2,"Global lock age: ".$ret.'secs');
      }
      _sm_close($sm);
    }

    return $ret;
  }

  function releaseGlobalLock($lockID)
  {
    if (LOCK_DEBUG) _dumpY(2,2,"Releasing global lock ($lockID)");

    $ret=false;

    if (_sm_open($sm)) {
      if (shm_has_var($sm, $lockID))
        $ret=shm_put_var($sm, $lockID, 0);
      else
        if (LOCK_DEBUG) _dumpY(2,2,"Trying to release inexistent lock");

      _sm_close($sm);
    } else
      if (LOCK_DEBUG) _dumpY(2,2,"Error releasing global lock");


    return $ret;
  }

  function __keyToInt__($key)
  {
    // these chars are the only one could be used as semaphore name
    $keys=('0123456789-+_.,@!#qwertyuiopasdfghjklzxcvbnm');

    $key=strtolower($key);

    $key=trim($key);
    $ret=0;
    $p=1;
    for($i=0; $i<strlen($key); $i++) {
      $c=substr($key,$i,1);
      $c=strpos(" $keys", $c);
      if ($c>0) {
        $x = $c*($i+1)*$p;
        // echo "$c (".$x.") <br>";
        $ret+=$x;
        $p*=2;
      }
    }
    if (LOCK_DEBUG) _dumpY(2,3,"Converting '$key' to intkey: '$ret'");
    return $ret;
  }

  function lock($lockName='userid', $abortOnLocked=false)
  {
    global $sysTimeStamp,$LOCK_DIR, $LOCK_VERSION, $isCLI;

    $ret=false;

    if ($LOCK_VERSION==1) {
      $redeemtionCount=0;
      $waitCount=0;

      $lockKey=__keyToInt__($lockName);
      $ret=true;
      while (!setGlobalLock($lockKey)) {
        // echo "*";
        $waitCount++;
        if (($waitCount>8) || ($abortOnLocked)) {
          $ret=false;
          break;
        }
        // wait half of a second
        usleep(500000);
      }

      if ($ret==false)
        redeemLock($lockName);

    } else {
      /*
       * OBSOLETO em 05/Março/2012 para permitir uso de semaforos SystemV ou sua emulação
       */
      $waitingFlag=1;
      while ((file_exists("$LOCK_DIR/$lockName.lck")) and ($waitingFlag<=8)) {
        $lastLock=stat("$LOCK_DIR/$lockName.lck");
        if ($lastLock) {
          $lastLock=$lastLock[9];
          $diff=(intval(date("U")) - $lastLock);
          // caso a trava exista há mais de 30 segundos, é provavel que quem travou o sistema
          // tenha caido, então, eliminamos a trava e esperamos um tempo prudencial
          if ($diff>30) {
            unlink("$LOCK_DIR/$lockName.lck");
            $waitingFlag=5;
            _recordError("Foi eliminada uma trava desamparada para realizar seu pedido");
          }
        }
        if ($abortOnLocked)
          break;
        sleep($waitingFlag);
        $waitingFlag = $waitingFlag * 2;
      }

      if (file_exists("$LOCK_DIR/$lockName.lck")) {
        if (!$abortOnLocked) {
          if (LOCK_DEBUG) _dumpY(2,0,"LOCK ABORTED ($LOCK_DIR/$lockName.lck)");
          _recordError("Erro ao travar o sistema de segurança.");
          _recordError("A trava '$LOCK_DIR/$lockName.lck' está aberta há mais de $diff segundos.");
          _recordError("Tente novamente e informe este erro ao pessoal de suporte.");
        }
      } else {
        if (is_writable("$LOCK_DIR")) {
          $err="<br>\nWas not possible to create a lock<br>\nInssuficient rights to write on '$LOCK_DIR/$lockName.lck'<br>\n\n";
          touch("$LOCK_DIR/$lockName.lck") or die($err);
          $ret=true;
        } else  {
          _recordError("Was not possible to create a lock");
          _recordError("Inssuficient rights to write on '$LOCK_DIR'");
        }
      }
    }
    /* */

    return $ret;
  }

  function keepLockAlive($lockName='userid')
  {
    global $LOCK_DIR, $LOCK_VERSION;

    if ($LOCK_VERSION==1) {
      $lockKey=__keyToInt__($lockName);
      touchGlobalLock($lockKey);
    } else {
      touch("$LOCK_DIR/$lockName.lck");
    }
  }

  function unlock($lockName='userid')
  {
    global $LOCK_DIR, $LOCK_VERSION;

    if ($LOCK_VERSION==1) {
      $lockKey=__keyToInt__($lockName);
      releaseGlobalLock($lockKey);
    } else {
      if (file_exists("$LOCK_DIR/$lockName.lck"))
        unlink("$LOCK_DIR/$lockName.lck");
    }
  }

  function redeemLock($lockName, $redeemtionTimeLimit=120)
  {
    global $LOCK_DIR, $LOCK_VERSION;

    if ($LOCK_VERSION==1) {
      $lockKey = __keyToInt__($lockName);
      $lockAge = getGlobalLockAge($lockKey);
      if ($lockAge>$redeemtionTimeLimit) {
        unlock($lockName);
        if (LOCK_DEBUG) _dumpY(2,0,"Lock being redeemed because it spent more than $redeemtionTimeLimit seconds");
      }

    } else {
      $lockFileName = "$LOCK_DIR/$lockName.lck";

      if (file_exists($lockFileName)) {
        $aux=stat($lockFileName);
        $aux = $aux[9];

        $now = date('U');
        if ($now - $aux <= $redeemtionTimeLimit) {
          if (LOCK_DEBUG) _dumpY(2,0,"Lock being redeemed because it spent more than $redeemtionTimeLimit seconds");
          unlock($lockName);
        }
      }
    }
  }

?>
