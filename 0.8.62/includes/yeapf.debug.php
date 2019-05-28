<?php
/*
    includes/yeapf.debug.php
    YeAPF 0.8.62-206 built on 2019-05-28 16:06 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
    2019-05-23 09:48:41 (-3 DST)
*/
  if (function_exists('_recordWastedTime'))
    _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  function yeapfVersion() {
    return "0.8.62";
  }

  function yeapfDevelVersion() {
    return ("%"."YEAPF_VERSION%")==yeapfVersion();
  }

  function yeapfBaseDir() {
    return dirname(__FILE__);
  }

  function globalDebug($flag)
  {
    if (($flag) || (file_exists('flag.dbgphp'))) {
      ini_set('display_errors','1');
      error_reporting (5);
    } else {
      ini_set('display_errors','0');
      error_reporting (0);
    }
  }

  global $echoCount;
  $_echoCount=0;

  function _echo()
  {
    global $logOutput, $user_IP, $canDoLog, $_echoCount, $isCLI, $cfgCurrentFolder, $cfgMainFolder;

    $args=func_get_args();
    $argList='';
    foreach ($args as $a) {
      if ($argList>'')
        $argList.=' ';
      $a=wordwrap($a,100,"\n");
      $a=substr($a,0,2).str_replace("\n","\n\t",substr($a,2));
      $argList.=$a;
    }
    if (substr($argList,strlen($argList)-2)=="\n\t")
      $argList=substr($argList,0,strlen($argList)-1);
    $argList=_caller_().'says: '.$argList;
    if (!isset($canDoLog))
      $canDoLog=true;
    if ($canDoLog) {
      if ($logOutput<0) {
        $canDoLog=((!$isCLI) && (is_dir("$cfgCurrentFolder/logs") &&
                                (is_writable("$cfgCurrentFolder/logs")))) ||
                   (($isCLI) && (is_dir('/var/log') &&
                                (is_writable("/var/log/yeapfApp.log"))));
        if ($canDoLog) {
          if ($isCLI)
            $logLocation = "/var/log/yeapfApp.log";
          else
            $logLocation = "$cfgCurrentFolder/logs/c.$user_IP.log";
          $logLocation = str_replace('..', '.', $logLocation);
          @error_log($argList, 3, $logLocation);
        }
      } else if ($logOutput==1)
        echo $argList;
      else if ($logOutput==2) {
        $_echoCount++;
        xq_context("sys.echo.$_echoCount", $argList);
      }
    }
  }

  function _dump()
  {
    global $logOutput;

    $args=func_get_args();
    $argList='';
    foreach ($args as $a) {
      if ($argList>'')
        $argList.=' ';
      $argList.=$a;
    }
    $aux=$logOutput;
    $logOutput=-1;
    _echo("$argList\n");
    $logOutput=$aux;
  }

  function _dumpY($logFlag, $level)
  {
    /*
     * yeapf.php as generated with configure.php will read flags/level.debug
     * and level.debug (in local path)
     * It's expected to have only an integer value inside both files that are 'ored'
     * to obtain a click debug level
     */
    global $yeapfLogFlags, $yeapfLogLevel;
    // echo "$yeapfLogFlags, $yeapfLogLevel ($logFlag, $level)<br>\n";

    if ($level<=$yeapfLogLevel) {
      if (($logFlag & $yeapfLogFlags) > 0) {
        $paramNdx=0;
        $args=func_get_args();
        $argList='';
        foreach ($args as $a) {
          $paramNdx++;
          if ($paramNdx>2) {
            if ($argList>'')
              $argList.=' ';
            $argList.=$a;
          }
        }
        _dump("$argList");
      }
    }
  }

  function _minimalCSS() {
    global $flgMinimalCSS;
    if (!$flgMinimalCSS) {
      if (!(outIsXML() || outIsJSON() || outIsText())) {
        echo "\n<style>input [type='submit'] {}.formBox {}.formBox h3 {}img {border: none;-ms-interpolation-mode: bicubic;max-width: 100%;}body {background-color: #f6f6f6;font-family: sans-serif;-webkit-font-smoothing: antialiased;font-size: 14px;line-height: 1.4;margin: 0;padding: 0;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;}table {border-collapse: separate;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;}.fanfold tbody tr:nth-child(even) {background: #e8e8e8 }.fanfold tbody tr:nth-child(odd) {background: #f0f0f0 }table td {font-family: sans-serif;font-size: 14px;vertical-align: top;}.body {background-color: #f6f6f6;width: 100%;max-width: 620px;}.container {display: block;Margin: 0 auto !important;max-width: 580px;padding: 10px;width: 580px;}.content {box-sizing: border-box;display: block;Margin: 0 auto;max-width: 580px;padding: 10px;}.main {background: #fff;border-radius: 3px;width: 100%;}.wrapper {box-sizing: border-box;padding: 20px;}.footer {clear: both;padding-top: 10px;text-align: center;width: 100%;}.footer td, .footer p, .footer span, .footer a {color: #999999;font-size: 12px;text-align: center;}.dbErr{color: #FF0000;}.dbOk {color: #00FF00}.dbWarn {color: #FF8000}h1, h2, h3, h4 {color: #000000;font-family: sans-serif;font-weight: 400;line-height: 1.4;margin: 0;Margin-bottom: 30px;}h1 {font-size: 35px;font-weight: 300;text-align: center;text-transform: capitalize;}p, ul, ol {font-family: sans-serif;font-size: 14px;font-weight: normal;margin: 0;Margin-bottom: 15px;}p li, ul li, ol li {list-style-position: inside;margin-left: 5px;}a {color: #3498db;text-decoration: underline;}.btn {box-sizing: border-box;width: 100%;}.btn>tbody>tr>td {padding-bottom: 15px;}.btn table {width: auto;}.btn table td {background-color: #ffffff;border-radius: 5px;text-align: center;}.btn a {background-color: #ffffff;border: solid 1px #3498db;border-radius: 5px;box-sizing: border-box;color: #3498db;cursor: pointer;display: inline-block;font-size: 14px;font-weight: bold;margin: 0;padding: 12px 25px;text-decoration: none;text-transform: capitalize;}.btn-primary table td {background-color: #3498db;}.btn-primary a {background-color: #3498db;border-color: #3498db;color: #ffffff;}.last {margin-bottom: 0;}.first {margin-top: 0;}.align-center {text-align: center;}.align-right {text-align: right;}.align-left {text-align: left;}.clear {clear: both;}.mt0 {margin-top: 0;}.mb0 {margin-bottom: 0;}.preheader {color: transparent;display: none;height: 0;max-height: 0;max-width: 0;opacity: 0;overflow: hidden;mso-hide: all;visibility: hidden;width: 0;}.powered-by a {text-decoration: none;}hr {border: 0;border-bottom: 1px solid #f6f6f6;Margin: 20px 0;}@media only screen and (max-width: 620px) {table[class=body] h1 {font-size: 28px !important;margin-bottom: 10px !important;}table[class=body] p, table[class=body] ul, table[class=body] ol, table[class=body] td, table[class=body] span, table[class=body] a {font-size: 16px !important;}table[class=body] .wrapper, table[class=body] .article {padding: 10px !important;}table[class=body] .content {padding: 0 !important;}table[class=body] .container {padding: 0 !important;width: 100% !important;}table[class=body] .main {border-left-width: 0 !important;border-radius: 0 !important;border-right-width: 0 !important;}table[class=body] .btn table {width: 100% !important;}table[class=body] .btn a {width: 100% !important;}table[class=body] .img-responsive {height: auto !important;max-width: 100% !important;width: auto !important;}}@media all {.ExternalClass {width: 100%;}.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;}.apple-link a {color: inherit !important;font-family: inherit !important;font-size: inherit !important;font-weight: inherit !important;line-height: inherit !important;text-decoration: none !important;}.btn-primary table td:hover {background-color: #34495e !important;}.btn-primary a:hover {background-color: #34495e !important;border-color: #34495e !important;}.logo {margin: 0px;padding: 0px;height: 132px;width: 399px;}}</style>\n";
      }
    }
    $flgMinimalCss = 1;
  }

  function _defaultExceptionHandler($exception) {
    global $_defaultExceptionHandler_done;
    if (!isset($_defaultExceptionHandler_done)) {
      $_defaultExceptionHandler_done=true;
      _die($exception->getMessage());
      // showDebugBackTrace($exception->getMessage(), true);
    }
  }
  set_exception_handler('_defaultExceptionHandler');


  function _die()
  {

    _yLoaderDie(false, func_get_args());

    // throw new YException("Fatal error:\n$auxArgs");
  }

  if ((isset($logRequest)) && ($logRequest)) {
    $_request_=publicarPOST(false,false);
    _log($_request_);
  }

  function alignRight($texto, $len)
  {
    $texto=trim($texto);
    while (strlen($texto)<$len)
      $texto=" $texto";
    return $texto;
  }

  function showDebugBackTrace($msg, $forceExit=false)
  {
    $trace = get_backtrace(1);
    if ($forceExit) {
      foreach($trace as $traceItem) {
        _recordError("DIE ".$traceItem);
      }
      _die($msg, $trace);
    }
    return $trace;
  }

  // testar com http://10.0.2.1/~esteban/webApps/metaForms/body.php??u=14&s=formGenerator&a=grantTable&id=cadastroDeFuncionarios&=&=
  function _caller_()
  {
    global $yeapfLogBacktrace, $_debugTag, $_lastTag;

    $traceCall=isset($yeapfLogBacktrace)?($yeapfLogBacktrace&1):false;
    $res='';
    if ((function_exists('debug_backtrace')) && (function_exists('getArrayValueIfExists'))) {
      $myBacktrace=debug_backtrace();
      $a=0;
      $stackRes='';
      $p=false;
      $fileName='';
      $lnAux2='';
      $ln='';

      $reservedFunctions=" :_caller_:_echo:_dump:_dumpY:";
      $reservedFilenames=" :yeapf.:rest.:query.:query.:xParser.:";

      $callerNdx=-1;
      $inReservedFunctions=true;
      while ($inReservedFunctions) {
        $callerNdx++;
        if (isset($myBacktrace[$callerNdx])) {
          $funName=getArrayValueIfExists($myBacktrace[$callerNdx],'function','annonymous');
          $funLine=getArrayValueIfExists($myBacktrace[$callerNdx],'line','');
          $funFileName=basename(getArrayValueIfExists($myBacktrace[$callerNdx],'file',''));
          $funPos=strpos($reservedFunctions, ":$funName:");

          $inReservedFunctions=($funPos>0) || ($yeapfLogBacktrace&2);
          if ($inReservedFunctions)
            $curCall="$funFileName at $funLine";
        } else
          $inReservedFunctions=false;
      }


      if (function_exists("decimalMicrotime")) {
        if (!isset($_lastTag))
          $_lastTag=$_debugTag;
        $_currentTag=decimalMicrotime();
        $wastedTime=$_currentTag-$_lastTag;
        $_lastTag = $_currentTag;
      } else {
        $wastedTime="?.0";
      }
      $wastedTime.="00000";
      $wastedTime=substr($wastedTime,0,7);

      $priorCall="$funFileName at $funLine";
      if ($curCall==$priorCall)
        $res="$_debugTag ($wastedTime):->$curCall:$funName() ";
      else
        $res="$_debugTag ($wastedTime):$priorCall -> $curCall:$funName() ";

      $res=date("YmdHis ").$res;

      do {
        $callerNdx++;
        $lnAux1=$lnAux2;
        $lnAux2=$ln;
        /*
        if (isset($myBacktrace[$callerNdx]))
          $myBacktrace[$callerNdx]=array('file'=>'', 'line'=>'', 'function'=>'');
        */
        if (isset($myBacktrace[$callerNdx])) {
          $ln=$myBacktrace[$callerNdx];
          if ($fileName>'')
            $lastFileName=$fileName;

          if (isset($ln['file'])) {
            $fileName=basename($ln['file']);

            $auxRes=$_debugTag;
            if (isset($ln['line']))
              $auxRes.=':'.$fileName.'('.$ln['line'].'):'.$ln['function'].' ';

            if ($stackRes>'')
              $stackRes.="\n";
            $stackRes.=$auxRes;
            // $p=strpos($fileName,'yeapf');
            $funName=isset($ln['function'])?$ln['function']:"_UNKNOWN_FUNCTION_";
            $p=strpos($reservedFunctions,":$funName:");
            if (!$p) {
              $auxFileName=substr($fileName.'.',0,strpos($fileName.'.','.')+1);
              $p2=strpos($reservedFilenames,":$auxFileName:");
            }
          }
        }
      } while ($callerNdx<count($myBacktrace));
      if ($traceCall)
        $res="\n\n$stackRes\n$res";
    }
    return $res;
  }

  function _log($_request_)
  {
    global $u, $sysTimeStamp, $_REQ_NO, $_REQ_BASE, $_LOG_SYS_REQUEST,
            $usrTableName, $usrSessionIDField, $usrUniqueIDField;

    if (($_LOG_SYS_REQUEST) && (db_tableExists('is_context'))) {
      if ($_REQ_BASE=='')
        $_REQ_BASE=y_uniqid();
      $_REQ_NO++;
      $q=$_REQ_NO;
      while (strlen($q)<3)
        $q="0$q";
      $_id_=$_REQ_BASE.'-'.$q;
      if ($u>'')
        $_user_=valorSQL("select $usrUniqueIDField from $usrTableName where $usrSessionIDField='$u'");
      fazerSQL("insert into is_sysrequest (id, ts, usr, request) values ('$_id_', '$sysTimeStamp', '$_user_', '$_request_')");
    }
  }

  function _record(&$var, $description)
  {
    if ($var>'')
      $var.="\n";
    $var.=$description;
  }

  function _recordError($errorDesc, $warnLevel=1)
  {
    global $lastError, $lastWarning, $errorCount, $warningCount;

    if ($warnLevel>0) {
      $errorCount++;
      _dump("ERROR: $errorDesc");
      _record($lastError,$errorDesc);
    } else {
      $warningCount++;
      _dump("WARNING: $errorDesc");
      _record($lastWarning,$errorDesc);
    }
  }

  function _getErrorCount($warnLevel=1)
  {
    global $lastError, $lastWarning;

    $aux=array();
    if ($warnLevel>0) {
      if (trim($lastError>''))
        $aux=explode("\n",trim($lastError));
    } else {
      if (trim($lastWarning>''))
        $aux=explode("\n",trim($lastWarning));
    }

    return count($aux);
  }

  function _recordAction($actionDesc)
  {
    global $lastAction;

    _record($lastAction,$actionDesc);
  }

  function _requiredField($fieldName)
  {
    global $_requiredFields;

    if ($_requiredFields>'')
      $_requiredFields.=',';
    $_requiredFields.=$fieldName;
  }

  function _statusBar($description)
  {
    global $statusBarPosition, $lastStatusBar;

    $statusBarPosition=intval($statusBarPosition);

    $aux = "<div style='background-color:#FFFF80;border-color:#C0C0C0;border-style:dotted;border-width:1px;";
    $aux.= "color:#000000;font-family:TrebuchetMS,Sans-Serif;font-size:10pt;font-weight:bold;right:0px;padding-right:4px;";
    $aux.= "padding-right:4px;position:absolute;top:0px;right:$statusBarPosition'>";
    $aux.= "$description";
    $aux.= "</div>";
    $statusBarPosition+=strlen($description)*8;

    _record($lastStatusBar,$aux);
  }

  function get_backtrace($traces_to_ignore = 1)
  {
    $ret = array();
    if (function_exists("debug_backtrace")) {
      $specFunctions = array('db_connect', 'connect', 'password', 'pwd');

      $traces = debug_backtrace();
      // die(print_r($traces));
      foreach($traces as $i => $call){
          if ($i < $traces_to_ignore ) {
              continue;
          }

          $object = '';
          if (isset($call['class'])) {
              $object = $call['class'].$call['type'];
              if (is_array($call['args'])) {
                  foreach ($call['args'] as $arg) {
                      get_arg($arg);
                  }
              }
          }

          $retLine = count($ret);
          $ret[$retLine]  = ""; // str_pad($i - $traces_to_ignore, 3, ' ').': ';
          $ret[$retLine] .= $object;
          if (isset($call['function'])) {
            $ret[$retLine].= $call['function'].'(';
            if (isset($call['args'])) {
              $cArgs=0;
              foreach($call['args'] as $ak => $av) {
                if ($cArgs++>0)
                  $ret[$retLine].=', ';
                if (is_string($av)) {
                  if (in_array($call['function'], $specFunctions))
                    $av=str_repeat("*", mt_rand(3,5));
                  $av="'".$av."'";
                }
                if (is_string($av))
                  $ret[$retLine].=$av;
              }
              $ret[$retLine].=')';
            }
          }

          if (isset($call['file'])) {
            $fileName = $call['file'];
            if (substr($fileName,0,strlen($_SERVER["DOCUMENT_ROOT"])) == $_SERVER["DOCUMENT_ROOT"])
              $fileName=substr($fileName, strlen($_SERVER["DOCUMENT_ROOT"]));
            $ret[$retLine].=' '.$fileName.':'.$call['line'];
          }
/*
          $ret[] = '#'.str_pad($i - $traces_to_ignore, 3, ' ')
          .$object.$call['function'].'('.implode(', ', $call['args'])
          .') called at ['.$call['file'].':'.$call['line'].']';
*/
      }
    }
    return $ret;
  }

  function get_arg(&$arg) {
      if (is_object($arg)) {
          $arr = (array)$arg;
          $args = array();
          foreach($arr as $key => $value) {
              if (strpos($key, chr(0)) !== false) {
                  $key = '';    // Private variable found
              }
              $args[] =  '['.$key.'] => '.get_arg($value);
          }

          $arg = get_class($arg) . ' Object ('.implode(',', $args).')';
      }
  }
?>
