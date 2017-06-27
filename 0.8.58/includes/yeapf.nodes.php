<?php
/*
    includes/yeapf.nodes.php
    YeAPF 0.8.58-91 built on 2017-06-27 16:13 (-3 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2017-06-27 16:11:39 (-3 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  global $cfgMaxSegmentReservationChunkCount,
         $cfgMaxUnattachedSegments;

  if (!isset($cfgMaxSegmentReservationChunkCount)) $cfgMaxSegmentReservationChunkCount = 2;
  $cfgMaxSegmentReservationChunkCount = max(0, min(intval($cfgMaxSegmentReservationChunkCount) , 2));

  if (!isset($cfgMaxUnattachedSegments)) $cfgMaxUnattachedSegments = 5;
  $cfgMaxUnattachedSegments = max(0, min(intval($cfgMaxUnattachedSegments) , 5));

  function urlAntiCache($url) {
    if (strpos($url,"?")===false)
      $url.="?";
    else
      $url.="&";
    $url.="r".y_rand(11,99)."z=".y_uniqid();
    return $url; 
  }

  class yNode {
    public function __construct() {
    }

    /* Common to both sides */
    public function generateKey($maxLen = 7, $onlyUpperCase=true) {
      $seq = "QWERTYUIOPASDFGHJKLZXCVBNM0123456789";
      if (!$onlyUpperCase)
        $seq.="qwertyuiopasdfghjklzxcvbnm";
      $key = "";
      /* falta verificar que a key nao exista */
      while (strlen($key) < $maxLen) {
        $n = y_rand(0, strlen($seq));
        $key.= substr($seq, $n, 1);
      }

      return $key;
    }

    public function isWorkingAsNodeController() {
      if (db_status(_DB_CONNECTED_) == _DB_CONNECTED_) {
        $cc1 = db_sql("select count(*) from is_node_control");
        $cc2 = db_sql("select count(*) from is_server_control");
        return (!self::isWorkingAsAppNode() && ($cc1 == 0) && ($cc2 == 0));
      }
      else return false;
    }

    public function isWorkingAsAppNode() {
      global $cfgNodePrefix;
      _recordWastedTime("isWorkingAsAppNode()");
      $ret = false;
      if (db_status(_DB_CONNECTED_) == _DB_CONNECTED_) {
        if (db_tableExists("is_node_control")) {
          $cc = db_sql("select count(*) from is_node_control");
          $ret = (($cfgNodePrefix != "UNK") && ($cc > 0));
        }
      }

      return $ret;
    }

    /* nodeController side */
    public function reserveSegments($serverKey, $nodeName, $count = 10) {
      global $cfgMaxSegmentReservationChunkCount, $cfgMaxUnattachedSegments;
      $ret=-1;
      if (self::isWorkingAsNodeController()) {
        $nodePrefix=_deviceId2deviceKey($serverKey, $nodeName);

        $ret = true;
        $sql="select count(*)
                    from is_segment_control
                    where serverKey='$serverKey'
                      and nodePrefix='$nodePrefix'
                      and regulation is null";
        // echo "$sql\n";
        $cc = db_sql($sql);
        $count = min($count, $cfgMaxSegmentReservationChunkCount);
        _dumpy(512,1,"NODE: reserveSegments() cc: $cc |  count: $count | cfgMaxUnattachedSegments: $cfgMaxUnattachedSegments");
        if ($cc<$cfgMaxUnattachedSegments) {
          // echo "cc=$cc | cfgMaxUnattachedSegments=$cfgMaxUnattachedSegments | cfgMaxSegmentReservationChunkCount=$cfgMaxSegmentReservationChunkCount\n";
          $count = min($count, $cfgMaxUnattachedSegments-$cc);
          _dumpy(512,1,"NODE: Reserving $count segments");
          if (lock("reserve-segments")) {
            $ret = array();
            while ($count > 0) {
              $count--;
              do {
                $key = self::generateKey(4);
                $cc = db_sql("select count(*) from is_segment_control where segment='$key'");
              } while ($cc > 0);
              $now = date("YmdHis");
              db_sql("insert into is_segment_control(serverKey, nodePrefix, identity, segment, creation, regulation) values ('$serverKey', '$nodePrefix', null, '$key', '$now', null) ");
              $ret[] = $key;
            }

            unlock("reserve-segments");
          }
        } else {
          _dumpy(512,1,"NODE: fetching already reserved $count segment(s)");
          $sql="select segment 
                from is_segment_control
                where serverKey='$serverKey'
                  and nodePrefix='$nodePrefix'
                  and regulation is null
                limit 0, $count";
          $ret = array();
          $q=db_query($sql);
          while ($d=db_fetch_array($q)) {
            extract($d);
            _dumpy(512,1,"NODE: segment: $segment");
            $ret[] = $segment;
          }
        }
      }

      return $ret;
    }

    public function associateSegment($serverKey, $nodeName, $segment, $identity) {
      $ret = array('errorMsg'=>'NodeController not found');
      if (self::isWorkingAsNodeController()) {
        $ret=array('errorMsg'=>'System cannot be locked');
        if (lock("associate-segment-$serverKey")) {
          $ret=array('errorMsg'=>'Segment not found in this server/node');

          $nodePrefix=_deviceId2deviceKey($serverKey, $nodeName);

          $cc = db_sql("select count(*)
                      from is_segment_control
                      where serverKey='$serverKey'
                        and nodePrefix='$nodePrefix'
                        and segment='$segment'
                        and regulation is null");

          $now = date("YmdHis");
          if ($cc == 1) {
            db_sql("update is_segment_control
                    set regulation='$now', 
                        identity='$identity'
                    where serverKey='$serverKey'
                      and nodePrefix='$nodePrefix'
                      and segment='$segment'
                      and regulation is null");
          }

          $regulation = db_sql("select regulation
                                  from is_segment_control
                                 where serverKey='$serverKey'
                                   and nodePrefix='$nodePrefix'
                                   and segment='$segment'
                                   and identity='$identity'
                                   and regulation is not null");

          if ($regulation>'') {
            $ret=array('regulation'=>$regulation, 'now'=>$now);
          }

          unlock("associate-segment-$serverKey");
        }
      }

      return $ret;
    }

    public function requestSegmentsId($serverKey, $nodeName, $count) {
      $ret=array();

      $sequence=_generateSegmentsId($serverKey, $nodeName, $count);
      if ($sequence == false) {
        $ret['error']=1;
      } else {
        $ret['error']=0;
        if ($sequence===true) {
          $ret['sequence']=array();
        } else {
          $ret['sequence']=$sequence;
        }
      }

      return $ret;
    }

    public function validateSequence($serverKey, $nodeName, $r) {
      $ret=array();

      $x=y_uniqid();
      $ax=$bx=$x;
      if (_validateDeviceSequence($serverKey, $nodeName, $r, $ax, $bx)) {
        $ret['a']=$ax;
        $ret['b']=$bx;
        $ret['c']=md5("$ax:$bx:1");
        $ret['error']=0;
      } else {
        $ret['c']=md5(0);
        $ret['error']=1;
      }

      return $ret;
    }

    public function checkServerKey($serverKey, $nodeName) {
      $ret=array();

      $ret['rn']=-1;
      $ret['error']=1;
      if (_projectExists($serverKey)) {
        $registeredNode = _verifyDeviceExistsInProject($serverKey, $nodeName);
        $ret['rn']=intval($registeredNode);

        if ($registeredNode) {
          $x=y_uniqid(); 
          $ax=$bx=$x;
          $auth=_registerDeviceIntoProject($serverKey, $nodeName);
          _grantSequenceKeys($nodeName, $ax, $bx);

          $ret['auth']=md5($auth);
          if ($ax!=$x) {
            $ret['a']=$ax;
            $ret['b']=$bx;
            $ret['error']=0;
          } else {
            $ret['error']=2;
          }
        } else {
          $ret['auth']=md5(1);
          $ret['error']=3;
        }
      } else {
        $ret['auth']=md5(0);
        $ret['rn']=0;
      }

      return $ret;
    }

    /* appNode side */

    public function _request($url, &$canEvaluate) {
      $ret=false;
      $url=urlAntiCache($url);
      _dumpy(512,1,"NODE: url '$url'");
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $canEvaluate = true;
      if (($ret = curl_exec($ch)) === false) {
        $errorMsg = "Error: #" . curl_errno($ch) . ", " . curl_error($ch);
        _dumpy(512,1,"NODE: $errorMsg");
        _recordError($errorMsg);
        $canEvaluate = false;
      }
      return $ret;
    }

    public function requestSegmentReservation($count=1) {
      global $cfgIdServerURL;
      $ret=-4;
      if (!self::isWorkingAsNodeController()) {
        $ret=-3;
        if (self::isWorkingAsAppNode()) {
          $ret=-2;
          $validServerURL = (isset($cfgIdServerURL)) && (!filter_var($cfgIdServerURL, FILTER_VALIDATE_URL) === false);
          if ($validServerURL) {
            $ret=-1;
            $urlBase = "$cfgIdServerURL/rest.php";
            $serverKey = $GLOBALS['cfgDBNode']['server_key'];
            $nodeName = $GLOBALS['cfgDBNode']['node_name'];
            $nodePrefix = $GLOBALS['cfgDBNode']['node_prefix'];
            $request=date("YmdHis");
            $count = min(intval($count), $GLOBALS['cfgMaxSegmentReservationChunkCount']);
            $url = "$urlBase?s=ynode&a=requestSegmentsId&serverKey=$serverKey&nodeName=$nodeName&count=$count";
            $url=urlAntiCache($url);
            _dumpy(512,1,"NODE: url '$url'");

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $canEvaluate = true;
            if (($retSegments = curl_exec($ch)) === false) {
              $errorMsg = "Error: #" . curl_errno($ch) . ", " . curl_error($ch);
              _dumpy(512,1,"NODE: $errorMsg");
              _recordError($errorMsg);
              $canEvaluate = false;
            } else {
              $ret=false;
              $retSegments = json_decode($retSegments, true);
              if ($retSegments['error'] == 0) {
                $ret=true;
                for ($i = 0; $i < count($retSegments['sequence']); $i++) {
                  $seg = $retSegments['sequence'][$i];
                  $cc = db_sql("select count(*) from is_segment_reservation where serverKey='$serverKey' and nodePrefix='$nodePrefix' and segment='$seg'");
                  if ($cc == 0) {
                    if (gettype($ret)=="boolean") {
                      $ret=array();
                    }
                    $ret[]=$seg;

                    db_sql("insert into is_segment_reservation(serverKey, nodePrefix, segment, identity, request, regulation) values ('$serverKey', '$nodePrefix', '$seg', null, '$request', null)");
                  }
                }
              }
            }

            curl_close($ch);
          } else {
            _dumpy(512,1,"NODE: '$cfgIdServerURL' is not a valid URL");
          }
        }
      }
      return $ret;
    }

    public function unassignedSegmentCount($allNodes=false) {
      $serverKey = $GLOBALS['cfgDBNode']['server_key'];
      $nodePrefix = $GLOBALS['cfgDBNode']['node_prefix'];
      if ($allNodes)
        $sql="select count(*) as cc from is_segment_reservation where identity is null";
      else
        $sql="select count(*) as cc 
              from is_segment_reservation 
              where identity is null 
                and serverKey='$serverKey' 
                and nodePrefix='$nodePrefix'";
      return db_sql($sql);
    }

    public function requestSegmentAssociation($identity) {
      global $cfgIdServerURL, $cfgMainFolder, $cfgNodePrefix;
      $ret = -4;
      $ok = false;
      $toDebug = true;
      $canEvaluate = false;
      if (self::isWorkingAsAppNode()) {
        $ret=-3;

        $sqlGetRegulation="select regulation, identity, segment, request
                             from is_segment_reservation
                            where identity='$identity'";
        $aux=intval(db_sql("select count(*) from ($sqlGetRegulation) t"));
        _dumpy(512,1,"NODE: count for identity '$identity' = $aux");
        if ($aux==0) {
          $cc = self::unassignedSegmentCount();
          if ($cc==0) {
            _dumpy(512,1,"NODE: requesting segment reservation");
            self::requestSegmentReservation();
            $cc = self::unassignedSegmentCount();
          }
          _dumpy(512,1,"NODE: unassignedSegmentCount() = $cc");

          if ($cc>0) {
            $ret = -2;
            $serverKey = $GLOBALS['cfgDBNode']['server_key'];
            $nodePrefix = $GLOBALS['cfgDBNode']['node_prefix'];
            if (lock("$serverKey-$nodePrefix")) {
              $segment=db_sql("select segment 
                               from is_segment_reservation 
                               where identity is null 
                                 and serverKey='$serverKey' 
                                 and nodePrefix='$nodePrefix'
                               order by request
                               limit 0,1");
              if ($segment>'') {
                try {
                  db_sql("update is_segment_reservation 
                             set identity='$identity' 
                           where segment='$segment' 
                             and serverKey='$serverKey'
                             and nodePrefix='$nodePrefix'");
                  $ret = -1;
                  $validServerURL = (isset($cfgIdServerURL)) && (!filter_var($cfgIdServerURL, FILTER_VALIDATE_URL) === false);
                  if ($validServerURL) {
                    $urlBase = "$cfgIdServerURL/rest.php";
                    _dumpy(512,1,"NODE: urlBase '$urlBase'");

                    $serverKey = $GLOBALS['cfgDBNode']['server_key'];
                    $nodeName = $GLOBALS['cfgDBNode']['node_name'];
                    $url = "$urlBase?s=ynode&a=associateSegment&serverKey=$serverKey&nodeName=$nodeName&segment=$segment&identity=$identity";
                    _dumpy(512,1,"NODE: url '$url'");

                    $canEvaluate=null;
                    $regulationInfo=self::_request($url, $canEvaluate);
                    if ($canEvaluate) {
                      $ret=false;
                      _dumpy(512,1,"NODE: regulationInfo=$regulationInfo ".gettype($regulationInfo));

                    
                      $regulationInfo=json_decode($regulationInfo, true);
                      $errorMsg=isset($regulationInfo['errorMsg'])?$regulationInfo['errorMsg']:"";
                      if ("$errorMsg"=='') {
                        $regulation=isset($regulationInfo['regulation'])?$regulationInfo['regulation']:"";
                        if ($regulation>'') {
                          db_sql("update is_segment_reservation 
                                     set regulation='$regulation' 
                                   where segment='$segment' 
                                     and serverKey='$serverKey'
                                     and nodePrefix='$nodePrefix'");                        
                        }
                      } else {
                        db_sql("update is_segment_reservation 
                                   set regulation_message='$errorMsg' 
                                 where segment='$segment' 
                                  and serverKey='$serverKey'
                                  and nodePrefix='$nodePrefix'");
                      }                      
                    }
                  }
                } catch(Exception $e) {
                  _dumpy(512,1,"NODE: Error trying to associate a segment with an identity: ".$e->getMessage());
                }
              }  

              unlock("$serverKey-$nodePrefix");                  
            }
          }
        } 
        
        $ret=db_queryAndFillArray("$sqlGetRegulation",false);
        if (is_array($ret))
          $ret=isset($ret[0])?$ret[0]:false;
        else
          $ret=false;
      }
      return $ret;
    }

    public function requestNodeSequenceVerification($force=false) {
      global $cfgIdServerURL, $cfgMainFolder, $cfgNodePrefix;
      $ret = -3;
      $ok = false;
      $toDebug = true;
      $canEvaluate = false;
      if (self::isWorkingAsAppNode()) {
        $ret=-2;
        if (lock('verify-node-sequence', true)) {
          $ret=-1;
          $tempTimeMark = sys_get_temp_dir() . "/ctrl-tm-seq";
          $toTest = false;
          $now = date('U');
          if ($toDebug) _dumpy(512,1,"NODE: $tempTimeMark");
          if (file_exists($tempTimeMark)) {
            $tm = filemtime($tempTimeMark);
            $desired = intval(file_get_contents($tempTimeMark));
            $maxT = $tm + (4 * 60 * 60);
            $dtm = min(max($now, $maxT) , $desired);
            $toTest = intval(($now >= $dtm));
            if ($toDebug) {
              $xmaxT = date("Y-m-d H:i:s", $maxT);
              $xdesired = date("Y-m-d H:i:s", $desired);
              $xdtm = date("Y-m-d H:i:s", $dtm);
              $xnow = date("Y-m-d H:i:s", $now);
              _dumpy(512,1,"NODE: maxT=$xmaxT |desired=$xdesired | dtm=$xdtm | now=$xnow | toTest=$toTest");
            }
          } else {
            $toTest = true;
          }

          $toTest = $toTest || $force;
          if ($force) _dumpy(512,1,"NODE: checked enforced");

          if ($toTest) {
            $validServerURL = (isset($cfgIdServerURL)) && (!filter_var($cfgIdServerURL, FILTER_VALIDATE_URL) === false);
            if ($validServerURL) {
              $urlBase = "$cfgIdServerURL/rest.php";
              _dumpy(512,1,"NODE: urlBase '$urlBase'");
              $nodeSeq = @file_get_contents("$cfgMainFolder/.config/cloudAppNode.seq");
              $nodeSeq = explode(":", $nodeSeq);
              $a = $nodeSeq[0];
              $b = $nodeSeq[1];
              $r = $a + $b;
              $serverKey = $GLOBALS['cfgDBNode']['server_key'];
              $nodeName = $GLOBALS['cfgDBNode']['node_name'];
              $url = "$urlBase?s=ynode&a=validateSequence&r=$r&serverKey=$serverKey&nodeName=$nodeName";
              $url=urlAntiCache($url);
              _dumpy(512,1,"NODE: url '$url'");
              $ch = curl_init();
              curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
              curl_setopt($ch, CURLOPT_TIMEOUT, 5);
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
              curl_setopt($ch, CURLOPT_URL, $url);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
              $canEvaluate = true;
              if (($ret = curl_exec($ch)) === false) {
                $errorMsg = "Error: #" . curl_errno($ch) . ", " . curl_error($ch);
                _dumpy(512,1,"NODE: $errorMsg");
                _recordError($errorMsg);
                $canEvaluate = false;
              }

              curl_close($ch);
              if ($canEvaluate) {
                $ret = json_decode($ret, true);
                $check="*".date("U")."*";
                if (isset($ret['error'])) {
                  if ($ret['error']===0) {
                    $check = md5($ret['a'] . ':' . $ret['b'] . ':1');
                  }
                }
                if ($check == $ret['c']) {
                  @file_put_contents("$cfgMainFolder/.config/cloudAppNode.seq", $ret['a'] . ':' . $ret['b']);
                  $dt = $now + y_rand(15, 60 * 60);
                  @file_put_contents($tempTimeMark, $dt);
                  $ok = true;
                } else {
                  _dumpy(512,1,"NODE: Is this a clone node?");
                  if (is_array($ret))
                    foreach($ret as $k => $v) _dumpy(512,1,"NODE: $k = '$v'");
                }
              } else {
                _dumpy(512,1,"NODE: '$cfgIdServerURL' is not a valid url");
                $toTest = false;
              }
            }
          }

          if ($canEvaluate) {
            if (!$ok) {
              _dumpy(512,1,"NODE: Node out of sequence.\nNode disabled. IdServerURL:'$cfgIdServerURL'");
              self::disableThisNode();
            }
          }

          $ret = ($canEvaluate) ? ((bool)$ok) : -1;

          _dumpy(512,1,"NODE: canEvaluate: $canEvaluate | toTest: $toTest | ok: $ok | ret: $ret");

          unlock('verify-node-sequence');
        }
      }

      return $ret;
    }

    public function checkNodeConfig() {
      global $cfgNodePrefix, $cfgMainFolder;
      $ret = true;
      $secondsPerDay = 24 * 60 * 60;
      _recordWastedTime("checkNodeConfig()");
      if (self::isWorkingAsAppNode()) {
        $ret = false;
        $now = date('U');
        $dbNodeInfo = db_sql("select n.serverKey, n.enabled as nodeEnabled,
                                     n.last_verification, n.external_ip,
                                     n.nodePrefix,
                                     s.enabled as serverEnabled,
                                     s.serverKey as sp2
                              from      is_node_control n
                              left join is_server_control s on s.serverKey=n.serverKey
                              where nodePrefix='$cfgNodePrefix'", false);
        extract($dbNodeInfo);
        if ($serverKey > '') {
          $currentIP = getCurrentIp();
          if ($last_verification == '') $last_verification = $now;
          $dif = intval(intval($now) - intval($last_verification));
          if ($currentIP != $external_ip) {
            _recordError("Error: node_control says '$external_ip' while your current ip is '$currentIP'");
            db_close();
            db_set_flag(_DB_LOCKED | _DB_LOCK_EXTERNAL_IP_MISTAKE);
          }
          else
          if ($dif > $secondsPerDay) {
            $difHours = floor($dif / 60 / 60);
            _recordError("Error: node_control has been checked $difHours hours ago. It need to be checked each 24 hours");
            db_close();
            db_set_flag(_DB_LOCKED | _DB_LOCK_TIME_MISTAKE);
          }
          else
          if ($serverEnabled != 'Y') {
            _recordError("Error: server_control has been disabled");
            db_close();
            db_set_flag(_DB_LOCKED | _DB_LOCK_DISABLED);
          }
          else
          if ($nodeEnabled == 'N') {
            _recordError("Error: node_control has been disabled");
            db_close();
            db_set_flag(_DB_LOCKED | _DB_LOCK_DISABLED);
          }
          else
          if ($nodePrefix != $cfgNodePrefix) {
            _recordError("Error: node_control node prefix '$nodePrefix' differs from '$cfgNodePrefix' declared in $cfgMainFolder/.config/cloudAppNode.ini");
            db_close();
            db_set_flag(_DB_LOCKED | _DB_LOCK_NODE_PREFIX_MISTAKE);
          }
        } else if ($cfgNodePrefix != 'UNK') {
          _recordError("Error: node_prefix '$cfgNodePrefix' defined in $cfgMainFolder/.config/cloudAppNode.ini cannot be located is_node_control");
          db_close();
          db_set_flag(_DB_LOCKED | _DB_LOCK_WRONG_SERVER_PREFIX);
        } else if (yNode::requestNodeSequenceVerification() === false) {
          _recordError("Error: this node is out of sequence with id controller");
          db_close();
          db_set_flag(_DB_LOCKED | _DB_LOCK_WRONG_SEQUENCE);
        }

        $ret = (db_status(_DB_LOCKED) == 0);
      }

      _recordWastedTime("is_node_control checked");
      return $ret;
    }

    public function thisNodeExists($onlyEnabled = true) {
      global $cfgNodePrefix;
      if ($onlyEnabled) {
        $w = "n.enabled='Y' and s.enabled='Y' and ";
      } else {
        $w = "";
      }
      $sql = "select count(*)
            from is_node_control n, is_server_control s
            where $w nodePrefix='$cfgNodePrefix'
              and s.serverKey=n.serverKey";
      $cc = db_sql($sql);
      return ($cc == 1);
    }

    public function disableThisNode() {
      global $cfgNodePrefix;
      db_sql("update is_node_control set enabled='N' where nodePrefix='$cfgNodePrefix'");
    }

    public function enableThisNode() {
      global $cfgNodePrefix;
      db_sql("update is_node_control set enabled='Y' where nodePrefix='$cfgNodePrefix'");
    }

    public function nodeKeepAlive() {
      global $cfgNodePrefix, $cfgClientConfig, $serverIP;
      if (db_status(_DB_LOCKED) == 0) {
        if (db_tableExists('is_node_control')) {
          if (thisNodeExists()) {
            $dbNodeInfo = db_sql("select enabled, last_verification, external_ip, internal_ip
                                  from is_node_control
                                  where nodePrefix='$cfgNodePrefix'", false);
            extract($dbNodeInfo);
            $currentIP = getCurrentIp();
            if ($currentIP == $external_ip) {
              if ($internal_ip == $server_IP) {
                $t = date('U');
                $reverse_ip = gethostbyaddr($currentIP);
                db_sql("update is_node_control
                        set last_verification=$t,
                            reverse_ip='$reverse_ip'
                        where nodePrefix='$cfgNodePrefix'");
                $ret['reverse_ip'] = $reverse_ip;
                $ret['last_verification'] = $t;
              } else {
                $ret['error'] = "Current internal ip '$server_IP' differs from '$internal_ip'";
              }
            } else {
              $ret['error'] = "Current ip '$currentIP' differs from '$ip4'";
            }
          } else {
            $ret['error'] = "No configuration enabled at 'is_node_control'";
          }
        } else {
          $ret['error'] = "Table 'is_node_control' not found in database";
        }
      }
      else {
        $ret['flags'] = db_status();
        $ret['error'] = explainDBError();
        $ret['lastError'] = $GLOBALS['lastError'];
      }

      /*
      $ret['folder'] = $cfgMainFolder;
      $ret['cfgNodePrefix'] = $cfgNodePrefix;
      $ret['cfgClientConfig'] = $cfgClientConfig;
      */
      return $ret;
    }

    /* appNode side diagnosis functions */
    public function diag_getSegmentReservationList() {
      $ret=array();
      $serverKey = $GLOBALS['cfgDBNode']['server_key'];
      $nodeName = $GLOBALS['cfgDBNode']['node_name'];
      $nodePrefix = $GLOBALS['cfgDBNode']['node_prefix'];

      $sql="select segment from is_segment_reservation where serverKey='$serverKey' and nodePrefix='$nodePrefix' and identity is null";
      $q=db_query($sql);
      while ($d=db_fetch_array($q)) {
        $ret[]=$d['segment'];
      }
      return $ret;
    }

    public function diag_getNextNodeVerification() {
      $now = date('U');
      $tempTimeMark = sys_get_temp_dir() . "/ctrl-tm-seq";
      if (file_exists($tempTimeMark)) {
        $tm = filemtime($tempTimeMark);
        $desired = intval(file_get_contents($tempTimeMark));
        $maxT = $tm + (4 * 60 * 60);
        $ret = min(max($now, $maxT) , $desired);
      } else {
        $ret=$now;
      }
      return $ret;
    }

  }

  function rynode($a) {
    extract(xq_extractValuesFromQuery());

    $ret=array();

    if ($a=='ping') {
      $ret['serverTime'] = date('U');
      $ret['timezone'] = date('Z');
      $ret['ip'] = getCurrentIp();
    }

    if (yNode::isWorkingAsAppNode()) {
      /*
                                  #     #
             ##    #####   #####  ##    #   ####   #####   ######
            #  #   #    #  #    # # #   #  #    #  #    #  #
           #    #  #    #  #    # #  #  #  #    #  #    #  #####
           ######  #####   #####  #   # #  #    #  #    #  #
           #    #  #       #      #    ##  #    #  #    #  #
           #    #  #       #      #     #   ####   #####   ######
       */
      switch($a) {
        case 'nodeKeepAlive':
          $ret=yNode::nodeKeepAlive();
        break;

        case 'requestNodeSequenceVerification':
          $r=yNode::requestNodeSequenceVerification();
          $ret['r']=$r;
          switch ($r) {
            case '-3':
              $r['result']='This is not an appNode';
              break;

            case '-2':
              $r['result']='Node is locked';
              break;

            case '-1':
              $r['result']='Node cannot be tested';
              break;

            case '0':
              $r['result']='Node sequence cannot be verified. DISABLED';
              break;

            case '1':
              $r['result']='Ok';
              break;

            default:
              $r['result']='Unknown';
              break;
          }
        break;
      }
    } else if (yNode::isWorkingAsNodeController()) {
      /*
          ####    ####   #    #   #####  #####    ####   #       #       ######  #####
         #    #  #    #  ##   #     #    #    #  #    #  #       #       #       #    #
         #       #    #  # #  #     #    #    #  #    #  #       #       #####   #    #
         #       #    #  #  # #     #    #####   #    #  #       #       #       #####
         #    #  #    #  #   ##     #    #   #   #    #  #       #       #       #   #
          ####    ####   #    #     #    #    #   ####   ######  ######  ######  #    #
       */
      $ret['error']=-1;
      if (function_exists("_projectExists")) {
        switch($a) {

          case 'checkServerKey':
            $ret=yNode::checkServerKey($serverKey, $nodeName);
            break;

          case 'validateSequence':
            $ret=yNode::validateSequence($serverKey, $nodeName, $r);
            break;

          case 'requestSegmentsId':
            $ret=yNode::requestSegmentsId($serverKey, $nodeName, $count);
            break;

          case 'associateSegment':
            $ret=yNode::associateSegment($serverKey, $nodeName, $segment, $identity);
            break;
        }
      }
    }

    $jsonRet = json_encode($ret);
    echo produceRestOutput($jsonRet);
  }

  yNode::checkNodeConfig();

  _recordWastedTime("yeapf.nodes.php Carregado");

?>