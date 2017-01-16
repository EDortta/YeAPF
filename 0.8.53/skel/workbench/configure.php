<?php
/*
    skel/workbench/configure.php
    YeAPF 0.8.53-65 built on 2017-01-16 14:41 (-2 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2017-01-16 14:41:33 (-2 DST)
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

  $isCLI=(php_sapi_name() == "cli");
  $curStep=0;

  function sayStep()
  {
    global $curStep, $isCLI;
    if ($isCLI)
      $gt=" -> ";
    else
      $gt=" -&gt; ";
    $back = debug_backtrace();
    $line = $back[0]['line'];
    if (isset($back[1]))
      $line1=$back[1]['line']." $gt ";
    else
      $line1="";

    $argList='';
    $args=func_get_args();
    foreach ($args as $a) {
      if ($argList>'')
        $argList.=' ';
      $argList.=$a;
    }
    $curStep++;
    if ($isCLI) {
      $argList=strip_tags($argList, "<BR><p><div>");
      $argList=str_ireplace("<br>","\n",$argList);
      $argList=str_ireplace("<p>","\n",$argList);
      $argList=str_ireplace("</p>","\n",$argList);
      $argList=str_ireplace("<div>","\n",$argList);
      $argList=str_ireplace("</div>","\n",$argList);
      $argList=str_replace("\n\n","\n", $argList);
      $argList=strip_tags($argList);
      $argList=str_ireplace("&nbsp;"," ",$argList);
      $ret="$line1$line: $argList\n";
    } else {
      $ret="<div class=info>
              <div style='float:left; width: 40px;'>$curStep)</div>
              <div style='float:left; width: 760px; overflow: auto'> $argList&nbsp;<span style='color:#BBB'>$line1$line</span></div>
            </div>";
    }

    if (function_exists('_dump'))
      _dump("$curStep) $argList");
    return $ret;
  }

  function echoStep() 
  {
    global $curStep, $isCLI, $debugSteps;
    if ($isCLI)
      $gt=" -> ";
    else
      $gt=" -&gt; ";
    $back = debug_backtrace();
    $line = $back[0]['line'];
    if (isset($back[1]))
      $line1=$back[1]['line']." $gt ";
    else
      $line1="";

    $argList='';
    $args=func_get_args();
    foreach ($args as $a) {
      if ($argList>'')
        $argList.=' ';
      $argList.=$a;
    }
    $curStep++;
    if ($isCLI) {
      $argList=strip_tags($argList, "<BR><p><div>");
      $argList=str_ireplace("<br>","\n",$argList);
      $argList=str_ireplace("<p>","\n",$argList);
      $argList=str_ireplace("</p>","\n",$argList);
      $argList=str_ireplace("<div>","\n",$argList);
      $argList=str_ireplace("</div>","\n",$argList);
      $argList=str_replace("\n\n","\n", $argList);
      $argList=strip_tags($argList);
      $argList=str_ireplace("&nbsp;"," ",$argList);
      $ret="$line1$line: $argList\n";
    } else {
      $ret="<div class=info>
              <div style='float:left; width: 40px;'>$curStep)</div>
              <div style='float:left; width: 760px; overflow: auto'> $argList&nbsp;<span style='color:#BBB'>$line1$line</span></div>
            </div>";
    }

    if (function_exists('_dump'))
      _dump("$curStep) $argList");
    return ($debugSteps?$ret:"");
  }

  function whereis($aPath, $aFileName, $showPlace=false)
  {
    if ($showPlace)
      echo echoStep("Looking '<b>$aFileName</b>' in <ul>".join('<b>;</b><br>&nbsp;&nbsp;&nbsp;&nbsp;',$aPath)."</ul>");
    $ret='';
    // $aPath=array_reverse($aPath);
    foreach($aPath as $folderName) {
      $folderName=preg_replace('/[^\x20-\x7E]/','', $folderName);
      if (strtolower(substr($folderName,0,5))=='http:')
        $folderName=substr($folderName,0,7).str_replace('//','/',substr($folderName,7));

      $fileName="$folderName/$aFileName";
      $fileName=preg_replace('#/+#','/',$fileName);
      if ($showPlace)
        echo echoStep("Trying $fileName");

      if (substr($fileName,0,5)=='http:') {
        $fAux=file($fileName);
        $fileExists=(!is_bool($fAux)) and (count($fAux)>0);
      } else
        $fileExists=file_exists($fileName);

      if ($fileExists) {
        $ret=$folderName;
        if ($showPlace)
          echo echoStep(" (absolute): $ret");
        break;
      } else if (file_exists(realpath($fileName))) {
        $ret=dirname(realpath($fileName));
        if ($showPlace)
          echo echoStep(" (realpath): $ret");
        break;
      } else if (file_exists($fileName)) {
        $ret=dirname($fileName);
        if ($showPlace)
          echo echoStep(" (path): $ret");
        break;
      }
    }
    if ($showPlace) {
      if ($ret>'') {
          echo echoStep("$aFileName = &#32;<span style='font-weight:800; color: #147; font-size: 11px'>$ret</span>");
      } else
        echo echoStep("<span class=err>$aFileName <b>NOT FOUND</b></span>");
    }
    str_replace('//', '/', $ret);
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

    echo echoStep("Opening config file '.config/yeapf.config'");
    $configFile=fopen('.config/yeapf.config','w');
    if ($configFile) {
      $date=date("Y-m-d");
      $time=date("G:i:s");
      fwrite($configFile,"<?php\n\n/* \n");
      fwrite($configFile," * yeapf.config\n");
      fwrite($configFile," * YeAPF 0.8.53-65 built on 2017-01-16 14:41 (-2 DST)\n");
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
    echo echoStep("Closing config file");

    fwrite($configFile,"\n\n?>");
    fclose($configFile);
  }

  $url=getenv("QUERY_STRING");
  parse_str($url);

  ini_set('display_errors','0');
  error_reporting ('E_NONE');
  $yeapfLogFlags = 65535;
  $yeapfLogLevel = 10;

  if (!$isCLI) {
    echo "<style>body{padding:8px; font-size:12px; font-family: 'Courier New, Courier, monospace'; } * {border-radius: 3px;} .info{margin:2px; padding-bottom:8px; width: 800px} .warn{border-top:dotted 1px #900; border-bottom:dotted 1px #900; color:#A86D00;  font-weight:800; margin:2px; padding:8px; background-color:#FFEAC0} .cpyrght{font-size:14px; border-bottom:solid 2px #BFBFBF; padding-bottom:4px} .code { font-family:Consolas,monospace;  background-color: #E5E5E5;  margin: 8px;  padding: 4px;  border: dotted 1px #7F7F7F} .err {background-color:#FFC0CB;  border-style:solid;  border-width:2px;  border-color:#FF0000; margin:32px; padding:32px; border-radius:4px; width: 800px; font-weigth: 800; color: #900} .errItem {border-bottom: dotted 1px #FF0000;  margin-bottom:4px; }</style>";
  }


  echo sayStep("<div class=cpyrght><strong><big><I>skel/workbench/configure.php</I></big></strong><br>
    YeAPF 0.8.53-65 built on 2017-01-16 14:41 (-2 DST)<br>
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com<br>
    2017-01-16 14:41:33 (-2 DST)</div>");

  if (!getMinPath($homeFolder, $homeURL, $relPath)) {
    die(sayStep("<div class=err><b>$homeFolder</b> is not a real dir.<br>Probably '$relPath' is not a real path.<br>Maybe it's an alias or link<hr>Try again using an real path</div>"));
  }

  echo echoStep("<b>homeFolder</b>: '$homeFolder' is equivalent to homeURL: '$homeURL'\n");
  /*
   * YeAPF could be installed in any of these relative paths:
   *   includes/
   *   YeAPF/includes/
   *   lib/
   *   lib/YeAPF/
   *   lib/YeAPF/includes/
   * They could be absolute (DOCUMENT_ROOT) or relatives to the current appdir
   */

  $__PL__='';

  $ySearchPath="includes;YeAPF/includes;YeAPF/.distribution/0.8.53/includes;lib;lib/YeAPF;lib/YeAPF/includes";
  if (file_exists('yeapf.path')) {
    echo echoStep("Trying to use 'yeapf.path'");
    $tempYSearchPath = file('yeapf.path');
    $tempYSearchPath[]=$tempYSearchPath[0].'/includes';
    echo echoStep("Looking at ".join(';',$tempYSearchPath));

    $__PL__=whereis($tempYSearchPath,'yeapf.db.php',file_exists('flag.dbgphp.configure'));
    if ($__PL__>'') {
      echo sayStep("YeAPF located at $__PL__");
      $ySearchPath=$__PL__;
    } else
      echo echoStep("Resuming begger");
  }
  $ySearchPath=explode(';',$ySearchPath);

  if ($__PL__=='') {
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

    echo echoStep("Checking 'search.path'");

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
  }

  // date_default_timezone_set("America/Sao_Paulo");
  if (file_exists("flags/timezone"))
    $auxDefaultTimeZone=file_get_contents("flags/timezone");
  else
    $auxDefaultTimeZone = @date_default_timezone_get();

  echo sayStep("TimeZone: $auxDefaultTimeZone");
  if ($auxDefaultTimeZone!='UTC') {

    function verifyConfigFolder($folderName) {
      echo echoStep("Granting '$folderName' folder");
      $canConfig=false;
      if (is_link($folderName)) {
        $folderNameTarget=readlink($folderName);
        echo echoStep("<span class=warn>folder '$folderName' is a link that points to '$folderNameTarget'</span>");
        $folderName=$folderNameTarget;
      }

      $tmpfname=$folderName.'/'.basename(tempnam($folderName,"cfgTest"));
      echo echoStep("Testing write rights using '$tmpfname' temporary file");
      if (!is_dir($folderName))
        mkdir($folderName, 0777, true);
      if (touch($tmpfname)) {
        unlink($tmpfname);
        $canConfig=true;
      } else {
        echo sayStep("'$tmpfname' cannot be created");
        if (is_file($folderName))
          die(sayStep("<ul class=err>$folderName is a file and we need to create a folder with that name</ul>"));
        if (!is_dir($folderName)) {
          echo echoStep("<b>$folderName</b> is not a folder");
          $canConfig = is_writable(".");
          if ($canConfig)
            $canConfig = mkdir($folderName, 0766) or die(sayStep("<p><ul class=err>Not enough rights to create '<b>$folderName</b>' folder</ul></p>"));
        } else
          $canConfig = is_writable($folderName);
      }
      return $canConfig;
    }

    $canConfig = verifyConfigFolder("lock") && verifyConfigFolder(".config");

    if ($canConfig) {
      echo sayStep("Loading <em>$__PL__/yeapf.debug.php</em>");
      (@include_once ($__PL__."/yeapf.debug.php")) || die(sayStep("<ul class=err>Err loading $__PL__/yeapf.debug.php</ul>"));

      if (function_exists("yeapfVersion")) {
        if (("%"."YEAPF_VERSION%")==yeapfVersion())
          echo sayStep("<div class=warn>Warning: You're using a developer version while your installed version is ".yeapfVersion()."</div>");
        else
          if (("0.8.53") != yeapfVersion())
            die(sayStep("<ul class=err>Your configure version is '0.8.53' while your installed version is '".yeapfVersion()."'<br><small>You can use 'yeapf.path' file to indicate where is your YeAPF distribution</small></ul>"));
      }


      echo echoStep("canConfig=".intval($canConfig)."<br>Loading <em>$__PL__/yeapf.locks.php</em>");
      (@include_once ($__PL__."/yeapf.locks.php")) || die(sayStep("Err loading $__PL__/yeapf.locks.php"));


      /*
      ini_set('display_errors','0');
      error_reporting (E_NONE);
      */
      $cfgSQLiteInstalled = class_exists('SQLiteDatabase');
      echo echoStep("SQLiteDatabase installed: ".intval($cfgSQLiteInstalled));

      if (!file_exists('includes/security.php'))
        echo echoStep("<div class=warn>'includes/security.php' was not found!<br>Verify appFolderName.def file</div>");
      $lockCanBeCreated=0;
      if ((is_writable('./')) && (touch('flag.test'))) {
        $lockCanBeCreated=1;
        unlink('flag.test');

        if (!is_dir("logs")) {
          echo sayStep("Creating '<em>logs</em>' folder");
          mkdir("logs", 0764, true);
        }
        if (!is_dir(".config")) {
          echo sayStep("Creating '<em>.config</em>' folder");
          mkdir(".config", 0764, true);
        }

        echo echoStep("Trying to create 'configure' lock");

        if (lock('configure')) {
          echo echoStep("Lock subsystem working well");
          $lockCanBeCreated=2;

          $md5Files=array('body.php', 'index.php', 'configure.php', 'search.path');
          $myMD5='';
          foreach($md5Files as $aFileName)
            if (file_exists($aFileName))
              $myMD5.=join('', file($aFileName));
          $myMD5=md5($myMD5);
          if ((file_exists('configure.md5')) && (!is_writable('configure.md5')))
            die(sayStep("<div class=err>Impossible to write on 'configure.md5'</div>"));
          else
            file_put_contents('configure.md5',$myMD5);

          $d=dir('.');
          while (false !== ( $entry=$d->read() ) ) {
            if (substr($entry,0,19)=='yeapf.config.files.') {
              echo echoStep("Deleting $entry");
              @unlink($entry);
            }
          }

          foreach(glob(".config/yeapf.config*") as $entry) {
            echo echoStep("Deleting $entry");
            @unlink($entry);
          }

          $d=dir('lock');
          while (false !== ( $entry=$d->read() ) ) {
            if (substr($entry,0,1)!='.')
              if ($entry!='configure') {
                echo echoStep("Deleting lock/$entry");
                @unlink("lock/$entry");
              }
          }

          $server_IP=$_SERVER["SERVER_ADDR"];
          $user_IP=$_SERVER["REMOTE_ADDR"];

          if (!openConfigFile())
            die(sayStep("IMPOSSIVEL CRIAR ARQUIVO DE CONFIGURA&Ccedil;&Atilde;O"));

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

          $yeapfDB.=','.getSgugPath(getcwd());
          $yeapfDB.=','.getSgugPath(dirname(getcwd()));
          $yeapfDB.=','.getSgugPath($_httpReferer_);

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

          $yeapfDB=array_unique(explode(',',$yeapfDB));
          foreach($yeapfDB as $k=>$v)
            if ($v<$_SERVER['DOCUMENT_ROOT'])
              unset($yeapfDB[$k]);

          /* introduced in 0.8.44
           * .config/db.ini is a cached copy of the active
           * database connection configuration file specified
           * in db.csv (former sgug.ini)*/
          if (file_exists(".config/db.ini"))
            unlink(".config/db.ini");

          $sgugIni=whereis($yeapfDB, 'sgug.ini', true);
          $dbCSV=whereis($yeapfDB, 'db.csv', true);
          /* introduced in 0.8.44
           * 'sgug.ini' renamed to 'db.csv'
           * 0.8.47 db.csv has higher priority in mixed environments */
          if (!file_exists($dbCSV)) {
            if (file_exists("$sgugIni/sgug.ini")) {
              if (!file_exists("$sgugIni/db.csv")) {
                echo sayStep("Renaming 'sgug.ini' to 'db.csv'");
                $canConfig=rename("$sgugIni/sgug.ini", "$sgugIni/db.csv");
              } else {
                echo sayStep("Cannot continue. db.csv already exists!");
                $canConfig=false;
              }
              if (!$canConfig)
                die(sayStep("<div class=err>Impossible to rename sgug.ini to db.csv</div>"));
            }
          }

          if ($canConfig) {
            $sgugIni=whereis($yeapfDB, 'db.csv', true);
            if ((isset($destroydb)) && ($destroydb=='yes')) {
              if (file_exists("$sgugIni/db.csv"))
                unlink("$sgugIni/db.csv");
              $sgugIni='';
              echo sayStep("<div class=warn>Destroying DB connection (no database itself, just connection config)</div>");
            }

            if ($sgugIni>'') {
              $sgugIni="$sgugIni/db.csv";
              $yeapfDB_ini='';
            } else
              $yeapfDB_ini=whereis($yeapfDB, 'yeapf.db.ini');

            $yeapfDB_configured = false;
            /* flags/flag.nodb */
            if ($yeapfDB_ini>'') {
              echo sayStep("Loading <em>$__PL__/yeapf.dbText.php</em>");
              (@include_once $__PL__."/yeapf.dbText.php") || die(sayStep("Error loading $__PL__/yeapf.dbText.php"));

              $newSgug=($sgugIni=='');

              if ($newSgug)
                $sgugIni="$yeapfDB_ini/db.csv";

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
                  die(sayStep("Tem mais de uma entrada de banco de dados ativa\n$curAppName, $activeAppName"));
              }

              if (($setupIni->locate("active",1))==$dbTEXT_NO_ERROR)
                $curAppRegistry=$setupIni->getValue('appRegistry');
              else
                $curAppRegistry=-1;

              foreach($yeapfINI as $key => $val)  {
                // get rootPassword from .ini and codifies it for db.csv
                $rootFirstPassword=trim($yeapfINI[$key]['cfgRootFirstPassword']);
                if ($rootFirstPassword=='')
                  $rootFirstPassword='masterkey';
                $rootFirstPassword=md5($rootFirstPassword);
                $yeapfINI[$key]['cfgRootFirstPassword']=$rootFirstPassword;
              }

              $appRegistryList=array();

              foreach($yeapfINI as $key => $val)  {

                // try to update current db.csv entry
                $appNameKey=$key;
                if (($setupIni->locate("appName",$appNameKey))!=$dbTEXT_NO_ERROR)
                  $setupIni->addRecord();

                $curAppName='';
                if (isset($val['dbConnect'])) {
                  $tempDBConnect=strtolower(substr($val['dbConnect'],0,1));
                  if (($tempDBConnect=='n') || (intval($tempDBConnect)==0) || ($tempDBConnect=='f')) 
                    $tempDBConnect='no';
                  else
                    $tempDBConnect='yes';
                } else 
                  $tempDBConnect='no';

                foreach($val as $k1 => $v1) {
                  if ($v1>'') {
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
                    if ($newSgug) {
                      if (file_exists($sgugIni))
                        unlink($sgugIni);
                    }
                    if ($tempDBConnect!='no')
                      die(sayStep("<div class=err><b>yeapf.db.ini</b> malformed<br>**** $k1 needs to be defined</div>"));
                  }
                }

              }

              $xyzField = 'x58e1d9ca63ef85abef352d3306a6fac3';

              if (!$setupIni->fieldExists($xyzField)) {
                $setupIni->addField($xyzField);
                $setupIni->setValue($xyzField,'5356565058626263613536433536');
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

              $yeapfDB_configured = ($setupIni->commit() || ($tempDBConnect=='no'));

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
              echo echoStep("Search path: $aux");
              if (!$cfgSOAPInstalled) {
                $nusoapPath=whereis($searchPath,'nusoap.php',file_exists('flag.dbgphp.configure'));
                if ($nusoapPath=='') {
                  unlock('configure');
                  die(sayStep("<div class=err>** nusoap.php not found</div>"));
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
              array_unshift($searchPath, dirname($__PL__));

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

              echo sayStep("Ready to write stubloader");

              if ((file_exists('yeapf.php')) && (!is_writable('yeapf.php')))
                die(sayStep("<div class=err>Impossible to write to 'yeapf.php'</div>"));
              $f=fopen("yeapf.php", "w");

              $yeapfStub = '
              /*
              * yeapf.php
              * (C) 2004-2014 Esteban Daniel Dortta (dortta@yahoo.com)
              */

              function _yLoaderDie($reconfigureLinkEnabled)
              {
                global $callback;
                $script=basename($_SERVER["PHP_SELF"]);
                $isXML=(strpos("query.php",$script)!==false);
                $isJSON=(strpos("rest.php",$script)!==false);
                $isCLI=(php_sapi_name() == "cli");

                if (file_exists("flags/flag.dbgloader")) error_log(basename(__FILE__)." ".date("i:s").": app dying\n",3,"logs/yeapf.loader.log");

                $args=func_get_args();
                if (func_num_args()>1)
                  array_shift($args);
                if ((!$isXML) && (!$isCLI))
                  $msg=join("<br>",$args);
                else
                  $msg=join("\n",$args);
                $lineMsg=$args;
                $lineMsg = str_replace("<br>",";\t",$lineMsg);
                $lineMsg = str_replace("\n",". ",$lineMsg);
                syslog(LOG_INFO,$script." (running at ".getcwd().")"." - ".$lineMsg);

                if (function_exists("_die")) {
                  if ($isCLI)
                    _die(htmlentities($lineMsg));
                  else
                    _die($lineMsg);
                } else {
                  if (!$isXML) {
                    if ($isCLI) {
                      echo "\n$msg\n";
                    } else if ($isJSON) {
                      echo "_dump(\'$lineMsg\');\n";
                      if ((is_string($callback)) && (trim($callback)>""))
                        echo "if (typeof $callback == \'function\') $callback(null);";
                    } else {
                      header("Content-Type: text/html; charset=UTF-8");
                      echo "<style> .err {background-color:#FFC0CB; border-style:solid; border-width:2px; border-color:#FF0000;margin:32px;padding:32px;border-radius:4px}</style>";
                      if ($reconfigureLinkEnabled)
                      $doConfig="<div>Click <b><a href=\"configure.php\">here</a></b> to configure<br></div>";
                      $copyrightNote="<small><em>YeAPF (C) 2004-2014 Esteban Dortta</em></small>";
                      echo "<div class=err><div>$msg</div>$doConfig$copyrightNote</div>";
                    }
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

              if (file_exists("flags/timezone"))
                $auxDefaultTimeZone=file_get_contents("flags/timezone");
              else
                $auxDefaultTimeZone = @date_default_timezone_get();
              
              date_default_timezone_set($auxDefaultTimeZone);


              if (file_exists("flags/flag.dbgloader")) error_log(basename(__FILE__)." ".date("i:s").": preparing flags\n",3,"logs/yeapf.loader.log");

              $yeapfLogFlags=_yeapf_getFileValue("debug.flags");
              $yeapfLogLevel=_yeapf_getFileValue("debug.level");
              $yeapfLogBacktrace=_yeapf_getFileValue("debug.trace");
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

              if (file_exists("flags/flag.dbgloader")) error_log(basename(__FILE__)." ".date("i:s").": loading config files\n",3,"logs/yeapf.loader.log");

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
              if (file_exists("flags/flag.dbgloader")) error_log(basename(__FILE__)." ".date("i:s").": loading $__yeapfPath/yeapf.functions.php\n",3,"logs/yeapf.loader.log");
              (@include_once $__yeapfPath."/yeapf.functions.php") || (_yLoaderDie("$__yeapfPath/yeapf.functions.php not found"));

              if (!function_exists("decimalMicrotime")) die("decimalMicrotime() required");

              $t0=decimalMicrotime();

              if (file_exists("flags/flag.dbgloader")) error_log(basename(__FILE__)." ".date("i:s").": verifiyng yeapf version\n",3,"logs/yeapf.loader.log");

              _recordWastedTime("Checking YeAPF Version");

              if (function_exists("yeapfVersion"))
                if (("%"."YEAPF_VERSION%")==yeapfVersion()) {
                  error_log(basename(__FILE__)." ".date("i:s").": WARNING: Using developer version\n",3,"logs/yeapf.loader.log");
                } else {
                  if (("0.8.53") != yeapfVersion())
                    _yLoaderDie(true, "Your configure version is \'0.8.53\' while your installed version is \'".yeapfVersion()."\'");
                }
              if (!isset($appName))
                $appName = "dummy";
              $yeapfConfig["searchPath"]=$appName.";".$yeapfConfig["searchPath"];
              set_include_path(get_include_path().":".str_replace(";",":",$yeapfConfig["searchPath"]));
              ';

              fwrite($f,"<?php\n$yeapfStub");

              // appFolder
              $appFolderLoader = '
              _recordWastedTime("Checking distribution");
              $md5Files=array("body.php", "index.php", "configure.php", "search.path");
              $configMD5="";
              foreach($md5Files as $aFileName)
                if (file_exists($aFileName))
                  $configMD5.=join("", file($aFileName));
              $configMD5=md5($configMD5);

              $savedConfigMD5 = join("",file("configure.md5"));
              if ((file_exists("configure.php")) && ($configMD5 != $savedConfigMD5)) {
                _yLoaderDie(true, "YeAPF not configured\nRun <a href=\"configure.php\">configure.php</a> again.");
              }
              ';

              fwrite($f,$appFolderLoader);

              // appScript and appFolderScript
              $appScriptLoader = '
              if (file_exists("flags/flag.dbgloader")) error_log(basename(__FILE__)." ".date("i:s").": loading application script\n",3,"logs/yeapf.loader.log");
              // load application script
              $appWD=basename(getcwd());
              // drop version info as in "customers-3.5" keeping with "costumers"
              $appWD=substr($appWD,0,strpos($appWD."-","-"));
              $__scriptList = array("$appWD.php", "$appWD.rules.php",
                                    "$appName.php", "$appName.$appWD.php",
                                    "$appName.rules.php", "$appName.$appWD.rules.php", "rules.php",
                                    bestName("$appName.security.php"),
                                    "includes/security.php");
              _recordWastedTime("Loading app libraries");
              $t1=decimalMicrotime();
              foreach($__scriptList as $__scriptName) {
                $__scriptName = bestName($__scriptName);
                if ((file_exists($__scriptName)) && (!is_dir($__scriptName))) {
                  _recordWastedTime("Loading $__scriptName");

                  (@include_once "$__scriptName") or _yLoaderDie(true, dirname(__FILE__)."Error loading $__scriptName");
                }
              }
              $t2=decimalMicrotime()-$t1;
              _recordWastedTime("App libraries loaded ($t2)");

              ';

              fwrite($f,$appScriptLoader);

              fwrite($f,"\n          _dumpY(1,1,'yeapf loaded');\n\n");

              $appStarter = '
              yeapfStage("click");
              yeapfStage("registerAppEvents_$appWD");
              yeapfStage("registerAppEvents");
              ';
              fwrite($f,$appStarter);

              fwrite($f,'
              $t0=decimalMicrotime()-$t0;
              _recordWastedTime("overall loader wasted time: $t0");
              ?>');
              fclose($f);

              $referer_uri=isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'./';
              if ($referer_uri==$_SERVER['SCRIPT_NAME'])
                $referer_uri='./';

              if (file_exists('develop.php'))
                $developLink="Click <a href='develop.php'>here</a> to start developing your app.<br>";
              else
                $developLink='';

              echo sayStep("<div style='box-shadow: 5px 5px 2px #888888; background: #90EE90; border-style: solid; border-width: 2px; border-color: #00BD00; padding: 32px'><big><u>YeAPF 0.8.53 well configured!</u></big><div style='padding-left: 16px'>Location: <b>$__PL__</b><br>DB config: <b>$sgugIni</b></div><br>Click <a href='$referer_uri'>here</a> to go back.<br>Click <a href='configure.php?debugSteps=1'>here</a> to debug configure process.<br> Click <a href='index.php'>here</a> to start your app.<br>$developLink<small style='margin: 16px'>If you wish to destroy database connection an recreate it, click <a href='configure.php?destroydb=yes'>here</a><br><i>It will preserve your database data but will remove all other definitions except the one contained in<em>yeapf.db.ini</em></i></small></div>");

              $aux=join(file('.config/yeapf.config'),'<br>');
              // echo echoStep("<div class=code>$aux</div>");
              $aux=join(file('yeapf.php'),'<br>');
              // echo echoStep("<div class=code>$aux</div>");

            } else
              echo sayStep("<BR>The dbConnection could not be written.<br>Check your access rights to '".getcwd()."' folder ");
          }
          unlock('configure');
        } else {
          echo sayStep("<div>LOCK CANNOT BE CREATED</div>");
        }
      }

      if ($lockCanBeCreated<2) {
        echo sayStep("<div class=err>");

        echo sayStep("<div class=errItem>Was not possible to lock the system (stage $lockCanBeCreated. LOCK_VERSION=$LOCK_VERSION)<br>Check your installation</div>");
        if ($lockCanBeCreated<1)
          echo sayStep("<div class=errItem>You have not enough rights to write to the filesystem on <b>".getcwd()."</b><br>Please give write, read and execution rights to this folder and try again</div>");

        echo sayStep("</div>");
      }

    } else {
      echo sayStep("<span class=err>Was not possible to create support folders</span>");
      echo sayStep("<span class=err>Your main folder ($homeURL) must to have enough rights to be written by httpd server</span>");
    }

  } else {
    echo sayStep("<span class=err>Define PHP timeZone before configure</span>");
    echo sayStep("<span class=err>UTC is not accepted as default timeZone</span>");
  }
?>
