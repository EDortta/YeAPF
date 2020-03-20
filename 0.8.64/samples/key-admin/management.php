<?php

  require_once dirname(__FILE__)."/keys.php";

  function wmanagement($a)
  {
    global $devSession, $cfgAdminBaseFolder, $cfgHtPasswdRequired, $keyCfgDeviceIdLen, $cfgIdServerURL;

    $countLimit=50;

    extract(xq_extractValuesFromQuery());
    $xq_start=isset($xq_start)?intval($xq_start):0;

    $ret=array(
      'devSession' => $devSession
    );

    if ($cfgHtPasswdRequired=='yes') {

      switch($a) {
        case 'createProject':
          /* garante a pasta contendo as .keys */
          if (!is_dir("$cfgAdminBaseFolder/.keys"))
            mkdir("$cfgAdminBaseFolder/.keys");
          $newProjectKey=generateKey($GLOBALS['keyCfgProjectKeyLen']);
          mkdir("$cfgAdminBaseFolder/.keys/$newProjectKey");
          $projectInfo=array(
            'key'    => "$newProjectKey",
            'name'   => "$newProjectName",
            'status' => "stop"
          );
          file_put_contents("$cfgAdminBaseFolder/.keys/$newProjectKey/.info", json_encode($projectInfo));

          $ret=array($newProjectKey);
          break;

        case 'removeProject':


          break;

        case 'cfgRegisterDevice':
          _registerDeviceIntoProject($projectKey, $deviceId);
          break;

        case 'verifyDeviceBond':
          $validKey=_verifyDeviceBond($projectKey, $deviceId, $deviceKey);
          $ret=array("validKey"=>$validKey);
          break;

        case 'unboundDevice':
          break;

        case 'getProjectKeys':
          $projectKey=trim(strtoupper($projectKey));
          chdir("$cfgAdminBaseFolder/.keys/$projectKey");
          $devices=glob("*");
          $ret=array();
          foreach($devices as $d) {
            if (substr($d,0,1)!='.') {
              $deviceInfo=__getDeviceInfo($projectKey, $d);
              $key=file_get_contents($d);
              $ret[]=array( "deviceId" =>$d,
                            "deviceKey"=>$deviceInfo['key'],
                            "enabled"=>$deviceInfo['enabled'],
                            "status"=>$deviceInfo['status'],
                            "statusDescription"=>$deviceInfo['statusDescription']
                          );
            }
          }
          break;

        case 'generateKey':
          break;

        case 'getProjects':
          if (!is_dir("$cfgAdminBaseFolder/.keys"))
            mkdir("$cfgAdminBaseFolder/.keys");
          chdir("$cfgAdminBaseFolder/.keys");
          $projetos=glob("*");
          $ret=array();
          foreach($projetos as $p) {
            if (file_exists("$p/.info")) {
              $info=file_get_contents("$p/.info");
              $ret[]=json_decode($info);
            } else {
              $ret[]=array(
                "key" => $p,
                "name"=>"Sem Nome",
                "status"=>"stop"
              );
            }
          }
          break;

        case 'changeProjectStatus':
          $ret=array('status'=>'unknown');
          $projectKey=trim(strtoupper($projectKey));
          $projectStatus=trim(strtolower($projectStatus));
          if (strpos("*stop*play*pause*finished*", "*$projectStatus*")!==false) {

            if (!file_exists("$cfgAdminBaseFolder/.keys/$projectKey/.info")) {
              /* criar um projeto vazio (apenas para compatibilizar com projetos anteriores )*/
              $ret['status']='stop';
              $ret['key']=$projectKey;
              $ret['name']="Sem nome";
            } else {
              $info=file_get_contents("$cfgAdminBaseFolder/.keys/$projectKey/.info");
              $ret=json_decode($info, true);
            }

            $ret["status"]=$projectStatus;
            $info=json_encode($ret);
            file_put_contents("$cfgAdminBaseFolder/.keys/$projectKey/.info", $info);

          }

          break;

        case 'createDevice':
          $projectKey=trim(strtoupper($projectKey));
          $deviceId=rawChars(trim(str_replace(" ","",strtoupper(decodeURI($deviceId)))));
          if (substr($deviceId,0,4)==$USR.'_')
            $deviceId=substr($deviceId, 4);
          if ($deviceId=='')
            $deviceId=generateKey($GLOBALS['keyCfgDeviceIdLen']);
          if ($USR>'')
            $deviceId=$USR.'_'.$deviceId;
          _recreateDeviceKey($projectKey, $deviceId, false);
          break;

        case 'recreateDeviceKey':
          _recreateDeviceKey($projectKey, $deviceId, false);
          break;

        case 'removeDeviceKey':
          $projectKey=trim(strtoupper($projectKey));
          $deviceId=trim($deviceId);
          if (is_dir("$cfgAdminBaseFolder/.keys")) {
            if (is_dir("$cfgAdminBaseFolder/.keys/$projectKey")) {
              @unlink("$cfgAdminBaseFolder/.keys/$projectKey/$deviceId");
              @unlink("$cfgAdminBaseFolder/.keys/.sequence/$deviceId.seq");
              @unlink("$cfgAdminBaseFolder/.ips/$deviceId.ip");
            }
          }
          break;

        case 'toggleDevice':
          $projectKey=trim(strtoupper($projectKey));
          $deviceId=trim($deviceId);
          $deviceInfo = __getDeviceInfo($projectKey, $deviceId);
          $deviceInfo['enabled'] = ($deviceInfo['enabled']==1)?0:1;
          __setDeviceInfo($projectKey, $deviceId, $deviceInfo);
          break;

        case 'getLastModifiedData':

          break;

        case 'getProjectStats':

          break;

      }

    }

    if ($a=='canWork') {
      // cfgIdServerURL
      $validServerURL = (isset($cfgIdServerURL)) &&
                        ($cfgIdServerURL>'') &&
                        (filter_var($cfgIdServerURL, FILTER_VALIDATE_URL) === true);

      $ret['canWork']=((!$validServerURL) &&
                       (isset($keyCfgDeviceIdLen)) &&
                       (($cfgHtPasswdRequired=='yes')))?'yes':'no';
      $ret['user']=$_SERVER['REMOTE_USER'];
      if ($ret['canWork']=='no') {
        if ($validServerURL) {
          $ret['reason'].="'cfgIdServerURL' is a valid URL ($cfgIdServerURL) and need to be undefined\n";
        }

        if (!isset($keyCfgDeviceIdLen))
          $ret['reason'].='keys.config.php unconfigured at '.dirname(__FILE__)."\n";

        if (($cfgHtPasswdRequired!='yes'))
          $ret['reason'].="'cfgHtPasswdRequired' need to be defined as  'yes'\n";

      }
    }
    return jr_produceReturnLines($ret);
  }

  function rmanagement($a)
  {
    $jsonRet=wmanagement($a);
    echo produceRestOutput($jsonRet);
  }

?>
