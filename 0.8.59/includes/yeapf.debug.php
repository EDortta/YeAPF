<?php
/*
    includes/yeapf.debug.php
    YeAPF 0.8.59-198 built on 2018-05-11 06:23 (-3 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2018-05-11 06:21:39 (-3 DST)
*/
  if (function_exists('_recordWastedTime'))
    _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  function yeapfVersion() {
    return "0.8.59";
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
    if (!$flgMinimalCSS)
      if (!(outIsXML() || outIsJSON() || outIsText())) {
        echo "\n<style>input [type='submit] {}.formBox {}.formBox h3 {}img {border: none;-ms-interpolation-mode: bicubic;max-width: 100%;}body {background-color: #f6f6f6;font-family: sans-serif;-webkit-font-smoothing: antialiased;font-size: 14px;line-height: 1.4;margin: 0;padding: 0;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;}table {border-collapse: separate;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;}.fanfold tbody tr:nth-child(even) {background: #e8e8e8 }.fanfold tbody tr:nth-child(odd) {background: #f0f0f0 }table td {font-family: sans-serif;font-size: 14px;vertical-align: top;}.body {background-color: #f6f6f6;width: 100%;max-width: 620px;}.container {display: block;Margin: 0 auto !important;max-width: 580px;padding: 10px;width: 580px;}.content {box-sizing: border-box;display: block;Margin: 0 auto;max-width: 580px;padding: 10px;}.main {background: #fff;border-radius: 3px;width: 100%;}.wrapper {box-sizing: border-box;padding: 20px;}.footer {clear: both;padding-top: 10px;text-align: center;width: 100%;}.footer td, .footer p, .footer span, .footer a {color: #999999;font-size: 12px;text-align: center;}.dbErr{color: #FF0000;}.dbOk {color: #00FF00}.dbWarn {color: #FF8000}h1, h2, h3, h4 {color: #000000;font-family: sans-serif;font-weight: 400;line-height: 1.4;margin: 0;Margin-bottom: 30px;}h1 {font-size: 35px;font-weight: 300;text-align: center;text-transform: capitalize;}p, ul, ol {font-family: sans-serif;font-size: 14px;font-weight: normal;margin: 0;Margin-bottom: 15px;}p li, ul li, ol li {list-style-position: inside;margin-left: 5px;}a {color: #3498db;text-decoration: underline;}.btn {box-sizing: border-box;width: 100%;}.btn>tbody>tr>td {padding-bottom: 15px;}.btn table {width: auto;}.btn table td {background-color: #ffffff;border-radius: 5px;text-align: center;}.btn a {background-color: #ffffff;border: solid 1px #3498db;border-radius: 5px;box-sizing: border-box;color: #3498db;cursor: pointer;display: inline-block;font-size: 14px;font-weight: bold;margin: 0;padding: 12px 25px;text-decoration: none;text-transform: capitalize;}.btn-primary table td {background-color: #3498db;}.btn-primary a {background-color: #3498db;border-color: #3498db;color: #ffffff;}.last {margin-bottom: 0;}.first {margin-top: 0;}.align-center {text-align: center;}.align-right {text-align: right;}.align-left {text-align: left;}.clear {clear: both;}.mt0 {margin-top: 0;}.mb0 {margin-bottom: 0;}.preheader {color: transparent;display: none;height: 0;max-height: 0;max-width: 0;opacity: 0;overflow: hidden;mso-hide: all;visibility: hidden;width: 0;}.powered-by a {text-decoration: none;}hr {border: 0;border-bottom: 1px solid #f6f6f6;Margin: 20px 0;}@media only screen and (max-width: 620px) {table[class=body] h1 {font-size: 28px !important;margin-bottom: 10px !important;}table[class=body] p, table[class=body] ul, table[class=body] ol, table[class=body] td, table[class=body] span, table[class=body] a {font-size: 16px !important;}table[class=body] .wrapper, table[class=body] .article {padding: 10px !important;}table[class=body] .content {padding: 0 !important;}table[class=body] .container {padding: 0 !important;width: 100% !important;}table[class=body] .main {border-left-width: 0 !important;border-radius: 0 !important;border-right-width: 0 !important;}table[class=body] .btn table {width: 100% !important;}table[class=body] .btn a {width: 100% !important;}table[class=body] .img-responsive {height: auto !important;max-width: 100% !important;width: auto !important;}}@media all {.ExternalClass {width: 100%;}.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;}.apple-link a {color: inherit !important;font-family: inherit !important;font-size: inherit !important;font-weight: inherit !important;line-height: inherit !important;text-decoration: none !important;}.btn-primary table td:hover {background-color: #34495e !important;}.btn-primary a:hover {background-color: #34495e !important;border-color: #34495e !important;}.logo {margin: 0px;padding: 0px;height: 132px;width: 399px;}}</style>\n";
      }
    $flgMinimalCss = 1;
  }


  function _die()
  {
    global $user_IP, $sysDate, $isCLI;

    _minimalCSS();
    $auxArgs='';

    $args=func_get_args();
    $argList='';
    foreach ($args as $a) {
      if ($argList>'')
        $argList.=' ';
      if (is_array($a)) {
        foreach($a as $v)
          $argList.="$v ";
      } else {
        $argList.=$a;
        // echo "****'$a'***\n";
      }
    }

    if (!file_exists('deathLogs'))
      mkdir('deathLogs',0777);
    $f=fopen("deathLogs/c.$user_IP.log","a");
    if ($f) {
      $auxArgs=stripNL($argList);
      $auxArgs=stripNL($auxArgs);
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
    throw new YException("APP DIES:\n$auxArgs");
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
           $dbCSVFilename, $dbTEXT_NO_ERROR, $debugNotifierSevice, $appName,
           $ydb_conn, $isCLI, $isWebservice, $appCharset;

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

          if (isset($ln['file'])) {
            $currentFile=basename($ln['file']);
            $dbLine='';

            // echo "\n\t$currentFile    \t";

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
          }

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
            $debugOutput.=";\n";
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

        if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_))) {
          if (db_connectionTypeIs(_MYSQLI_)) {
            $errno = mysqli_errno($ydb_conn);
            $error = mysqli_error($ydb_conn);
          } else {
            $errno = mysql_errno($ydb_conn);
            $error = mysql_error($ydb_conn);
          }
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

        $setupIni=createDBText($dbCSVFilename);
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
                  } else if ((!is_object($v)) && (!is_resource($v))) {
                    if (trim("$v")>'')
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

        /*
        if ($logOutput<0) {
          $debugOutput.="\nGlobal Vars\n";
          _dumpArray_($debugOutput, $GLOBALS,"  ");
        }
        */

        if ($logOutput<0)
          $debugOutput.="\n";
        else if ($logOutput==1)
          $debugOutput.= "</table>";
      }

      if ($logOutput<0)
        $debugOutput.="\n$dbCSVFilename\n";
      else if ($logOutput==1)
        $debugOutput.= "<div><b>$dbCSVFilename</b></div>";

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
      if (!($isCLI || $isWebservice)) {
        if (!headers_sent())
          header("Content-Type: text/html; charset=$appCharset");
        $debugOutput=trim($debugOutput);
        if ($debugOutput=='<sys.stack></sys.stack>')
          $debugOutput="";
        if ($debugOutput>'')
          $debugOutput = "<h3>Call context</h3>$debugOutput";
        _die("<style>pre { white-space: pre-wrap; white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word; }</style><h3>Forced exit ($isWebservice)</h3><h5>YeAPF 0.8.59-198 built on 2018-05-11 06:23 (-3 DST)</h5><div style='padding:32px;color:#C40002;font-weight:800'><big><b><pre>$msg</pre></b></big></div><div>$debugOutput</div>");
      } else
        _die("\nFORCED EXIT!\n$debugOutput");
    }
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
