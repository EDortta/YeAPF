<?php
/*
    includes/yeapf.debug.php
    YeAPF 0.8.52-1 built on 2016-11-10 13:41 (-2 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-09-09 13:04:09 (-2 DST)
*/
  if (function_exists('_recordWastedTime'))
    _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  function yeapfVersion() {
    return "0.8.52";
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
    global $logOutput, $user_IP, $canDoLog, $_echoCount, $isCLI;

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
    $argList=_caller_().$argList;
    if (!isset($canDoLog))
      $canDoLog=true;
    if ($canDoLog) {
      if ($logOutput<0) {
        $canDoLog=((!$isCLI) && (is_dir('logs') && (is_writable("logs")))) ||
                   (($isCLI) && (is_dir('/var/log') && (is_writable("/var/log/yeapfApp.log"))));
        if ($canDoLog) {
          if ($isCLI)
            $logLocation = "/var/log/yeapfApp.log";
          else
            $logLocation = "logs/c.$user_IP.log";
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
    if (!$flgMinimalCSS)
      if (!(outIsXML() || outIsJSON())) {
        echo "<style>.dbErr{padding: 8px; border-radius: 8px; border:solid 1px #E31818; background:#8A0101; color: white} .dbOk {border-radius: 8px; border:solid 1px #39E318; background:#0E8A01; color: white} .dbWarn {border-radius: 8px; border:solid 1px #E3DF18; background:#8A6601; color: white}</style>\n";
      }
    $flgMinimalCss = 1;
  }


  function _die()
  {
    global $user_IP, $sysDate, $isCLI;

    _minimalCSS();

    $args=func_get_args();
    $argList='';
    foreach ($args as $a) {
      if ($argList>'')
        $argList.=' ';
      if (is_array($a)) {
        foreach($a as $v)
          $argList.="$v ";
      } else
        $argList.=$a;
    }

    if (!file_exists('deathLogs'))
      mkdir('deathLogs',0777);
    $f=fopen("deathLogs/c.$user_IP.log","a");
    if ($f) {
      $auxArgs=str_replace("<br>","\n",$argList);
      $auxArgs=str_replace("<BR>","\n",$auxArgs);
      fwrite($f, "$auxArgs\n");
      fclose($f);
    }

    $sysDate = date("YmdHis");
    $outputType=outIsJSON()*100+outIsText()*10+outIsXML()*1;
    $deathLogMessage=strip_tags(stripNL("APP DIES AT $sysDate<BR>$argList<br>outType: $outputType"));
    _recordWastedTime($deathLogMessage);

    _dump($deathLogMessage);
    if (outIsText()) {
      echo $deathLogMessage."\n\n";

    } else if (outIsXML()) {
      $argList=strip_tags(  html_entity_decode(htmlspecialchars_decode(br2nl($argList))));
      foreach(explode("\n",$argList) as $argElement)
        _recordError($argElement);
      /*
      $sysDieNum=0;
      foreach(explode("\n",$argList) as $argElement) {
        if (is_array($argElement)) {
          foreach($argElement as $k=>$v) {
            xq_context("sys.die.$sysDieNum","$k=$v");
            $sysDieNum++;
          }
        } else
          xq_context("sys.die.$sysDieNum",$argElement);
        $sysDieNum++;
      }
      */

      if (!isset($callBackFunction))
        $callBackFunction="alert";
      if (!isset($xq_return))
        $xq_return=null;
      if (!isset($xq_regCount))
        $xq_regCount=null;

      $GLOBALS['xmlData']=xq_produceContext($callBackFunction,$xq_return,$xq_regCount);

      if (!file_exists("e_body.xml"))
        _dumpY(0,0,"FATAL ERROR: 'e_body.xml' could not be found");
      else {
        $xResult=_file("e_body.xml");

        $xResult=iconv(detect_encoding($xResult),"ISO-8859-1", $xResult);
        if ($logging>2)
          _dumpY(1,1,$xResult);

        echo "$xResult";
      }

    } else if (outIsJSON()) {

    } else {

      echo "<div>$argList</div>";

    }
    throw new Exception("APP DIES:\n$auxArgs");
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
    global $logOutput, $SQLdebugLevel, $includedFiles, $lastCommands,
           $sgugIni, $dbTEXT_NO_ERROR, $debugNotifierSevice, $appName,
           $ydb_conn, $isCLI, $appCharset;

    $xCores = array("#000000","#009900","#3366CC","#FF6600","#CC66CC","#999999");

    $debugOutput='';
    if ($forceExit) {
      if ($SQLdebugLevel<2)
        $SQLdebugLevel=2;
    }

    if ($SQLdebugLevel>0) {
      if (function_exists('debug_backtrace'))
        $aux=debug_backtrace();
      if ($logOutput<0) {
        $debugOutput.="\n\n====================================\n$msg\n CWD=".getcwd()."\n";
      } else if ($logOutput==1) {
        $debugOutput.= "<style>";
        $debugOutput.= ".lblError { color:  #661111; font-family: Verdana, Arial, Sans-Serif; font-size:  8pt; font-weight: bold; } ";
        $debugOutput.= "</style>";
        $debugOutput.= "<big><b>$msg</b></big><br>".getcwd();
      } else if ($logOutput==2) {
        // xq_printXML($debugOutput,"backtrace.msg",$msg);
        xq_printXML($debugOutput,"backtrace.cwd",getcwd());
      }

      $incFileCount=0;

      foreach($includedFiles as $if)
        if ($logOutput<0)
          $debugOutput.="\t$if\n";
        else if ($logOutput==1)
          $debugOutput.= "&nbsp;&nbsp;&nbsp;&nbsp;$if<br>";
        else if ($logOutput==2) {
          $incFileCount++;
          xq_printXML($debugOutput,"backtrace.includedFiles.$incFileCount", $if);
        }

      if ($SQLdebugLevel>1) {
        if ($logOutput<0)
          $debugOutput.="\nCall stack";
        else if ($logOutput==1)
          $debugOutput.= "<table class=lblError>";

        $stackCounter=0;
        $stackXML='';

        $lastFile='';
        for($a=1; $a<count($aux); $a++) {
          $auxStackXML='';

          if ($logOutput<0)
            $debugOutput.="\n";
          else if ($logOutput==1)
            $debugOutput.= "<tr>";

          $ln=$aux[$a];

          $currentFile=basename($ln['file']);
          $dbLine='';

          if ($currentFile!=$lastFile) {
            if ($logOutput<0)
              $debugOutput.="\t".alignRight($currentFile,40);
            else if ($logOutput==1)
              $debugOutput.= "<td valign=top><b>$currentFile</b></td>";
          } else {
            if ($logOutput<0)
              $debugOutput.="\t".alignRight($currentFile,40);
            else if ($logOutput==1)
              $debugOutput.= "<td></td>";
          }

          if ($logOutput==2)
            xq_printXML($auxStackXML, "file", $currentFile);

          $lastFile=$currentFile;

          if ($logOutput<0)
            $debugOutput.= "\t".$ln['line']."\t".$ln['function'];
          else if ($logOutput==1)
            $debugOutput.= "<td valign=top align=right>".$ln['line']."</td><td align=right valign=top><i><b>".$ln['function']."</b></i></td>";
          else  if ($logOutput==2) {
            xq_printXML($auxStackXML, "line", $ln['line']);
            xq_printXML($auxStackXML, "function", $ln['function']);
          }

          $args='';
          if ($SQLdebugLevel>2) {
            if ($ln['args']) {
              $xc=1;
              $argCount=0;
              foreach($ln['args'] as $ak => $av) {
                if ($args>'')
                  $args.=', ';
                $av=strip_tags($av);
                if (!is_numeric($av))
                  $av='"'.$av.'"';
                $cc=$xCores[$xc];
                $xc=($xc+1) % count($xCores);
                if ($logOutput<0)
                  $args.=str_replace("\n",'\n',str_replace("\r",'',$av));
                else if ($logOutput==1)
                  $args.="<font color='$cc'>$av</font>";
                else if ($logOutput==2) {
                  $argCount++;
                  xq_printXML($auxStackXML, "arg.$argCount", $av);
                }
              }
            }
          }
          if ($logOutput<0)
            $debugOutput.="($args)";
          else if ($logOutput==1)
            $debugOutput.= "<td>($args)</td>";


          /*
          foreach($aux[$a] as $kk=>$vv) {
            if ($kk=='args') {
              foreach($vv as $ak=>$av) {
                $av=strip_tags($av);
                $debugOutput.= "$ak $av<br>";
              }
            } else
              $debugOutput.= "$kk = $vv<br>";
          }
          */
          if ($logOutput<0)
            $debugOutput.=";";
          else if ($logOutput==1)
            $debugOutput.= "</tr>";
          else if ($logOutput==2) {
            $stackCounter++;
            xq_printXML($stackXML,"stack.$stackCounter",$auxStackXML);
          }
        }
        xq_printXML($debugOutput, "sys.stack", $stackXML);

        if ($logOutput<0)
          $debugOutput.="\nSQL Usage\n\t".str_replace("<BR>","\n\t",$lastCommands);
        else if ($logOutput==1)
          $debugOutput.= "<tr><td colspan=4>$lastCommands</td></tr>";
        else if ($logOutput==2)
          xq_printXML($debugOutput,"sys.sqlTrace",br2nl($lastCommands));

        if (db_connectionTypeIs(_MYSQL_)) {
          $errno = mysql_errno($ydb_conn);
          $error = mysql_error($ydb_conn);
          if ($logOutput<0)
            $debugOutput.="\nLast MySQL Error: $errno:$error\n";
          else if ($logOutput==1)
            $debugOutput.= "<tr><td colspan=4><font size=+3 color=#dd0000>$errno: $error</font></td></tr>";

        } else if (db_connectionTypeIs(_FIREBIRD_)) {
          $errno = ibase_errcode();
          $error = ibase_errmsg();
          if ($logOutput<0)
            $debugOutput.="Last FIREBIRD Error: $errno:$error";
          else if ($logOutput==1)
            $debugOutput.= "<tr><td colspan=4><font size=+3 color=#dd0000>$errno: $error</font></td></tr>";
        }

        if ($logOutput==2)
          xq_printXML($debugOutput,"sys.sqlError","$errno:$error");

        $setupIni=createDBText($sgugIni);
        if (($setupIni->locate("active",1))==$dbTEXT_NO_ERROR) {
          $dbType=$setupIni->getValue('dbType');
          $dbServer=$setupIni->getValue('dbServer');
          $dbName=$setupIni->getValue('dbName');
          if ($logOutput<0)
            $debugOutput.="\n\tdbServer: $dbName ($dbType) on $dbServer";
          else if ($logOutput==1)
            $debugOutput.= "<tr><td colspan=4 style='border-style: solid; border-width:1px'><i>$dbType</i><br>$dbServer : $dbName</td></tr>";
        }

        if (!function_exists("_dumpArray_")) {

          function _dumpArray_(&$debugOutput, $aArray, $margin='')
          {
            global $logOutput;

            _dumpY(1,7,$margin."INICIO");
            foreach($aArray as $k=>$v)
            {
              if (($k!='GLOBALS') && ($k!='debugOutput') && ($k!='abbreviations') && ($k!='mimeExtensions') && ($k!='colorList') && ($k!='setupIni')) {
                if ($logOutput<0) {
                  if (is_array($v)) {
                    $debugOutput.="$margin$k = ";
                    _dumpY(1,7,"$margin  $k");
                    _dumpArray_($debugOutput, $v,"$margin  ");
                  } else if (!is_object($v)) {
                    if (trim($v)>'')
                      $debugOutput.="$margin$k =  '$v'\n";
                    _dumpY(1,7,"$margin  $k = '$v'");
                  }
                }
              }
            }

            _dumpY(1,7,$margin."FIM");
          }
        }
        // die(var_dump($GLOBALS));

        if ($logOutput<0) {
          $debugOutput.="\nGlobal Vars\n";
          _dumpArray_($debugOutput, $GLOBALS,"  ");
        }

        if ($logOutput<0)
          $debugOutput.="\n";
        else if ($logOutput==1)
          $debugOutput.= "</table>";
      }

      if ($logOutput<0)
        $debugOutput.="\n$sgugIni\n";
      else if ($logOutput==1)
        $debugOutput.= "<div><b>$sgugIni</b></div>";

      if ($SQLdebugLevel>3)
        $forceExit=true;

      $result=0;

      if ($debugNotifierSevice>'') {
        $dbgService=getNextValue($debugNotifierService,'&').'?wsdl';
        $dbgEmail=getNextValue($debugNotifierService,'&');
        if ($dbgEmail=='')
          $dbgEmail='dortta@yahoo.com';

        if (class_exists("nusoap_client")) {
          $dbgNotifier=new nusoap_client($dbgService, true);
          $result = $dbgNotifier->call('getStatus', array('uid' => ''));
          if ($result==512) {
            $result = $dbgNotifier->call('sendNotification',
                                  array('uid' => '',
                                        'to' => $dest,
                                        'cc' => "$dbgEmail",
                                        'bcc' => '',
                                        'subject' => "SGUG.DEBUGGER: $appName",
                                        'body' => $debugOutput));

          } else
            $result=0;
        }
        if ($logOutput<0)
          $debugOutput.="\nNotificação online - ";
        else if ($logOutput==1)
          $debugOutput.='<br><font size=-1>';

        if ($result==0) {
          if ($logOutput<0)
            $debugOutput.="NÃO enviada";
          else if ($logOutput==1)
            $debugOutput.="<font color=#dd0000>Notificação online NÃO enviada</font>";
        } else {
          if ($logOutput<0)
            $debugOutput.="$dbgEmail";
          else if ($logOutput==1)
            $debugOutput.="Notificação online enviada para <b>$dbgEmail</b>";
        }
        if ($logOutput<0)
          $debugOutput.="\n";
        else if ($logOutput==1)
          $debugOutput.='</font><br>';
      }
    }

    if ($logOutput==2) {
      xq_printXML($auxOutput,'errMsg', $msg);
      xq_printXML($auxOutput,'errDetail', $debugOutput);
      xq_context('error', $auxOutput);
    } else
      _echo("$debugOutput");

    // testar  com view-source:http://localhost/SGH/query.php?s=lancamentosUTI&u=0&a=carregarListaLeitos&fieldName=&fieldValue=&ts=1332500612805&callBackFunction=listaLeitos


    if ($forceExit) {
      if (!$isCLI) {
        if (!headers_sent())
          header("Content-Type: text/html; charset=$appCharset");
        _die("<h3>Forced exit</h3><div style='padding:32px;color:#900;font-weight:800'><big><b>$msg</b></big></div><div><h3>Click Context</h3>".nl2br($debugOutput)."</div>");
      } else
        _die("\nFORCED EXIT\n$debugOutput");
    }
  }

  // testar com http://10.0.2.1/~esteban/webApps/metaForms/body.php??u=14&s=formGenerator&a=grantTable&id=cadastroDeFuncionarios&=&=
  function _caller_()
  {
    global $yeapfLogBacktrace, $_debugTag;

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
      $reservedFilenames=" :yeapf.:query.:query.:xParser.:";

      $callerNdx=-1;
      $inReservedFunctions=true;
      while ($inReservedFunctions) {
        $callerNdx++;
        if (isset($myBacktrace[$callerNdx])) {
          $funName=getArrayValueIfExists($myBacktrace[$callerNdx],'function','annonymous');
          $funLine=getArrayValueIfExists($myBacktrace[$callerNdx],'line','');
          $funFileName=basename(getArrayValueIfExists($myBacktrace[$callerNdx],'file',''));
          $funPos=strpos($reservedFunctions, ":$funName:");
          $inReservedFunctions=($funPos>0) || !($yeapfLogBacktrace&2);
          if ($inReservedFunctions)
            $curCall="$funFileName at $funLine";
        } else
          $inReservedFunctions=false;
      }

      if (function_exists("decimalMicrotime"))
        $wastedTime=decimalMicrotime()-$_debugTag;
      else
        $wastedTime=0;

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
          $fileName=basename($ln['file']);

          $auxRes=$_debugTag.':'.$fileName.'('.$ln['line'].'):'.$ln['function'].' ';
          if ($stackRes>'')
            $stackRes.="\n";
          $stackRes.=$auxRes;
          // $p=strpos($fileName,'yeapf');
          $funName=$ln['function'];
          $p=strpos($reservedFunctions,":$funName:");
          if (!$p) {
            $auxFileName=substr($fileName.'.',0,strpos($fileName.'.','.')+1);
            $p2=strpos($reservedFilenames,":$auxFileName:");
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
      $traces = debug_backtrace();
      $ret = array();
      foreach($traces as $i => $call){
          if ($i < $traces_to_ignore ) {
              continue;
          }

          $object = '';
          if (isset($call['class'])) {
              $object = $call['class'].$call['type'];
              if (is_array($call['args'])) {
                  foreach ($call['args'] as $arg) {
                      get_argx($arg);
                  }
              }
          }

          $ret[] = '#'.str_pad($i - $traces_to_ignore, 3, ' ')
          .$object.$call['function'].'('.implode(', ', $call['args'])
          .') called at ['.$call['file'].':'.$call['line'].']';
      }

      return implode("\n<br>",$ret);
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
