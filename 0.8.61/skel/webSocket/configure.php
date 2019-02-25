<?php
/*
    skel/webSocket/configure.php
    YeAPF 0.8.61-182 built on 2019-02-25 20:32 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
    2019-02-25 20:32:13 (-3 DST)
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
   * 2017-09-01 - 'silent' paramenter
   */

  if (isset($silent))
    $silent=true;
  else
    $silent=false;

  function verifyActiveApp()
  {
    global $curAppName, $activeCount, $activeAppName;

    if ($activeCount==1)
      $activeAppName=$curAppName;
    else if ($activeCount>1)
      dieConfig(sayStep("You have more than one active database entry\nCurrent AppName: $curAppName. Active AppName: $activeAppName"));
  }

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
  clearstatcache(true);

  function dieConfig() {
    global $silent;
    $argList='';
    $args=func_get_args();
    foreach ($args as $a) {
      if ($argList>'')
        $argList.=' ';
      $argList.=$a;
    }
    $silent=false;
    $ret=sayStep($argList);
    unlock('configure');
    die($ret);
  }

  function sayStep()
  {
    global $curStep, $isCLI, $silent, $debugSteps;
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
      if (($debugSteps) && ($curStep>1)) {
      $ret="<div style='width: 100%'>
              <span><b class=stepInfo>$curStep)</b>$argList&nbsp;<span style='color:#BBB'>$line1$line</span></span>
            </div>";
      } else {
        $ret="<div style='width: 100%'>
                <span>$argList&nbsp;<span style='color:#BBB'>$line1$line</span></span>
              </div>";
      }
    }

    if (function_exists('_dump'))
      _dump("$curStep) $argList");
    return $silent?"$curStep.":$ret;
  }

  function echoStep()
  {
    global $curStep, $isCLI, $debugSteps, $silent;
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
      $ret="<div style='width: 100%'>
              <span><b class=stepInfo>$curStep)</b>$argList&nbsp;<span style='color:#BBB'>$line1$line</span></span>
            </div>";
    }

    if (function_exists('_dump'))
      _dump("$curStep) $argList");
    $sret = $silent?"$curStep.":$ret;
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
        echo echoStep("<span class=redDot></span><span class=err>$aFileName <b>NOT FOUND</b></span>");
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
      $absPath=str_replace("\\" ,"/" ,getcwd());
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
      fwrite($configFile," * YeAPF 0.8.61-182 built on 2019-02-25 20:32 (-3 DST)\n");
      fwrite($configFile," * Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com\n");
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
  $retArray = array();
  parse_str($url, $retArray);
  extract($retArray);

  ini_set('display_errors','0');
  error_reporting ('E_NONE');
  $yeapfLogFlags = 65535;
  $yeapfLogLevel = 10;

  $__yeapfCSS="\n<style>input [type='submit'] {}.formBox {}.formBox h3 {}img {border: none;-ms-interpolation-mode: bicubic;max-width: 100%;}body {background-color: #f6f6f6;font-family: sans-serif;-webkit-font-smoothing: antialiased;font-size: 14px;line-height: 1.4;margin: 0;padding: 0;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;}table {border-collapse: separate;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;}.fanfold tbody tr:nth-child(even) {background: #e8e8e8 }.fanfold tbody tr:nth-child(odd) {background: #f0f0f0 }table td {font-family: sans-serif;font-size: 14px;vertical-align: top;}.body {background-color: #f6f6f6;width: 100%;max-width: 620px;}.container {display: block;Margin: 0 auto !important;max-width: 580px;padding: 10px;width: 580px;}.content {box-sizing: border-box;display: block;Margin: 0 auto;max-width: 580px;padding: 10px;}.main {background: #fff;border-radius: 3px;width: 100%;}.wrapper {box-sizing: border-box;padding: 20px;}.footer {clear: both;padding-top: 10px;text-align: center;width: 100%;}.footer td, .footer p, .footer span, .footer a {color: #999999;font-size: 12px;text-align: center;}.dbErr{color: #FF0000;}.dbOk {color: #00FF00}.dbWarn {color: #FF8000}h1, h2, h3, h4 {color: #000000;font-family: sans-serif;font-weight: 400;line-height: 1.4;margin: 0;Margin-bottom: 30px;}h1 {font-size: 35px;font-weight: 300;text-align: center;text-transform: capitalize;}p, ul, ol {font-family: sans-serif;font-size: 14px;font-weight: normal;margin: 0;Margin-bottom: 15px;}p li, ul li, ol li {list-style-position: inside;margin-left: 5px;}a {color: #3498db;text-decoration: underline;}.btn {box-sizing: border-box;width: 100%;}.btn>tbody>tr>td {padding-bottom: 15px;}.btn table {width: auto;}.btn table td {background-color: #ffffff;border-radius: 5px;text-align: center;}.btn a {background-color: #ffffff;border: solid 1px #3498db;border-radius: 5px;box-sizing: border-box;color: #3498db;cursor: pointer;display: inline-block;font-size: 14px;font-weight: bold;margin: 0;padding: 12px 25px;text-decoration: none;text-transform: capitalize;}.btn-primary table td {background-color: #3498db;}.btn-primary a {background-color: #3498db;border-color: #3498db;color: #ffffff;}.last {margin-bottom: 0;}.first {margin-top: 0;}.align-center {text-align: center;}.align-right {text-align: right;}.align-left {text-align: left;}.clear {clear: both;}.mt0 {margin-top: 0;}.mb0 {margin-bottom: 0;}.preheader {color: transparent;display: none;height: 0;max-height: 0;max-width: 0;opacity: 0;overflow: hidden;mso-hide: all;visibility: hidden;width: 0;}.powered-by a {text-decoration: none;}hr {border: 0;border-bottom: 1px solid #f6f6f6;Margin: 20px 0;}@media only screen and (max-width: 620px) {table[class=body] h1 {font-size: 28px !important;margin-bottom: 10px !important;}table[class=body] p, table[class=body] ul, table[class=body] ol, table[class=body] td, table[class=body] span, table[class=body] a {font-size: 16px !important;}table[class=body] .wrapper, table[class=body] .article {padding: 10px !important;}table[class=body] .content {padding: 0 !important;}table[class=body] .container {padding: 0 !important;width: 100% !important;}table[class=body] .main {border-left-width: 0 !important;border-radius: 0 !important;border-right-width: 0 !important;}table[class=body] .btn table {width: 100% !important;}table[class=body] .btn a {width: 100% !important;}table[class=body] .img-responsive {height: auto !important;max-width: 100% !important;width: auto !important;}}@media all {.ExternalClass {width: 100%;}.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;}.apple-link a {color: inherit !important;font-family: inherit !important;font-size: inherit !important;font-weight: inherit !important;line-height: inherit !important;text-decoration: none !important;}.btn-primary table td:hover {background-color: #34495e !important;}.btn-primary a:hover {background-color: #34495e !important;border-color: #34495e !important;}.logo {margin: 0px;padding: 0px;height: 132px;width: 399px;}} .stepInfo{ width: 24px;  text-align: right;  float: left;  padding-right: 8px;}
    .err { border: dotted 1px #f00; background-color: #FF6666 }
    .warn { border: dotted 1px #FF8000; background-color: #FFCC66 }
    .severeWarn { border: dotted 1px #FF6666; background-color: #FFCC66; color: #333333 }
    .goodStep { border: dotted 1px #00FF00; background-color: #408000; color: #E6E6E6 }
    .greenDot { margin-left: 16px; margin-right: 16px; height: 16px; width: 16px; background-color: #408000; border-radius: 50%; display: inline-block; }
    .redDot { margin-left: 16px; margin-right: 16px; height: 16px; width: 16px; background-color: #FF3300; border-radius: 50%; display: inline-block; } </style>\n";

  if (!$isCLI) {
    echo "<!doctype html>\n\n<html><head><meta name='viewport' content='width=device-width' /><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />";
    echo "<title>configure</title>";
    echo $__yeapfCSS;
    echo "</head>\n<body style='padding: 16px; margin: 16px;'><div align=center><div style='max-width:860px' align=left>\n";
  }

  $timestamp=date('U');
  echo sayStep("<div style='border-left: solid 4px black; padding: 12px; background-color: #fff'>
    <div><a href='http://www.yeapf.com' target='x$timestamp'><img src='http://www.yeapf.com/logo.php'></a></div>
    <h2><big><I>skel/webSocket/configure.php</I></big></h2>
    <h3>YeAPF 0.8.61-182 built on 2019-02-25 20:32 (-3 DST)<br>
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com<br>
    Last modification: 2019-02-25 20:32:13 (-3 DST)</h3></div>");

  if (!getMinPath($homeFolder, $homeURL, $relPath)) {
    dieConfig(sayStep("<span class=redDot></span><div class=err><b>$homeFolder</b> is not a real dir.<br>Probably '$relPath' is not a real path.<br>Maybe it's an alias or link<hr>Try again using an real path</div>"));
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

  $ySearchPath="includes;YeAPF/includes;YeAPF/.distribution/0.8.61/includes;lib;lib/YeAPF;lib/YeAPF/includes";
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


  function verifyConfigFolder($folderName) {
    echo sayStep("Granting '$folderName' folder");
    $canConfig=false;
    if (is_link($folderName)) {
      $folderNameTarget=readlink($folderName);
      echo echoStep("<span class=warn>folder '$folderName' is a link that points to '$folderNameTarget'</span>");
      $folderName=$folderNameTarget;
    }

    $tmpfname=$folderName.'/'.basename(tempnam($folderName,"cfgTest"));
    echo echoStep("Testing write rights using '$tmpfname' temporary file");
    if (!is_dir($folderName))
      if (!mkdir($folderName, 0755, true))
        dieConfig(sayStep("<p><span class=redDot></span><ul class=err>You have not enough rights to create '$folderName'</ul></p>"));
    if (@touch($tmpfname)) {
      unlink($tmpfname);
      $canConfig=true;
    } else {
      echo sayStep("'$tmpfname' cannot be created");
      if (is_file($folderName))
        dieConfig(sayStep("<span class=redDot></span><ul class=err>$folderName is a file and we need to create a folder with that name</ul>"));
      if (!is_dir($folderName)) {
        echo echoStep("<b>$folderName</b> is not a folder");
        $canConfig = is_writable(".");
        if ($canConfig)
          $canConfig = mkdir($folderName, 0766) or dieConfig(sayStep("<p><span class=redDot></span><ul class=err>Not enough rights to create '<b>$folderName</b>' folder</ul></p>"));
      } else
        $canConfig = is_writable($folderName);
    }
    return $canConfig;
  }

  $canConfig = verifyConfigFolder(".config");

  if ($canConfig) {
    echo sayStep("Loading <em>$__PL__/yeapf.debug.php</em>");
    (@include_once ($__PL__."/yeapf.debug.php")) || dieConfig(sayStep("<span class=redDot></span><ul class=err>Err loading $__PL__/yeapf.debug.php</ul>"));

    if (function_exists("yeapfVersion")) {
      if (("%"."YEAPF_VERSION%")==yeapfVersion())
        echo sayStep("<div class=warn>Warning: You're using a developer version.<br>Please, use a production version instead.</div>");
      else
        if (("0.8.61") != yeapfVersion())
          dieConfig(sayStep("<span class=redDot></span><ul class=err>Your configure version is '0.8.61' while your installed version is '".yeapfVersion()." at ".yeapfBaseDir()."'<br><small>You can use 'yeapf.path' file to indicate where is your YeAPF distribution</small></ul>"));
    }

    $CFG_LOCK_DIR=".config/lock";

    echo echoStep("canConfig=".intval($canConfig)."<br>Loading <em>$__PL__/yeapf.locks.php</em>");
    (@include_once ($__PL__."/yeapf.locks.php")) || dieConfig(sayStep("<span class=err>Err loading $__PL__/yeapf.locks.php</span>"));


    /*
    ini_set('display_errors','0');
    error_reporting (E_NONE);
    */
    $cfgSQLiteInstalled = class_exists('SQLiteDatabase');
    echo echoStep("SQLiteDatabase installed: ".intval($cfgSQLiteInstalled));

    if (!file_exists('includes/security.php'))
      echo echoStep("<div class=warn>'includes/security.php' was not found!<br>Verify appFolderName.def file</div>");

    $severeFunction = array(
         "utf8_decode"        => "Your application send/receive e-mail, is served from UTF8 and/or the DB is UTF8",
         "mb_convert_encoding"=> "Your application uses different charset for db and app",
         "ibase_connect"      => "Your database is Firebird/Interbase",
         "pg_connect"         => "Your database is POSTGRES",
         "mysql_connect"      => "Your database is mysql",
         "mysqli_connect"     => "Your database is mysql (using mysqli)",
         "curl_init"          => "Applications running in appNode mode");
    $notFoundSF="";

    foreach($severeFunction as $severe => $explanation)  {
      if (!function_exists($severe))  {
        $notFoundSF.="<div><em>$severe"."()</em> is required if $explanation</div>";
      }
    }

    if ($notFoundSF>'') {
      echo sayStep("<div class='severeWarn'>At least one important function was not found in your php installation.<br>The severity of this warning depends on how your application has been built.<div style='padding-left: 16px'>$notFoundSF</div></div>");
    }
    $lockCanBeCreated=0;
    if ((is_writable('./')) && (touch('flag.test'))) {
      $lockCanBeCreated=1;
      unlink('flag.test');

      if (!is_dir("logs")) {
        echo sayStep("Creating '<em>logs</em>' folder");
        mkdir("logs", 0777, true);
      }


      if (!is_dir(".config")) {
        echo sayStep("Creating '<em>.config</em>' folder");
        mkdir(".config", 0764, true);
      }
      if (!is_dir($CFG_LOCK_DIR))
        mkdir($CFG_LOCK_DIR);

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
          dieConfig(sayStep("<span class=redDot></span><div class=err>Impossible to write on 'configure.md5'</div>"));
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

        $server_IP=$_SERVER["SERVER_ADDR"];
        $user_IP=$_SERVER["REMOTE_ADDR"];


        $_MY_CONTEXT_=str_replace("\\" ,"/" ,getcwd());
        // $_MYSELF_=str_replace('\\','/',$_SERVER["DOCUMENT_ROOT"].$_SERVER["REQUEST_URI"]);
        $_MYSELF_=str_replace('\\','/',dirname($_SERVER["REQUEST_URI"]));
        if (!(($aux1=strpos($_MYSELF_,'?'))===FALSE))
          $_MYSELF_=substr($_MYSELF_,0,$aux1);
        $_MYSELF_=str_replace('//','/',$_MYSELF_);

        if (substr($_MYSELF_,0,1)=='/')
          $_MYSELF_=substr($_MYSELF_,1);


        $_THIS_SERVER_=str_replace('\\','/',$_SERVER["DOCUMENT_ROOT"]);
        $_THIS_SERVER_=str_replace('//','/',$_THIS_SERVER_);


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

        $yeapfDB='';

        $yeapfDB.=','.getSgugPath(str_replace("\\" ,"/" ,getcwd()));
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

        $aux=$_MY_CONTEXT_;
        while ($aux>$_SERVER['DOCUMENT_ROOT']) {
          $yeapfDB[count($yeapfDB)]=$aux;
          $aux=dirname($aux);
        }

        /* introduced in 0.8.44
         * .config/db.ini is a cached copy of the active
         * database connection configuration file specified
         * in db.csv (former sgug.ini)*/
        if (file_exists(".config/db.ini"))
          unlink(".config/db.ini");

        $SGUG_INI_PATH = whereis($yeapfDB, 'sgug.ini', true);
        $DB_CSV_PATH   = whereis($yeapfDB, 'db.csv',   true);
        $YEAPF_INI_PATH=whereis($yeapfDB, 'yeapf.db.ini', true);
        /* introduced in 0.8.44
         * 'sgug.ini' renamed to 'db.csv'
         * 0.8.47 db.csv has higher priority in mixed environments */
        if (!is_dir($DB_CSV_PATH)) {
          if (file_exists("$SGUG_INI_PATH/sgug.ini")) {
            if (!file_exists("$SGUG_INI_PATH/db.csv")) {
              echo sayStep("Renaming 'sgug.ini' to 'db.csv'");
              $canConfig=rename("$SGUG_INI_PATH/sgug.ini", "$SGUG_INI_PATH/db.csv");
            } else {
              echo sayStep("Cannot continue. db.csv already exists!");
              $canConfig=false;
            }
            if (!$canConfig)
              dieConfig(sayStep("<span class=redDot></span><div class=err>Impossible to rename sgug.ini to db.csv</div>"));
          }
        }

        if ($canConfig) {
          $destroydb=isset($destroydb)?mb_strtolower($destroydb):'no';

          $recreateCSVFile = false;

          if ( ($YEAPF_INI_PATH>'')   && (is_dir($YEAPF_INI_PATH)) &&
               ($DB_CSV_PATH>'') && (is_dir($DB_CSV_PATH)) ) {
            $yeapfDB_ini_M=filemtime("$YEAPF_INI_PATH/yeapf.db.ini");
            $dbCSVFilename_M=filemtime("$DB_CSV_PATH/db.csv");
            echo sayStep("<div class=info>'yeapf.db.ini' modified at: $yeapfDB_ini_M | db.csv modified at $dbCSVFilename_M</div>");
            $recreateCSVFile = $yeapfDB_ini_M > $dbCSVFilename_M;
          }
          if (($destroydb=='yes') || ($recreateCSVFile)) {
            if (file_exists("$DB_CSV_PATH/db.csv")) {
              echo sayStep("<div class=warn>Deleting '$DB_CSV_PATH/db.csv'</div>");
              unlink("$DB_CSV_PATH/db.csv");
            }
            $recreateCSVFile=true;
            echo sayStep("<div class=warn>Destroying DB connection (no database itself, just connection config)</div>");
          }

          if (($DB_CSV_PATH=='') || ($recreateCSVFile)) {
            if ($DB_CSV_PATH=='')
              $dbCSVFilename="$YEAPF_INI_PATH/db.csv";
            else
              $dbCSVFilename="$DB_CSV_PATH/db.csv";
          } else {
            $dbCSVFilename="$DB_CSV_PATH/db.csv";
            $YEAPF_INI_PATH='';
          }

          echo sayStep("Loading <em>$__PL__/yeapf.dbText.php</em>");
          (@include_once $__PL__."/yeapf.dbText.php") || dieConfig(sayStep("<span class=redDot></span>Error loading $__PL__/yeapf.dbText.php"));

          echo sayStep("<div class>Opening/Creating connection definition $dbCSVFilename</div>");

          /*
          ####### #######
             #         #
             #        #
             #       #
             #      #
             #     #
             #    #######
          */
          if (file_exists(dirname($dbCSVFilename)."/flags/timezone"))
            $auxDefaultTimeZone=file_get_contents(dirname($dbCSVFilename)."/flags/timezone");
          else
            $auxDefaultTimeZone = @date_default_timezone_get();

          $auxDefaultTimeZone=preg_replace('/[\x00-\x1F\x7F]/', '',$auxDefaultTimeZone);
          echo sayStep("TimeZone: $auxDefaultTimeZone");
          if ($auxDefaultTimeZone!='UTC') {
            if (date_default_timezone_set($auxDefaultTimeZone)) {
              $setupIni=createDBText($dbCSVFilename, true);

              $yeapfDB_configured = false;
              /* flags/flag.nodb */
              if (($YEAPF_INI_PATH>'') || ($recreateCSVFile)) {

                $recreateCSVFile=($recreateCSVFile) || ($dbCSVFilename=='');

                if ($dbCSVFilename=='') {
                  $dbCSVFilename="$YEAPF_INI_PATH/db.csv";
                  echo sayStep("<div class=info>Connection definition filename: '$dbCSVFilename'</div>");
                } else {
                  echo sayStep("<div class=warn>Overwriting connection definition '$dbCSVFilename'</div>");
                }

                $yeapfINI=@parse_ini_file("$YEAPF_INI_PATH/yeapf.db.ini",true);
                if ($yeapfINI==false) {
                  dieConfig(sayStep("<span class=redDot></span><span class=err>File '$YEAPF_INI_PATH/yeapf.db.ini' is not parseable</span>"));
                }
                $activeCount=0;
                $activeAppName='';

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

                  $cfgDebugIP='';
                  $auxCfgDebugIp='';
                  $thisIsActive=false;

                  /* added in 0.8.61
                     some values need to be explicity defined
                     when ini file is parsed 'no' is changed by an empty string
                   */
                  $requiredBooleanValues = array ('dbConnect'           => 'no',
                                                  'cfgHtPasswdRequired' => 'no',
                                                  'cfgHttpsRequired'    => 'no');
                  foreach($val as $k1 => $v1) {
                    if (isset($requiredBooleanValues[$k1])) {
                      $v1=mb_strtolower($v1);
                      /* only accept 'yes', '1' or 'true' for indicate 'enable' status */
                      if (!(($v1=='yes') || ($v1=='1') || ($v1=='true')))
                        $v1='';
                      if ($v1=='') {
                        $v1=$requiredBooleanValues[$k1];
                        echo sayStep("<div class=info>Required value $k1 defaults to $v1</div>");
                      }
                    }
                    if ($v1>'') {
                      $setupIni->addField($k1);

                      if ($k1 == 'dbConnect') {
                        $v1 = $v1>0?'yes':'no';
                        if ($v1=='no') {
                          /* probably you will not want to check the user count */
                          $setupIni->setValue("yUserCount",1);
                          echo sayStep("<div class=warn>Value <b>yUserCount</b> changed to <b>'1'</b> as you will not be connected to a database</div>");
                        }
                      }

                      $setupIni->setValue($k1,$v1);

                      if ($k1=='appRegistry')
                        if (!(in_array($v1,$appRegistryList)))
                          array_push($appRegistryList, $v1);

                      if ($k1=='appName') {
                        $curAppName=$v1;
                        verifyActiveApp();
                      }

                      if ($k1=='active') {
                        $thisIsActive=intval($v1)==1;
                        if ($thisIsActive) {
                          $activeCount++;
                          verifyActiveApp();                          
                        }
                      }

                      if ($k1=='aDebugIP') {
                        $k1='cfgDebugIP';
                        $val[$k1]=$v1;
                      }
                      if ($k1=='cfgDebugIP') {
                        $auxCfgDebugIp=$v1;
                        if ($thisIsActive)
                          $cfgDebugIP=$auxCfgDebugIp;
                      }

                    } else if (($k1=='dbType') || ($k1=='dbServer') || ($k1=='dbName')) {
                      if ($recreateCSVFile) {
                        if (file_exists($dbCSVFilename)) {
                          echo sayStep("<div class='warn'>Deleting '$dbCSVFilename' because <b>$k1</b> is empty</div>");
                          unlink($dbCSVFilename);
                        }
                      }
                      if ($tempDBConnect!='no') {
                        dieConfig(sayStep("<span class=redDot></span><div class=err><b>yeapf.db.ini</b> malformed<br>**** $k1 needs to be defined</div>"));
                      }
                    }
                  }

                }

                $xyzField = 'x58e1d9ca63ef85abef352d3306a6fac3';

                if (!$setupIni->fieldExists($xyzField)) {
                  $setupIni->addField($xyzField);
                  $setupIni->setValue($xyzField,'5356565058626263613536433536');
                }

                echo sayStep("<div>...</div>");

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

              } else {
                $yeapfDB_configured = true;
              }


              if ($setupIni->locate("active",1)==$dbTEXT_NO_ERROR)
                $cfgDebugIP=$setupIni->getValue('cfgDebugIP');

              // echo sayStep("<div class=goodStep>cfgDebugIP = $cfgDebugIP</div>");

              // @AQUI! verifyConfigFolder(dirname($dbCSVFilename)."/lock")


              $lockFolderName=dirname($dbCSVFilename)."/.lock";

              if ((!file_exists($dbCSVFilename)) || (substr($lockFolderName,0,3=='//.')))
                dieConfig("<div class='err'><span class=redDot></span>Please, add yeapf.db.ini or db.csv to your main folder. <div>'$dbCSVFilename' not found</div><div><strong>OR</strong></div>Not regular lockFolderName '$lockFolderName' ($yeapfDB_configured)</div>");

              if (verifyConfigFolder($lockFolderName)) {
                function cleanupfolder($folder) {
                  if (is_dir($folder)) {
                    if (is_writable($folder)) {
                      $d=dir($folder);
                      while (false !== ( $entry=$d->read() ) ) {
                        if (substr($entry,0,1)!='.')
                          if ($entry!='configure') {
                            echo echoStep("Deleting lock/$entry");
                            @unlink("lock/$entry");
                          }
                      }
                    } else
                      dieConfig(sayStep("<span class=redDot></span><span class=err>Folder '$folder' is not writable</span>"));
                  }
                }

                cleanupfolder($lockFolderName);
                cleanupfolder("logs");

                /* eliminate older 'lock' folder if exists */
                cleanupfolder("lock");
                if (is_dir("lock"))
                  @rmdir("lock");

                if (!openConfigFile()) {
                  dieConfig(sayStep("IMPOSSIVEL CRIAR ARQUIVO DE CONFIGURA&Ccedil;&Atilde;O"));
                }
                writeConfigFile('cfgCurrentFolder', $_MY_CONTEXT_);
                writeConfigFile('myself',$_MYSELF_);
                if (isset($cfgDebugIP))
                  writeConfigFile('cfgDebugIP',$cfgDebugIP);
                writeConfigFile('root',$_THIS_SERVER_);
                writeConfigFile("httpReferer",$_httpReferer_);
                writeConfigFile("httpHost",$_httpHost_);

                if ($yeapfDB_configured) {
                  writeConfigFile("yeapfDB",$dbCSVFilename);
                  writeConfigFile("cfgMainFolder",dirname($dbCSVFilename));

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
                      dieConfig(sayStep("<span class=redDot></span><div class=err>** nusoap.php not found</div>"));
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
                    dieConfig(sayStep("<span class=redDot></span><div class=err>Impossible to write to 'yeapf.php'</div>"));
                  $f=fopen("yeapf.php", "w");

                  $yeapfStub = '
                  /*
                  * yeapf.php
                  * (C) 2004-2018 Esteban Daniel Dortta (dortta@yahoo.com)
                  */

                  global $__yeapfGlobalError;
                  $__yeapfGlobalError = false;

                  function _yLoaderDie($reconfigureLinkEnabled)
                  {
                    global $__yeapfGlobalError, $callback, $user_IP, $callBackFunction, $s, $a, $v, $SQLDieOnError;
                    if (!$__yeapfGlobalError) {
                      $script=basename($_SERVER["PHP_SELF"]);
                      $isXML=intval(strpos("query.php",$script)!==false);
                      $isJSON=intval(strpos("rest.php",$script)!==false);
                      $isHTML=intval( (strpos("index.php",$script)!==false) || (strpos("body.php",$script)!==false) );
                      $isCLI=intval(php_sapi_name() == "cli");
                      $outputType = $isHTML *1000 +
                                    $isXML  * 100 +
                                    $isJSON *  10 +
                                    $isCLI  *   1;

                      $args=func_get_args();
                      array_shift($args);
                      $noHTMLArgs = array();
                      $deathLogMessage = "";
                      foreach($args as $kAux=>$vAux) {
                        if (is_array($vAux))
                          $vAux=json_encode($vAux);
                        $noHTMLArgs[$kAux] = preg_replace(\'!\s+!\', " ", str_replace("\n", " ", strip_tags($vAux)));
                        $deathLogMessage.=$noHTMLArgs[$kAux]." ";
                      }
                      $timestamp=date("U");
                      $now=date("Y-m-d H:i:s");
                      $reconfigureLinkEnabled = intval($reconfigureLinkEnabled);
                      $ret = array("reconfigureLinkEnabled" => $reconfigureLinkEnabled,
                                   "outputType" => $outputType,
                                   "isHTML" => $isHTML,
                                   "isJSON" => $isJSON,
                                   "isCLI" => $isCLI,
                                   "isXML" => $isXML,
                                   "s" => $s,
                                   "v" => $v,
                                   "a" => $a
                                   );

                      if ($isHTML)
                        $ret["userMsg"] = $args;
                      else
                        $ret["userMsg"] = $noHTMLArgs;

                      if (is_array($ret["userMsg"])) {
                        $ret["userMsgDetails"] = array_slice($ret["userMsg"], 1);
                        $ret["userMsg"]=$ret["userMsg"][0];
                      }

                      if (function_exists("get_backtrace")) {
                        $ret["sys"]=array();
                        $auxStack = get_backtrace();
                        $stackNum = 0;
                        foreach($auxStack as $item) {
                          $ret["stack"]["$stackNum"]="$item";
                          $stackNum++;
                        }
                      }

                      if (!file_exists("deathLogs"))
                        mkdir("deathLogs",0777);
                      $f=fopen("deathLogs/c.$user_IP.log","a");
                      if ($f) {
                        fwrite($f, "/---DEATH----------\n");
                        foreach($noHTMLArgs as $arg) {
                          fwrite($f, "| $now $arg\n");
                        }
                        fwrite($f, "\---DEATH----------\n");
                        fclose($f);
                      }

                      $deathLogMessage="$now Fatal Error: $deathLogMessage OutputType: $outputType";
                      if (function_exists("_recordWastedTime"))
                        _recordWastedTime($deathLogMessage);
                      if (function_exists("_dump"))
                        _dump($deathLogMessage);

                      switch ($outputType) {
                        case 10:
                          /* JSON */
                          if ((is_string($callback)) && (trim($callback)>"")) {
                            echo "if (typeof $callback == \'function\') $callback(500, \'error\', {}, ".json_encode($ret).");";
                          } else {
                            echo json_encode($ret);
                          }
                          break;

                        case 100:
                          /* XML */
                          $xmlData="";

                          if (!isset($callBackFunction))
                            $callBackFunction="alert";

                          foreach($ret as $kAux=>$vAux) {
                            if (is_array($vAux)) {
                              $auxV="";
                              foreach($vAux as $k1=>$v2) {
                                if (is_numeric($k1))
                                  $k1=$kAux."_$k1";
                                $auxV.="\t<$k1>$v2</$k1>\n";
                              }
                              $vAux="$auxV";
                            }
                            if (is_numeric($kAux))
                              $kAux="_$kAux"."_";
                            $xmlData.="<$kAux>$vAux</$kAux>";
                          }
                          $xmlData="<callBackFunction>$callBackFunction</callBackFunction><dataContext>$xmlData</dataContext>";
                          $xmlOutput="<?xml version=\'1.0\' encoding=\'UTF-8\'?>\n<root>$xmlData<sgug><timestamp>$timestamp</timestamp></sgug></root>";
                          echo $xmlOutput;
                          break;

                        case 1000:
                          /* HTML */
                          if (function_exists("_minimalCSS")) {
                            _minimalCSS();
                          } else {
                            echo "<style>body {background-color: #f6f6f6;font-family: sans-serif;-webkit-font-smoothing: antialiased;font-size: 14px;line-height: 1.4;margin: 0;padding: 0;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;}</style>";
                          }
                          echo "<style>.userMsg { color: #800000} .userMsg .explain { font-size: 120%; font-weight: 800} .stack { color: #666666; font-family: \'Courier New\', Courier, monospace }</style>";

                          echo "<div style=\'padding: 16px; margin: 16px; border: dotted 1px #66CCFF; border-radius: 6px; background-color: #fff\'>";
                          echo "<div><a href=\'http://www.yeapf.com\' target=x$timestamp><img src=\'http://www.yeapf.com/logo.php\'></a></div><table>";
                          foreach($ret as $k=>$vAux) {
                            if (is_array($vAux)) {
                              foreach($vAux as $kx=>$vx) {
                                echo "<tr><td width=150px><span class=$k><span class=number>$k.$kx</span></span></td><td><span class=$k><span class=explain>$vx</span></span></td></tr>\n";
                              }
                            } else {
                              echo "<tr><td width=150px>$k</td><td>$vAux</td></tr>\n";
                            }
                          }
                          echo "</table></div>";
                          break;
                        default:

                          /* TEXT (cli) */
                          print_r($ret);
                      }

                      if (function_exists("db_set_flag")) {
                        db_set_flag(_DB_DIRTY_);
                      }
                      if ($SQLDieOnError) {
                        if ($SQLDieOnError==1)
                          die();
                        else {
                          exit($SQLDieOnError);
                        }
                      }
                    }
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
                  $__yeapfContext=$yeapfConfig["cfgCurrentFolder"];
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
                  ';

                  fwrite($f,"<?php\n$yeapfStub");
                  $now=date('U')+3;

                  // appFolder
                  $appFolderLoader = '
                  $md5Files=array("body.php", "index.php", "configure.php", "search.path");
                  $configMD5="";
                  foreach($md5Files as $aFileName) {
                    if (file_exists($aFileName)) {
                      $configMD5.=join("", file($aFileName));
                    }
                  }
                  $configMD5=md5($configMD5);
                  $yeapfStubMTime = filemtime("yeapf.php");
                  if ($yeapfStubMTime>'.$now.')
                    $savedConfigMD5=md5('.$now.');
                  else
                    $savedConfigMD5 = join("",file("configure.md5"));

                  if ((file_exists("configure.php")) && ($configMD5 != $savedConfigMD5)) {
                    _yLoaderDie(true, "YeAPF not configured\nRun <a href=\"configure.php\">configure.php</a> again.");
                  }
                  ';

                  fwrite($f,$appFolderLoader);

                  $yeapfStub2='
                  (@include_once $__yeapfPath."/yeapf.functions.php") || (_yLoaderDie("$__yeapfPath/yeapf.functions.php not found"));

                  _recordWastedTime("StubLoader ready");
                  if (!function_exists("decimalMicrotime")) _die("decimalMicrotime() required");

                  $t0=decimalMicrotime();

                  if (file_exists("flags/flag.dbgloader")) error_log(basename(__FILE__)." ".date("i:s").": verifiyng yeapf version\n",3,"logs/yeapf.loader.log");

                  _recordWastedTime("Checking YeAPF Version");

                  if (function_exists("yeapfVersion"))
                    if (("%"."YEAPF_VERSION%")==yeapfVersion()) {
                      error_log(basename(__FILE__)." ".date("i:s").": WARNING: Using developer version\n",3,"logs/yeapf.loader.log");
                    } else {
                      if (("0.8.61") != yeapfVersion())
                        _yLoaderDie(true, "Your configure version is \'0.8.61\' while your installed version is \'".yeapfVersion()."\'");
                    }
                  if (!isset($appName))
                    $appName = "dummy";
                  $yeapfConfig["searchPath"]=$appName.";".$yeapfConfig["searchPath"];
                  set_include_path(get_include_path().":".str_replace(";",":",$yeapfConfig["searchPath"]));
                  ';
                  fwrite($f,$yeapfStub2);

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

                  echo sayStep("Stubloader 'yeapf.php' has been created");

                  $referer_uri=isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'./';
                  if ($referer_uri==$_SERVER['SCRIPT_NAME'])
                    $referer_uri='./';

                  if (file_exists('develop.php'))
                    $developLink="<div class='basicLink'><a class='minButton' href='develop.php'>Develop</a></div>";
                  else
                    $developLink='';

                  if ($silent) {
                    echo "<br>\n<u>YeAPF 0.8.61 well configured!<span class=greenDot></span></u>";
                    echo "<br>\nYeAPF Folder: <b>$__PL__</b>";
                    echo "<br>\nDB config: <b>$dbCSVFilename</b>";
                    echo "<br>\nDebug IP: <b>".(isset($cfgDebugIP)?$cfgDebugIP:'none')."</b>";
                  } else {
                    echo sayStep("<style>.basicLink {max-width: 240px; float: left; height: 24px; padding-right: 18px} .minButton {   display: inline-block;   margin: 0;   padding: 0.75rem 1rem;   border: 0;   border-radius: 0.317rem;   background-color: #aaa;   color: #fff;   text-decoration: none;   font-weight: 700;   font-size: 1rem;   line-height: 1.5;   font-family: 'Helvetica Neue', Arial, sans-serif;   cursor: pointer;   -webkit-appearance: none;   -webkit-font-smoothing: antialiased; }  .minButton:hover {   opacity: 0.85; }  .minButton:active {   box-shadow: inset 0 3px 4px hsla(0, 0%, 0%, 0.2); }  .minButton:focus {   outline: thin dotted #444;   outline: 5px auto -webkit-focus-ring-color;   outline-offset: -2px; }  .minButton_primary {   background-color: #1fa3ec; }  .minButton_secondary {   background-color: #e98724; }  .minButton-icon {   display: inline-block;   position: relative;   top: -0.1em;   vertical-align: middle;   margin-right: 0.317rem; }</style><div style='box-shadow: 5px 5px 2px #888888; padding: 16px; margin: 16px; border: dotted 1px #66CCFF; border-left: solid 8px #337001; border-radius: 6px; background-color: #fff'><big><u>YeAPF 0.8.61 well configured!</u>&nbsp;<span class=greenDot></span></big><div style='padding-left: 16px'>Location: <b>$__PL__</b><br>DB config: <b>$dbCSVFilename</b><br>Debug IP: <b>".(isset($cfgDebugIP)?$cfgDebugIP:'none')."</b></div><div style='width:100%; height: 24px'><div class='basicLink'><a class='minButton' href='$referer_uri'>Back</a></div><div class='basicLink'><a class='minButton' href='configure.php?debugSteps=1'>Debug configure</a></div><div class='basicLink'><a class='minButton minButton_primary' href='index.php'>Start</a></div><div class='basicLink'>$developLink</div><div class='basicLink'><a class='minButton minButton_secondary' href='configure.php?destroydb=yes'>Recreate DB conn</a></div></div><br></div>");
                  }
                  $aux=join(file('.config/yeapf.config'),'<br>');
                  // echo echoStep("<div class=code>$aux</div>");
                  $aux=join(file('yeapf.php'),'<br>');
                  // echo echoStep("<div class=code>$aux</div>");

                } else {
                  writeConfigFile("cfgMainFolder",getcwd());
                  $errMsg="<BR><span class=redDot></span>The dbConnection could not be written.<br>Check your access rights to '".getcwd()."' folder ";
                  echo sayStep($errMsg);
                  if ($silent) echo $errMsg;
                  echo sayStep("<span class=err>(You can debug configuration process clicking <a href='configure.php?debugSteps=1'>here</a>)</span>");
                }
              }

            } else {
              $errMsg="<BR><span class=redDot></span>The timezone cannot be setted.<br>Choose a correct one and try again.";
              echo sayStep($errMsg);
              if ($silent) echo $errMsg;
              echo sayStep("<span class=err>(You can debug configuration process clicking <a href='configure.php?debugSteps=1'>here</a>)</span>");
            }

          } else {
            $errMsg="<span class=redDot></span><span class=err>Define PHP timeZone before configure</span><span class=err>UTC is not accepted as default timeZone</span>";
            echo sayStep($errMsg);
              if ($silent) echo $errMsg;
          }
        }
        unlock('configure');
      } else {
        $errMsg="<div>LOCK CANNOT BE CREATED</div><div><small>LOCK_VERSION: $LOCK_VERSION</small></div><div><small>CFG_LOCK_DIR: $CFG_LOCK_DIR</small></div>";
        echo sayStep($errMsg);
        if ($silent)
          echo $errMsg;
        echo sayStep("<span class=redDot></span><span class=err>(You can debug configuration process clicking <a href='configure.php?debugSteps=1'>here</a>)</span>");

      }
    }

    if ($lockCanBeCreated<2) {
      $errMsg="<div class=err><div class=errItem>Was not possible to lock the system (stage $lockCanBeCreated. LOCK_VERSION=$LOCK_VERSION)<br>Check your installation</div>";
      if ($lockCanBeCreated<1)
        $errMsg.="<div class=errItem>You have not enough rights to write to the filesystem on <b>".getcwd()."</b><br>Please give write, read and execution rights to this folder and try again</div>";
      $errMsg.="</div>";
      echo sayStep($errMsg);
      echo sayStep("<span class=redDot></span><span class=err>(You can debug configuration process clicking <a href='configure.php?debugSteps=1'>here</a>)</span>");
      if ($silent) echo $errMsg;
    }

  } else {
    $errMsg="<span class=err>Was not possible to create support folders<br>Your main folder ($homeURL) must to have enough rights to be written by httpd server</span>";
    echo sayStep($errMsg);
    if ($silent) echo $errMsg;
    echo sayStep("<span class=redDot></span><span class=err>(You can debug configuration process clicking <a href='configure.php?debugSteps=1'>here</a>)</span>");
  }

  if (!$isCLI) {
    echo "</div></div></body>\n</html>\n";
  }
?>
