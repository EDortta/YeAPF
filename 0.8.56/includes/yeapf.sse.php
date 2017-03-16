<?php
  /*
    includes/yeapf.sse.php
    YeAPF 0.8.56-15 built on 2017-03-16 09:54 (-3 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2017-03-07 10:32:54 (-3 DST)
   */

  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  class SSE
  {
    static $eventSequence=0;
    static $__KeepAliveInterval__=30;
    static $__LastPacketTS=0;
    static $queue_folder="";
    static $__needToFlush=true;
    static $uai=-1;

    /* indicates that the SSE can be used */
  	public function enabled($sse_session_id, $w, $u, $evaluateTimestamp = true)
    {
      global $messagePeekerInterval;

      _dumpY(8,3,"SSE::enabled($sse_session_id)?");
      $ret=false;
      if (connection_status()==CONNECTION_NORMAL) {
        $sessionFile=".sse/sessions/$sse_session_id.session";
        if (file_exists($sessionFile)) {
          $w = preg_replace('/[[:^print:]]/', '', $w);
          $u = preg_replace('/[[:^print:]]/', '', $u);

          $sessionInfo=file($sessionFile);
          $w1 = preg_replace('/[[:^print:]]/', '', $sessionInfo[0]);
          $u1 = preg_replace('/[[:^print:]]/', '', $sessionInfo[1]);

          if (($w1==$w) && ($u1==$u)) {
            if (is_dir(".sse/$w")) {
              $ret=is_dir(".sse/$w/$u");
              if ($ret) {
                if ($evaluateTimestamp) {
                  clearstatcache();
                  $fT = filemtime($sessionFile);
                  $cT = date('U');
                  $difT = $cT - $fT; 
                  /* maximum idle time is eight times the messagePeekerInterval */
                  $maxT = self::getMaxUserAliveInterval() * 8;
                  $ret = ($difT<=$maxT);
                  _dumpY(8,0,"SSE difT: $difT maxT: $maxT");
                }
                if ($ret)
                  self::$queue_folder=".sse/$w/$u";
              } else {
                _dumpY(8,3,"SSE::disabled - user directory not found");
              }
            } else {
              _dumpY(8,3,"SSE::disabled - workgroup directory not found");
            }
          } else {
            _dumpY(8,3,"SSE::disabled - session info differs from requested");
          }
        } else {
          _dumpY(8,3,"SSE::disabled - session file not found");
        }
      } else {
        _dumpY(8,3,"SSE::disabled - disconnection detected");
      }
      _dumpY(8,3,"SSE::enabled = ".intval($ret));
  		return $ret;
  	}

    public function getMaxUserAliveInterval() 
    {      
      global $messagePeekerInterval;      
      /*  allow times between 750 and 12000 ms 
          BUT... as the file time stamp in UNIX are measured in seconds, 
          we translate it to seconds */
      if (self::$uai<=0) {
        self::$uai = (min(12000, max(750, isset($messagePeekerInterval)?intval($messagePeekerInterval):0)))/1000;
        _dumpY(8,0,"SSE::uai (userAliveInterval) : ".self::$uai);
      }
      return self::$uai;      
    }

    /* flush the output to the client */
    private function __flush()
    {
      if (self::$__needToFlush) {
        @ob_end_flush();
        @flush();
        self::$__LastPacketTS = date('U');
        self::$__needToFlush=false;
      }
    }

    private function __echo()
    {
      $arg_list = func_get_args();
      foreach($arg_list as $arg) {
        self::$__needToFlush=true;
        _dumpY(8,0,"SSE::__echo() $arg");
        echo $arg;
      }
    }

    /* send an event to the connected client */
    public function sendEvent($eventName, $eventData="")
    {
      _dumpY(8,3,"SSE::sendEvent('$eventName', '$eventData')");
      $evId = date('U').'.'.(self::$eventSequence++);
      self::__echo("event: $eventName\n");
      self::__echo("id: $evId\n");
      if (is_array($eventData))
        $eventData=json_encode($eventData);
      self::__echo("data: $eventData\n\n");
      self::__flush();
    }

    /* send a dummy packect when nothing has been sent in 30 seconds */
    public function keepAlive()
    {
      $t=date('U');
      if ( ($t-self::$__LastPacketTS) >= self::$__KeepAliveInterval__) {
        _dumpY(8,3,"SSE::keepAlive()");
        /* if nothing has been sent in the last n seconds, send a dummy packet */
        self::__echo(": ".$t."\n\n");
        self::__flush();
      }
    }

    public function processQueue($callback)
    {
      $u_target=basename(self::$queue_folder);
      $lockName=$u_target."-queue";

      if (lock($lockName,true)) {
        unlink($queueFlag);
        unlock($lockName);
      }

      _dumpY(8,3,"SSE::processQueue(".self::$queue_folder.")");
      $files=glob(self::$queue_folder."/*.*");
      array_multisort(
        array_map( 'filemtime', $files ),
        SORT_NUMERIC,
        SORT_ASC,
        $files
      );

      $cc=0;

      if (count($files)>0) {
        foreach ($files as $key => $messageFileName) {
          if ($cc<5) {
            $cc++;
            $ok=fnmatch("*.msg", basename($messageFileName));

            if ($ok) {
              _dumpY(8,0,"SSE::popQueue(".basename(self::$queue_folder).") - get file '".basename($messageFileName)."'");
              $f=fopen($messageFileName, "r");
              $eventName = trim(preg_replace('/[[:^print:]]/', '', fgets($f)));
              $eventData = preg_replace('/[[:^print:]]/', '', fgets($f));
              fclose($f);
              if ($eventName>'') {
                if ($eventName!='postpone_w') {
                  $callback($eventName, $eventData);
                } else {
                  $eventData = json_decode($eventData, true);
                  foreach($eventData as $k=>$v) {
                    if (($k=='s') || ($k=='a')) {
                      $$k=$v;
                    } else if ($k!='u') {
                      xq_injectValueIntoQuery($k, $v);
                    }
                  }
                  implementation($s, $a, 'w');
                }
              }
              @unlink($messageFileName);
            }
          }
        }
      }


    }

    public function dettachUser($w, $u)
    {
      _dumpY(8,1,"SSE::dettachUser('$w', '$u')");
      $ndxFile=".sse/$u.ndx";
      if (file_exists($ndxFile)) {
        $ndx = file($ndxFile);
        $w_target       = preg_replace('/[[:^print:]]/', '', $ndx[0]);
        $msg_ndx        = intval(preg_replace('/[[:^print:]]/', '', $ndx[1]))+1;
        $sse_session_id = preg_replace('/[[:^print:]]/', '', $ndx[2]);
        $si             = md5($sse_session_id);
        @unlink(".sse/sessions/$sse_session_id.session");
        @unlink(".sse/sessions/$si.md5");
      }
    }

    /* grants that the user folder exists (this function si meant to be called at login time)
       generate sse_session_id */
    public function attachUser($w, $u)
    {
      $w = preg_replace('/[[:^print:]]/', '', $w);
      $u = preg_replace('/[[:^print:]]/', '', $u);
      /* dettach other session for this pair */
      self::dettachUser($w, $u);

      _dumpY(8,1,"SSE::attachUser('$w', '$u')");
      $ret=null;
      if ($w>'') {
        if ($u>'') {
          if (!is_dir(".sse/sessions")) {
            if (!mkdir(".sse/sessions", 0777, true)) {
              _dump("SSE:: '.sse/sessions cannot be created");
            }
          }
          if (is_dir(".sse/sessions")) {
            if (is_writable(".sse/sessions")) {
              if (!is_dir(".sse/$w/$u")) {
                if (!mkdir(".sse/$w/$u", 0777, true))
                  _dump("SSE:: '.sse/$w/$u' cannot be created");
              }
              if (is_dir(".sse/$w/$u")) {
                sleep(2);

                $sse_session_id = UUID::v4();
                $si             = md5($sse_session_id);

                file_put_contents(".sse/$w/$u/.user", "$u");
                file_put_contents(".sse/$u.ndx", "$w\n1000\n$sse_session_id\n");
                file_put_contents(".sse/sessions/$sse_session_id.session", "$w\n$u\n");
                file_put_contents(".sse/sessions/$si.md5", $sse_session_id);
                $ret=$sse_session_id;

                _dumpY(8,2,"SSE::user attached: $sse_session_id ($si)");
              }
            } else
              _dump("SSE:: '.sse/sessions' is not writable");
          } else {
            _dumpY(8,0,"SSE:: folder '.sse/sessions' does not exists");
          }
        }
      }
      return $ret;
    }


    public function getSessionId($si)
    {
      _dumpY(8,0, "getSessionId($si)");
      $ret=null;
      if (file_exists(".sse/sessions/$si.md5")) {
        $ret=file_get_contents(".sse/sessions/$si.md5");
        // unlink(".sse/sessions/$si.md5");
      }
      return $ret;
    }

    public function getSessionInfo($sse_session_id)
    {
      _dumpY(8,0, "getSessionInfo($sse_session_id)");
      $ret=array();
      if (file_exists(".sse/sessions/$sse_session_id.session")) {
        $data=file(".sse/sessions/$sse_session_id.session");
        $ret["w"]=$data[0];
        $ret["u"]=$data[1];
      }
      return $ret;
    }

    public function reportUserOnline($u_target) 
    {
      $u_target = preg_replace('/[[:^print:]]/', '', $u_target);
      $ndxFile=".sse/$u_target.ndx";
      if (file_exists($ndxFile)) {
        $ndx = file($ndxFile);
        $sse_session_id = preg_replace('/[[:^print:]]/', '', $ndx[2]);
        $sessionFile=".sse/sessions/$sse_session_id.session";
        if (file_exists($sessionFile)) {
          touch($sessionFile);
        } 
      }
    } 

    /* messages being sent from a client (rest or query) to another client (sse) */
    function __enqueueMessage($u_target, $message, $data='')
    {
      global $u;

      $messageFile='';

      $u_target = preg_replace('/[[:^print:]]/', '', $u_target);
      $message  = preg_replace('/[[:^print:]]/', '', $message);
      $data     = preg_replace('/[[:^print:]]/', '', $data);

      _dumpY(8,3,"SSE::__enqueueMessage('$u_target', '$message', '$data')");
      $ndxFile=".sse/$u_target.ndx";
      if (file_exists($ndxFile)) {
        $ndx = file($ndxFile);
        $w_target       = preg_replace('/[[:^print:]]/', '', $ndx[0]);
        // $msg_ndx        = intval(preg_replace('/[[:^print:]]/', '', $ndx[1]))+1;
        $sse_session_id = preg_replace('/[[:^print:]]/', '', $ndx[2]);
        if ($u == $u_target) {
          $sessionFile=".sse/sessions/$sse_session_id.session";
          if (file_exists($sessionFile)) {
            touch($sessionFile);
          } 
        }
        $usr_folder = ".sse/$w_target/$u_target";
        if (is_dir($usr_folder)) {
          mt_srand();
          $msg_ndx = date("U")."-".mt_rand(1000,9999)."-".mt_rand(1000,9999);
          // file_put_contents("$ndxFile", "$w_target\n$msg_ndx\n$sse_session_id\n".date("U"));
          $messageFileI = "$usr_folder/$msg_ndx.new";
          $messageFileF = "$usr_folder/$msg_ndx.msg";
          _dumpY(8,0,"SSE::pushQueue($u_target) - set file '".basename($messageFileI)."'");

          $f=fopen($messageFileI, "wt");
          fputs($f, "$message\n");
          fputs($f, "$data\n");
          fclose($f);

          rename($messageFileI, $messageFileF);

        } else {
          _dumpY(8,3,"SSE:: user folder '$usr_folder' cannot be accessed");
        }
      } else {
        _dumpY(8,3,"SSE:: index file '$ndxFile' not found");
      }
      return $messageFile;
    }

    /*  push a event to be processed later by the caller itself 
        The (s,a) pair will be used to imitate an application normal call (xmlHttpRequest, RESTful, URL ...)
        These calls will be atended by a 'w' prefixed function as when a webSocket or a RESTful is used.
        The 'w' function will use SSE::postMessage() or SSE::sendMesage() in order to send it result to 
        the original client (or not).
     */
    public function postpone_w($s, $a, $data)
    {
      global $u;
      if (isset($u)) {
        if (is_string($data))
          $data=json_decode($data, true);
        if (is_array($data)) {
          $data["s"]=$s;
          $data["a"]=$a;
          $data=json_encode($data);
          self::__enqueueMessage($u, 'postpone_w', $data);
        }
      } else {
        _dumpY(8,0,"You cannot postpone a message without 'u' parameter");
      }
    }

    /* send a message and wait to it be processed by the target
       returns true if the message was delivered
       returns false if the queue does not exists */
    public function sendMessage($u_target, $message, $data='')
    {
      _dumpY(8,2,"SSE::sendMessage('$u_target', '$message', '$data')");
      $ret=false;
      $messageFile=self::__enqueueMessage($u_target, $message, $data);
      if ($messageFile>'') {
        $ret=true;
        while (file_exists($messageFile)) {
          usleep(500000);
        }
      }
      return $ret;
    }

    /* post a message and return immediatly */
    public function postMessage($u_target, $message, $data='')
    {
      _dumpY(8,2,"SSE::postMessage('$u_target', '$message', '$data')");
      $ret=false;
      $messageFile=self::__enqueueMessage($u_target, $message, $data);
      if ($messageFile>'') {
        $ret=true;
      }
      return $ret;
    }

    /* post a message to all the workgroup */
    public function broadcastMessage($message, $data='', $w_target='*')
    {
      _dumpY(8,2,"SSE::broadcastMessage('$message', '$data', '$w_target')");
      $w_target = preg_replace('/[[:^print:]]/', '', $w_target);
      if ($w_target=='*') {
        $dh=opendir(".sse");
        if ($dh) {
          while (($f = readdir($dh)) !== false) {
            $fileinfo=pathinfo($f);
            $ok=fnmatch("*.ndx", $fileinfo['basename']);
            if ($ok) {
              self::postMessage($fileinfo['filename'], $message, $data);
            }
          }
          closedir($dh);
        }
      } else {
        if (is_dir(".sse/$w_target")) {
          $dh=opendir(".sse/$w_target");
          if ($dh) {
            while (($f = readdir($dh)) !== false ) {
              $u_target = basename($f);
              if (!is_dir($f)) {
                self::postMessage($u_target, $message, $data);
              }
            }
            closedir($dh);
          }
        }
      }
    }
  }


  function q_sse($a)
  {
    global $userContext, $sysDate, $u,
           $fieldValue, $fieldName,
           $userMsg, $xq_start, $__sse_ret;

    $useColNames = true;
    $countLimit=20;
    $ret='';

    extract(xq_extractValuesFromQuery());
    $xq_start=isset($xq_start)?intval($xq_start):0;

    switch($a)
    {
      case 'attachUser':      
        $sse_session_id  = SSE::attachUser($w, $user);        
        $userAliveInterval    = SSE::getMaxUserAliveInterval() * 7;
        $ret = array(
              'ok'             => $sse_session_id>'',
              'sse_session_id' => $sse_session_id,
              'userAliveInterval'   => $userAliveInterval
            );

        break;

      case 'userAlive':
      case 'peekMessage':
        $__sse_ret=array();
        $sessionInfo = SSE::getSessionInfo($sse_session_id);
        extract($sessionInfo);
        if (SSE::enabled($sse_session_id, $w, $u)) {
          $sessionFile=".sse/sessions/$sse_session_id.session";
          if (file_exists($sessionFile)) {
            touch($sessionFile);
          } 

          if ($a=='peekMessage') {
            $sse_dispatch = function($eventName, $eventData) {
              global $__sse_ret;
              _dumpY(8,0,preg_replace('/[[:^print:]]/', '', "event: $eventName, data: $eventData"));
              $__sse_ret[]=array(  'event' => $eventName,
                                   'data'  => $eventData   );
            };
            SSE::processQueue($sse_dispatch);
          }

        } else {
          $__sse_ret=array(  'event' => 'close',
                             'data'  => ''   );
        }
        _dumpY(8,0,"ret: ".json_encode($__sse_ret));
        $ret=$__sse_ret;
        break;
    }

    xq_produceReturnLines($ret, $useColNames, $countLimit);

  }


?>