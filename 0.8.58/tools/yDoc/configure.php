<?php
/*
    tools/yDoc/configure.php
    YeAPF 0.8.58-6 built on 2017-05-29 15:54 (-3 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2014-06-12 08:11:31 (-3 DST)
*/


  /*
   * This script generate the basic configuration file (yeapf.inc)
   * at current directory within paths and specification which helps
   * yeapf run faster
   *
   * 2011-12-20 - Corrections in search path to indetify where YeAPF is installed
   * 2012-09-20 - Revision in search path algorithm in order to identify user_dir paths
   * 2012-10-29 - In order to mantain distribution under the same tree, we need to modify the searchPath algorithm
   * 2013-02-12 - add cfgInitialVerb to insecure events
   * 2014-02-21 - invert the searchPath order.  preserve the search.path lines order
   * 2014-03-10 - Check if configure.php and yeapf.functions are using the same version
   */

  function getSgugPath($base)
  {
    $aux=array('sgug.cfg','dbAdmin');
    $res=$base;
    foreach ($aux as $a) {
      if ($res>'')
        $res.=',';
      $res.="$base/$a";
    }
    return $res;
  }

  function validatePath($aPath)
  {
    $ret=array();
    $aPath=array_unique($aPath);
    foreach($aPath as $folderName) {
      $folderName=str_replace("\\","/",trim($folderName));
      if ($folderName>'') {
        if (file_exists($folderName))
          array_push($ret,$folderName);
        else if (file_exists(realpath($folderName)))
          array_push($ret, realpath($folderName));
        else if (file_exists("$folderName/configure.php"))
          array_push($ret,$folderName);
      }
    }
    return $ret;
  }

  function whereis($aPath, $aFileName, $showPlace=false)
  {
    if ($showPlace)
      echoStep("Looking '<b>$aFileName</b>' in ".join('<b>;</b><br>&nbsp;&nbsp;&nbsp;&nbsp;',$aPath));
    $ret='';
    $aPath=array_reverse($aPath);
    foreach($aPath as $folderName) {
      if (strtolower(substr($folderName,0,5))=='http:')
        $folderName=substr($folderName,0,7).str_replace('//','/',substr($folderName,7));
      else
        $folderName=str_replace('//','/',$folderName);

      $fileName="$folderName/$aFileName";

      if (substr($fileName,0,5)=='http:') {
        $fAux=file($fileName);
        $fileExists=(!is_bool($fAux)) and (count($fAux)>0);
      } else
        $fileExists=file_exists($fileName);

      if ($fileExists) {
        $ret=$folderName;
        if ($showPlace)
          echoStep(" (absolute): $ret");
        break;
      } else if (file_exists(realpath($fileName))) {
        $ret=dirname(realpath($fileName));
        if ($showPlace)
          echoStep(" (realpath): $ret");
        break;
      } else if (file_exists($fileName)) {
        $ret=dirname($fileName);
        if ($showPlace)
          echoStep(" (path): $ret");
        break;
      }
    }
    if ($showPlace) {
      if ($ret>'') {
          echoStep("$aFileName = &#32;<span style='font-weight:800; color: #147; font-size: 11px'>$ret</span>");
      } else
        echoStep("<span class=err>$aFileName <b>NOT FOUND</b></span>");
    }
    return $ret;
  }

  /*
   * The webApp can be located at an absolute folder like '/var/www/html/MyProject'
   * or it can be at user home folder like '~myself/www/MyProject' that will be achieved by url 'http://localhost:~myself/MyProject'
   * We need some sort of single translation between '/home/myself/www/MyProject' and '~myself/www/MyProject'
   * that need to work with absolute folders too.
   * We need to avoid to use linked path too.
   *
   * homeFolder is filesystem absolute path to achieve this user base (/home/myself/www)
   * homeURL is the path as the user need to known when navigating (~myself)
   * relPath is for tell the user when using a symlink
   */
  function getMinPath(&$homeFolder, &$homeURL, &$relPath)
  {
    $homeURL=dirname($_SERVER['SCRIPT_NAME']);
    if (substr($homeURL,0,2)=='/~') {
      $absPath=getcwd();
      $relPath=explode('/',$homeURL);
      array_shift($relPath);
      array_shift($relPath);
      $relPath=join('/',$relPath);
      $homeFolder=substr($absPath,0,strlen($absPath)-strlen($relPath));

      $i=strlen($relPath)-1;
      $k=strlen($homeURL)-1;
      while (($i>0) && (substr($relPath,$i,1)==substr($homeURL,$k,1))) {
        $i--;
        $k--;
      }
      $homeURL=substr($homeURL,0,$k);
      $ret=is_dir($homeFolder);
    } else {
      $homeFolder='/';
      $ret=true;
    }

    return $ret;
  }

  function locateFile($dirBase, $fileName)
  {
    $ret='';
    //echo "$dirBase ($fileName)<br>";
    if (is_dir($dirBase) && is_readable($dirBase)) {
      $d=dir($dirBase);
      while (($ret=='') &&  ($entry=$d->read())) {
        $cName="$dirBase/$entry";
        if ($entry==$fileName) {
          $ret=$dirBase;
          break;
        } else
          if ((is_dir($cName)) && (substr($entry,0,1)!='.')) {
            if ($entry!='.distribution')
              $ret=locateFile($cName, $fileName);
          }
      }
      $d->close();
    }
    $ret=str_replace('//','/',$ret);
    return $ret;
  }

  function locateFileInPath($aPathArray, $fileName)
  {
    $ret='';
    foreach($aPathArray as $dirBase) {
      if ($ret=='') {
        $ret=locateFile($dirBase, $fileName);
      }
    }
    return $ret;
  }

  function openConfigFile()
  {
    global $configFile;

    echoStep("Opening config file '.config/yeapf.config'");
    $configFile=fopen('.config/yeapf.config','w');
    if ($configFile) {
      $date=date("Y-m-d");
      $time=date("G:i:s");
      fwrite($configFile,"<?php\n\n/* \n");
      fwrite($configFile," * yeapf.config\n");
      fwrite($configFile," * YeAPF 0.8.58-6 built on 2017-05-29 15:54 (-3 DST)\n");
      fwrite($configFile," * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com\n");
      fwrite($configFile," * YEAPF (C) 2004-2014 Esteban Dortta (dortta@yahoo.com)\n");
      fwrite($configFile," * This config file was created using configure.php\n");
      fwrite($configFile," * On $date at $time\n");
      fwrite($configFile," */\n\n");
    }
    return $configFile;
  }

  function writeConfigFile($itemName, $itemValue)
  {
    global $configFile;
    $itemValue=str_replace('\\','/',$itemValue);
    fwrite($configFile,"    \$yeapfConfig['$itemName']='$itemValue';\n");
  }

  function closeConfigFile()
  {
    global $configFile;
    echoStep("Closing config file");

    fwrite($configFile,"\n\n?>");
    fclose($configFile);
  }

  $curStep=0;
  function echoStep()
  {
    global $curStep;
    $back = debug_backtrace();
    $line = $back[0]['line'];

    $argList='';
    $args=func_get_args();
    foreach ($args as $a) {
      if ($argList>'')
        $argList.=' ';
      $argList.=$a;
    }
    $curStep++;
    echo "<div class=info>
            <div style='float:left; width: 40px;'>$curStep)</div>
            <div style='float:left; width: 760px; overflow: auto'> $argList&nbsp;<span style='color:#BBB'>$line</span></div>
          </div>";

    if (function_exists('_dump'))
      _dump("$curStep) $argList");
  }

  $url=getenv("QUERY_STRING");
  parse_str($url);

  ini_set('display_errors','0');
  error_reporting ('E_NONE');
  $yeapfLogFlags = 65535;
  $yeapfLogLevel = 10;

  echo "<style>body{padding:8px; font-size:12px; font-family: arial; } .info{margin:2px; padding-bottom:8px; width: 800px} .warn{border-top:dotted 1px #900; border-bottom:dotted 1px #900; color:#A86D00;  font-weight:800; margin:2px; padding:8px; background-color:#FFEAC0} .cpyrght{font-size:14px; border-bottom:solid 2px #BFBFBF; padding-bottom:4px} .code { font-family:Consolas,monospace;  background-color: #E5E5E5;  margin: 8px;  padding: 4px;  border: dotted 1px #7F7F7F} .err {background-color:#FFC0CB;  border-style:solid;  border-width:2px;  border-color:#FF0000; margin:32px; padding:32px; border-radius:4px} .errItem {border-bottom: dotted 1px #FF0000;  margin-bottom:4px; }</style>";

  echo "<div class=cpyrght><strong><big><I>tools/yDoc/configure.php</I></big></strong><br>
    YeAPF 0.8.58-6 built on 2017-05-29 15:54 (-3 DST)<br>
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com<br>
    2014-06-12 08:11:31 (-3 DST)</div>";

  if (!getMinPath($homeFolder, $homeURL, $relPath))
    die("<div class=err><b>$homeFolder</b> is not a real dir.<br>Probably '$relPath' is not a real path.<br>Maybe it's an alias or link<hr>Try again using an real path</div>");

  echoStep("<b>homeFolder</b>: '$homeFolder' is equivalent to homeURL: '$homeURL'\n");
  /*
   * YeAPF could be installed in any of these relative paths:
   *   includes/
   *   YeAPF/includes/
   *   lib/
   *   lib/YeAPF/
   *   lib/YeAPF/includes/
   * They could be absolute (DOCUMENT_ROOT) or relatives to the current appdir
   */

  $ySearchPath="includes;YeAPF/includes;YeAPF/.distribution/0.8.58/includes;lib;lib/YeAPF;lib/YeAPF/includes";
  if (file_exists('yeapf.path'))
    $ySearchPath=join(';',file('yeapf.path')).';'.$ySearchPath;
  $ySearchPath=explode(';',$ySearchPath);

  $ySearchWay=array("");
  $base=$_SERVER['SCRIPT_NAME'];
  while ($base>$homeFolder) {
    $base=dirname($base);
    $base=str_replace('\\','/',$base);
    $auxBase=explode('/',$base);
    array_shift($auxBase);
    $auxBase=join('/',$auxBase);
    array_push($ySearchWay, "$homeFolder$auxBase");
  }
  array_push($ySearchWay, $_SERVER["DOCUMENT_ROOT"]);

  echoStep("Checking 'search.path'");

  if (file_exists('search.path')) {
    $auxSearchPath=file('search.path');
    foreach($auxSearchPath as $asp) {
      $asp=trim($asp);
      if ((substr($asp,0,1)!=';') && (substr($asp,0,1)!='#'))
        array_push($ySearchWay, $asp);
    }
  }

  ini_set('display_errors','1');
  error_reporting (E_ALL);

  foreach($ySearchWay as $way) {
    $auxSearchPath=$way;
    foreach($ySearchPath as $path) {
      $auxSearchPath.=";";
      if ($way>'')
        $auxSearchPath.="$way/";
      $auxSearchPath.=trim($path);
    }
    $__PL__=whereis(explode(';',$auxSearchPath),'yeapf.db.php',file_exists('flag.dbgphp.configure'));
    if ($__PL__>'')
      break;
  }

  $auxDefaultTimeZone = @date_default_timezone_get();
  echoStep("TimeZone: $auxDefaultTimeZone");
  if ($auxDefaultTimeZone!='UTC') {

    function verifyConfigFolder($folderName) {
      echoStep("Granting '$folderName' folder");
      $canConfig=false;
      if (is_link($folderName)) {
        $folderNameTarget=readlink($folderName);
        echoStep("<span class=warn>folder '$folderName' is a link that points to '$folderNameTarget'</span>");
        $folderName=$folderNameTarget;
      }

      if (is_file($folderName))
        die("<ul class=err>$folderName is a file and we need to create a folder with that name</ul>");
      if (!is_dir($folderName)) {
        $canConfig = is_writable(".");
        if ($canConfig)
          $canConfig = mkdir($folderName, 0766) or die("<p><ul class=err>Not enough rights to create '<b>$folderName</b>' folder</ul></p>");
      } else
        $canConfig = is_writable($folderName);
      return $canConfig;
    }

    $canConfig = verifyConfigFolder("lock") && verifyConfigFolder(".config");

    if ($canConfig) {
      echoStep("Loading <em>$__PL__/yeapf.debug.php</em>");
      (@include_once ($__PL__."/yeapf.debug.php")) || die("<ul class=err>Err loading $__PL__/yeapf.debug.php</ul>");

      if (function_exists("yeapfVersion"))
        if (("0.8.58") != yeapfVersion())
          die("<ul class=err>Your configure version is '0.8.58' while your installed version is '".yeapfVersion()."'<br><small>You can use 'yeapf.path' file to indicate where is your YeAPF distribution</small></ul>");


      echoStep("canConfig=".intval($canConfig)."<br>Loading <em>$__PL__/yeapf.locks.php</em>");
      (@include_once ($__PL__."/yeapf.locks.php")) || die("Err loading $__PL__/yeapf.locks.php");


      /*
      ini_set('display_errors','0');
      error_reporting (E_NONE);
      */
      $cfgSQLiteInstalled = class_exists('SQLiteDatabase');
      echoStep("SQLiteDatabase installed: ".intval($cfgSQLiteInstalled));

      if (!file_exists('includes/security.php'))
        echoStep("<div class=warn>'includes/security.php' was not found!<br>Verify appFolderName.def file</div>");
      $lockCanBeCreated=0;
      if ((is_writable('./')) && (touch('flag.test'))) {
        $lockCanBeCreated=1;
        unlink('flag.test');

        if (!is_dir("logs")) {
          echoStep("Creating '<em>logs</em>' folder");
          mkdir("logs", 0764, true);
        }
        if (!is_dir(".config")) {
          echoStep("Creating '<em>.config</em>' folder");
          mkdir(".config", 0764, true);
        }

        echoStep("Trying to create 'configure' lock");

        if (lock('configure')) {
          echoStep("Lock subsystem working well");
          $lockCanBeCreated=2;

          $md5Files=array('body.php', 'index.php', 'configure.php', 'search.path');
          $myMD5='';
          foreach($md5Files as $aFileName)
            if (file_exists($aFileName))
              $myMD5.=join('', file($aFileName));
          $myMD5=md5($myMD5);
          if ((file_exists('configure.md5')) && (!is_writable('configure.md5')))
            die("<div class=err>Impossible to write on 'configure.md5'</div>");
          else
            file_put_contents('configure.md5',$myMD5);

          $d=dir('.');
          while (false !== ( $entry=$d->read() ) ) {
            if (substr($entry,0,19)=='yeapf.config.files.') {
              echoStep("Deleting $entry");
              @unlink($entry);
            }
          }

          foreach(glob(".config/yeapf.config*") as $entry) {
            echoStep("Deleting $entry");
            @unlink($entry);
          }

          $d=dir('lock');
          while (false !== ( $entry=$d->read() ) ) {
            if (substr($entry,0,1)!='.')
              if ($entry!='configure') {
                echoStep("Deleting lock/$entry");
                @unlink("lock/$entry");
              }
          }

          $server_IP=$_SERVER["SERVER_ADDR"];
          $user_IP=$_SERVER["REMOTE_ADDR"];

          if (!openConfigFile())
            die("IMPOSSIVEL CRIAR ARQUIVO DE CONFIGURA&Ccedil;&Atilde;O");

          $_MY_CONTEXT_=getcwd();
          // $_MYSELF_=str_replace('\\','/',$_SERVER["DOCUMENT_ROOT"].$_SERVER["REQUEST_URI"]);
          $_MYSELF_=str_replace('\\','/',dirname($_SERVER["REQUEST_URI"]));
          if (!(($aux1=strpos($_MYSELF_,'?'))===FALSE))
            $_MYSELF_=substr($_MYSELF_,0,$aux1);
          $_MYSELF_=str_replace('//','/',$_MYSELF_);

          if (substr($_MYSELF_,0,1)=='/')
            $_MYSELF_=substr($_MYSELF_,1);
          writeConfigFile('context', $_MY_CONTEXT_);
          writeConfigFile('myself',$_MYSELF_);

          $_THIS_SERVER_=str_replace('\\','/',$_SERVER["DOCUMENT_ROOT"]);
          $_THIS_SERVER_=str_replace('//','/',$_THIS_SERVER_);
          writeConfigFile('root',$_THIS_SERVER_);

          $_httpHost_='http://'.$_SERVER["HTTP_HOST"];
          if ((isset($_SERVER["HTTP_REFERER"])) && ($_SERVER["HTTP_REFERER"]>''))
            $_httpReferer_=substr($_SERVER["HTTP_REFERER"], strlen($_httpHost_));
          else
            $_httpReferer_=$_SERVER["REQUEST_URI"];
          $_httpReferer_=$_SERVER["DOCUMENT_ROOT"].substr($_httpReferer_,0,strlen($_httpReferer_)-1);
          if ($pp=strpos($_httpReferer_,'?'))
            $_httpReferer_=substr($_httpReferer_,0,$pp);
          if (strpos($_httpReferer_,'.')>0)
            $_httpReferer_=dirname($_httpReferer_);
          $_httpReferer_=str_replace('//','/',$_httpReferer_);
          writeConfigFile("httpReferer",$_httpReferer_);
          writeConfigFile("httpHost",$_httpHost_);

          $yeapfDB='';

          /*
          $yeapfDB.=','.getSgugPath($_MYSELF_);
          $yeapfDB.=','.getSgugPath(dirname($_MYSELF_));
          */
          $yeapfDB.=','.getSgugPath(dirname("$_httpReferer_"));

          $auxDir=dirname(dirname("$_httpReferer_"));
          if ($auxDir!='/home')
            $yeapfDB.=','.getSgugPath($auxDir);


          $yeapfDB=str_replace('\\','/',$yeapfDB);
          $yeapfDB=str_replace('//','/',$yeapfDB);

          $yeapfDB.=','.getSgugPath($_httpReferer_);
          $yeapfDB.=','.getSgugPath(dirname(getcwd()));
          $yeapfDB.=','.getSgugPath(getcwd());

          $yeapfDB=str_replace('\\', '/', $yeapfDB);
          $yeapfDB=array_unique(explode(',',$yeapfDB));

          $sgugIni=whereis($yeapfDB, 'sgug.ini', true);
          if ((isset($destroydb)) && ($destroydb=='yes')) {
            if (file_exists("$sgugIni/sgug.ini"))
              unlink("$sgugIni/sgug.ini");
            $sgugIni='';
            echoStep("<div class=warn>Destroying DB connection</div>");
          }

          if ($sgugIni>'') {
            $sgugIni="$sgugIni/sgug.ini";
            $yeapfDB_ini='';
          } else
            $yeapfDB_ini=whereis($yeapfDB, 'yeapf.db.ini');

          $yeapfDB_configured = false;
          if ($yeapfDB_ini>'') {
            (@include_once $__PL__."/yeapf.dbText.php") || die("Error loading $__PL__/yeapf.dbText.php");

            $newSgug=($sgugIni=='');

            if ($newSgug)
              $sgugIni="$yeapfDB_ini/sgug.ini";

            $setupIni=createDBText($sgugIni, true);

            $yeapfINI=parse_ini_file("$yeapfDB_ini/yeapf.db.ini",true);

            $activeCount=0;
            $activeAppName='';

            function verifyActiveApp()
            {
              global $curAppName, $activeCount, $activeAppName;

              if ($activeCount==1)
                $activeAppName=$curAppName;
              else if ($activeCount>1)
                die("Tem mais de uma entrada de banco de dados ativa\n$curAppName, $activeAppName");
            }

            if (($setupIni->locate("active",1))==$dbTEXT_NO_ERROR)
              $curAppRegistry=$setupIni->getValue('appRegistry');
            else
              $curAppRegistry=-1;

            foreach($yeapfINI as $key => $val)  {
              // get rootPassword from .ini and codifies it for sgug.ini
              $rootFirstPassword=trim($yeapfINI[$key]['cfgRootFirstPassword']);
              if ($rootFirstPassword=='')
                $rootFirstPassword='masterkey';
              $rootFirstPassword=md5($rootFirstPassword);
              $yeapfINI[$key]['cfgRootFirstPassword']=$rootFirstPassword;
            }

            $appRegistryList=array();

            foreach($yeapfINI as $key => $val)  {

              // try to update current SGUG.INI entry
              $appNameKey=$key;
              if (($setupIni->locate("appName",$appNameKey))!=$dbTEXT_NO_ERROR)
                $setupIni->addRecord();

              $curAppName='';

              foreach($val as $k1 => $v1) {
                if($v1>'') {
                  $setupIni->addField($k1);

                  if ($k1 == 'dbConnect')
                    $v1 = $v1>0?'yes':'no';

                  $setupIni->setValue($k1,$v1);

                  if ($k1=='appRegistry')
                    if (!(in_array($v1,$appRegistryList)))
                      array_push($appRegistryList, $v1);

                  if ($k1=='appName') {
                    $curAppName=$v1;
                    verifyActiveApp();
                  }

                  if ($k1=='active') {
                    $activeCount++;
                    verifyActiveApp();
                  }
                } else if (($k1=='dbType') || ($k1=='dbServer') || ($k1=='dbName')) {
                  if ($newSgug)
                    unlink($sgugIni);
                  die("<div class=err><b>yeapf.db.ini</b> malformed<br>**** $k1 needs to be defined</div>");
                }
              }

            }

            $setupIni->addField('yUserCount');
            $auxYUserCount=$setupIni->getValue('yUserCount');
            if (!is_numeric($auxYUserCount) || ($auxYUserCount<0))
              $setupIni->setValue('yUserCount',0);

            if (($curAppRegistry>=0) && ($activeCount>1)) {
              if (!(in_array($curAppRegistry, $appRegistryList))) {
                $setupIni->locate("appRegistry",$curAppRegistry);
                $setupIni->setValue("active",0);
              }
            }

            $yeapfDB_configured = $setupIni->commit();

          } else
            $yeapfDB_configured = true;

          if ($yeapfDB_configured) {
            writeConfigFile("yeapfDB",$sgugIni);

            if (ini_get("open_basedir")>'')
              $searchPath = explode( PATH_SEPARATOR, str_replace('\\','/',ini_get('open_basedir')) );
            else {
              $searchPath = explode( PATH_SEPARATOR, str_replace('\\','/',ini_get('include_path')) );
              foreach($searchPath as $k=>$v)
                if (strpos($v,'SGUG')!==false)
                  unset($searchPath[$k]);
            }

            $cfgSOAPInstalled=function_exists("is_soap_fault");

            array_push($searchPath,'../includes');
            array_push($searchPath,'../../includes');
            if (!$cfgSOAPInstalled) {
              array_push($searchPath,'includes/nuSOAP');
              array_push($searchPath,'../includes/nuSOAP');
              array_push($searchPath,'../../includes/nuSOAP');
            }
            if (ini_get("open_basedir")=='') {
              array_push($searchPath,$_MYSELF_);
              // array_push($searchPath,'./');
              array_push($searchPath,$_SERVER["DOCUMENT_ROOT"].'/lib');
              array_push($searchPath,$_SERVER["DOCUMENT_ROOT"].'/lib/nuSOAP');
              array_push($searchPath,'lib');
              array_push($searchPath,'../lib');
              array_push($searchPath,'../../lib');
              if (!$cfgSOAPInstalled) {
                array_push($searchPath,'lib/nuSOAP');
                array_push($searchPath,'../lib/nuSOAP');
                array_push($searchPath,'../../lib/nuSOAP');
              }
              if (file_exists('flags/flag.production')) {
                array_push($searchPath,'../../YeAPF/includes');
                array_push($searchPath,'YeAPF/includes');
                array_push($searchPath,'..');
              }
              array_push($searchPath,'includes');
              if (file_exists('flags/flag.production'))
                array_push($searchPath,'../YeAPF/includes');
              array_push($searchPath,'includes');
              array_push($searchPath,'imagens');
              array_push($searchPath,'images');
            }

            if ($homeFolder!='/') {
              array_push($searchPath,$homeFolder.'lib');
              array_push($searchPath,$homeFolder.'lib/nuSOAP');
            }
            array_unshift($searchPath,'mdForms');

            $searchPath=array_unique($searchPath);
            $aux='';
            foreach($searchPath as $asp)
              $aux.="<span style='padding-left:4px; padding-right:4px; border-left: dotted 1px #8A8A8A'>$asp</span>";
            echoStep("Search path: $aux");
            if (!$cfgSOAPInstalled) {
              $nusoapPath=whereis($searchPath,'nusoap.php');
              if ($nusoapPath=='') {
                unlock('configure');
                die("** nusoap.php not found");
              }
            }

            if (file_exists('search.path')) {
              $auxSearchPath=file('search.path');
              $auxPath = array();
              foreach($auxSearchPath as $asp) {
                if ((substr($asp,0,1)!=';') && (substr($asp,0,1)!='#'))
                  array_unshift($auxPath, $asp);
              }
              foreach($auxPath as $asp) {
                array_unshift($searchPath, $asp);
              }
            }

            array_unshift($searchPath, $_SERVER["DOCUMENT_ROOT"].'/lib/YeAPF');
            array_unshift($searchPath, $_SERVER["DOCUMENT_ROOT"].'/YeAPF');

            $searchPath=array_unique($searchPath);

            $auxPath=locateFileInPath($searchPath, "yeapf.js", true);
            if ($auxPath=='')
              $auxPath=locateFile($_SERVER["DOCUMENT_ROOT"].'/YeAPF',"yeapf.js");
            array_unshift($searchPath,$auxPath);
            array_unshift($searchPath,"$auxPath/develop");

            $auxPath=locateFileInPath($searchPath, "yeapf.develop.php", true);
            if ($auxPath=='')
              $auxPath=locateFile($_SERVER["DOCUMENT_ROOT"].'/YeAPF/develop',"yeapf.develop.php");
            array_unshift($searchPath, $auxPath);
            array_unshift($searchPath, $__PL__);

            $searchPath=join(';',validatePath($searchPath));

            writeConfigFile("searchPath",$searchPath);
            if (!$cfgSOAPInstalled)
              writeConfigFile("nusoapPath",$nusoapPath);

            writeConfigFile("homeURL",$homeURL);
            writeConfigFile("homeFolder",$homeFolder);

            writeConfigFile("yeapfPath",$__PL__);
            closeConfigFile();

            // ??? OBSOLETO
            // fwrite($f,"if (\"\$imBuildForm\"=='Y') chdir('$_MY_CONTEXT_');\n\n\t");

            if ((file_exists('yeapf.php')) && (!is_writable('yeapf.php')))
              die("<div class=err>Impossible to write to 'yeapf.php'</div>");
            $f=fopen("yeapf.php", "w");

            $yeapfStub = '
            /*
            * yeapf.php
            * (C) 2004-2014 Esteban Daniel Dortta (dortta@yahoo.com)
            */

            function _yLoaderDie($reconfigureLinkEnabled)
            {
              $script=basename($_SERVER["PHP_SELF"]);
              $isXML=(strpos("query.php",$script)!==false);
              $isCLI=(php_sapi_name() == "cli");

              $args=func_get_args();
              array_shift($args);
              if ((!$isXML) && (!$isCLI))
                $msg=join("<br>",$args);
              else
                $msg=join("\n",$args);
              $lineMsg=join(". ", $args);
              syslog(LOG_INFO,$script." (running at ".getcwd().")"." - ".$lineMsg);
              if (!$isXML) {
                if ($isCLI) {
                  echo "\n$msg\n";
                } else {
                  header("Content-Type: text/html; charset=UTF-8");
                  echo "<style> .err {background-color:#FFC0CB; border-style:solid; border-width:2px; border-color:#FF0000;margin:32px;padding:32px;border-radius:4px}</style>";
                  if ($reconfigureLinkEnabled)
                  $doConfig="<div>Click <b><a href=\"configure.php\">here</a></b> to configure<br></div>";
                  $copyrightNote="<small><em>YeAPF (C) 2004-2014 Esteban Dortta</em></small>";
                  echo "<div class=err><div>$msg</div>$doConfig$copyrightNote</div>";
                }
              }
              die("");
            }

            function _yeapf_getFileValue($fileName)
            {
              $aux1=$aux2=0;
              if (file_exists($fileName))
                $aux1=join("",file($fileName));
              if (file_exists("flags/".$fileName))
                $aux2=join("",file("flags/".$fileName));
              $ret=intval($aux1) | intval($aux2);
              return $ret;
            }

            if (file_exists("flags/flag.dbgloader")) error_log(basename(__FILE__)." ".date("i:s").": preparing flags\n",3,"logs/c.log");

            $yeapfLogFlags=_yeapf_getFileValue("debug.flags");
            $yeapfLogLevel=_yeapf_getFileValue("debug.level");
            $yeapfDebugAll=intval(file_exists("flags/flag.dbgphp"))?1:0;
            $yeapfPauseAfterClickFlag=_yeapf_getFileValue("flags/flag.pause");

            $logOutput=0;  // default is to not produce debug output

            if ($yeapfDebugAll) {
              ini_set("display_errors","1");
              error_reporting (E_ALL);
            }

            if (file_exists("flags/development.debug"))
             $developmentStage = join("",file("flags/development.debug"));

            $jsDumpEnabled = intval(file_exists("flags/debug.javascript")) || isset($jsDumpEnabled)?intval($jsDumpEnabled):0;
            $aDebugIP = trim(file_exists("flags/debug.ip")?join(file("flags/debug.ip")):"");

            if (file_exists("flags/flag.dbgloader")) error_log(basename(__FILE__)." ".date("i:s").": loading config files\n",3,"logs/c.log");

            if (file_exists(dirname(__FILE__)."/.config/yeapf.config"))
              (@include_once dirname(__FILE__)."/.config/yeapf.config") || _yLoaderDie(true, dirname(__FILE__)."Error loading /.config/yeapf.config");
            else
              _yLoaderDie(true, dirname(__FILE__)."/.config/yeapf.config not found");

            $__yeapfPath=$yeapfConfig["yeapfPath"];
            $__yeapfContext=$yeapfConfig["context"];
            $__yeapfCWD = getcwd();
            $__yeapfCWD = str_replace("\\\\","/",$__yeapfCWD);
            if ($__yeapfContext != $__yeapfCWD) _yLoaderDie(true,"YeAPF running out of original context or is missconfigured\n * $__yeapfCWD differs from $__yeapfContext");

            $auxAppFolderName="";
            if (file_exists("appFolderName.def"))
              $auxAppFolderName="appFolderName.def";

            if ($auxAppFolderName>"") {
              $appFolder=file($auxAppFolderName);
              while (count($appFolder)<3)
                $appFolder[count($appFolder)]="";
              $appFolderName=$appFolder[0];
              $appFolderRights=intval($appFolder[1]);
              $appFolderInsecureEvents=trim($appFolder[2]);
            } else {
              $appFolderName=basename(getcwd());
              $appFolderRights=0;
              // md5("*.") = "3db6003ce6c1725a9edb9d0e99a9ac3d"
              $appFolderInsecureEvents="3db6003ce6c1725a9edb9d0e99a9ac3d";
            }
            // in case cfgInitialVerb is defined, we need to put this verb as insecure
            if (isset($cfgInitialVerb)) {
              if ($appFolderInsecureEvents>"")
                $appFolderInsecureEvents.=",";
              $appFolderInsecureEvents.=md5($cfgInitialVerb.".");
            }
            unset($auxAppFolderName);
            if (file_exists("flags/flag.dbgloader")) error_log(basename(__FILE__)." ".date("i:s").": loading yeapf.functions.php\n",3,"logs/c.log");
            (@include_once $__yeapfPath."/yeapf.functions.php") || (_yLoaderDie("$__yeapfPath/yeapf.functions.php not found"));

            if (file_exists("flags/flag.dbgloader")) error_log(basename(__FILE__)." ".date("i:s").": verifiyng yeapf version\n",3,"logs/c.log");

            if (function_exists("yeapfVersion"))
              if (("0.8.58") != yeapfVersion())
                _yLoaderDie(true, "Your configure version is \'0.8.58\' while your installed version is \'".yeapfVersion()."\'");
            if (!isset($appName))
              $appName = "dummy";
            $yeapfConfig["searchPath"]=$appName.";".$yeapfConfig["searchPath"];
            set_include_path(get_include_path().":".str_replace(";",":",$yeapfConfig["searchPath"]));
            ';

            fwrite($f,"<?php\n$yeapfStub");

            // appFolder
            $appFolderLoader = '

            $md5Files=array("body.php", "index.php", "configure.php", "search.path");
            $configMD5="";
            foreach($md5Files as $aFileName)
              if (file_exists($aFileName))
                $configMD5.=join("", file($aFileName));
            $configMD5=md5($configMD5);

            $savedConfigMD5 = join("",file("configure.md5"));
            if ((file_exists("configure.php")) && ($configMD5 != $savedConfigMD5)) {

              _yLoaderDie(true, \'YeAPF not configured\');
            }
            ';

            fwrite($f,$appFolderLoader);

            // appScript and appFolderScript
            $appScriptLoader = '
            if (file_exists("flags/flag.dbgloader")) error_log(basename(__FILE__)." ".date("i:s").": loading application script\n",3,"logs/c.log");
            // load application script
            $appWD=basename(getcwd());
            // drop version info as in "customers-3.5" keeping with "costumers"
            $appWD=substr($appWD,0,strpos($appWD."-","-"));
            $__scriptList = array("$appWD.php", "$appWD.rules.php",
                                  "$appName.php", "$appName.$appWD.php",
                                  "$appName.rules.php", "$appName.$appWD.rules.php", "rules.php",
                                  bestName("$appName.security.php"),
                                  "includes/security.php");
            foreach($__scriptList as $__scriptName) {
              $__scriptName = bestName($__scriptName);
              if ((file_exists($__scriptName)) && (!is_dir($__scriptName))) {
                error_log("Loading $__scriptName ... ",3,"logs/yeapf.loader.log");
                $t1=decimalMicrotime();
                _dumpY(1,1,"Loading $__scriptName");
                (@include_once "$__scriptName") or _yLoaderDie(true, dirname(__FILE__)."Error loading $__scriptName");
                $t2=decimalMicrotime()-$t1;
                error_log("    wasted time: $t2\n",3,"logs/yeapf.loader.log");
              }
            }

            ';

            fwrite($f,$appScriptLoader);

            fwrite($f,"\n          _dumpY(1,1,'yeapf loaded');\n\n");

            $appStarter = '
            yeapfStage("click");
            yeapfStage("registerAppEvents_$appWD");
            yeapfStage("registerAppEvents");
            ';
            fwrite($f,$appStarter);

            fwrite($f,"\n?>");
            fclose($f);

            $referer_uri=isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'./';
            if ($_SERVER['REQUEST_URI']==$_SERVER['SCRIPT_NAME'])
              $referer_uri='./';

            if (file_exists('develop.php'))
              $developLink="Click <a href='develop.php'>here</a> to start developing your app.<br>";
            else
              $developLink='';

            echoStep("<div style='background: #90EE90; border-style: solid; border-width: 2px; border-color: #00BD00; padding: 32px'><big>YeAPF well configured!</big><br>Click <a href='$referer_uri'>here</a> to go back.<br> Click <a href='index.php'>here</a> to start your app.<br>$developLink<small style='margin: 16px'>If you wish to destroy database connection an recreate it, click <a href='configure.php?destroydb=yes'>here</a><br><i>It will preserve your database data but will remove all other definitions except the one contained in<em>yeapf.db.ini</em></i></small></div>");

            $aux=join(file('.config/yeapf.config'),'<br>');
            echoStep("<div class=code>$aux</div>");
            $aux=join(file('yeapf.php'),'<br>');
            echoStep("<div class=code>$aux</div>");

          } else
            echoStep("<BR>The dbConnection could not be written.<br>Check your access rights to '".getcwd()."' folder ");
          unlock('configure');
        } else {
          echoStep("<div>LOCK CANNOT BE CREATED</div>");
        }
      }

      if ($lockCanBeCreated<2) {
        echo "<div class=err>";

        echo "<div class=errItem>Was not possible to lock the system (stage $lockCanBeCreated. LOCK_VERSION=$LOCK_VERSION)<br>Check your installation</div>";
        if ($lockCanBeCreated<1)
          echo "<div class=errItem>You have not enough rights to write to the filesystem on <b>".getcwd()."</b><br>Please give write, read and execution rights to this folder and try again</div>";

        echo "</div>";
      }

    } else {
      echoStep("<span class=err>Was not possible to create support folders</span>");
      echoStep("<span class=err>Your main folder ($homeURL) must to have enough rights to be written by httpd server</span>");
    }

  } else {
    echoStep("<span class=err>Define PHP timeZone before configure</span>");
    echoStep("<span class=err>UTC is not accepted as default timeZone</span>");
  }
?>
