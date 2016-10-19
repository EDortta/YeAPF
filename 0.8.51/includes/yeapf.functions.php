<?php
  /*
    includes/yeapf.functions.php
    YeAPF 0.8.51-71 built on 2016-10-19 11:18 (-2 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-10-18 17:24:42 (-2 DST)
   */

  /*

    usar addUserFunc($funcName) para acrescentar um processador de funções
    usar _recordError($errorDesc, $warnLevel) para acrescentar uma indicação de erro ao usuário
    usar _recordAction($actionDesc) para acrescentar uma indicação de ação bem-sucedida ao usuário
    usar _record($var, $descripion) para registrar ações pequenas (linhas)

    indica se todos os formulários processados precisam ser recompilados
  */

  function serverSafeVarValue($aName)
  {
    $ret='';
    if (isset($_SERVER[$aName]))
      $ret=$_SERVER[$aName];
    return $ret;
  }

  function globalSafeVarValue($aName)
  {
    $ret='';
    if (isset($GLOBALS[$aName]))
      $ret=$GLOBALS[$aName];
    return $ret;
  }

  if (file_exists("flags/flag.dbgloader")) error_log(date("YmdHis ").globalSafeVarValue('_debugTag')." ".basename(__FILE__)." 0.8.51 ".": START\n",3,"logs/yeapf.loader.log");

  if (file_exists("flags/flag.nodb"))
    $dbConnect='no';

  function _recordWastedTime()
  {
    global $_lastTimeMark, $_debugTag, $_debugSequence;

    if (file_exists("logs/wastedTime.log")) {
      $_debugSequence++;
      $aux=microtime(true)*1000;
      $wt=number_format($aux-$_lastTimeMark,4);
      $wt=str_pad($wt,10,' ',STR_PAD_LEFT);

      $url=basename(serverSafeVarValue('PHP_SELF')).'?s='.globalSafeVarValue('s').'&a='.globalSafeVarValue('a');
      $dbg=debug_backtrace();
      $dbg0=$dbg[0];
      if (isset($dbg[1])) {
        $dbg1=$dbg[1];
        $argList=$_debugTag.'/'.$_debugSequence.' '. basename($dbg1['file']).'.'.$dbg1['line'].' -> '.basename($dbg0['file']).'.'.$dbg0['line'].': ';
      } else
        $argList=$_debugTag.'/'.$_debugSequence."  \t -> ".basename($dbg0['file']).'.'.$dbg0['line'].': ';


      $args=func_get_args();
      $argList.="\t";
      foreach ($args as $a) {
        if ($argList>'')
          $argList.=' ';
        $argList.=$a;
      }

      error_log(date('i:s').": ($wt) ".$argList."\n", 3, 'logs/wastedTime.log');
      $_lastTimeMark=microtime(true)*1000;;
    }
  }

  function decimalMicrotime()
  {
    $auxMT=microtime(false);
    $auxMT=substr($auxMT,0,strpos($auxMT,' ')) * 1000;
    return $auxMT;
  }

  function escapeString($value)
  {
    /*
     * http://stackoverflow.com/questions/1162491/alternative-to-mysql-real-escape-string-without-connecting-to-db
     */
      $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
      $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

      return str_replace($search, $replace, $value);
  }

  $_debugTag=decimalMicrotime();
  $_debugSequence=0;
  $_lastTimeMark=microtime(true)*1000;

  if (file_exists("logs/wastedTime.log"))
    error_log("\n\n\n",3,"logs/wastedTime.log");
  $recordLoaderWastedTime = (file_exists('logs/wastedTime.log')) && (function_exists('_recordWastedTime'));

  _recordWastedTime("STARTUP 0.8.51 -----------------------------------------");

  if (strlen($_debugTag)<7) {
    $debugTagParts=explode(".",$_debugTag);
    for ($n=1; $n>=0; $n--) {
      while (strlen($debugTagParts[$n])<3)
        $debugTagParts[$n]="0".$debugTagParts[$n];
    }
    $_debugTag=$debugTagParts[0].'.'.$debugTagParts[0];
  }

  function outIsXML()
  {
    $ret = false;
    $headers = headers_list();
    foreach($headers as $k=>$v) {
      $v=strtoupper($v);
      if (substr($v,0,12)=='CONTENT-TYPE') {
        $ret=$ret || (strpos($v, 'XML')>0);
      }
    }
    return $ret;
  }

  function outIsJSON()
  {
    $ret = false;
    $headers = headers_list();
    foreach($headers as $k=>$v) {
      if (strtoupper(substr($v,0,12))=='CONTENT-TYPE') {
        $ret=$ret || strpos($v, 'javascript')>0;
      }
    }
    return $ret;
  }

  function outIsText()
  {
    $ret = (php_sapi_name() == "cli");
    return $ret;
  }

  $recompileAllForms=false;
  $maxSQLCommands=-1;
  if ((!isset($dbCharset)) || (trim("$dbCharset")==''))
    $dbCharset='ISO-8859-1';

  if ((!isset($appCharset)) || (trim("$appCharset")==''))
    $appCharset='UTF-8';

  $toDebug = isset($toDebug)?$toDebug:false;

  $iPhone = (strpos(serverSafeVarValue('HTTP_USER_AGENT'),"iPhone")>0);
  $iPad = (strpos(serverSafeVarValue('HTTP_USER_AGENT'),"iPad")>0);
  $iMaemo = (strpos(serverSafeVarValue('HTTP_USER_AGENT'),"Maemo")>0);
  $iMaemo |= (strpos(serverSafeVarValue('HTTP_USER_AGENT'),"Tablet")>0);
  $iAndroid = (strpos(serverSafeVarValue('HTTP_USER_AGENT'),"Android")>0);

  $useragent=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'CLI';

  $isMobile=(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)));


  $isCLI=outIsText();
  $isCYGWIN=strtolower(getenv('OSTYPE'))=='cygwin' ||
            strtolower(getenv('TERM'))=='cygwin' ||
            strpos(strtolower(getenv('TEMP')),'cygwin')!==FALSE;
  $isHTTPS= (!$isCLI) && ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443);

  $cfgSOAPInstalled=function_exists("is_soap_fault");

  $cfgDBCureFields=true;


  function _whoiam_()
  {
    global $_MYSELF_;
    $arrStr = explode("/", $_SERVER['SCRIPT_NAME'] );
    $arrStr = array_reverse($arrStr );
    $_MYSELF_=$arrStr[0];
  }

  _whoiam_();

  if ($isCLI) {
    $GLOBALS['yeapfConfig']['wwwBase'] = isset($GLOBALS['yeapfConfig'])?$GLOBALS['yeapfConfig']['myself']:'./';
    $GLOBALS['yeapfConfig']['yeapfPath'] = dirname(__FILE__);
  }
  setMySelf($_MYSELF_);

  // -1 = arquivo yeapf.loader.log, 0 = nul, 1 = html output, 2 = xml output
  if (!isset($logOutput))
    $logOutput=$isCLI?-1:1;

  // it means that I can do log to a local file
  // if it fails, this flag is turned off, so we don't trigger the same event again
  $canDoLog=true;

  // indica a profundidade de depuração que quer nos erros de SQL
  // 0 - mostra nada
  // 1 - mostra a linha errada
  // 2 - faz 1 e mostra o traçado de diagnostico
  // 3 - faz 2 e mostra os argumentos das chamadas às funções
  // 4 - faz 3 e para a execução do script
  $SQLdebugLevel = 0;
  if (!isset($SQLDieOnError))
    $SQLDieOnError = true;
  else if (is_numeric($SQLDieOnError))
    $SQLDieOnError=($SQLDieOnError>0);
  else if (is_string($SQLDieOnError))
    $SQLDieOnError=(strtoupper($SQLDieOnError)=='TRUE') || (strtoupper($SQLDieOnError)=='YES');

  $SQLLog=false;
  $_LOG_SYS_REQUEST=false;

  $debugBestName=false;

  $dbgErrorCount=0;
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);
  $phpErrorDebug=0;
  $logRequest=false;

  $lastError='';    $errorCount=0;
  $lastAction='';
  $lastWarning='';  $warningCount=0;

  // variaveis internas do #coisa()
  $userFunctions=array();

  // vetor para aprimorar as buscas em tabelas
  // usa-se com doCacheSQL()
  $_sqlCache=array();

  // vetor para impedir que um .JS seja carregado mais de uma vez
  $_JSFilesCache=array();
  // array to avoid cyclic loading of the same file
  $_IncludedFiles=array();
  $_CurrentFileName='';

  // to be used with db_grant_cached_query()
  $_SQL_cleanCache=false;
  $_SQL_cleanAllCaches=false;
  $_SQL_doCacheOnTable=false;
  $_SQL_cacheTTL=80;
  $_SQL_cacheLimit=2000;


  /*
  // configuração da tabela de segurança de acesso do usuario
  // ;usrTableName;usrSessionIDField;usrSuperField;usrNicknameField;usrUniqueIDField
  $usrTableName = 'is_usuarios';
  $usrSessionIDField = 'userID';
  $usrSuperField='super';
  $usrNicknameField='apelido';
  $usrPassword='senha';
  $usrRightsField='userRights';
  $usrPasswordAlgorithm='md5';
  $usrLastAccess='lastAccess';
  $usrUniqueIDField='id';
  $usrIPField='lastIP';
  */

  $sgugLog="logs/yeapf.log";

  $intoFormFile=0;
  $formErr=0;
  //104615
  $includedFiles=array();

  $user_IP=serverSafeVarValue("REMOTE_ADDR");
  $server_IP=serverSafeVarValue("SERVER_ADDR");
  $safe_user_IP=str_replace(":", "", $user_IP);

  if (file_exists("flags/flag.dbgloader")) error_log(date("YmdHis ").$GLOBALS['_debugTag']." ".basename(__FILE__)." 0.8.51 ".": loading $user_IP config file\n",3,"logs/yeapf.loader.log");


  if (file_exists(".config/yeapf.config.files.$safe_user_IP"))
    include_once ".config/yeapf.config.files.$safe_user_IP";

  if (!isset($yeapfConfig))
    $yeapfConfig = array();

  if ((!isset($__yeapfPath)) || ("$__yeapfPath"=='')) {
    $__yeapfPath=dirname(__FILE__);
    $yeapfConfig['yeapfPath']=$__yeapfPath;
  }

  if (!$isCLI) {
    if (!$cfgSOAPInstalled) {
      if (!$yeapfConfig['nusoapPath'])
        $yeapfConfig['nusoapPath']="nuSOAP/nusoap.php";
      else
        $yeapfConfig['nusoapPath'].="/nusoap.php";
    } else
      $yeapfConfig['nusoapPath']='';
  } else {
    $yeapfConfig['nusoapPath']='';
  }

  function setGlobalIfClear($gVarName, $gVarValue)
  {
    if (!isset($GLOBALS[$gVarName]) || ($GLOBALS[$gVarName]=='')) {
      _dumpY(1, 0, "creating $gVarName with value '$gVarValue'");
      $GLOBALS[$gVarName]=$gVarValue;
    } else
      _dumpY(1, 0, "$gVarName exists with value '".$GLOBALS[$gVarName]."'");
  }

  function db_checkConfig()
  {
    // this function is called by 'yeapf.db.php' at the end of its load
    // if (trim($GLOBALS['usrTableName'])=='') {
      setGlobalIfClear('usrTableName','is_usuarios');
      setGlobalIfClear('usrEMail','email');
      setGlobalIfClear('usrRightsField','NULL');
      setGlobalIfClear('usrSessionIDField','userID');
      setGlobalIfClear('usrSuperField','super');
      setGlobalIfClear('usrNicknameField','apelido');
      setGlobalIfClear('usrPassword','senha');
      setGlobalIfClear('usrPasswordAlgorithm','md5');
      setGlobalIfClear('usrLastAccess','lastAccess');
      setGlobalIfClear('usrUniqueIDField','id');
      setGlobalIfClear('usrIPField','lastIP');
      setGlobalIfClear('yMenuAttr','2');
    // }
  }

  if (!is_dir("logs"))
    mkdir("logs");

  global $__EventBuffer, $__EventBufferFilled;
  $__EventBuffer=array();
  $__EventBufferFilled=false;

  function yeapfStage($functionName = '')
  {
    global $s, $a, $currentYeapfStage, $devMsgQueue,
           $__EventBuffer, $__EventBufferFilled;

    _recordWastedTime("Starting YeAPF stage: $functionName($a, $a)");

    if ($functionName>'')
      $fe = function_exists("$functionName");
    else
      $fe = false;

    if ($devMsgQueue)
      $devMsgQueue->sendStagedMessage('changeStage');

    _dumpY(1,1,"YEAPF STAGE: $functionName    BEGIN ".intval($fe));

    $currentYeapfStage=$functionName;

    if ($fe)
      $functionName($s, $a);
    _dumpY(1,1,"YEAPF STAGE: $functionName    eventHandler ");
    if (function_exists('doEventHandler')) {
      if ($__EventBufferFilled) {
        for ($i=0; $i<count($__EventBuffer); $i++)
          doEventHandler($__EventBuffer[$i][0],$__EventBuffer[$i][1]);
        $__EventBufferFilled = false;
        $__EventBuffer = array();
      }
      doEventHandler('yeapf', $functionName);
    } else {
      $__EventBufferFilled = true;
      $__EventBuffer[] = array('yeapf', $functionName);
    }

    $currentYeapfStage='';

    _dumpY(1,1,"YEAPF STAGE: $functionName    END");
  }

  $yeapfLibrary = array(
                      'yeapf.exceptions.php',
                      'yeapf.uuid.php',
                      'yeapf.debug.php',
                      'yeapf.dbText.php',
                      'yeapf.i18n.php',
                      'yeapf.misctools.php',
                      'yeapf.sql.php',
                      'yeapf.misc.br.php',
                      'cXFormInterface.php',
                      'yeapf.db.php',

                      // here is db_checkConfig() called

                      'yeapf.locks.php',
                      'yeapf.cache.php',
                      'xParser.php',
                      'xSyntax.php',
                      'xForms.php',
                      'xMjson.php',
                      'yeapf.support.php',
                      'xPhonetize.php',
                      'yeapf.network.php',

                      $yeapfConfig['nusoapPath'],

                      'yeapf.userContext.php',
                      'yeapf.eventHandler.php',
                      'yeapf.application.php',
                      'yeapf.tasks.php',
                      'yeapf.colors.php',
                      'yeapf.dataset.php',
                      'yeapf.csvTools.php',
                      'yeapf.txtTools.php',

                      'yeapf.dbUpdate.php',
                      'yeapf.auditTrack.php',
                      'yeapf.migrateTools.php',

                      'yeapf.pre-processor.php'
                        );
  function DEFINE_yLoaderDie() {
    function _yLoaderDie($reconfigureLinkEnabled)
    {
      $script=basename($_SERVER["PHP_SELF"]);
      $isXML=(strpos("query.php",$script)!==false);
      $isJSON=(strpos("rest.php",$script)!==false);
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
        } else if ($isJSON) {
        } else {
          header("Content-Type: text/html; charset=$appCharset");
          echo "<style> .err {background-color:#FFC0CB; border-style:solid; border-width:2px; border-color:#FF0000;margin:32px;padding:32px;border-radius:4px}</style>";
          if ($reconfigureLinkEnabled)
          $doConfig='<div>Click <b><a href="configure.php">here</a></b> to configure<br></div>';
          $copyrightNote='<small><em>YeAPF Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com</em></small>';
          echo "<div class=err><div>$msg</div>$doConfig$copyrightNote</div>";
        }
      }
      die("");
    }
  }

  if (!function_exists('_yLoaderDie')) {
    DEFINE_yLoaderDie();
  }

  if (!function_exists('http_response_code')) {
    function http_response_code($code = NULL) {
      $codeMap = array (
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported'
      );
        if ($code !== NULL) {
          if (isset($codeMap[$code]))
            $text=$codeMap[$code];
          else
            exit('Unknown http status code "' . htmlentities($code) . '"');

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $code . ' ' . $text);
            $GLOBALS['http_response_code'] = $code;
        } else {
          $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
        }
        return $code;
    }
  }


  if (file_exists(getcwd()."/includes.lst")) {
    $auxIncludeFile=file(getcwd()."/includes.lst");
    foreach($auxIncludeFile as $aIncFile) {
      if (!((substr($aIncFile,0,1)=='#') || (substr($aIncFile,0,1)==';') || (substr($aIncFile,0,2)=='//')))
        $yeapfLibrary[count($yeapfLibrary)]=$aIncFile;
    }
  }

  if (file_exists("flags/flag.dbgphp"))
    if (file_exists("logs/yeapf.loader.log"))
      unlink("logs/yeapf.loader.log");

  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  function _recordSuspiciousIP($ip)
  {
    global $dbTEXT_EOF;
    $dbt=createDBText('logs/suspicious-ip.txt', true);
    $dbt->goTop();
    $dbt->addField('IP');
    $dbt->addField('lastSeen');
    $dbt->addField('CC');
    $res=($dbt->locate("IP",$ip));
    if ($res==$dbTEXT_EOF) {
      $dbt->addRecord();
      $cc=0;
    } else
      $cc = $dbt->getValue('CC');
    $cc++;
    $dbt->setValue('IP', $ip);
    $dbt->setValue('lastSeen', date("Ymdhis"));
    $dbt->setValue('CC', $cc);
    $dbt->commit();
  }


  if (file_exists("logs/yeapf.loader.log"))
    _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  foreach($yeapfLibrary as $libName) {
    $libName=trim($libName);
    // nusoap puede haber sido eliminado de la carga por se tratar de um CLI ou estarem usando a soap nativa
    if ($libName>'') {
      if (file_exists($GLOBALS["__yeapfPath"]."/$libName"))
        $libName=$GLOBALS["__yeapfPath"]."/$libName";
      if (file_exists($GLOBALS["__yeapfPath"]."/includes/$libName"))
        $libName=$GLOBALS["__yeapfPath"]."/includes/$libName";
      if (!file_exists($libName))
        _yLoaderDie(true, "Error loading '$libName'","File not found. path: ".$GLOBALS["__yeapfPath"]);
      else {
        if (file_exists("flags/flag.dbgphp")) {
          $userFuncs=get_defined_functions();
          if (isset($userFuncs['user']))
            $_definedFunctions_ = join(';',$userFuncs['user']);
          else
            $_definedFunctions_ = "";
          file_put_contents('logs/yeapf.loader.functions', $_definedFunctions_);
          error_log("Loading $libName ... ",3,'logs/yeapf.loader.log');
          $t1=decimalMicrotime();
        }
        $loadOk=@include_once "$libName";
        if ($recordLoaderWastedTime)
          _recordWastedTime(basename($libName)." lib_ready");
        if (!$loadOk)
          _yLoaderDie(false, "Fatal error trying to load $libName");
        if (file_exists("flags/flag.dbgphp")) {
          $t2=decimalMicrotime()-$t1;
          error_log("    wasted time: $t2\n",3,'logs/yeapf.loader.log');
        }
      }
    }
  }
  if (file_exists("flags/flag.dbgphp"))
    error_log("Ready...\n\n",3,'logs/yeapf.loader.log');
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  $secondsPerHour = 60 * 60;
  $secondsPerDay = 24 * $secondsPerHour;
  $sysDate = date("YmdHis");                $IB_sysDate=date('mdYHis');
  $sysToday = date("Ymd");                  $IB_sysToday=date('mdY');
  $sysTimeStamp = intval(date("U"));        $IB_TimeStamp = date("m-d-Y H:i:s");

  $sysTimeStampToday = dateSQL2timestamp($sysToday,true);
  $secondsFromMidnight = $sysTimeStamp - $sysTimeStampToday;
  $sysTimeStampYesterday = $sysTimeStampToday - $secondsPerDay;
  $sysTimeStampTomorrow = $sysTimeStampToday + $secondsPerDay;

  if (!isset($yeapfConfig['searchPath']))
    $yeapfConfig['searchPath'] = '';


  $searchPath=explode(';',$yeapfConfig['searchPath']);
  if (isset($appName))
    array_push($searchPath,getcwd()."/$appName");
  if (isset($yeapfConfig['httpReferer']))
    array_push($searchPath,$yeapfConfig['httpReferer']);

  if (isset($appName))
    array_push($searchPath,$yeapfConfig['httpReferer']."/$appName");

  $searchPath=array_unique($searchPath);

  for ($n=0; $n<count($searchPath); $n++)
    if (isset($searchPath[$n]))
      $searchPath[$n]=str_replace('\\','/',"$searchPath[$n]");

  /*
  foreach($searchPath as $sp)
    echo "$sp<br>";
  */

  globalDebug($phpErrorDebug==1) ;

  // db_setConnectionType($dbType);

  $resultado = 0;
  $lastAccess = 0;  // so aos efeitos que seja global $yeapfLogFlags,.  o valor mesmo sera colocado por usuario valido

  function __extractInputValues__ () {
    global $user_IP, $safe_user_IP;

    /*
    $qs = getenv("QUERY_STRING").'&';
    $qs = substr($qs,strpos($qs, '?'), strlen($qs));
    while (substr($qs,0,1)=='?')
      $qs=substr($qs,1);
    */

    $outOfPattern=0;
    $request=array();
    publishFormRequest($request, true);

    foreach($request as $qsn => $qsv) {
      $qOutOfPattern=false;
      $patternRequired='';
      /*
       * Cure basic registers: s, u and a
       * S and A need ALWAYS to be a valid string
       * U can be an integer or a 32 bytes length string.
       */
      if (($qsn == 's') || ($qsn == 'a')) {
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $qsv)) {
          $qOutOfPattern=true;
          $patternRequired="Invalid identifier";
        }
        if ($qsv=='') {
          $qOutOfPattern=true;
          $patternRequired="Empty";
        }
      }

      if ($qsn == 'u') {
        if ($qsv>'') {
          $intU = (is_numeric($qsv) && intval($qsv) == "$qsv");
          $md5U = preg_match('/^[a-f0-9]{32}$/', $qsv);

          $qOutOfPattern=(!$intU && !$md5U);
          if (($qOutOfPattern) && (!$intU))
            $patternRequired="Not integer";

          if (($qOutOfPattern) && (!$md5U))
            $patternRequired="Not MD5";
        }
      }

      if ($qOutOfPattern) {
        error_log("$user_IP:$qsn:$patternRequired:[ $qsv ]:".date('YmdHis')."\n",3,"logs/out-of-pattern.log");
        $outOfPattern++;
      } else
        $GLOBALS[$qsn]=$qsv;
    }

    if ($outOfPattern>0) {
      _recordSuspiciousIP($user_IP);
      unset($GLOBALS['s']);
      unset($GLOBALS['u']);
      unset($GLOBALS['a']);      
      if (!outIsXML()) {
        http_response_code(404);
        exit();
      }
    }

    if (!$GLOBALS['isCLI']) {
      $url='';
      foreach($request as $qsn => $qsv) {
        if ($url>'')
          $url.='&';
        $url.="$qsn=".$GLOBALS[$qsn];
      }
      $request_uri = "http".(!empty($_SERVER['HTTPS'])?"s":"")."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'?';
      $request_uri=substr($request_uri,0,strpos($request_uri,'?'));
      //$url=basename($_SERVER["SCRIPT_FILENAME"]).'?'.$url;
      $url=$request_uri.'?'.$url;
      /*
      https://wadl.java.net
      https://en.wikipedia.org/wiki/Web_Application_Description_Language 
      */
      error_log(date("YmdHis ").$GLOBALS['_debugTag']." "."$url\n", 3, "logs/access.$safe_user_IP.log");
    }
  };

  // parse_str($qs);
  if (file_exists("flags/flag.dbgloader")) error_log(date("YmdHis ").$GLOBALS['_debugTag']." ".basename(__FILE__)." ".__LINE__." 0.8.51 ".": publishing form request\n",3,"logs/yeapf.loader.log");

  __extractInputValues__();


  $isTablet = intval($iPhone || $iPad || $iMaemo || $iAndroid || $isTablet);

  if ($s=='y_msg')
    $yeapfLogLevel=-1;
  else {
    _dump("\n$_debugTag ===[".basename($_MYSELF_)."]========================= YeAPF 0.8.51");
    if ($yeapfLogFlags>0)
      _dump(str_replace('&',' &',$qs));
    _dumpY(1,0,"\ts=$s.a=$a.u=$u.e=$e\n\tyeapfLogFlags=$yeapfLogFlags.yeapfLogLevel=$yeapfLogLevel.\n\tyeapfPauseAfterClickFlag=$yeapfPauseAfterClickFlag.appFolderRights=$appFolderRights");
  }

  $uploadErrList = array( 'UPLOAD_ERR_OK',
                            'UPLOAD_ERR_INI_SIZE',
                            'UPLOAD_ERR_FORM_SIZE',
                            'UPLOAD_ERR_PARTIAL',
                            'UPLOAD_ERR_NO_FILE',
                            'UPLOAD_ERR_NO_TMP_DIR',
                            'UPLOAD_ERR_CANT_WRITE',
                            'UPLOAD_ERR_EXTENSION' );

  function __log($text, $logType=3)
  {
    global $canDoLog;
    if ($canDoLog)
      if (!error_log("$text\n",$logType,'logs/yeapf.log'))
        $canDoLog=false;
  }

  function setMySelf($aScriptName)
  {
    if (function_exists('__log'))
      __log("setMySelf($aScriptName);",3);
    $GLOBALS['yeapfConfig']['myself']=$aScriptName;
  }

  function prepareStrForSql($aStr, $quoted=true)
  {
    if ((strtoupper($aStr)=='NULL') || ($aStr=='00-00-0000 00:00:00'))
      return 'NULL';
    else {
      $aStr=str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $aStr);
      _dump($aStr);
      if ($quoted)
        $aStr = "'$aStr'";
    }
    return $aStr;
  }

  function isQuoted($line, &$quote)
  {
    $quote='';
    if (substr($line,0,1)=='"')
      $quote='"';
    if (substr($line,0,1)=="'")
      $quote="'";
    return ($quote>'');
  }

  function getCharUntil($line, $aPos, $char, $asExpression=true)
  {
    // """(6MMx20CM)GDC MICROMOLA ESPIRAL DESTACAVEL"
    // "HASTE FEMORAL CIMENTADA POLIDA """" CP3 """""
    if (($char=='"') || ($char=="'")) {
      $doubleQuote=$char.$char;
      $escapedQuote="\\".$char;
    } else {
      $doubleQuote='""';
      $escapedQuote="\\".'"';
    }

    $inDoubleQuote=false;
    $aPos++;
    while ($aPos<strlen($line)) {
      if (substr($line,$aPos,2)==$escapedQuote)
        $aPos+=2;
      if (substr($line,$aPos,1)==$char) {
        if (substr($line,$aPos,2)==$doubleQuote) {
          $inDoubleQuote=false;
          $aPos++;
        } else {
          if (!$inDoubleQuote)
            break;
          $inDoubleQuote=false;
        }
      }
      $aPos++;
    }
    $res=substr($line,0,$aPos+1);
    if (isQuoted($res, $quote)) {
      $res=unquote($res);
      $res=str_replace($doubleQuote, $escapedQuote, $res);
      $res=$quote.$res.$quote;
    } else
      $res=str_replace($doubleQuote, $escapedQuote, $res);
    // echo "[ $res ]\n";
    return $res;
  }

  function strNotEscapedPos($line, $sep, $offset=0)
  {
    $p=strpos($line, $sep, $offset);
    if (!($p===false)) {
      if (substr($line, $p-1, 1)=='\\')
        $p=strNotEscapedPos($line, $sep, $p+1);
    }
    return $p;
  }

  function getNextValue(&$line, $sep=',', $asExpression=true)
  {
    $line=trim($line);
    if (substr($line,strlen($line)-1,1)==$sep)
      $line.="NULL";
    if (isQuoted($line,$quote)) {
      $p=0;
      $res=getCharUntil($line,$p,$quote,$asExpression);
    } else {
      $p=strNotEscapedPos($line,$sep);
      if ($p===false)
        $res=$line;
      else {
        $res='';
        $auxLine=$line;
        $ok=true;
        while ($ok) {
          $ok=false;
          $p=strNotEscapedPos($auxLine,$sep);
          if ($asExpression)
            $pp=strpos($auxLine,'(');
          else
            $pp='';
          if (("$pp"==='') || ($p<$pp)) {
            if ($asExpression)
              $pq1=strpos($auxLine,'"');
            else
              $pq1='';
            if (("$pq1"=='') || ($p<$pq1)) {
              if ($asExpression)
                $pq2=strpos($auxLine,"'");
              else
                $pq2='';
              $auxRes1=intval("$pq2"=='');
              $auxRes2=intval($p<$pq2);
              if (("$pq2"=='') || ($p<$pq2))
                $auxRes=substr($auxLine,0,$p);
              else {
                $auxRes=getCharUntil($auxLine, $pq2,"'");
                $ok=true;
              }
            } else {
              $auxRes=getCharUntil($auxLine, $pq1,'"');
              $ok=true;
            }
          } else {
            $pc=1;
            $pp++;
            $slash=false;
            while ($pp<strlen($auxLine)) {
              if ($slash)
                $slash=false;
              else if (substr($auxLine,$pp,1)=='\\')
                $slash=true;
              if (substr($auxLine,$pp,1)=='(')
                $pc++;
              if (substr($auxLine,$pp,1)==')')
                $pc--;
              $pp++;
              if (($pc==0) && (!$slash)) {
                if (substr($auxLine, $pp, 1)==$sep)
                  break;
              }
            }
            $auxRes=substr($auxLine,0,$pp);
          }
          $auxLine=trim(substr($auxLine,strlen($auxRes)+ord(!$ok),strlen($auxLine)));
          $res.=$auxRes;
        }
      }
    }
    $line=trim(substr($line,strlen($res)+1,strlen($line)));
    $res=str_replace('\\,', ',', $res);
    return trim(unquote($res));
  }

  function getNextValueGroup(&$line)
  {
    $res='';
    // die("$line");
    if (strlen($line)>0) {
      $line=trim($line);
      if (isQuoted($line,$quote)) {
        $p=0;
        $res=getCharUntil($line,$p,$quote);
        $line=trim(substr($line,strlen($res)+1,strlen($line)));
        $res=unquote($res);
      } else if (substr($line,0,1)=='(') {
        $pc=1;
        $pp=1;
        while (($pp<strlen($line)) && ($pc>0)) {
          if (substr($line,$pp,1)=='(')
            $pc++;
          if (substr($line,$pp,1)==')')
            $pc--;
          $pp++;
        }
        $res=substr($line,0,$pp);
        $line=trim(substr($line,$pp+1,strlen($line)));
      } else
        $res=getNextValue($line,',');
    }
    $res=unparentesis($res);
    return ($res);
  }

  function getNextNumber(&$line)
  {
    $i=0;
    while (($i<strlen($line)) && (($line[$i]>='0') && ($line[$i]<='9')))
      $i++;
    $ret=substr($line,0,$i);
    $line=substr($line,$i+1);
    return $ret;
  }

  if (file_exists("flags/flag.dbgloader")) error_log(date("YmdHis ").$GLOBALS['_debugTag']." ".basename(__FILE__)." ".__LINE__." 0.8.51 ".": function block #1\n",3,"logs/yeapf.loader.log");

  //========================================================================
  function binMask($mask)
  {
    $binMask='';
    while ($mask-->0)
      $binMask.='1';

    while (strlen($binMask)<32)
      $binMask.='0';

    return $binMask;
  }


  function inet_pton2B($inet)
  {
    $r='';
    $v=getNextValue($inet,'.');
    while ($v>'') {
      $r.=chr(intval($v));
      $v=getNextValue($inet,'.');
    }
    return $r;
  }

  function ip2intB($hostIP)
  {
    $n=16581375;
    $ip=0;
    for ($i=0; $i<4; $i++) {
      $v=seguinteValor($hostIP,'.');
      $vn=$v*$n;
      $ip+=$vn;
      $n=$n/255;
    }
    return $ip;
  }

  function int2ipB($intIP)
  {
    $n=16581375;
    $res='';
    while ($n>=1) {
      $v=floor($intIP / $n);
      $intIP=$intIP-$v*$n;
      if ($res>'')
        $res.='.';
      $res.=intval($v);
      $n=$n/255;

    }
    return $res;
  }


  //========================================================================
  function swapVars(&$a, &$b)
  {
    $aux=$a;
    $a=$b;
    $b=$aux;
  }

  //========================================================================
  include_once "mime_types.inc";

  function mimeType($fileName)
  {
    $fileName=" $fileName";
    $p=strrpos($fileName,'.');
    $ext=strtolower(trim(substr($fileName,$p,strlen($fileName))));
    $mt=$mimeExtensions[$ext];
    return ($mt);
  }

  function firstMimeExtension($mimeType)
  {
    global $mimeExtensions;
    $ret='';
    foreach ($mimeExtensions as $ext=>$mType)
      if ($mType==$mimeType) {
        $ret=$ext;
        break;
      }
    return $ret;
  }
  //========================================================================

  function removeComments($text)
  {
    $p=strpos(' '.$text, "<!--");
    while ($p>0) {
      $j=strpos($text,"-->",$p);
      $text=substr($text,0,$p-1).substr($text,$j+3,strlen($text));
      $p=strpos(' '.$text, "<!--");
    }
    return $text;
  }

  function removeDoubleCR($text)
  {
    $i=0;
    $res='';
    $text = str_replace("\xa0"," ",$text);
    $text = str_replace(" \n","\n",$text);
    $text = str_replace(" \n","\n",$text);
    while ($i<strlen($text)-1) {
      if ((substr($text,$i,1)==chr(10)) || (substr($text,$i,1)==chr(13)))
        while ((substr($text,$i+1,1)==chr(10)) || (substr($text,$i+1,1)==chr(13)))
          $i++;
      $res.=substr($text,$i,1);
      $i++;
    }
    return $res;
  }

  function closeApplication($debugLevel=0)
  {
    global $u,$lastCommands, $includeHistory, $qs, $sgugIni;

    // salvarVariavel($u,'lastError, lastAction');

    // db_commit();
    if ($debugLevel>0) {
      echo "<div class='postmortemDebug'>";
      echo "$includeHistory";
      if ($debugLevel>1)
        echo "<hr>$lastCommands<hr>";
      if ($debugLevel>2)
        echo " | ".publicarPost(false,false);
      if ($debugLevel>3) {
        $setupIni=createDBText($sgugIni);
        if (($setupIni->locate("active",1))==$dbTEXT_NO_ERROR) {
          $dbType=$setupIni->getValue('dbType');
          $dbServer=$setupIni->getValue('dbServer');
          $dbName=$setupIni->getValue('dbName');
          echo "<div>$dbType -&gt; $dbServer:$dbName</div>";
        }
      }
      echo "</div>";
    }
  }

  function br2nl($text)
  {
    $text = str_replace("<br />","\n",$text);
    $text = str_replace("<br>","\n",$text);
    $text = str_replace("<BR />","\n",$text);
    $text = str_replace("<BR>","\n",$text);
    return $text;
  }

  function cleanString($str, $trash='%|$|!|:|;|,|?|/|<|>')
  {
    // $str=htmlspecialchars($str,ENT_COMPAT,'ISO-8859-1');
    $trash=explode('|',$trash);
    foreach($trash as $l)
      $str=str_replace($l,'',$str);
    return $str;
  }

  function rawChars($str)
  {
    /*
     * BEWARE!
     * This function require to this file be saved in ISO-8859-1
     */
    $strCharset=detect_encoding($str);
    $str=iconv($strCharset, 'UTF-8', $str);
    $str1="áéíóúàèìòùãõâêîôûäëïöüçÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ";
    $str2="aeiouaeiouaoaeiouaeioucAEIOUAEIOUAOAEIOUAEIOUC";

    for ($i=0; $i<strlen($str1); $i++){
      $aux=substr($str1,$i,1);
      $p=strpos($str,$aux);
      while ($p!==FALSE) {
        $str=substr($str,0,$p).substr($str2,$i,1).substr($str,$p+1,strlen($str));
        $p=strpos($str,substr($str1,$i,1));
      }
    }
    return $str;
  }

  function suggestVarName($aStr)
  {
    // compactar, rebaixar e deixar só caracteres sem acentuação
    $aux=trim(strtolower(cleanString(rawChars($aStr))));
    if ($aux>'') {

      // eliminar carateres inválidos
      $aux1='';
      for($i=0; $i<strlen($aux); $i++) {
        $c=substr($aux,$i,1);
        if ((($c>='0') && ($c<='9')) || ($c=='_') || (($c>='a') && ($c<='z')) || ($c==' '))
          $aux1.=$c;
      }
      $aux=$aux1; unset($aux1);

      // permitir só inicio com letras
      while ((!((substr($aux,0,1)>='a') && (substr($aux,0,1)<='z'))) && ($aux>''))
        $aux=trim(substr($aux,1));

      // eliminar espaços duplicados
      $aux=str_replace('  ', ' ',$aux);

      // subir a caixa das primeiras letas das palavras (menos a primeira)
      $aux=explode(' ',$aux);
      for($i=1; $i<count($aux); $i++) {
        $vAux=$aux[$i];
        $vAux=strtoupper(substr($vAux,0,1)).substr($vAux,1);
        $aux[$i]=$vAux;
      }
      $id=join('',$aux);
    } else
      $id='';
    return $id;
  }

  function _saveConfigFile()
  {
    global $yeapfConfig, $user_IP, $safe_user_IP;

    if (lock('yeapfConfig')) {
      asort($yeapfConfig['files']);
      $f=fopen(".config/yeapf.config.files.$safe_user_IP",'w');
      if ($f) {
        $date=date("Y-m-d");
        $time=date("G:i:s");
        fputs($f,"<?php\n");
        fputs($f,"/* \n");
        fputs($f," * yeapf.config.files.$user_IP\n");
        fputs($f," * YEAPF Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com\n");
        fputs($f," * This config file was auto-updated by YeAPF\n");
        fputs($f," * On $date at $time\n");
        fputs($f," */\n\n");
        foreach($yeapfConfig['files'] as $fileNdx => $fileDef) {
          if ($fileDef>'') {
            $aux="\t\$yeapfConfig['files']['$fileNdx']='$fileDef';\n";
            fputs($f,$aux);
          }
          // fprintf($f,"\n\t\$yeapfConfig['files'][
        }
        fputs($f,"\n?>");
        fclose($f);
      } else
        die("Impossible to create .config/yeapf.config.files.$safe_user_IP in current dir");
      unlock('yeapfConfig');
    }
  }

  function saveBestName($formFile, $protocolIdentifier, $attemp)
  {
    global $yeapfConfig;


    $yeapfConfig['files'][intval($protocolIdentifier).':'.$formFile]=$attemp;
    if (strpos(strtolower($attemp),'.php')===FALSE) {
      _dumpY(1,1,"Saving config file for '$attemp' ($protocolIdentifier) path");
      _saveConfigFile();
    }
  }

  function bestName($formFile, $protocolIdentifier=0)
  {
    global $searchPath, $yeapfConfig, $appName, $s;

    if (file_exists($formFile)) {
      $ret=$formFile;
    } else {
      $formFile=str_replace('\\','/',$formFile);
      if ((substr($formFile,0,5)!='data:') && ($formFile>'')) {

        $priorityExtensions=array('.form','.min.html','.html','.htm',
                                  '.min.js','.js','.dataset','.txt',
                                  '.php','.inc','');
        _recordWastedTime("Looking '$formFile'");

        if ((strpos($formFile,':')===false) &&
            (substr($formFile,0,1)!='/')) {

          $attemp='';
          $fileID=intval($protocolIdentifier).':'.$formFile;
          _dumpY(1,5,"FileID: ".$fileID);
          if (isset($yeapfConfig['files'])) {
            if (isset($yeapfConfig['files'][$fileID])) {
                $attemp=$yeapfConfig['files'][intval($protocolIdentifier).':'.$formFile];
                if (($attemp!='NOT FOUND') && (substr($attemp,0,4)!='http')) {
                  if (!file_exists($attemp)) {
                    $aux=pathinfo($attemp);
                    $aux=$aux['dirname'].'/'.$aux['filename'];
                    for ($i=0; $i<5; $i++) {
                      _dumpY(1,5,"$aux as ".$priorityExtensions[$i]);
                      if (file_exists($aux.$priorityExtensions[$i])) {
                        $attemp=$aux.$priorityExtensions[$i];
                        break;
                      }
                    }
                    $attemp='';
                  }
                }
            }
          }

          if ($attemp=='') {
            _dumpY(1,3,"Looking for $formFile ($protocolIdentifier)");

            if (strpos($formFile,'.')===FALSE)
              $extensions=$priorityExtensions;
            else
              $extensions=array('');

            if (!isset($searchPath))
              $searchPath=array('./');

            $auxSearchPath=explode(',',"$appName,$appName/$s,$s,js/".join(',',$searchPath));

            $auxSearchPath = array_unique($auxSearchPath);

            foreach($auxSearchPath as $dir) {
              foreach($extensions as $ext) {
                $auxFolderName=array("$dir/$formFile$ext", "$formFile$ext");
                foreach($auxFolderName as $attemp) {
                  $attemp=str_replace("\\","/",$attemp);
                  $attemp=str_replace("//","/",$attemp);
                  $fex=file_exists($attemp);
                  _dumpY(1,5,"       $attemp [".substr($dir,0,1)."] [$fex]");
                  if ($fex) {
                    break;
                  } else
                    $attemp='';
                }
                if ($attemp>'')
                  break;
              }
              if ($attemp>'')
                break;
            }


            if ($attemp>'') {
              if ($protocolIdentifier==1) {
                $attemp=str_replace("\\","/",$attemp);
                $root=str_replace('\\','/',serverSafeVarValue("DOCUMENT_ROOT"));
                $i=0;
                _dumpY(1,5,"simplify 1 $attemp over $root");
                while (substr($attemp,$i,1)==substr($root,$i,1))
                  $i++;
                if (($i>1) || ((substr($attemp,1,1)==':') && ($i>3))) {
                  $attemp=substr($attemp,$i);
                } else {
                  if (file_exists($attemp)) {
                    _dumpY(1,5,"'$attemp' exists");
                    $attemp=str_replace("\\","/",$attemp);
                    $root=str_replace("\\","/",$root);

                    if (substr($attemp,0,1)=='.') {
                      $attemp=$yeapfConfig['myself'].'/'.$attemp;
                    } else if (substr($attemp,0,1)!='/') {
                      $attemp=dirname(serverSafeVarValue("SCRIPT_NAME")).'/'.$attemp;
                    } else {
                      $homeURL=$yeapfConfig['homeFolder'];
                      $i=0;
                      _dumpY(1,5,"simplify 2 $attemp over $homeURL");
                      while (substr($attemp,$i,1)==substr($homeURL,$i,1))
                        $i++;
                      $attemp=$yeapfConfig['homeURL'].substr($attemp,$i);
                    }

                  } else
                    $attemp='';
                }

                if ($attemp>'') {
                  $attemp=serverSafeVarValue("HTTP_HOST").'/'.$attemp;

                  _dumpY(1,5,"        = $attemp");

                  $attemp = str_replace('//','/',$attemp);
                  if ($GLOBALS['isHTTPS'])
                    $attemp = 'https://'.$attemp;
                  else
                    $attemp = 'http://'.$attemp;
                  $attemp=str_replace("///","//",$attemp);
                }

              } else if ($protocolIdentifier==2) {
                $root=serverSafeVarValue("DOCUMENT_ROOT");
                if (substr($root,strlen($root)-1,1)=='/')
                  $root=substr($root,0,strlen($root)-1);
                _dumpY(1,5,$attemp);
                _dumpY(1,5,$root);
                $attemp=substr($attemp, strlen($root));
              }
            }

            if ((($protocolIdentifier==1) && ($attemp>'')) || (file_exists($attemp))) {
              _dumpY(1,2,"$formFile = '$attemp'");
              if ($attemp>'')
                if (substr($attemp,0,strlen($appName))!=$appName) {
                  _dumpY(1,5,"Saving '$formFile' as '$attemp' on $protocolIdentifier protocol");
                  saveBestName($formFile, $protocolIdentifier, $attemp);
                }
            } else {
              _dumpY(1,2,"'$formFile' not found!");
              if (isset($yeapfConfig['files']))
                if (isset($yeapfConfig['files'][$fileID])) {
                  _dumpY(1,5,"Deleting '$formFile' as '$attemp' on $protocolIdentifier protocol");
                  unset($yeapfConfig['files'][$fileID]);
                  _saveConfigFile();
                }
            }

          } else if ($attemp=='NOT FOUND')
            $attemp='';
          $ret=$attemp;
        } else
          $ret=$formFile;
        _recordWastedTime("'$formFile' search result is '$ret'");
      } else
        $ret=$formFile;
    }
    return $ret;
  }

  function bestName_OBSOLETO($formFile, $protocolIdentifier=false)
  {
    global $prefixLoad, $searchPath, $sgugPath, $lastBestNameSearch, $debugBestName, $yeapfConfig;

    $lastBestNameSearch = '';
    $qmtrail='';

    $formFile=str_replace('\\','/',$formFile);

    $myself=$GLOBALS['_MYSELF_'];
    if ($p=strpos($myself,'*')>0)
      $myself=substr($myself,0,$p+1);
    $thisServer=$yeapfConfig['root'];

    $doRefer=true;
    if ((!file_exists($formFile)) || (is_dir($formFile))) {

      $formFile=trim($formFile);
      $qmp=strpos($formFile,'?');
      if ($qmp>0) {
        $qmtrail=substr($formFile,$qmp);
        $formFile=substr($formFile,0,$qmp);
      } else
        $qmtrail='';

      if ($formFile>'') {
        if (strpos($formFile,'.')===FALSE)
          $extensions=array('.form','.html','.htm','.js','.dataset','.txt','.php','.inc','');
        else
          $extensions=array('');
        $found=false;

        $sp=array_unique(array_merge($searchPath, $sgugPath));
        foreach ($extensions as $ext) {
          foreach ($sp as $item) {
            $f2="$item/$formFile$ext";
            $f2=str_replace('//','/',$f2);

            if ($lastBestNameSearch>'')
              $lastBestNameSearch.=',';
            $lastBestNameSearch.=$f2;

            if (file_exists($f2)) {
              $found=true;
              $formFile=$f2;
              break;
            }
          }
          if ($found) {
            $formFile=realpath($formFile);
            $formFile=str_replace('\\','/',$formFile);
            break;
          }
        }
      }
    } else {
      $found=true;
      if (strpos($formFile,'/')===false) {
        if ($protocolIdentifier)
          $folder=dirname(getenv('SCRIPT_NAME'));
        else
          $folder=getcwd();

        $formFile="$folder/$formFile";
      }
      $formFile=str_replace('\\','/',$formFile);
      $doRefer=$protocolIdentifier;
    }

    if (!$found)
      $doRefer=false;

    if ($doRefer) {
      if (substr($formFile,0,strlen($thisServer))==$thisServer) {
        if (!$protocolIdentifier) {
          $paux=0;
          if (!(strpos($thisServer,':')===FALSE))
            $paux=2;
          // echo "<P>$thisServer [$paux]<hr>";
          //$formFile=substr($formFile,strlen($thisServer));
          $formFile=substr($formFile,$paux);
          /*
          for ($i=1;$i<substr_count($thisServer,'/')+$paux; $i++)
            $formFile="../$formFile";
          $formFile=str_replace('//','/',$formFile);
          */
         } else
            $formFile='/'.substr($formFile,strlen($thisServer));
        $formFile=str_replace('//','/',$formFile);
      } else if (substr($formFile,0,strlen($myself))==$myself) {
        $formFile=substr($formFile,strlen($myself));
      }
    }

    if ($found) {
      $cwd=str_replace('\\','/',getcwd()).'/';
      $formFile=str_replace('\\','/',realpath($formFile));
      _dumpY(1,2,"$protocolIdentifier\n\tformFile:$formFile\n\tmyself:$myself\n\tcwd:$cwd");


      if ($protocolIdentifier) {
        if (substr($formFile,0,2)=='./') {
          // é buildForm se chamando a sim mesmo...
          $aux=substr($myself,strlen($thisServer));
          if (strpos($aux,'?')>0)
            $aux=substr($aux,0,strpos($aux,'?')).'?';
          else {
            $aux=dirname($myself);
            $aux=substr($aux,strlen($thisServer)).'/';
            if (substr($aux,0,1)!='/')
              $aux="/$aux";
          }
          $formFile=$aux.basename($formFile);
        } else {
          $i=0;
          while (($i<strlen($formFile)) && (substr($formFile,0,$i+1)==substr($cwd,0,$i+1)))
            $i++;
          $formFile=substr($formFile,$i);
          if (($i>0) && ($protocolIdentifier))
            $formFile="/$formFile";
          _dumpY(1,3,$formFile);
        }
        $httpHost=serverSafeVarValue("HTTP_HOST");
        if ($GLOBALS['isHTTPS'])
          $formFile="https://$httpHost$formFile";
        else
          $formFile="http://$httpHost$formFile";
      }
    }

    _dumpY(1,1,"BestName = $formFile$qmtrail");

    return $formFile.$qmtrail;
  }

  function bestPicture($id)
  {
    $extensions=array('png','gif','jpg','jpeg');
    $locations=array('pictures','../pictures','images','../images');

    $found=false;
    $ret='';
    foreach($extensions as $ext)
      foreach($locations as $loc) {
        $fn=$loc.'/'.$id.'.'."$ext";
        if (file_exists($fn)) {
          $found=true;
          $ret=$fn;
          break;
        }
      }

    if ($found)
      return $ret;
    else
      return '';
  }

  function complementaryColor($color)
  {
    $xDiv=32;

    $r=hexdec(substr($color,0,2));
    $g=hexdec(substr($color,2,2));
    $b=hexdec(substr($color,4,2));

    $r=floor($r / $xDiv) * $xDiv;
    $g=floor($g / $xDiv) * $xDiv;
    $b=floor($b / $xDiv) * $xDiv;

    /*
    $aux=$r;
    $r=255-$b;
    $g=255-$g;
    $b=255-$aux;
    $res=dechex($r).dechex($g).dechex($b);
    */

    $gray=($r+$g+$b) / 3;

    $res=dechex($gray).dechex($gray).dechex($gray);

    /*
    echo "$color $res  - - - * ";
    echo floor((hexdec($res)-hexdec($color)) / 2);
    echo '<br>';
    */

    if ($res<='7f7f7f')
      $res='ffffff';
    else
      $res='000000';
    return ($res);
  }

  function getAppScript()
  {
    global $appName;

    /* O programador pode criar seus scripts que serão carregados de
     * automática pelo YeAPF mas só após a autenticação.
     * Baseado no nome do script chamado (body.php) ele procura
     * por um script que comece com o nome do aplicativo.
     * Assim se o aplicativo se chama teste o script a ser carregado
     * desde o body levará o nome de teste.body.php
     */

    $sn=serverSafeVarValue("SCRIPT_NAME");
    $sn=basename($sn,'.php');

    $appScript=bestname("$sn.$appName.php");
    _dump(1,1,"appScript: '$appScript' ($sn.$appName.php)");

    if (file_exists("$appScript"))
      return $appScript;
    else
      return '';
  }

  if (file_exists("flags/flag.dbgloader")) error_log(date("YmdHis ").$GLOBALS['_debugTag']." ".basename(__FILE__)." ".__LINE__." 0.8.51 ".": function block #2\n",3,"logs/yeapf.loader.log");

  function implementation($s, $a='', $prefix='f', $onlyTest=false)
  {
    global $lastImplementation, $_ydb_ready;

    $implemented=0;

    /*
    if (db_connectionTypeIs(_FIREBIRD_))
      $page=valorSQL("select implementation from is_menu where s='$s'");
    else
      $page=valorSQL("select page from is_menu where s='$s'");
    */
    if ($_ydb_ready & _DB_CONNECTED_) {
      if ($s!='y_msg')
        if (db_tableExists('is_menu'))
          $page=valorSQL("select implementation from is_menu where s='$s'");
    } else
      $page='';

    if ((!isset($page)) || ($page==''))
      $page=$s;
    // multiplexed events comes in the following form:  <main>:<secondary>
    // but, ':' is not a good splitter name for a function or
    // a filename, so we change ':' to '.' in order to retrieve file
    // and then we change '.' by '_' to use it as a function name
    $page=str_replace(':','.',$page);
    $implementation=bestname("$page.php");
    // echo "$implementation<br>";
    if ($implementation>'') {
      _dumpY(1,1,"file_exists('$implementation')?");
      if (file_exists("$implementation")) {
        _record($lastImplementation,"$implementation");
        try {

          _dumpY(1,1,"Loading... $implementation");
          _recordWastedTime("Loading $implementation");
          (@include_once "$implementation") || _yLoaderDie(false,"Error loading '$implementation'");
          _recordWastedTime("$implementation loaded");
          _dumpY(1,1,"Implementation '$implementation' loaded");

        } catch(Exception $ee) {
          die("Err loading $implentation");
        }
        $functions=array($prefix.trim(str_replace('.','_',$s)), $prefix.str_replace('.','_',$page));
        $functions=array_unique($functions);
        foreach($functions as $func) {
          if (!$implemented) {
            _dumpY(1,1,"function_exists('$func')?");
            if (function_exists("$func")) {
              if ($onlyTest)
                _dumpY(1,1,"Testing $func('$a')");
              else
                _dumpY(1,1,"Calling $func('$a')");
              _record($lastImplementation,"using $func");
              $implemented++;
              if (!$onlyTest) {
                _recordWastedTime("Preparing to call $func($a)");
                $__impt0=decimalMicrotime();
                call_user_func($func, $a);
                $__impt0=decimalMicrotime()-$__impt0;
                _recordWastedTime("Time wasted calling $func($a): $__impt0");
              }
            } else
              _dumpY(1,1,"function $func doesen't found");
          }
        }
      }
    } else {
      if ($page>'')
        _record($lastImplementation,"implementation '$page.php' not found");
      _record($lastImplementation,"trying undeclared implementation");
      $func=$prefix.trim($s);
      if (function_exists($func)) {
        $implemented++;
        $func($a);
      }
    }

    if ($implemented==0)
      doEventHandler($s, $a);

    return $implemented;
  }

  function rbBooleanValue($name, $value, $booleanTag, $pegarDadosDaTabela=0, $nomeTabela='', $campoChave='', $valorChave='', $asBinaryValue=false)
  {
    $r=false;
    $name=unquote($name);
    $value=unquote($value);
    $v=trim(analisarString($name, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave));
    if ($v=='')
      $v=$name;
    $value=trim(analisarString($value, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave));
    if (isint(substr($v,0,1))) {
      $v=intval($v);
      $value=intval($value);
      if ($asBinaryValue)
        $r=($v & $value)>0?1:0;
      else
        $r=(floatval($v)==floatval($value));
      _dumpY(1,3,"$r = ($v & $value) > 0");
    } else {
      if ($v==$value)
        $r=1;
      else
        $r=0;
      _dumpY(1,3,"$v = $value ? $r");
    }

    if ($r==1)
      return $booleanTag;
    else
      return '';
  }

  function isCondition($linha, $pos)
  {
    $res=((substr($linha, $pos,1)=='>') or
          (substr($linha, $pos,1)=='<') or
          (substr($linha, $pos,2)=='==') or
          (substr($linha, $pos,2)=='>=') or
          (substr($linha, $pos,2)=='<=') or
          (substr($linha, $pos,2)=='!='));
    return $res;
  }

  // tipo =
  //        1 - literal
  //        2 - valor
  //        3 - macro
  //        4 - logico
  function pegaValor($linha, &$pos, &$tipo)
  {
    global $autoDocumentation;

    if ((substr($linha,$pos,1)==')') or (substr($linha,$pos,1)==','))
      $pos++;
    while (($pos<strlen($linha)) and (substr($linha,$pos,1)<=' '))
      $pos++;

    $inicio = $pos;
    $valor='';
    $tipoToken = 0;

    if (($linha[$inicio]=='"') or ($linha[$inicio]=="'") or (strtolower(substr($linha, $inicio, 6))=='&quot;') or ($linha[$inicio]=="{"))
    {
      $tipoToken = 1;
      if (strtolower(substr($linha, $inicio, 6))=='&quot;') {
        $aspa='&quot;';
        $inicio+=6;
      } else if (substr($linha,$inicio,1)=='{') {
        $aspa='}';
        $inicio++;
      } else
        $aspa=$linha[$inicio++];

      $pos=$inicio;
      $parentesis=0;
      while ($pos<strlen($linha)) {
        if (substr($linha,$pos,1)=='(')
          $parentesis++;
        else if (substr($linha,$pos,1)==')')
          $parentesis--;

        if ( (strtolower(substr($linha, $pos, strlen($aspa)))==$aspa) and ($parentesis<=0) )
          break;

        $pos++;
      }
      $valor='"'.substr($linha,$inicio,$pos-$inicio).'"';
      $pos++;
    } else {
      $tipoToken = 2;
      if (substr($linha, $pos, 1)=='#') {
        $tipoToken = 3;
        $parentesis=0;
        while ($pos<strlen($linha)) {
          if (substr($linha,$pos,1)=='(')
            $parentesis++;
          else if (substr($linha,$pos,1)==')') {
            $parentesis--;
            if ($parentesis<=0)
              break;
          }
          $pos++;
        }
        $pos++;
      } else if (isCondition($linha, $pos)) {
        $tipoToken = 4;
        while (($pos<strlen($linha)) and (substr($linha, $pos, 1)!=',')) {
          if (strpos('·<>=!', substr($linha, $pos, 1))==0)
            break;
          else
           $pos++;
        }

      } else {
        while (($pos<strlen($linha)) and (((substr($linha,$pos,1)!=')') and ((substr($linha,$pos,1)!=',')))))
          $pos++;
      }
      $valor=substr($linha,$inicio,$pos-$inicio);
    }
    if (isset($tipo))
      $tipo=$tipoToken;


    if ($autoDocumentation)
      addDocParam();

    return trim($valor);
  }

  function getQuote($v,$default='"')
  {
    $q=$default;
    if (strlen($v)>1) {
      if ((substr($v,0,1)=='"') or (substr($v,0,1)=="'"))
        $q=substr($v,0,1);
      else if (strtolower(substr($v,0,6))=='&quot;')
        $q=strtolower(substr($v,0,6));
    }
    return($q);
  }

  function unquote($v)
  {
    $v=trim($v);
    if (strlen($v)>1) {
      if ((substr($v,0,1)=='"') or (substr($v,0,1)=="'"))
        $v=substr($v,1,strlen($v)-2);
      else if (strtolower(substr($v,0,6))=='&quot;')
        $v=substr($v,6,strlen($v)-12);
      else if (substr($v,0,2)=='\\"')
        $v=substr($v,2,strlen($v)-4);
    }
    return($v);
  }

  function unparentesis($v)
  {
    if (strlen($v)>1) {
      if ((substr($v,0,1)=='(') or (substr($v,0,1)=='[') or (substr($v,0,1)=='{'))
        $v=substr($v,1,strlen($v)-2);
    }
    return ($v);
  }

  function valorArray($dados, $nomeCampo, &$existe)
  {
    $valor = '';
    $existe = false;

    reset($dados);
    while (list ($key, $val) = each ($dados)) {
      if (strtoupper($key) == strtoupper($nomeCampo)) {
        $existe = true;
        $valor=$val;
        break;
      }
    }

    return $valor;
  }

  function menor($a, $b)
  {
    $r=0;
    if (($a>'') and ($a>=0)) {
      if (($b>'') and ($b>=0))
        $r = ($a<$b);
      else
        $r=1;
    } else
      $r=0;
    return $r;
  }

  function ourPos($s, $substr)
  {
    if (($s>'') and ($substr>'')) {
      $r = strpos($s,$substr);
      if (($r==0) and (substr($s,$r,strlen($substr))!=$substr))
        $r=-1;
    } else
      $r=-1;
    return $r;
  }

  function verificarMenor($s, $token, &$a)
  {
    $b = ourPos($s, $token);
    if (menor($b,$a))
      $a=$b;
  }

  $autoDocCurToken='';
  $autoDocLevel=0;
  $autoDocParamCounter=0;

  function grantDocumentation($token)
  {
    global $autoDocCurToken,$autoDocParamCounter, $autoDocLevel, $_ydb_ready;

    if ($_ydb_ready & _DB_CONNECTED_) {
      $autoDocCurToken=$token;
      $autoDocParamCounter=0;

      $cc=valorSQL("select count(*) from is_doc_tokens where token='$token'");
      if ($cc==0)
        db_sql("insert into is_doc_tokens (token, appLevel) values ('$token', $autoDocLevel)");
    }
  }

  function addDocParam()
  {
    global $autoDocCurToken,$autoDocParamCounter, $_ydb_ready;

    if ($_ydb_ready & _DB_CONNECTED_) {
      $autoDocParamCounter++;
      $cc=valorSQL("select count(*) from is_doc_parameters where token='$autoDocCurToken' and paramNdx='$autoDocParamCounter'");
      if ($cc<=0)
        db_sql("insert into is_doc_parameters(token, paramNdx) values ('$autoDocCurToken', '$autoDocParamCounter')");
    }
  }

  function isToken($s,$r,&$a)
  {
    global $autoDocumentation;

    if (substr($s,$r,1)=='#') {
      $i=++$r;
//      echo "<ul>len=".strlen($s)."<br>";
      while ($i<strlen($s)) {
        $c=strtoupper(substr($s,$i,1));
//        echo "i=$i&#32;c=$c<br>";
        if ((($c>='A') and ($c<='Z')) or ($c=='_') or (($c>='0') and ($c<='9')))
          $i++;
        else
          break;
      }
      $a='';
//      echo "</ul>";
      if (($i<strlen($s)) and (substr($s,$i,1)=='(')) {
        $a=substr($s,$r,$i-$r);
        if ($autoDocumentation)
          grantDocumentation($a);
        return true;
      } else
        return false;
    } else
      return false;
  }

  function seguinteValor(&$k, $sep='_')
  {
    $w=$k.$sep;
    if ((substr($w,0,1)=='"') || (substr($w,0,1)=="'")) {
      $c=substr($w,0,1);
      $p=1;
      while (substr($w,$p,1)!=$c)
        $p++;
      $p++;
    } else
      $p=strpos($w,$sep);

    $r=substr($w,0,$p);
    $k=substr($k,strlen($r)+1,strlen($k));
    if (isset($c))
      $r=unquote($r);
    return $r;
  }

  function seguinteToken($s, &$a)
  {
    global $autoDocumentation;

    $a='';
    $r=0;
    do {
      $n=strpos(substr($s,$r,strlen($s)),'#');
      if ($n===false)
        break;
      $r=$r+$n;
      $t=isToken($s,$r,$a);
      if (!$t)
        $r++;
//      echo "r=$r&#32;t=$t&#32;a=$a<br>";
    } while ((!$t) and ($r<strlen($s)));
    return $r;
  }

  function seguinteTokenXXX($s)
  {
    global $comandosUsuario;

    $a=ourPos($s, '#campo(');                 verificarMenor($s,'#(',$a);
    verificarMenor($s,'#campoBR(',$a);
    verificarMenor($s,'#campoNL(',$a);
    verificarMenor($s,'#campoNL2BR(',$a);
    verificarMenor($s,'#campoNoHTML(',$a);
    verificarMenor($s,'#noHTML(',$a);
    verificarMenor($s,'#campoInteiro(',$a);    verificarMenor($s,'#int(',$a);    verificarMenor($s,'#intZ(',$a);
    verificarMenor($s,'#campoDecimal(',$a);    verificarMenor($s,'#decimal(',$a);
    verificarMenor($s,'#campoDecimalN(',$a);   verificarMenor($s,'#decimalN(',$a);
    verificarMenor($s,'#campoDecimalZ(',$a);   verificarMenor($s,'#decimalZ(',$a);
    verificarMenor($s,'#campoBin(',$a);   verificarMenor($s,'#bin(',$a);
    verificarMenor($s,'#campoID2DEC(',$a);   verificarMenor($s,'#id2dec(',$a);
    verificarMenor($s,'#campoDEC2HEX(',$a);   verificarMenor($s,'#dec2hex(',$a);
    verificarMenor($s,'#campoData(',$a);
    verificarMenor($s,'#campoHora(',$a);
    verificarMenor($s,'#campoHoraSeg(',$a);
    verificarMenor($s,'#campoMes(',$a);
    verificarMenor($s,'#campoDia(',$a);
    verificarMenor($s,'#campoNomeDia(',$a);
    verificarMenor($s,'#campoAno(',$a);
    verificarMenor($s,'#campoNomeMes(',$a);
    verificarMenor($s,'#campoTelefone(',$a);
    verificarMenor($s,'#campoRG(',$a);
    verificarMenor($s,'#campoCPF(',$a);
    verificarMenor($s,'#campoCNPJ(',$a);
    verificarMenor($s,'#campoASCII(',$a);
    verificarMenor($s,'#formatarCPF(',$a);
    verificarMenor($s,'#palavras(',$a);
    verificarMenor($s,'#letras(',$a);
    verificarMenor($s,'#fillOnlineForm(',$a);

    verificarMenor($s,'#limparVariaveis(',$a);

    verificarMenor($s,'#parametro(',$a);
    verificarMenor($s,'#parametroInteiro(',$a);

    verificarMenor($s,'#include(',$a);
    verificarMenor($s,'#bestName(',$a);
    verificarMenor($s,'#versionedName(',$a);

    verificarMenor($s,'#tornar(',$a);
    verificarMenor($s,'#somar(',$a);
    verificarMenor($s,'#modulo(',$a);
    verificarMenor($s,'#div(',$a);
    verificarMenor($s,'#dividir(',$a);
    verificarMenor($s,'#multiplicar(',$a);

    verificarMenor($s,'#incDate(',$a);


    verificarMenor($s,'#superUser(', $a);

    verificarMenor($s,'#for(',$a);
    verificarMenor($s,'#setRowColors(',$a);
    verificarMenor($s,'#rowColor(',$a);
    verificarMenor($s,'#jumpRowIndex(',$a);

    verificarMenor($s,'#consulta(',$a);
    verificarMenor($s,'#sql(',$a);
    verificarMenor($s,'#sqlFilter(',$a);

    verificarMenor($s,'#checked(',$a);
    verificarMenor($s,'#selected(',$a);
    verificarMenor($s,'#getOptions(',$a);
    verificarMenor($s,'#getOptionsSQL(',$a);
    verificarMenor($s,'#banners(',$a);
    verificarMenor($s,'#banner(',$a);

    verificarMenor($s,'#complementaryColor(',$a);
    verificarMenor($s,'#imagemVazia(',$a);
    verificarMenor($s,'#linkVazio(',$a);

    verificarMenor($s,'#pageIndex(',$a);

    verificarMenor($s,'#se(',$a);
    verificarMenor($s,'#depurar(',$a);
    verificarMenor($s,'#existe(',$a);
    verificarMenor($s,'#primeiraImagem(', $a);
    verificarMenor($s,'#getImages(',$a);
    verificarMenor($s,'#getImagesForm(',$a);
    verificarMenor($s,'#getFileList(',$a);

    verificarMenor($s,'#intersecta(',$a);

    verificarMenor($s,'#doTable(',$a);
    verificarMenor($s,'#doTreeTable(',$a);
    verificarMenor($s,'#doOverflowedTable(',$a);
    verificarMenor($s,'#timeProducer(',$a);
    verificarMenor($s,'#dateProducer(',$a);
    verificarMenor($s,'#sqlProducer(',$a);
    verificarMenor($s,'#sequenceProducer(',$a);
    verificarMenor($s,'#fileProducer(', $a);
    verificarMenor($s,'#dbTextProducer(', $a);

    verificarMenor($s,'#monthTable(',$a);

    verificarMenor($s,'#box(',$a);

    verificarMenor($s,'#marcar(',$a);

    if (isset($comandosUsuario)) {
      $x=sizeof($comandosUsuario);
      for ($i=0; $i<$x; $i++) {
        $k='#'.$comandosUsuario[$i].'(';
        verificarMenor($s,$k,$a);
      }
    }
    return $a;
  }

  function tokenValido($s, $token, $pos)
  {
    global $tokensDefinidos, $autoDocumentation;

    if (!isset($tokensDefinidos))
      $tokensDefinidos=array();

    if (!in_array($token, $tokensDefinidos))
      array_push($tokensDefinidos,$token);

    if (substr($s,$pos,strlen($token))==$token) {
      return true;
    } else {
      if ($autoDocumentation)
        grantDocumentation(substr($token,1,strlen($token)-2));
      return false;
    }
  }

  function addWord(&$list, $word, $splitter=',')
  {
    if ($list>'')
      $list.=$splitter;
    $list.=$word;
  }

  if (file_exists("flags/flag.dbgloader")) error_log(date("YmdHis ").$GLOBALS['_debugTag']." ".basename(__FILE__)." ".__LINE__." 0.8.51 ".": function block #3\n",3,"logs/yeapf.loader.log");

  function decimalSQL($v)
  {
    if ((strpos($v,',')>0) || (strpos($v, "%2C")>0)) {
      $v=str_replace("%2C",",",$v);
      $v=str_replace(".","",$v);
      $v=str_replace(",",".",$v);
    }
    $v=floatval($v);
    return $v;
  }

  function horaFormatada($valorCampo,$segundos=false)
  {
    if (strpos($valorCampo,':')>0) {
      $xHora=explode(':',$valorCampo);
      $valorCampo='';
      foreach($xHora as $v) {
        if ($valorCampo>'')
          $valorCampo.=":";
        $valorCampo.=str_pad($v,2,'0', STR_PAD_LEFT);
      }
    }
    $valorCampo= preg_replace("/[^0-9]/", "", $valorCampo);
    $res='';

    if (strlen($valorCampo)>8) {
      $res=substr($valorCampo,8,2).':'.substr($valorCampo,10,2);


    if ($segundos)
      $res.=':'.substr($valorCampo,12,2);
    } else if (strlen($valorCampo)>=4) {
      $res=substr($valorCampo,0,2).':'.substr($valorCampo,2,2);
    if ($segundos)
      $res.=':'.substr($valorCampo,4,2);
    }
    if ($res==':')
      $res='';
    return $res;
  }

  function corrigirDataHora($aux)
  {
    $p=0;
    $a=getNumberValueFromStr($aux,$p);
    $b=getNumberValueFromStr($aux,$p);
    if ($p<strlen($aux)) {
      $c=getNumberValueFromStr($aux,$p);
      if (strlen($c)<4)
        $c=2000+strval($c);
    } else
      $c='';
    return $a.$b.$c;
  }

  function extractDateValues($aStr, $dateFormat)
  {
    $ret=array();
    if ($aStr>'') {
      $YoPOS=_ppos('y',$dateFormat,$loy);
      $MoPOS=_ppos('m',$dateFormat,$lom);
      $DoPOS=_ppos('d',$dateFormat,$lod);
      $HHoPOS=_ppos('H',$dateFormat,$loHH);
      $MMoPOS=_ppos('M',$dateFormat,$loMM);
      $SSoPOS=_ppos('S',$dateFormat,$loSS);

      $ret['year']=substr($aStr,$YoPOS,$loy);
      $ret['month']=substr($aStr,$MoPOS,$lom);
      $ret['day']=substr($aStr,$DoPOS,$lod);
      $ret['hour']=substr($aStr,$HHoPOS,$loHH);
      $ret['minutes']=substr($aStr,$MMoPOS,$loMM);
      $ret['seconds']=substr($aStr,$SSoPOS,$loSS);
    }
    return $ret;
  }

  function dateTransform($valorOriginal, $formatoOriginal, $formatoDestino)
  {
    $valorVariavel='';
    if ($valorOriginal>'') {
      $YoPOS=_ppos('y',$formatoOriginal,$loy);
      $MoPOS=_ppos('m',$formatoOriginal,$lom);
      $DoPOS=_ppos('d',$formatoOriginal,$lod);
      $HHoPOS=_ppos('H',$formatoOriginal,$loHH);
      $MMoPOS=_ppos('M',$formatoOriginal,$loMM);
      $SSoPOS=_ppos('S',$formatoOriginal,$loSS);

      $YdPOS=_ppos('y',$formatoDestino,$ldy);
      $MdPOS=_ppos('m',$formatoDestino,$ldm);
      $DdPOS=_ppos('d',$formatoDestino,$ldd);
      $HHdPOS=_ppos('H',$formatoDestino,$ldHH);
      $MMdPOS=_ppos('M',$formatoDestino,$ldMM);
      $SSdPOS=_ppos('S',$formatoDestino,$ldSS);

      $valorVariavel=$formatoDestino;
      while (strlen($valorVariavel)<$ldy+$ldm+$ldd+$ldHH+$ldMM+$ldSS)
        $valorVariavel.=' ';
      while (strlen($valorOriginal)<$loy+$lom+$lod+$loHH+$loMM+$loSS)
        $valorOriginal.=' ';

      if ($ldy*$loy>0) {
        if ($ldy<$loy) {
          $YoPOS+=($loy-$ldy);
          $loy=$ldy;
        } else if ($ldy>$loy) {
          _dump("ERRO DE CONVERSAO dateTransform() tamanho ano destino maior que ano inicial");
        }
        $valorVariavel=substr_replace($valorVariavel,substr($valorOriginal,$YoPOS,$loy),$YdPOS, $ldy);
      }
      if ($ldm*$lom>0)
        $valorVariavel=substr_replace($valorVariavel,substr($valorOriginal,$MoPOS,$lom),$MdPOS, $ldm);
      if ($ldd*$lod>0)
        $valorVariavel=substr_replace($valorVariavel,substr($valorOriginal,$DoPOS,$lod),$DdPOS, $ldd);

      if ($ldHH*$loHH>0)
        $valorVariavel=substr_replace($valorVariavel,str_replace(' ','0',substr($valorOriginal,$HHoPOS,$loHH)),$HHdPOS, $ldHH);
      if ($ldMM*$loMM>0)
        $valorVariavel=substr_replace($valorVariavel,str_replace(' ','0',substr($valorOriginal,$MMoPOS,$loMM)),$MMdPOS, $ldMM);
      if ($ldSS*$loSS>0)
        $valorVariavel=substr_replace($valorVariavel,str_replace(' ','0',substr($valorOriginal,$SSoPOS,$loSS)),$SSdPOS, $ldSS);
    }
    return $valorVariavel;
  }

  function dataSQL($data, $hora='',$internalFormat=false)
  {
    $dstFormat='';
    if (!is_bool($internalFormat)) {
      $destFormat = $internalFormat;
      $internalFormat = true;
    } else {
      if ($internalFormat)
        $destFormat='yyyymmddHHMMSS';
    }

    $data=corrigirDataHora($data);
    $hora=soNumeros($hora);
    if ((db_connectionTypeIs(_PGSQL_)) || (db_connectionTypeIs(_MYSQL_)) || ($internalFormat)) {
      $data=substr($data,4,4).substr($data,2,2).substr($data,0,2);
      while (strlen($data)<8)
        $data.='0';
      if (strlen($hora)>4)
        $hora=substr($hora,0,2).substr($hora,2,2).substr($hora,4,2);
      else
        $hora=substr($hora,0,2).substr($hora,2,2);
      while (strlen($hora)<6)
        $hora.='0';
      $ret = $data.$hora;
      $srcFormat = 'yyyymmddHHMMSS';
    } else {
      $aa=substr($data,4,4);
      $am=substr($data,2,2);
      $ad=substr($data,0,2);

      $bh=substr($hora,0,2);
      $bm=substr($hora,2,2);
      $bs=substr($hora,4,2);

      $data=sprintf("%02d-%02d-%04d %02d:%02d:%02d",$am,$ad,$aa,$bh,$bm,$bs);
      // $data=sprintf("%04d-%02d-%02d %02d:%02d:%02d",$aa,$am,$ad,$bh,$bm,$bs);
      $ret = $data;

      $srcFormat='mm/dd/yyyy HH:MM:SS';
    }

    if ((isset($destFormat)) && (($srcFormat != $destFormat) && ($destFormat>'')))
      $ret = dateTransform($ret, $srcFormat, $destFormat);

    return $ret;
  }

  function dataFormatada($valorCampo, $forceInternalFormat=false)
  {


    $valorCampo= ereg_replace("[^0-9]", "", $valorCampo);
    if (($valorCampo>'') && ($valorCampo!='00000000') && ($valorCampo !='000000000000') && ($valorCampo!='00000000000000')){
      if ((db_connectionTypeIs(_PGSQL_)) || (db_connectionTypeIs(_MYSQL_)) || ($forceInternalFormat))
        $res=substr($valorCampo,6,2).'-'.substr($valorCampo,4,2).'-'.substr($valorCampo,0,4);
      else
        $res=substr($valorCampo,2,2).'-'.substr($valorCampo,0,2).'-'.substr($valorCampo,4,4);
      while (substr($res,0,1)=='-')
        $res=substr($res,1,strlen($res));
    } else
      $res='';

    return $res;
  }

  function dataDividida($data, &$year, &$month, &$day, $forceInternalFormat=false)
  {

    $data=soNumeros($data);
    if ((db_connectionTypeIs(_PGSQL_)) ||(db_connectionTypeIs(_MYSQL_)) || ($forceInternalFormat)) {
      $year = substr($data,0,4);
      $month= substr($data,4,2);
      $day  = substr($data,6,2);
    } else {
      $month= substr($data,0,2);
      $day  = substr($data,2,2);
      $year = substr($data,4,4);
    }
  }

  function timeStamp($datahora)
  {
    $datahora=ereg_replace("[^0-9]", "",$datahora);
    while (strlen($datahora)<14)
      $datahora.='0';
    $year = substr($datahora,0,4);
    $month= substr($datahora,4,2);
    $day  = substr($datahora,6,2);
    $hour = substr($datahora,8,2);
    $min  = substr($datahora,10,2);
    $sec  = substr($datahora,12,2);
    return mktime($hour, $min, $sec, $month, $day, $year);
  }

  function date2timestamp($valorCampo, $forceInternalFormat=false)
  {
    $valorCampo=ereg_replace("[^0-9]", "", $valorCampo);
    if ((!$forceInternalFormat) && (db_connectionTypeIs(_FIREBIRD_)))
      $valorCampo=dateTransform($valorCampo, 'mmddyyyyHHMMSS','yyyymmddHHMMSS');
    $valorCampo=timeStamp($valorCampo);
    return $valorCampo;
  }

  // converte um horario dado em hh:mm para minutos
  function time2minutes($aTime=0)
  {
    $h=0;
    $m=0;
    if ($aTime>'') {
      if (($p=strpos($aTime,'h'))===FALSE)
        $p=strpos($aTime,':');
      if ($p!==FALSE) {
        $h=substr($aTime,0,$p);
        $m=intval(substr($aTime,$p+1));
      } else {
        $h=0;
        $m=intval($aTime);
      }
      $aTime=$h*60+$m;
    }

    if ($aTime<0)
      $aTime=0;

    return $aTime;
  }

  // converte minutos para hh:mm
  function minutes2time($aMinutes)
  {
    $h=floor($aMinutes / 60);
    $m=str_pad($aMinutes % 60,2,'0');
    return "".$h.':'.$m;
  }

  function timestamp2date($valorCampo, $forceInternalFormat=false)
  {


    if ($valorCampo!='') {
      if ((db_connectionTypeIs(_PGSQL_)) || (db_connectionTypeIs(_MYSQL_)) || ($forceInternalFormat))
        $valorCampo=date("YmdHis",$valorCampo);
      else
        $valorCampo=date("mdYHis",$valorCampo);
    }
    // $valorCampo=dataFormatada($valorCampo);
    return $valorCampo;
  }

  function dateFromTimeStamp($v)
  {
    showDebugBackTrace("Chamada a função obsoleta",TRUE);
    /*


    if (db_connectionTypeIs(_MYSQL_))
      $v=date("YmdHis",$v);
    else
      $v=date("mdYHis",$v);
    $v=dataFormatada($v);
    return $v;
    */
  }

  function getNumberValueFromStr($aux, &$p)
  {
    $resultado='';
    while (($p<strlen($aux)) and (is_numeric(substr($aux,$p,1))))
      $resultado.=substr($aux,$p++,1);
    $p++;
    while (strlen($resultado) % 2 > 0)
      $resultado='0'.$resultado;
    return $resultado;
  }

  if (file_exists("flags/flag.dbgloader")) error_log(date("YmdHis ").$GLOBALS['_debugTag']." ".basename(__FILE__)." ".__LINE__." 0.8.51 ".": function block #4\n",3,"logs/yeapf.loader.log");

  function buildCalendar($aDate, $aContext='', $aID='', $eachCell='#monthCell', $colFormat='', $rowFormat='', $colDef='', $rowDef='', $daysPerWeek=7, $firstDayOfWeek=0)
  {


    $mNames = array("","janeiro", "fevereiro","março","abril","maio","junho","julho","agosto","setembro","outubro","novembro","dezembro");

    $bg='#ffffff';

    $styleSunday="style='background-color:#6699cc; color:#ffffff;'";
    $styleText="style='font-size: 14px; font-family: \"Lucida Sans\";'";
    $styleCurrentDay="style='border-color:  #000000; border-style:  dotted; border-width: 1px;'";
    $dayNumberBoxSize=10;

    if ((db_connectionTypeIs(_PGSQL_)) || (db_connectionTypeIs(_MYSQL_))) {
      $aDate=substr($aDate,0,8);
      $aDate1=substr($aDate,0,6).'01';
    } else {
      $aDate=substr($aDate,4,2).substr($aDate,2,2).substr($aDate,0,4);
      $aDate1=substr($aDate,0,2).'01'.substr($aDate,4,4);
    }

    $auxDate=dateSQL2timestamp($aDate1);
    $firstDay = date('w',$auxDate);
    $aDay   = date('j',$auxDate);
    $aMonth = date('n',$auxDate);
    $aYear  = date('Y',$auxDate);
    $aMonthName=$mNames[$aMonth];

    $nextMonth = getDate(mktime(0, 0, 0, $aMonth + 1, 1, $aYear));
    $daysInMonth = date('t',$auxDate);

    /*
    $ret="\n<div class=calendarWeek>";
    for ($d=0; $d<$firstDay; $d++)
      $ret.='<div class=noDay>&#32;</div>';

    $dAtual=date("Ymd");
    for ($d=0; $d<$daysInMonth; $d++) {
      if ((($d+$firstDay) % $daysPerWeek==0) and ($d>0))
        $ret.="</div>\n<div class=calendarWeek>";

      $dd=$d+1;

      $dAux=date('Ymd',mktime(0,0,0,$aMonth,$dd,$aYear));
      if ($dd<10)
        $dd="&#32;$dd";

      if ($dAux==$dAtual) {
        $dd="<b>$dd</b>";
        $boxBG='LightGreen';
      } else
        $boxBG='LightYellow';

      // $dd="<a href=javascript:choseDay('$dAux') $styleText>$dd</a>";
      $da=($d+$firstDay)%7;

      if ($dAux==$aDate)
        $marcaDia='currentDay';
      else if ($da==0)
        $marcaDia='sundayDay';
      else
        $marcaDia='monthDay';

      $ret.="<div class=$marcaDia>$dd</div>";

    }
    $ret.="</div>";

    $ret="<div class=calendar>$ret</div>";
    */

    $ret="<tr bgcolor='$bg' $rowDef class=calendarWeek>";
    for ($d=0; $d<$firstDay; $d++)
      $ret="$ret<td $colDef class=noDay>&#32;&#32;</td>";

    $dAtual=date("Ymd");
    for ($d=0; $d<$daysInMonth; $d++) {
      if ((($d+$firstDay) % $daysPerWeek==0) and ($d>0))
        $ret="$ret</tr>\n\t<tr bgcolor='$bg' $rowDef class=calendarWeek>";
      $dd=$d+1;

      $dAux=date('Ymd',mktime(0,0,0,$aMonth,$dd,$aYear));
      if ($dd<10)
        $dd="&#32;$dd";

      if ($dAux==$dAtual)
        $dd="<b>$dd</b>";

      $dd="<a href=javascript:choseDay('$dAux') $styleText>$dd</a>";
      $da=($d+$firstDay)%7;

      if ($dAux==$aDate)
        $specDay=$styleCurrentDay;
      else
        $specDay='';

      if ($dAux==$dAtual)
        $marcaDia='currentDay';
      else if ($da==0)
        $marcaDia='sundayDay';
      else {
        $marcaDia='monthDay';
      }

      $ret.="<td $extra class=$marcaDia $colDef $specDay valign=top>";
      $ret.="<div class=calendarDay>$dd</div>";
      /*
      $ret.="<table align=right bgcolor='000000' cellspacing=1 cellpadding=2>";
      $ret.="<tbody><tr><td bgcolor='$boxBG' align=right width=$dayNumberBoxSize height=$dayNumberBoxSize>$dd</td></tr></tbody>";
      $ret.="</table>";
      */
      $ret.="<span $styleText>$eachCell('$dAux','$aContext','$aId')</span></td>";
    }

    if (($daysInMonth+$firstDay) % $daysPerWeek>0)
      for ($d=($daysInMonth+$firstDay) % $daysPerWeek; $d<$daysPerWeek; $d++)
        $ret="$ret<td $colDef class=noDay>&#32;&#32;</td>";
    $ret.='</tr>';
    $ret="<table class=calendar>\n\t$ret\n</table>";

    return $ret;
  }

  function makeCalendarTable($aDate, $dayValues=array(), $link='', $higlights = array(),
                    $cellWidth=80, $cellHeight=40, $bg='#E8F2FC', $fg='#000000',
                    $tablebg='#444444', $sundaycolor='#FFaaaa', $highlightToday=false, $titleLink='' )
  {


    $meses = array("","janeiro", "fevereiro","março","abril","maio","junho","julho","agosto","setembro","outubro","novembro","dezembro");

    if ((db_connectionTypeIs(_PGSQL_)) || (db_connectionTypeIs(_MYSQL_))) {
      $aDate=substr($aDate,0,8);
      $aDate1=substr($aDate,0,6).'01';
    } else {
      // $aDate=substr($aDate,4,2).substr($aDate,2,2).substr($aDate,0,4);
      $aDate1=substr($aDate,0,2).'01'.substr($aDate,4,4);
    }

    $auxDate=dateSQL2timestamp($aDate1);
    $date = getDate($auxDate);

    $style="style='background-color:$bg; color:$fg; width:$cellWidth;'";
    $emptyStyle="style='background-color:FFFFFF; color:$fg; width:$cellWidth;'";

    $day = $date["mday"];
    $month = $date["mon"];
    $year = $date["year"];
    $month_name = $meses[$month];

    $this_month = getDate(mktime(0, 0, 0, $month, 1, $year));
    $next_month = getDate(mktime(0, 0, 0, $month + 1, 1, $year));

    //Find out when this month starts and ends.
    $first_week_day = $this_month["wday"];
    // $days_in_this_month = floor(($next_month[0] - $this_month[0]) / (60 * 60 * 24));
    $days_in_this_month=date('t',$auxDate);

    $calendar_html = "<table bgcolor='$tablebg' cellspacing=1 cellpadding=2>";

    $month_title="<B>$month_name $year</B>";
    if ($titleLink>'')
      $month_title="<a href='$titleLink'>$month_title</a>";

    $calendar_html .= "<tr><td colspan='7' align='right' $style><font size=+1>$month_title</font></td></tr>";

    $calendar_html .= "<tr height=$cellHeight>";

    //Fill the first week of the month with the appropriate number of blanks.
    for($week_day = 0; $week_day < $first_week_day; $week_day++)
      $calendar_html .= "<td $emptyStyle>&#32;</td>";

    $week_day = $first_week_day;
    for($day_counter = 1; $day_counter <= $days_in_this_month; $day_counter++) {
      $week_day %= 7;

      if (($week_day == 0) and ($day_counter>1))
         $calendar_html .= "</tr><tr height=$cellHeight>";

      $day_text=$day_counter;

      if ($link>'') {
        if (strpos($link,"?")>0)
          $my_conn='&date';
        else
          $my_conn='?date';
        $day_spec="<a href='$link$my_conn=".date("Ymd", mktime(0,0,0,$month, $day_counter, $year))."'>$day_text</a>";
      } else
        $day_spec=$day_text;

      if ($week_day==0)
        $day_spec="<font color='$sundaycolor'><strong>$day_spec</strong></font>";

      $day_content=trim($dayValues[$day_counter]);
      if ($day_content>'')
        $day_spec.="<div class=dayContent>$day_content</div>";

        $hl = in_array(intval($day_counter), $higlights);
      if ($hl) {
        $day_spec="<font size='+1'>*$day_spec</font>";
      }
      //Do something different for the current day.
      if (($day == $day_counter) and ($highlightToday))
         $calendar_html .= "<td align='center'><table cellpadding=2 cellspacing=0 bgcolor='#ff0000'><tr><td><table bgcolor='$tablebg'><tr><td><b>".$day_spec."</b></td></tr></table></td></tr></table></td>";
      else if ($hl)
         $calendar_html .= "<td align='center'><b>$day_spec</b></td>";
      else
         $calendar_html .= "<td align='center' $style>&#32;".$day_spec."&#32;</td>";

      $week_day++;
    }

    // fill out last days of the month
    while ($week_day<=6) {
      $calendar_html.="<td $emptyStyle>&#32;</td>";
      $week_day++;
    }

    $calendar_html .= "</tr>";
    $calendar_html .= "</table>";

    return($calendar_html);
  }

  function doBanners($funcaoLinks, $orientation='V', $maximoNumeroBanners = 5)
  {
    global $larguraBanner, $bannersUsados;

    if (!isset($bannersUsados))
      $bannersUsados=array();

    if ($larguraBanner<=0)
      $larguraBanner=123;

    srand ((double) microtime() * 10000000);

    $res = '';
    if (is_dir("banners")) {
//      echo "banners<BR>";
      $d=dir("banners");
    } else if (is_dir("../banners")) {
//      echo "../banners<BR>";
      $d=dir("../banners");
    } else if (is_dir("../images/banners")) {
//      echo "../images/banners<BR>";
      $d = dir("../images/banners");
    } else if (is_dir("images/banners")) {
//      echo "images/banners<BR>";
      $d = dir("images/banners");
    }

    if ($d) {
      $nomesBanners = array();
      while ($entry=$d->read()) {
        if (($entry>'') and ($entry[0]!='.')) {
          if ((strtolower($entry)!='thumbs.db') and (strtolower($entry)!='ws_ftp.log')) {
            if ((strpos($entry,'-Fx')==0) && (strpos($entry,'cached')===false)) {
              $entry = $d->path.'/'.$entry;
              if (!is_dir($entry))
                if (!in_array($entry, $bannersUsados))
                  array_push($nomesBanners, $entry);
            }
          }
        }
      }
      $d->close();

      if ((isset($funcaoLinks)) and ($funcaoLinks>''))
        for ($jh=0; $jh<sizeof($nomesBanners); $jh++) {
          $vezes=0;
          $funcaoLinks(basename($nomesBanners[$jh]),$vezes);
          $vezes++;
          while (strlen($vezes)<6)
            $vezes="0$vezes";
          $nomesBanners[$jh]="$vezes:".$nomesBanners[$jh];
        }

      sort($nomesBanners);

      $max = sizeof($nomesBanners);
      if ($max>$maximoNumeroBanners) {
        $max=$maximoNumeroBanners;
        $nomesBanners=array_slice($nomesBanners,0,$max);
      }

      srand((float)microtime() * 1000000);

      shuffle($nomesBanners);

      /*
      foreach($nomesBanners as $kk)
        echo "$kk<br>";
      echo "<hr>";
      */

      $res.='<table border="0" cellspacing="0" cellpadding="0">';
      if ($orientation=='H')
        $res.='<TR>';
      for ($i=0; $i<$max; $i++) {
        if (substr($nomesBanners[$i],6,1)==':')
          $nomesBanners[$i]=substr($nomesBanners[$i],7,strlen($nomesBanners[$i]));

        array_push($bannersUsados, $nomesBanners[$i]);

        if (file_exists(realpath("images/caixa/espaco.gif")))
          $espaco="images/caixa/espaco.gif";
        else
          $espaco="../images/caixa/espaco.gif";
        if ($orientation=='V')
          $res.='  <tr>';
        $vezes=0;
        if ((isset($funcaoLinks)) and ($funcaoLinks>''))
          $lnk01=$funcaoLinks(basename($nomesBanners[$i]),$vezes,true);
        else
          $lnk01='';

        if ($lnk01>'') {
          $lnk01="<a href='$lnk01'>";
          $lnk02='</a>';
        } else
          $lnk02='';

        $res.='    <td valign=middle>'.$lnk01.'<img valign=top src="'.$nomesBanners[$i].'" alt="'.basename($nomesBanners[$i]).'" border="0" width="'.$larguraBanner.'">'.$lnk02.'<br><img src="'.$espaco.'" border=0></td>';
        if ($orientation=='V')
          $res.='  </tr>';
        $res.="\n";
      }
      if ($orientation=='H')
        $res.='</TR>';
      $res.='</table>';
    }
    return $res;
  }

  function doBanner($folder, $funcName)
  {
    srand ((double) microtime() * 10000000);
    $maximoNumeroBanners = 10;

    $res = '';
    if (is_dir("images/$folder"))
      $folder="images/$folder";
    else if (is_dir("../$folder"))
      $folder="../$folder";

    $d = dir($folder);

    if ($d) {
      $nomesBanners = array();
      while ($entry=$d->read()) {
        if (($entry>'') and ($entry[0]!='.')) {
          if ((strtolower($entry)!='thumbs.db') and (strtolower($entry)!='ws_ftp.log')) {
            if (strpos($entry,'-Fx')==0) {
              $entry = $d->path.'/'.$entry;
              array_push($nomesBanners, $entry);
            }
          }
        }
      }
      $d->close();

      shuffle($nomesBanners);

      if ((isset($funcName)) and ($funcName>''))
        $lnk01=$funcName(basename($nomesBanners[0]));
      else
        $lnk01='';

      if ($lnk01>'') {
        $lnk01="<a href='$lnk01'>";
        $lnk02='</a>';
      } else
        $lnk02='';

      $res = $lnk01.'<img valign=top src="'.$nomesBanners[0].'" alt="'.basename($nomesBanners[0]).'" border="0">'.$lnk02;
    }
    return $res;
  }

  // processa um diretório
  // pega os nomes que começem com "SEED"
  // passa esses nomes para a "FUNCAO"
  // e devolve a concatenação dos resultados de cada chamada à função separado pelo hifen
  function doFileList($diretorio, $colunas, $hifen='&#32;', $seed = '', $funcao = '', $inicio=0, $limite=9999)
  {
    global $fundoImagem;

    if ($fundoImagem=='')
      $fundoImagem='#FFFFCC';
    $c=0;
    $linhaAberta=false;
    $r='';

//    echo "d=$diretorio<BR>hifen=$hifen<br>seed=$seed<br>funcao=$funcao<br>inicio=$incio<br>limite=$limite<br>";
    $atalho='';
    if ($seed>'')
      $seed.='-';
    if (is_dir($diretorio))
      $d=dir($diretorio);
    if ($d) {
      $r="<table width='100%' cellspacing=0 cellpadding=1>\n";
      while ($entry=$d->read()) {
        if (($entry>'') and ($entry[0]!='.')) {
          if ((strtolower($entry)!='thumbs.db') and (strtolower($entry)!='ws_ftp.log') and (substr($entry,0,strlen($seed))==$seed) and ($entry!='cached')) {
            if ((strpos($entry,'-Fx')==0) && (!is_dir($entry))) {
              $contador++;
              if (($contador>=$inicio) and ($contador<=$limite)) {
                if ($c==0) {
                  if ($linhaAberta) {
                    $r.="  </tr>\n";
                    $linhaAberta=false;
                  }
                  $r.="  <tr>\n";
                  $linhaAberta=true;
                }

                $r.="<TD>";
                $c=($c+1) % $colunas;

                $singleName=substr($entry, strlen($seed), strlen($entry));
                if ($r>'')
                  $r.=$hifen;
                if ($funcao>'')
                  $singleName=$funcao($diretorio, substr($seed,0,strlen($seed)-1), $singleName);
                $singleName=' '.$singleName;
                $r.=$singleName;
                $r.="</TD>";
              }
            }
          }
        }
      }
      if ($linhaAberta) {
        while (($c>0) and ($c<$colunas)) {
          $r.="    <td bgcolor='$fundoImagem'>&#32;</td>\n";
          $c++;
        }
        $r.="  </tr>";
      }
      $r.="</table>";
    }
    return $r;
  }

  function doImagesTable($diretorio, $colunas, $tipo,  $inicio, $limite, $seed, $nomes, $link, $prepos='', $complemento='')
  {
    global $primeiraImagem, $larguraImagem, $fundoImagem;

    /*
     #tornar(link01,'#campo(linkBase)&mesAno=#campo(mesAno)&id=#campo(id)&action=gestaoFotos&foto=#campo(pic)&tabela=#campo(tabela)')
    */

    // echo "doImagesTable($diretorio, $colunas, $tipo,  $inicio, $limite, $seed, $nomes, $link, $prepos, $complemento)<BR>";

    $imagesTable='';
    $linhaAberta=false;
    $c=0;
    $contador=0;
    $atalho='';
    if ($seed>'')
      $seed.='-';

    if (is_dir(realpath($diretorio)))
      $d=dir($diretorio);
    if ($d) {
      if ($larguraImagem<=0)
        $larguraImagem=70;
      if ($fundoImagem=='')
        $fundoImagem='white';

      $imagesTable ="<table width='100%' cellspacing=0 cellpadding=1>\n";
      while ($entry=$d->read()) {
        if (($entry>'') and ($entry[0]!='.')) {
          if ((strtolower($entry)!='thumbs.db') and (strtolower($entry)!='ws_ftp.log') and (substr($entry,0,strlen($seed))==$seed)) {
            if ((strpos($entry,'-Fx')==0) and (strpos($entry,'-MW')==0) and ($entry!="cached") and (!is_dir("$diretorio/$entry"))) {
              // echo "$entry<BR>";
              $contador++;
              if (($contador>=$inicio) and ($contador<=$limite)) {
                if ($primeiraImagem=='')
                  $primeiraImagem=$entry;

                if ($link>'') {
                  $q=strpos($link,'$entry');
                  if ($q>0) {
                    $atalho=substr($link,0,$q)."pic=$entry&seed=".substr($seed,0,strlen($seed)-1).substr($link,$q+6,100);
                  } else {
                    if (substr($link,0,10)=='javascript')
                      $atalho="$link('$entry')";
                    else if (strpos($link,'?')==0)
                      $atalho="$link&pic=$entry";
                    else
                      $atalho="$link?pic=$entry";
                  }
                }

                if ($nomes)
                  if ($atalho>'')
                    $singleName="<a href=$atalho>".substr($entry, strlen($seed), strlen($entry))."</a>";
                  else
                    $singleName=' '.substr($entry, strlen($seed), strlen($entry));
                else
                  $singleName='';

                $pic=$entry;
                $entry = $d->path.'/'.$entry;

                if ($c==0) {
                  if ($linhaAberta) {
                    $imagesTable.="  </tr>\n";
                    $linhaAberta=false;
                  }
                  $imagesTable.="  <tr>\n";
                  $linhaAberta=true;
                }

                $c=($c+1) % $colunas;
                $imagesTable.="    <td bgcolor='$fundoImagem' align=center><div align=center>\n";
                if ($atalho>'')
                  $imagesTable.="    <a href=$atalho><img src='$prepos$entry' width=$larguraImagem border=0></a><BR>\n";
                else
                  $imagesTable.="    <img src='$prepos$entry' width=$larguraImagem><BR>\n";
                if ($tipo>'')
                  $imagesTable.="    <input type=$tipo name=imageName value='".trim($singleName)."'>".basename($singleName)."</div></td>\n";

                if ($complemento>'') {

                  if ($c==0) {
                    if ($linhaAberta) {
                      $imagesTable.="  </tr>\n";
                      $linhaAberta=false;
                    }
                    $imagesTable.="  <tr>\n";
                    $linhaAberta=true;
                  }
                  $c=($c+1) % $colunas;

                  $comp=$complemento;
                  if ($pic>'') {
                    $q=strpos($comp,'$pic');
                    if ($q>0)
                      $comp=substr($comp,0,$q).trim($singleName).substr($comp,$q+4,strlen($comp));
                  } else
                    echo "AAAGH<br>";
                  $comp=analisarString($comp);
                  $imagesTable.="    <td bgcolor='$fundoImagem' align=right valign=top>$comp</td>\n";
                }
              }
            }
          }
        }
      }
      if ($linhaAberta) {
        while (($c>0) and ($c<$colunas)) {
          $imagesTable.="    <td width=114 bgcolor='$fundoImagem'>&#32;</td>\n";
          $c++;
        }
        $imagesTable.="  </tr>";
      }
      $imagesTable.="</table>\n";
    }
    return $imagesTable;
  }

  function largoToken($s, $i)
  {
    $n=0;
    if ($i+$n>=0) {
      while (($i+$n<strlen($s)) and ($s[$i+$n]!='('))
        $n++;
      return $n+1;
    } else
      return 0;
  }

  function addUserFunc($funcName)
  {
    global $userFunctions;

    _dumpY(1,2,"Registering $funcName ");
    if (!in_array($funcName,$userFunctions)) {
      array_push($userFunctions,$funcName);
      _dumpY(1,3,var_export($userFunctions,true));
    } else
      _dumpY(1,0,"WARNING - Registering '$funcName' twice");
  }

  function grantCoisaFunc($funcCall)
  {
    if ($funcCall>'')
      if (substr($funcCall,0,1)!='#')
        $funcCall="#$funcCall";
    return $funcCall;
  }


  function _ppos($char,$formato,&$largo)
  {
    $largo=0;
    $i=0;
    $p=-1;
    while ($i<strlen($formato)) {
      if (substr($formato,$i,1)==$char) {
        if ($p<0)
          $p=$i;
        $largo++;
      } else
        if ($p>0)
          break;
      $i++;
    }
    return $p;
  }

  function checkFieldDimensions($value, $maxFieldSize, $fieldIsName)
  {
    $p=strpos($value,'-');
    if ($p>0) {
      $particle=substr($value,$p-1);
      $value=substr($value,0,$p-1);
    }

    $value=trim(unquote($value));
    if ($maxFieldSize>0) {
      if ($fieldIsName) {
        preg_replace("  "," ",$value);
        if (strlen($value)>$maxFieldSize) {
          // maxFieldSize = 35
          // maria do rosario prazeres goncalves catarino
          while ($value>'') {
            $name=getNextValue($value,' ');
            // eliminamos 'do', 'de', 'di', 'das', dos', 'da'...
            if (strlen($name)>3)
              $names=trim("$names $name");
          }
          // maria rosario prazeres goncalves catarino
          $words=explode(' ',trim($names));
          $names='';
          $surnames='';
          $side=false;

          while ((count($words)>0) && (strlen($names)+1+strlen($surnames) < $maxFieldSize))
          {
            if ($side) {
              $word=array_shift($words);
              $estimatedLen=strlen(trim("$names $word"))+1+strlen($surnames);
              if ($estimatedLen < $maxFieldSize)
                $names=trim("$names $word");
            } else {
              $word=array_pop($words);
              $estimatedLen=strlen($names)+1+strlen(trim("$word $surnames"));
              if ($estimatedLen < $maxFieldSize)
                $surnames=trim("$word $surnames");
            }
            $side=!$side;
          }
          $value=trim("$names $surnames");
        }
      } else {
        $value=substr($value,0,$maxFieldSize);
      }
    }
    $ret="$value$particle";

    return $ret;
  }

  function xml2array($xmlString) {
    $xml = simplexml_load_string($xmlString);
    $data = json_encode($xml);
    $data = json_decode($data, true);

    return $data;
  }

  function xmlGenHtmlLines($level, $_valores_, $c, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave)
  {
    global $curLevel, $curRowCounter, $curForNdx, $curRowColor;

    $curLevel=$level;
    $aux='';
    $auxData=array();
    foreach($_valores_ as $k=>$v) {
      if (is_array($v)) {
        $aux.=xmlGenHtmlLines($level+1, $v, $c, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave);
      } else {
        $auxData[$k]=$v;
      }
    }
    $ret=analisarString($c, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $auxData);
    $aux.=$ret;

    $curRowCounter[$curForNdx]++;
    $curRowColor[$curForNdx]=$curRowCounter[$curForNdx] % 2;

    return $aux;
  }

  if (file_exists("flags/flag.dbgloader")) error_log(date("YmdHis ").$GLOBALS['_debugTag']." ".basename(__FILE__)." ".__LINE__." 0.8.51 ".": function block #?\n",3,"logs/yeapf.loader.log");


  function canIncludeDebugInfo($fileName)
  {
    if (!(strpos($fileName,".html")===false))
      return 1;
    else if (!(strpos($fileName,".js")===false))
      return 2;
    else if (!(strpos($fileName,".php")===false))
      return 3;
    else if (!(strpos($fileName,".xml")===false))
      return 4;
    else
      return 0;

    /*
      return ( (strpos($fileName,".js")===false) &&
               (strpos($fileName,".php")===false) &&
               (strpos($fileName,".viewer.")===false) &&
               (strpos($fileName,"logoff")===false) &&
               (strpos($fileName,".xml")===false));
    */
  }

  function canIncludeFile($fileName)
  {
    global $_JSFilesCache;
    if (strpos($fileName,".js")!==false) {
      if ($_JSFilesCache[$fileName])
        return false;
      else {
        $_JSFilesCache[$fileName]=true;
        return true;
      }
    } else
      return true;
  }

  function _arquivo($fileName, $pegarDadosDaTabela=0, $nomeTabela='', $campoChave='', $valorChave='', $valores='')
  {
    return _file($fileName, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave,$valores);
  }

/*
  define('PATTERN_REPLACE_JS_SRC', '/<script[^>]*(?<=\\s)src=("|\')\s*(.*?)\s*\\1.*?>/e');
  define('PATTERN_REPLACE_IMG_SRC', '/<img[^>]*(?<=\\s)src=("|\')\s*(.*?)\s*\\1.*?>/e');
  define('PATTERN_REPLACE_INPUT_SRC', '/<input[^>]*(?<=\\s)src=("|\')\s*(.*?)\s*\\1.*?>/e');
*/
  define('PATTERN_REPLACE_JS_SRC', '/<script[^>]*(?<=\\s)src=("|\')\s*(.*?)\s*\\1.*?>/');
  define('PATTERN_REPLACE_IMG_SRC', '/<img[^>]*(?<=\\s)src=("|\')\s*(.*?)\s*\\1.*?>/');
  define('PATTERN_REPLACE_INPUT_SRC', '/<input[^>]*(?<=\\s)src=("|\')\s*(.*?)\s*\\1.*?>/');

  function doChangeRef($aSrc)
  {
    global $appName;
    if (!((substr($aSrc,0,1)=='/') ||
          (strtoupper(substr($aSrc,0,3))=='www') ||
          (strtolower(substr($aSrc,0,5))=='http:') ||
          (strtolower(substr($aSrc,0,6))=='https:') )) {
      if (!file_exists($aSrc))
        $aNewSrc = bestName($aSrc,0);
      else
        $aNewSrc = $aSrc;
      if (!file_exists($aNewSrc)) {
        _dump("WARNING: $aSrc not found!");
      } else
        $aSrc = $aNewSrc;
    } else
      $aSrc="'$aSrc'";
    return $aSrc;
  }

  function changeImgRef($aStr)
  {
    $aStr=str_replace('\"',"'",$aStr);
    // $aStr=preg_replace('/src=("|\')\s*(.*?)\s*\\1.*?/e',"'src='.doChangeRef('\\2')",$aStr);
    $aStr=preg_replace_callback(
              '/src=("|\')\s*(.*?)\s*\\1.*?/',
              create_function('$matches', 'return "src=".doChangeRef($matches[2]);'),
              $aStr
          );

    return $aStr;
  }

  function detect_encoding($string)
  {
    static $list = array('utf-8', 'iso-8859-1', 'windows-1252', 'windows-1251', 'windows-1250');

    foreach ($list as $item) {
      try {
        $sample = @iconv($item, $item, $string);
        if (md5($sample) == md5($string))
          return $item;
      } catch(Exception $e) {
      }
    }
    return null;
  }

  function _file($fileName, $pegarDadosDaTabela=0, $nomeTabela='',
                 $campoChave='', $valorChave='', $valores='')
  {
    global $intoFormFile, $includedFiles, $appName, $includeHistory,
           $lastCommands, $yeapfConfig, $appCharset,
           $_IncludedFiles, $cfgCurrentFile;

    $s=null;
    $priorFile = $cfgCurrentFile;
    try {
      $cfgCurrentFile = $fileName;
      if (!isset($_IncludedFiles[$fileName])) {
        $_IncludedFiles[$fileName]=true;
        // echo "incluindo $fileName $intoFormFile<br>";
        $lastCommands.="<b>$fileName</b><br>{<ul>";

        $p=strrpos($fileName, '.');
        if ($includeHistory>'')
          $includeHistory.=' -> ';
        $includeHistory.=date('U').' '.$fileName;

        $podeProcessar=true;
        if (substr($fileName,$p,3)=='.js') {
          $podeProcessar=false;
          if (!in_array($fileName,$includedFiles))
            array_push($includedFiles,$fileName);
        }

        if ((substr($fileName,$p,5)=='.form') && ($podeProcessar))
          $s=getForm($fileName, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
        else if ($fileName>'') {
          if (file_exists($fileName))  {
            $s='';
            $f = fopen($fileName,"r");
            if ($f) {
              $primeiro=true;
              while (!feof($f)) {
                $aux=fgets($f, 4096);
                $s.=$aux;
              }
              fclose($f);

              // echo " $fileName OK<br>";

            } else
              $s="<!-- Impossivel abrir $fileName  -->";
          } else if ( (substr(strtolower($fileName),0,5)=='http:') ||
                       (substr(strtolower($fileName),0,6)=='https:')) {
            $s=join("\n",file($fileName));
          } else {
            $s="<!-- WARNING: \n$fileName not found \n-->";
            _dumpY(1,1,"$fileName not found");
          }

          if ($appCharset>'') {
            $sEncoding=detect_encoding($s);
            if ($sEncoding!=$appCharset)
              _dumpY(1,2,"Warning! '$fileName' encoded as '$sEncoding'");
            $newS=iconv($sEncoding, $appCharset, $s);
            if ($newS===false) {
              _dump("ERROR! string cannot be converted from $sEncoding to $appCharset");
            } else {
              $s=$newS;
            }
          }

          $s=analisarString($s, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave);

          if (isset($yeapfConfig['context'])) {

            /* near reference substitution, so WebDesigner
             * can express image path and other as referenced
             * to the local file and not as absolute path
             */
            $rpFileName = realpath($fileName);
            if (
                 (substr($rpFileName,0,strlen($yeapfConfig['context']))==$yeapfConfig['context']) ||
                 (substr($rpFileName,0,strlen($appName))==$appName)
               ) {

              /* 20150703
              $s = preg_replace(PATTERN_REPLACE_JS_SRC, "changeImgRef('\\0')", $s);
              $s = preg_replace(PATTERN_REPLACE_IMG_SRC, "changeImgRef('\\0')", $s);
              $s = preg_replace(PATTERN_REPLACE_INPUT_SRC, "changeImgRef('\\0')", $s);
              */

              $s = preg_replace_callback(PATTERN_REPLACE_JS_SRC, create_function('$matches', 'return changeImgRef($matches[0]);'), $s);
              $s = preg_replace_callback(PATTERN_REPLACE_IMG_SRC, create_function('$matches', 'return changeImgRef($matches[0]);'), $s);
              $s = preg_replace_callback(PATTERN_REPLACE_INPUT_SRC, create_function('$matches', 'return changeImgRef($matches[0]);'), $s);
            }
          }


          // $s = strtr($s,$normalized);
          _dumpY(1,6,$s);
        }

        $lastCommands.='</ul>}';
        unset($_IncludedFiles[$fileName]);
      } else {
        _die("Cyclic error: Trying to load '$fileName' again");
      }
    } catch (Exception $e) {
      _recordError($e->getMessage());
    }
    $cfgCurrentFile = $priorFile;
    return $s;
  }

  function processFile($fileName, $pegarDadosDaTabela=0, $nomeTabela='', $campoChave='', $valorChave='')
  {
    global $_CurrentFileName, $user_IP, $aDebugIP, $yeapfConfig, $sessionCWD;

    // echo "*$fileName<br>";
    _dumpY(1,1,"looking for $fileName");
    $auxFileName=bestName($fileName);
    $canIncludeDebugInfo=canIncludeDebugInfo($auxFileName);
    if ($auxFileName>'') {
      $aux=$_CurrentFileName;

      $_CurrentFileName=$auxFileName;

      if (strpos($auxFileName,".xml")>0) {        
        header("Content-Type: text/xml;  charset=ISO-8859-1", true);
      }

      if ($canIncludeDebugInfo == 1)
        _echo("\n\n<!-- START $auxFileName -->\n\n\t\t");
      _dumpY(1,1,"LOADING $auxFileName");

      $oldSessionCWD=isset($sessionCWD)?$sessionCWD:getcwd();
      // $sessionCWD=dirname(substr($auxFileName,strlen($yeapfConfig['root'])));
      $sessionCWD=dirname($auxFileName);
      $fcontents= _file($auxFileName, $pegarDadosDaTabela=0, $nomeTabela='', $campoChave='', $valorChave='');
      $sessionCWD=$oldSessionCWD;
      unset($oldSessionCWD);

      $fcontents=str_replace("\n", "\n\t\t",$fcontents);

      if (strpos($auxFileName,"logoff")>0) {
        if (($user_IP==$aDebugIP) && (file_exists('flags/flag.develop'))) {
          echo "\n<code>$fcontents</code>";
          $GLOBALS['SQLdebugLevel']=8;
          showDebugBackTrace("User logged off",true);
          die("");
        } else
          echo $fcontents;
      } else
        echo $fcontents;

      $_CurrentFileName=$aux;

      if ($canIncludeDebugInfo == 1)
        _echo("\n\n<!-- END $auxFileName -->\n\n");
    } else {
      _dumpY(1,1,"$fileName not found");
      if ($canIncludeDebugInfo == 1) {
        _echo("\n\n<!-- $fileName not found -->\n\n");
      }
    }
  }
  if (file_exists("flags/flag.dbgloader")) error_log(date("YmdHis ").$GLOBALS['_debugTag']." ".basename(__FILE__)." 0.8.51 ".": yeapf.functions.php loaded and ready\n",3,"logs/yeapf.loader.log");

  _recordWastedTime("READY 0.8.51 ---------------------------------------- ");

?>
