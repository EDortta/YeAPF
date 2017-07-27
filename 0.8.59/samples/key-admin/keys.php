<?php
  global $cfgAdminBaseFolder;
  $cfgAdminBaseFolder=dirname(__FILE__);

  (@include_once(dirname(__FILE__)."/keys.config.php"));

  function generateKey($maxLen=7) {
    $seq="QWERTYUIOPASDFGHJKLZXCVBNM0123456789";
    $key="";

    // srand();

    /* falta verificar que a key nao exista */
    while (strlen($key)<$maxLen) {
      $n=y_rand(0,strlen($seq));
      $key.=substr($seq,$n,1);
    }

    return $key;
  }

  function decodeURI($smth) {
    $smth = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($smth));
    $smth = html_entity_decode($smth,null,'UTF-8');
    return $smth;
  }

  function md5toId($base, $itemIdMd5) {
    $ret=false;
    $d=glob("$base/*");
    foreach($d as $itemId) {
      $itemId=basename($itemId);
      $auxMd5=md5($itemId);
      if (md5($itemId)==$itemIdMd5) {
        $ret=$itemId;
        break;
      }
    }
    return $ret;
  }

  function clientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'192.168.1.1';
    }
    return $ip;
  }

  function _do_logoff($deviceId) {
    global $cfgAdminBaseFolder;
    $ret=0;
    $filename="$cfgAdminBaseFolder/.login/$deviceId";
    if (file_exists($filename)) {
      $loginInfo=json_decode(file_get_contents($filename), true);
      $sessionId=$loginInfo["sessionId"];
      $sessionFilename="$cfgAdminBaseFolder/.login/$sessionId.session";
      if (file_exists("$sessionFilename"))
        unlink($sessionFilename);
      unlink($filename);
      $ret=1;
    }
    return $ret;
  }

  function _do_login($projectId, $deviceId) {
    global $cfgAdminBaseFolder;

    $ret="";
    /* travo para impedir dois logins concomitantes do mesmo cliente */
    if (lock($deviceId)) {
      /* garanto estrutura de .login */
      if (!is_dir("$cfgAdminBaseFolder/.login"))
        mkdir("$cfgAdminBaseFolder/.login");
      /* caso este mesmo usuario esteja conectado, derrubo ele */
      if (file_exists("$cfgAdminBaseFolder/.login/$deviceId"))
        _do_logoff($deviceId);
      /* crio o sessionId */
      $sessionId=y_uniqid();

      /* preparo a info de logon */
      $loginInfo = array(
        "loginTime" => date('U'),
        "sessionId" => $sessionId
      );
      $jLoginInfo=json_encode($loginInfo);

      /* preparo a info de sessao */
      $jSessionInfo=json_encode(
        array(
          'projectId'  => $projectId,
          'deviceId'   => $deviceId,
          'ip'         => clientIP(),
          'last_action'=> date('U')
        )
      );

      /* salvo a informacao de sessao e de .login */
      file_put_contents("$cfgAdminBaseFolder/.login/$deviceId", $jLoginInfo);
      file_put_contents("$cfgAdminBaseFolder/.login/$sessionId.session", $jSessionInfo);

      $ret=$sessionId;

      /* destravo o dispositivo */
      unlock($deviceId);
    }
    return $ret;
  }

  function getProjectIdFromSession($sessionId) {
    global $cfgAdminBaseFolder;
    $ret=null;
    $sessionFilename="$cfgAdminBaseFolder/.login/$sessionId.session";
    if (file_exists($sessionFilename)) {
      $sessionInfo=json_decode(file_get_contents($sessionFilename), true);
      $ret=strtoupper($sessionInfo['projectId']);
    }
    return $ret;
  }

  function _verifyDeviceBond($projectKey, $deviceId, $deviceKey) {
    global $cfgAdminBaseFolder;

    $validKey="N";
    $projectKey=trim(strtoupper($projectKey));
    $deviceId=trim(strtoupper($deviceId));
    $deviceKey=trim(strtoupper($deviceKey));
    if ($projectKey>'') {
      if (is_dir("$cfgAdminBaseFolder/.keys")) {
        if (is_dir("$cfgAdminBaseFolder/.keys/$projectKey")) {
          if (file_exists("$cfgAdminBaseFolder/.keys/$projectKey/$deviceId")) {
            $key=file_get_contents("$cfgAdminBaseFolder/.keys/$projectKey/$deviceId");
            if ($key==$deviceKey)
              $validKey="Y";
          }
        }
      }
    }
    return $validKey;
  }

  function _projectExists($projectKey) {
    global $cfgAdminBaseFolder;

    $ret=false;

    $projectKey=trim(strtoupper($projectKey));
    $ret=(($projectKey>'') && (is_dir("$cfgAdminBaseFolder/.keys/$projectKey")));

    return $ret;
  }

  function _verifyDeviceExistsInProject($projectKey, $deviceId) {
    global $cfgAdminBaseFolder;

    $ret=false;

    $projectKey=trim(strtoupper($projectKey));
    $deviceId=trim(strtoupper($deviceId));
    /* garante a pasta contendo as .keys */
    if (!is_dir("$cfgAdminBaseFolder/.keys"))
      mkdir("$cfgAdminBaseFolder/.keys");
    /* garantimos que a pasta do projeto exista */
    if (is_dir("$cfgAdminBaseFolder/.keys/$projectKey")) {
      /* verifica e o dispositivo existe no projeto*/
      $ret=(file_exists("$cfgAdminBaseFolder/.keys/$projectKey/$deviceId"));
    }
    return $ret;
  }

  function _grantSequenceKeys($deviceId, &$a, &$b) {
    global $cfgAdminBaseFolder;

    $sequenceFilename="$cfgAdminBaseFolder/.keys/.sequence/$deviceId.seq";
    if (!is_dir("$cfgAdminBaseFolder/.keys/.sequence"))
      mkdir("$cfgAdminBaseFolder/.keys/.sequence");

    if (!file_exists($sequenceFilename)) {
      $a=y_rand(0,10);
      $b=y_rand(-1,1);
      file_put_contents($sequenceFilename, "$a:$b");
    }
  }

  function _recreateDeviceKey($projectKey, $deviceId, $createSequence=false, &$a, &$b)  {
    global $cfgAdminBaseFolder;

    $key='';
    $projectKey=trim(strtoupper($projectKey));
    $deviceId=trim(strtoupper($deviceId));
    if (is_dir("$cfgAdminBaseFolder/.keys")) {
      if (is_dir("$cfgAdminBaseFolder/.keys/$projectKey")) {
        $key=generateKey($GLOBALS['keyCfgDeviceKeyLen']);
        file_put_contents("$cfgAdminBaseFolder/.keys/$projectKey/$deviceId", $key);

        if (!is_dir("$cfgAdminBaseFolder/.keys/.sequence"))
          mkdir("$cfgAdminBaseFolder/.keys/.sequence");
        $sequenceFilename="$cfgAdminBaseFolder/.keys/.sequence/$deviceId.seq";

        if ($createSequence) {
          _grantSequenceKeys($deviceId, $a, $b);
        } else {
          @unlink($sequenceFilename);
        }
      }
    }
    return $key;
  }

  function _generateSegmentsId($projectKey, $deviceId, $count) {
    global $cfgAdminBaseFolder;

    $ret=false;
    $projectKey=trim(strtoupper($projectKey));
    $deviceId=trim(strtoupper($deviceId));
    $lockName="$projectKey-$deviceId";

    if (lock($lockName)) {
      if (is_dir("$cfgAdminBaseFolder/.keys")) {
        if (is_dir("$cfgAdminBaseFolder/.keys/$projectKey")) {
          $ret=yNode::reserveSegments($projectKey, $deviceId, $count);
        }
      }
      unlock($lockName);
    }
    return $ret;
  }

  function _validateDeviceSequence($projectKey, $deviceId, $r, &$a, &$b) {
    global $cfgAdminBaseFolder;

    $ret=false;
    $projectKey=trim(strtoupper($projectKey));
    $deviceId=trim(strtoupper($deviceId));
    if (lock("$projectKey-$deviceId")) {
      if (is_dir("$cfgAdminBaseFolder/.keys")) {
        if (is_dir("$cfgAdminBaseFolder/.keys/$projectKey")) {
          $sequenceFilename="$cfgAdminBaseFolder/.keys/.sequence/$deviceId.seq";

          if (file_exists($sequenceFilename)) {
            $k=file_get_contents($sequenceFilename);
            $k=explode(":", $k);

            $ax=$k[0];
            $bx=$k[1];
            $c=$ax+$bx;

            if ($c==$r) {
              /* create a new sequence */
              $a=y_rand(-100,100);
              $b=y_rand(-7,7);
              file_put_contents($sequenceFilename, "$a:$b");
              $ret=true;
            } else {
              /* Destroy node sequence... This causes original and clone node to stop */
              // @unlink($sequenceFilename);
            }

          }

        }
      }
      unlock("$projectKey-$deviceId");
    }

    return $ret;
  }

  function _registerDeviceIntoProject($projectKey, $deviceId, &$a, &$b) {
    global $cfgAdminBaseFolder;

    $key='';
    $projectKey=trim(strtoupper($projectKey));
    $deviceId=trim(strtoupper($deviceId));

    if (!_verifyDeviceExistsInProject($projectKey, $deviceId)) {
      $x=y_uniqid();
      $ax=$bx=$x;
      $key = _recreateDeviceKey($projectKey, $deviceId, true, $ax, $bx);
      if ($ax!=$x) {
        $a=$ax;
        $b=$bx;
      }

    } else {
      $deviceIdFile="$cfgAdminBaseFolder/.keys/$projectKey/$deviceId";
      $key=file_get_contents($deviceIdFile);
    }
    if (!is_dir("$cfgAdminBaseFolder/.ips"))
      mkdir("$cfgAdminBaseFolder/.ips");
    file_put_contents("$cfgAdminBaseFolder/.ips/$deviceId.ip", getRemoteIp());

    return trim($key);
  }

  function _deviceId2deviceKey($projectKey, $deviceId) {
    global $cfgAdminBaseFolder;
    $ret="UNK";
    if (_projectExists($projectKey)) {
      $deviceIdFile="$cfgAdminBaseFolder/.keys/$projectKey/$deviceId";
      if (file_exists($deviceIdFile))
        $ret=file_get_contents($deviceIdFile);
    }
    return $ret;
  }

/*
  $projectId = "20d2f686566fdd235b7513aced0dd749";
  $deviceId = "ad63f3d80490a4045a2b57545ba4989e";
  $projectId = md5toId("$cfgAdminBaseFolder/.keys/",$projectId);
  $project="$cfgAdminBaseFolder/.keys/$projectId";
  $deviceId = md5toId($project, $deviceId);
  $originalDeviceKey=file_get_contents("$project/$deviceId");
  echo "\n$project : $deviceId : $originalDeviceKey\n";
*/



?>