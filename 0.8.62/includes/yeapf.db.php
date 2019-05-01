<?php
/*
    includes/yeapf.db.php
    YeAPF 0.8.62-81 built on 2019-05-01 13:06 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
    2019-04-23 16:06:00 (-3 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  function getConfigFileName()
  {
    global $sgugPath, $dbCSVFilename, $yeapfConfig;

    if (($yeapfConfig) && (isset($yeapfConfig['yeapfDB']))) {
      $dbCSVFilename=$yeapfConfig['yeapfDB'];
      if (!file_exists("$dbCSVFilename"))
        $dbCSVFilename='';
    } else
      $dbCSVFilename='';

    if ($dbCSVFilename=='') {
      if ($sgugPath) {
        if (!is_array($sgugPath))
          $sgugPath=explode(',',$sgugPath);
      } else
        $sgugPath=array(getcwd(),
                        getcwd()."/sgug.cfg",
                        getcwd()."/../admin",
                        getcwd()."/admin",
                        "admin",
                        getcwd()."/../sgug.cfg",
                        "sgug.cfg",
                        getcwd()."/../dbAdmin",
                        getcwd()."/dbAdmin",
                        "dbAdmin",
                        "../sgug.cfg");
      $achado=false;
      foreach($sgugPath as $sp) {
        $aux="$sp/db.csv";
        // echo "$aux<BR>";
        if (file_exists($aux)) {
          $achado=true;
          $dbCSVFilename=$sp.'/db.csv';
          break;
        }
      }
    }
    // die("<hr>$dbCSVFilename");
    return ($dbCSVFilename);
  }


  define('_DB_DIRTY_',                                512);
  define('_DB_CONNECTED_',                           1024);
  define('_DB_ANALYZED_',                            2048);
  define('_DB_UPDATABLE',                            4096);

  define('_DB_LOCKED',                               8192);

  define('_DB_LOCK_DISABLED',              8192+    16384);
  define('_DB_LOCK_TIME_MISTAKE',          8192+    32768);
  define('_DB_LOCK_EXTERNAL_IP_MISTAKE',   8192+    65536);
  define('_DB_LOCK_INTERNAL_IP_MISTAKE',   8192+   131072);
  define('_DB_LOCK_WRONG_SERVER_PREFIX',   8192+   262144);
  define('_DB_LOCK_NODE_PREFIX_MISTAKE',   8192+  5242888);
  define('_DB_LOCK_WRONG_SEQUENCE',        8192+ 10485776);

  global $_ydb_ready;
  db_setstatus(0);

  function explainDBError($value=null, $stack=null)
  {
    $ret="";
    if ($stack) {
      for($i=count($stack)-1; $i>0; $i--) {
        $ret.="\t".$stack[$i]['file'].':'.$stack[$i]['line']."\n";
      }
    }
    if (db_status(_DB_DIRTY_, $value)==_DB_DIRTY_)
      $ret .= "DB dirty\n";
    if (db_status(_DB_CONNECTED_, $value)==_DB_CONNECTED_)
      $ret .= "DB connected\n";
    if (db_status(_DB_ANALYZED_, $value)==_DB_ANALYZED_)
      $ret .= "DB analised\n";
    if (db_status(_DB_UPDATABLE, $value)==_DB_UPDATABLE)
      $ret .= "DB can be updated\n";

    if (db_status(_DB_LOCKED, $value)==_DB_LOCKED)
      $ret .= "Node locked\n";
    if (db_status(_DB_LOCK_DISABLED, $value)==_DB_LOCK_DISABLED)
      $ret .= "Node disabled\n";
    if (db_status(_DB_LOCK_TIME_MISTAKE, $value)==_DB_LOCK_TIME_MISTAKE)
      $ret .= "Node timestamp mistake\n";
    if (db_status(_DB_LOCK_EXTERNAL_IP_MISTAKE, $value)==_DB_LOCK_EXTERNAL_IP_MISTAKE)
      $ret .= "Node external_ip mistake\n";
    if (db_status(_DB_LOCK_INTERNAL_IP_MISTAKE, $value)==_DB_LOCK_INTERNAL_IP_MISTAKE)
      $ret .= "Node internal_ip mistake\n";
    if (db_status(_DB_LOCK_WRONG_SERVER_PREFIX, $value)==_DB_LOCK_WRONG_SERVER_PREFIX)
      $ret .= "Node with wrong server prefix\n";
    if (db_status(_DB_LOCK_NODE_PREFIX_MISTAKE, $value)==_DB_LOCK_NODE_PREFIX_MISTAKE)
      $ret .= "Node server prefix mistake\n";
    return $ret;
  }

  define('_MYSQL_',   1);
  define('_FIREBIRD_',2);
  define('_PGSQL_',   3);
  define('_MYSQLI_',  4);
  define('_PDO_',     5);

  $ydb_conn = NULL;
  $ydb_type = 0;
  $ydb_type_names = array('UNKNOWED','mysql', 'firebird', 'postgresql', 'mysqli', 'pdo');

  // dbType is the global value that comes from db.csv
  // here we analise it and describe database connection with a binary value
  // and stores this value in global variable 'ydb_type'
  function db_setConnectionType(&$dbType)
  {
    /*
    global $usingInterbase, $usingMysql;

    $usingMysql=($dbType=='mysql');
    $usingInterbase=($dbType=='interbase') || ($dbType=='firebird');;
    */
    $dbType=trim(strtolower($dbType));
    switch($dbType)
    {
      case 'mysqli':
        $GLOBALS['ydb_type'] = _MYSQLI_;
        break;

      case 'mysql':
        $GLOBALS['ydb_type'] = _MYSQL_;
        break;

      case 'interbase':
      case 'firebird':
        $GLOBALS['ydb_type'] = _FIREBIRD_;
        $dbType='firebird';
        break;

      case 'postgresql':
      case 'postgres':
      case 'pgsql':
        $GLOBALS['ydb_type'] = _PGSQL_;
        $dbType='postgresql';
        break;

      case 'pdo':
        $GLOBALS['ydb_type'] = _PDO_;
        $dbType='pdo';
        break;

      default:
        _dump("ERROR trying to open database");
        _die("ERROR: '$dbType' is not known as valid database connection type");
    }
    // die("$dbType = ".$GLOBALS['ydb_type']);
  }

  function db_getConnectionType()
  {
    return $GLOBALS['ydb_type'];
  }

  function db_connectionTypeIs($adbType)
  {
    return $GLOBALS['ydb_type']==$adbType;
  }

  function db_getFirebirdDialect() {
    $sql="select mon\$sql_dialect from mon\$database";
    try {
      return db_sql($sql);
    } catch (Exception $e) {
      return 1;
    }
  }

  function db_getConnectionTypeName()
  {
    global $ydb_type, $ydb_type_names;
    // die ("$ydb_type = ".$ydb_type_names[$ydb_type]);
    return $ydb_type_names[$ydb_type];
  }

  function db_status($mask=65535, $value=null)
  {
    global $_ydb_ready;
    if ($value==null)
      $value=$_ydb_ready;
    return (isset($value)?($value & $mask):0);
  }

  function db_setstatus($flag=0) {
    global $_ydb_ready;
    _recordWastedTime("DBSTATUS $_ydb_ready -> $flag");
    $_ydb_ready=$flag;

    /*
    $info=debug_backtrace();
    echo explainDBError($flag, $info)."\n";
    */
  }

  function db_set_flag($flag)
  {
    global $_ydb_ready;
    db_setstatus((isset($_ydb_ready)?intval($_ydb_ready):0) | $flag);
  }

  function db_unset_flag($flag)
  {
    global $_ydb_ready;
    db_setstatus((isset($_ydb_ready)?intval($_ydb_ready):0) & (!$flag));

    /*
    $info=debug_backtrace();
    echo explainDBError($flag, $info)."\n";
    */
  }

  function db_die($errorMsg, $className = 'dbErr')
  {
    _die("$errorMsg");
  }

  function db_close()
  {
    global $ydb_conn;
    if (db_status(_DB_CONNECTED_)==_DB_CONNECTED_) {
      if (db_connectionTypeIs(_MYSQLI_)) {
        mysqli_close($ydb_conn);
        db_unset_flag(_DB_CONNECTED_);
        unset($GLOBALS['ydb_conn']);

      } else if (db_connectionTypeIs(_MYSQL_)) {
        mysql_close($ydb_conn);
        db_unset_flag(_DB_CONNECTED_);
        unset($GLOBALS['ydb_conn']);

      } else if (db_connectionTypeIs(_FIREBIRD_)) {
        ibase_close($ydb_conn);
        db_unset_flag(_DB_CONNECTED_);
        unset($GLOBALS['ydb_conn']);

      } else if (db_connectionTypeIs(_PGSQL_)) {
        pg_close($ydb_conn);
        db_unset_flag(_DB_CONNECTED_);
        unset($GLOBALS['ydb_conn']);

      } else if (db_connectionTypeIs(_PDO_)) {
        $ydb_conn=null;
        db_unset_flag(_DB_CONNECTED_);
        unset($GLOBALS['ydb_conn']);

      } else {
        _yLoaderDie(false, "Database connection type, unknown");
      }
    }
  }

  function db_reconnect()
  {
    global $_ydb_connection_info;
    db_connect($_ydb_connection_info['dbType'], $_ydb_connection_info['dbServer'], $_ydb_connection_info['dbName'], $_ydb_connection_info['dbUser'],$_ydb_connection_info['dbPassword']);
  }

  function db_errormsg()
  {
    global $ydb_conn;
    $errCode=''; $errMessage='';
    if (db_connectionTypeIs(_MYSQLI_)) {
      $errCode=mysqli_errno($ydb_conn);
      $errMessage=mysqli_error($ydb_conn);

    } if (db_connectionTypeIs(_MYSQL_)) {
      $errCode=mysql_errno();
      $errMessage=mysql_error();

    } else if (db_connectionTypeIs(_FIREBIRD_)) {
      $errCode=ibase_errcode();
      $errMessage=ibase_errmsg();

    } else if (db_connectionTypeIs(_PGSQL_)) {
      $errMessage=pg_last_error();

    } else if (db_connectionTypeIs(_PDO_)) {
      $errInfo    = $ydb_conn->errorInfo();
      $errCode    = $errInfo[1];
      $errMessage = $errInfo[2];
    }
    return "$errCode:$errMessage";
  }

  global $_CFG_LICENSE_EXPIRATION;
  $_CFG_LICENSE_EXPIRATION="x58e1d9ca63ef85abef352d3306a6fac3";


  function _91955157449b8d6aeb45f5ba292db3eb_($codedExpirationDate) {
    $ret='';
    for($i=0; $i<strlen($codedExpirationDate) / 2; $i++) {
      $ch=substr($codedExpirationDate,$i*2,2) ^ (7+$i);
      $ret.=chr($ch);
    }
    return $ret;
  }

  function __dbProtectErrorMsg($errorMessage) {
    global $dbServer, $dbUser, $dbCSVFilename;
    $diffuseDbServer      = substr($dbServer,0,3).str_repeat('*', strlen($dbServer)-3);
    $diffuseUser          = substr($dbUser,0,3).str_repeat('*', strlen($dbUser)-3);
    $diffuseDbCSVFilename = substr($dbCSVFilename,0,3).str_repeat('*', strlen($dbCSVFilename)-3);
    $ret = str_replace($dbUser,        $diffuseUser,          $errorMessage);
    $ret = str_replace($dbServer,      $diffuseDbServer,      $ret);
    $ret = str_replace($dbCSVFilename, $diffuseDbCSVFilename, $ret);
    return $ret;
  }

  function db_connect($dbType, $dbServer, $dbName, $dbUser, $dbPassword)
  {
    global $ydb_conn, $dbCSVFilename, $server_IP, $_ydb_connection_info, $dbCharset;

    _recordWastedTime("dbType: $dbType dbServer: $dbServer dbName: $dbName");

    $max_time = ini_get("max_execution_time");
    set_time_limit(2);

    $computerName=getenv("COMPUTERNAME");
    $servidor = "LOCAL [$server_IP]";

    // echo "[ $dbType | $dbServer | $dbName | $dbUser | $dbPassword ]";

    _dump("set connection type to '$dbType'");
    db_setConnectionType($dbType);

    if (db_connectionTypeIs(_MYSQLI_)) {
      if (function_exists("mysqli_connect")) {
        $mysqlConn="mysqli_connect";

        $ydb_conn = $mysqlConn("$dbServer", "$dbUser", "$dbPassword", "$dbName") or
             _yLoaderDie(false, __dbProtectErrorMsg("Was not possible to connect as '$dbUser' to db server.","Server: $dbServer","db.csv: $dbCSVFilename"));

        db_setstatus(_DB_ANALYZED_ + _DB_CONNECTED_);

        _recordWastedTime("......ready to work");
        yeapfStage("afterDBConnect");
      } else
        _die("mysqli_connect not present");

    } else if (db_connectionTypeIs(_MYSQL_)) {

      if (function_exists("mysql_pconnect")) {
        $mysqlConn="mysql_pconnect";
      } else {
        if (function_exists("mysql_connect"))
          $mysqlConn="mysql_connect";
        else
          _yLoaderDie(false, "mysql_connect() not present");
      }


      _recordWastedTime("......ready to '$mysqlConn' on $dbServer as $dbUser");

      $ydb_conn = $mysqlConn("$dbServer", "$dbUser", "$dbPassword") or
           _yLoaderDie(false, __dbProtectErrorMsg("Was not possible to connect as '$dbUser' to db server.","Server: $dbServer","db.csv: $dbCSVFilename"));

      _recordWastedTime("......ready to mysql_select_db $dbName");
      mysql_select_db("$dbName", $ydb_conn) or
          _yLoaderDie(false, __dbProtectErrorMsg("O banco de dados principal '$dbName' n&atilde;o foi achado em '$dbServer:$computerName'"));

      db_setstatus(_DB_ANALYZED_ + _DB_CONNECTED_);

      _recordWastedTime("......ready to work");
      yeapfStage("afterDBConnect");

    } elseif (db_connectionTypeIs(_FIREBIRD_)) {

      if ($dbServer>'')
        $dbDef="$dbServer:$dbName";
      else
        $dbDef="$dbName";
      if ((!function_exists('ibase_connect')) || (!function_exists('ibase_pconnect')))
        _yLoaderDie(false, "Cannot connect to Interbase/Firebird library.", "Is php-interbase installed?","(neither function ibase_pconnect() nor ibase_connect() exists)");

      // php defines the name of ISO8859_1 as ISO-8859-1
      // so we need to change this.
      $connDBCharset=$dbCharset;
      if (substr(strtolower($dbCharset),0,3)=='iso') {
        if (substr($dbCharset,3,1)=='-')
          $connDBCharset="ISO".substr($dbCharset,4);
        else
          $connDBCharset="ISO".substr($dbCharset,3);
        $connDBCharset=str_replace("-","_",$connDBCharset);
      }

      $ydb_conn = ibase_connect("$dbDef", "$dbUser", "$dbPassword", "$connDBCharset") or
                  _yLoaderDie(false, __dbProtectErrorMsg("Was not possible to connect as '$dbUser' to db server.","Server: $dbServer","db.csv: $dbCSVFilename\n".db_errormsg()));
      db_setstatus(_DB_ANALYZED_ + _DB_CONNECTED_);
      yeapfStage("afterDBConnect");

    } else if (db_connectionTypeIs(_PGSQL_)) {

      if (!function_exists('pg_connect')) {
        _yLoaderDie(false, "Cannot connect to postgresql library.","Is phpX-pgsql installed?","X means php version");
      }
      $conn_string = "host=$dbServer port=5432 dbname=$dbName user=$dbUser password=$dbPassword";
      $ydb_conn = pg_pconnect($conn_string);
      db_setstatus(_DB_ANALYZED_ + _DB_CONNECTED_);
      if (trim($dbCharset)>'')
        db_sql("SET CLIENT_ENCODING TO '$dbCharset'");
      yeapfStage("afterDBConnect");

    } else if (db_connectionTypeIs(_PDO_)) {
      $dsn = "$dbPDOType:host=$dbServer;dbname=$dbName;charset=$dbCharset";
      $opt = array(
          PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES   => false,
      );
      $ydb_conn = new PDO($dsn, $dbUser, $dbPassword, $opt);
    } else {
      _yLoaderDie(false, 
                  __dbProtectErrorMsg("Database type '$dbType' is not recognized by the system"),
                  __dbProtectErrorMsg("Check your database connection<br><small>$dbCSVFilename</small>"));
    }

    if (db_status(_DB_CONNECTED_)==_DB_CONNECTED_) {
      $_ydb_connection_info['dbType']=$dbType;
      $_ydb_connection_info['dbServer']=$dbServer;
      $_ydb_connection_info['dbName']=$dbName;
      $_ydb_connection_info['dbUser']=$dbUser;
      $_ydb_connection_info['dbPassword']=$dbPassword;

    }

    if (!is_resource($ydb_conn))
      if (!is_object($ydb_conn))
        _dump("ydb_conn is not a resource neither an object!");

    set_time_limit($max_time);

    return $ydb_conn;
  }

  function db_createDBConfig()
  {
    global $dbCSVFilename;

    if (is_writable(".config")) {

      _recordWastedTime("Creating cache copy of $dbCSVFilename");
      $dbKeys=array();
      $dbActive='';

      /* open text file */
      $setupIni=createDBText($dbCSVFilename);

      /* go to top of text file */
      $setupIni->goTop();

      /* recover all appRegistry keys only in order to check they are unique */
      for($n=0; $n<$setupIni->recCount(); $n++) {
        $appRegistry=trim(unquote(strtoupper($setupIni->getValue("appRegistry"))));
        if ($appRegistry>'') {
          $active=$setupIni->getValue("active");
          $active=unquote(strtoupper($active));
          if ($active)
            if ($dbActive=='') {
              $dbActive=$appRegistry;
            } else
              _die("You cannot have two active db connections");
          if (!isset($dbKeys[$appRegistry])) {
            foreach($setupIni->data[$n] as $k=>$v)
              $dbKeys[$appRegistry][$k]=$v;
          } else
            _die("Err. '$appRegistry' is present twice at least");
        }

        $setupIni->skip();
      }

      $fConfig=fopen(".config/db.ini","w");
      if ($fConfig)  {
        fwrite($fConfig,"[db]\nactive=$dbActive\n");

        foreach($dbKeys as $k=>$v) {
          fwrite($fConfig,"\n[$k]\n");
          foreach($v as $k1=>$v1)
            fwrite($fConfig,"$k1='".escapeString($v1)."'\n");
        }

        fclose($fConfig);
        _recordWastedTime("Copy ready");
      }
    } else {
      _die("'.config' folder is not writable");
    }
  }

  function db_populateDBConfig($dbINI, $exceptionList)
  {
    $exceptionList=explode(',', $exceptionList);
    foreach($dbINI as $k=>$v) {
      if (!in_array($k, $exceptionList)) {
        preg_match_all('/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/', $v, $var_names);
        if (count($var_names[1])>0) {
          for($i=0; $i<count($var_names[1]); $i++) {
            $envValue=getenv($var_names[1][$i]);
            $v=str_replace($var_names[0][$i], $envValue, $v);
          }
        }
        $GLOBALS[$k]=unquote($v);
      }
    }
  }

  function db_startup($appRegistry='')
  {
    global $ydb_conn,
           $dbTEXT_NO_ERROR,
           $dontCrashWhenNoDB, $dbConnect, $dontUpdate,
           $dontConnect, $dbOnline,
           $silentQuit,
           $dbCSVFilename, $cfgMainFolder, $dbINI, $cfgCurrentAppRegistry, $sgugPath,
           $lastError,
           $yeapfConfig,
           $user_IP, $server_IP,
           $pauseCause,
           $dontWorkUntil,$freeIP,$dbName, $dbType, $dbServer, $dbUser, $dbPassword,
           $xmlData,$xq_return,$xq_regCount,
           $cfgNodePrefix, $cfgSegmentPrefix,
           $cfgHtPasswdRequired;

    $appRegistry=trim("$appRegistry");

    _recordWastedTime("appRegistry='$appRegistry' dbConnect='$dbConnect'");

    $ret = false;
    // echo "dbConnect=$dbConnect\n";
    if (isset($dontConnect))
      db_die("'dontConnect=$dontConnect' OBSOLETE!  change for 'dbConnect=no/yes'");

    // if the flag is undefined, then let's connect to the DB
    if ((!isset($dbConnect)) || ($appRegistry>'')) {
      $dbConnect='yes';
    }

    // if there is no 'yeapfDB' definition, let's don't connect
    if ((!isset($yeapfConfig['yeapfDB'])) || ($yeapfConfig['yeapfDB']==''))
      $dbConnect='no!';

    /*
    $dbg=debug_backtrace();
    $dbg=$dbg[0];
    $msg=basename($dbg['file']).'.'.$dbg['line'].': ';
    echo "$msg $dbConnect\n";
    */

    _recordWastedTime("appRegistry='$appRegistry' dbConnect='$dbConnect'");

    _recordWastedTime("Connecting to db?: $dbConnect");
    if ((strtolower($dbConnect)=='yes') || ($dbConnect=='')) {
      $original=filemtime($dbCSVFilename);
      $cfgMainFolderX=dirname($dbCSVFilename);

      if ($cfgMainFolder!=$cfgMainFolderX) {
        _recordError("cfgMainFolder '$cfgMainFolder' differs from '$cfgMainFolderX'");

      } else {
        $cfgNodePrefix="UNK";
        if (file_exists("$cfgMainFolder/.config/cloudAppNode.ini")) {
          /*
            [node]
            key="6GZALM55FMQ5MY7W"
            server_prefix="ZX0"
            node_prefix="L00"
            external_ip="192.168.200.1"
            enabled="Y"
          */
          $GLOBALS['cfgDBNode']=parse_ini_file("$cfgMainFolder/.config/cloudAppNode.ini");
          $cfgNodePrefix=$GLOBALS['cfgDBNode']['node_prefix'];
        }

        $cfgSegmentPrefix="UNDF";

        $cache=file_exists(".config/db.ini")?filemtime(".config/db.ini"):0;
        _recordWastedTime("...verifying dbconfig cache ($original over $cache)");
        if ($cache<$original) {
          db_createDBConfig();
        }
        $dbINI=parse_ini_file('.config/db.ini', true);
        if ($appRegistry=='')
          $appRegistry=$dbINI['db']['active'];

        $cfgCurrentAppRegistry=$appRegistry;

        _recordWastedTime("Using '$cfgCurrentAppRegistry' connection");
        if (isset($dbINI[$cfgCurrentAppRegistry])) {
          //$setupIni->populateValues(false, 'SQLDieOnError', true);
          db_populateDBConfig($dbINI[$cfgCurrentAppRegistry], 'SQLDieOnError');
          $dbConnect=mb_strtolower( trim( (
                                    ($dbConnect==1) || 
                                    (mb_strtoupper($dbConnect)=='ENABLE') || 
                                    (mb_strtoupper($dbConnect)=='TRUE') || 
                                    (mb_strtoupper($dbConnect)=='YES') )?'yes':'no') );
          _recordWastedTime("...populating values (dbConnect: $dbConnect)");
          // after this point, dbConnect could be redefined

          if (isset($cfgHtPasswdRequired)) {
            $cfgHtPasswdRequired=strtolower(trim((($cfgHtPasswdRequired==1) || (strtoupper($cfgHtPasswdRequired)=='TRUE')|| (strtoupper($cfgHtPasswdRequired)=='YES'))?'yes':'no'));

            if ($cfgHtPasswdRequired=='yes') {
              if (file_exists("$cfgMainFolder/.htaccess"))
                $htaccessFilename="$cfgMainFolder/.htaccess";
              else
                $htaccessFilename=getcwd()."/.htaccess";

              if (file_exists($htaccessFilename)) {
                $htaccess=file("$htaccessFilename");
                $ok=false;
                foreach($htaccess as $htaccessLine) {
                  if (strpos($htaccessLine, "AuthType")!==false) {
                    $ok=true;
                  }
                }
                if (!$ok) {
                  _recordError("cfgHtPasswdRequired but AuthType not defined in '$cfgMainFolder/.htaccess'");
                  $dbConnect='no';
                }
              } else {
                _recordError("cfgHtPasswdRequired is defined but '$cfgMainFolder/.htaccess' was not found");
                $dbConnect='no';
              }
              if ($lastError>'') {
                db_die("'.htaccess' required via cfgHtPasswdRequired='yes' but not present");
              }
            }
          }

          if (isset($cfgHttpsRequired)) {
            $cfgHttpsRequired=strtolower(trim((($cfgHttpsRequired==1) || (strtoupper($cfgHttpsRequired)=='TRUE')|| (strtoupper($cfgHttpsRequired)=='YES'))?'yes':'no'));
            if ($cfgHttpsRequired=='yes')
              if (!isSSL()) {
                _recordError("This site requires a SSL connection");
                $dbConnect='no';
              }
          }

          $dontUpdate=intval($dontUpdate);
        } else {
          $cwd=getcwd();
          _recordError("Define database to be used");
          _recordError("(CWD: $cwd | db.csv: $dbCSVFilename | cfgCurrentAppRegistry: $cfgCurrentAppRegistry | db.active=".$dbINI['db']['active'].")");
          _recordError("cache=$cache original=$original");

          if (!$dontCrashWhenNoDB) {
            // phpinfo();
            db_die("DB CONNECTION NOT DEFINED<br>\n".nl2br($GLOBALS['lastError']));
          } else
            echo "$lastError";
        }
      }

      if (("$lastError"=='')) {
        _recordWastedTime("...preparing to open");
        $canWork=true;
        // dbOnline = 06:00-11:59; 12:30-17:00
        if ("$dbOnline">'') {
          $dbOnline=str_replace(" ",'',$dbOnline);
          $xOnline=explode(',',$dbOnline);

          // limits corrections
          $dbOnline2='';
          foreach($xOnline as $xOnLineSet) {
            if ($dbOnline2>'')
              $dbOnline2.=",";

            $xLimits=explode('-', $xOnLineSet);
            for($i=0; $i<count($xLimits); $i++) {
              $xLimits[$i]=horaFormatada($xLimits[$i].':00:00');
            }
            if ($xLimits[1]<$xLimits[0]) {
              $dbOnline2.=$xLimits[0].'-23:59,';
              $dbOnline2.='00:00-'.$xLimits[1];
            } else
              $dbOnline2.=$xLimits[0].'-'.$xLimits[1];
          }

          $xOnline=explode(',',$dbOnline2);

          $xNow=date('H:i');
          $canWork=false;
          foreach($xOnline as $xOnLineSet) {
            $xLimits=explode('-', $xOnLineSet);
            if (($xNow>=$xLimits[0]) && ($xNow<=$xLimits[1]))
              $canWork=true;
          }
          _recordWastedTime("...calculating openning times");
          // die("<div>$xNow</div><div>$dbOnline</div><div>$dbOnline2</div>".intval($canWork));
        }
        $horaSistema = date("YmdHis");
        $dontWorkUntil=substr($dontWorkUntil.'00000000',0,14);
        $freeIPList = explode(',', $freeIP);
        foreach($freeIPList as $auxIP) {
          $auxIP=trim($auxIP);
          if ($user_IP=="$auxIP")
            $dontWorkUntil='';
        }
        // echo "$horaSistema<BR>$dontWorkUntil<BR>";
        $workHourLimit=(trim($dontWorkUntil)>'');
        _recordWastedTime("are there workable hours?: $workHourLimit");

        if (($workHourLimit) && ($horaSistema<$dontWorkUntil)) {
          function d2secs($d)
          {
            while (strlen($d)<14)
              $datahora.='0';
            $year = substr($d,0,4);
            $month= substr($d,4,2);
            $day  = substr($d,6,2);
            $hour = substr($d,8,2);
            $min  = substr($d,10,2);
            $sec  = substr($d,12,2);
            return mktime($hour, $min, $sec, $month, $day, $year);
          }

          if (!$silentQuit) {
            $le=dataFormatada($dontWorkUntil,true).' '.horaFormatada($dontWorkUntil);
            $ha=dataFormatada($horaSistema,true).' '.horaFormatada($horaSistema);
            $pauseCause = str_replace("\n","<br>",$pauseCause);
            $waitSecs=d2secs($dontWorkUntil)-d2secs($horaSistema);

            db_minimalCSS();
            echo "<meta http-equiv='refresh' content='$waitSecs'>";
            db_die("<b>Aguarde...</b>
                  <p><div align=center class=dbErr><b>Sistema em manuten&ccedil;&atilde;o</b><BR><div>$pauseCause</div><br>Libera&ccedil;&atilde;o estimada para $le<BR>Hora do sistema: $ha</div>", "dbWarn");
          }
          exit;
        } else if ($canWork) {
          if (!isset($ydb_conn)) {

            if ($dbConnect=='yes') {
              $cfgLicenseExpirationFieldName=$GLOBALS['_CFG_LICENSE_EXPIRATION'];
              $licenseExpirationDate=_91955157449b8d6aeb45f5ba292db3eb_($GLOBALS[$cfgLicenseExpirationFieldName]);

              if ($licenseExpirationDate=='') {
                db_die("License information missing");
              } else if ($horaSistema>$licenseExpirationDate) {
                db_die("License date expired\n<br>SysTime: $horaSistema : $licenseExpirationDate");
              } else {
                _recordWastedTime("...getting ready to connect $dbType/$dbServer:$dbName");
                db_connect($dbType, $dbServer, $dbName, $dbUser, $dbPassword);
                _recordWastedTime("...connected");
              }
            }

            if (db_status(_DB_CONNECTED_)==_DB_CONNECTED_) {
              db_set_flag(1 >> db_getConnectionType());
              if ($dontUpdate==0) {
                db_set_flag(_DB_UPDATABLE);
                if (function_exists("_db_upd_checkStructure"))
                  _db_upd_checkStructure();
              }
            }
          } else {
            _recordWastedTime("ydb_conn is already defined");
          }
        } else {
          if (!$silentQuit) {
            $auxOnlineTime = '';
            foreach($xOnline as $xOnLineSet) {
              if ($auxOnlineTime>'')
                $auxOnlineTime.=", ";
              $auxOnlineTime.="$xOnLineSet";
            }
            $auxForward = $_SERVER['HTTP_X_FORWARDED_FOR'];
            $auxRemote = $_SERVER['REMOTE_ADDR'];
            if (($auxForward!=$auxRemote) && ($auxForward>''))
              $auxIP = "( $auxRemote -> $auxForward )";
            else
              $auxIP = "( $auxRemote )";
            /*
             * ver https://developer.mozilla.org/en-US/docs/Web/API/console.warn
             * para enviar ao console por grupo
             */
            $now=date("Y-m-d H:i:s");
            db_die("<div><h3>Application out of service</h3>Try again on $auxOnlineTime<br>Server time: $now</div>");
          }
          exit;
        }
      }
    } else {
      db_setstatus(_DB_ANALYZED_);
    }

    return $ret;
  }

  function db_query($sql, $first=-1, $count=0)
  {
    global $ydb_conn,  $lastCommands,
           $sqlCount, $maxSQLCommands,
           $SQLdebugLevel, $SQLDieOnError,
           $sqlErrNo, $sqlError, $appCharset, $dbCharset, $cfgDBCureFields;


    $sql=unquote($sql);
    // @20140629 $sql=str_replace('\\'."'","'",$sql);

    if (db_status(_DB_CONNECTED_)==_DB_CONNECTED_) {
      $sqlCount++;

      if (($sqlCount>$maxSQLCommands) && ($maxSQLCommands>0))
        _die("<UL><b><font color=#cc0000>SQL statement limit reached</font></b><BR>$lastCommands</UL>");

      if ($cfgDBCureFields) {
        if ($dbCharset>'') {
          $auxCharset = mb_detect_encoding($sql, "UTF-8,ISO-8859-1,$appCharset");
          if ((substr(strtoupper($sql),0,3)!='SET') && (substr(strtoupper($sql),0,4)!='SHOW')) {
            _dumpY(4,5,"converting \"$sql\" from $auxCharset to $dbCharset");

            $sql=mb_convert_encoding($sql, "$dbCharset", $auxCharset);
          }
        }
      }

      $sql=unquote($sql);

      _dumpY(4,0,"[$sql]");
      if ((strtoupper(substr($sql,0,6))=='INSERT') ||
          (strtoupper(substr($sql,0,6))=='UPDATE') ||
          (strtoupper(substr($sql,0,7))=='CREATE') ||
          (strtoupper(substr($sql,0,7))=='REPLACE') ||
          (strtoupper(substr($sql,0,6))=='DELETE')) {
        $updateSQLStatement=true;
        doSystemLog($sql);
      } else {
        $updateSQLStatement=false;
        if (strtoupper(substr($sql,0,6))=='SELECT') {
          if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_))) {
            if ($count>'')
              $sql=$sql." limit $first, $count";
          } else if (db_connectionTypeIs(_FIREBIRD_)) {
            $sqlLimit='';
            if ($first>=0)
              $sqlLimit="SKIP $first ";
            if ($count>0)
              $sqlLimit="FIRST $count ".$sqlLimit;
            if ($count>0)
              $sql="SELECT $sqlLimit ".substr($sql,7);
          }
        }
      }

      if (db_connectionTypeIs(_MYSQLI_)) {

        $rs = mysqli_query($ydb_conn, $sql);
        $sqlErrNo = mysqli_errno($ydb_conn);
        $sqlError = mysqli_error($ydb_conn);
        if ($sqlErrNo!=0) {
          _yLoaderDie(false,"Error:\n\t'$sqlError'\nwhen run:\n\t( $sql; )");
        } else {
          $lastCommands.="$sqlCount) $sql;<BR>";
        }

      } else if (db_connectionTypeIs(_MYSQL_)) {

        $rs = mysql_query($sql, $ydb_conn);
        $sqlErrNo = mysql_errno();
        $sqlError = mysql_error();
        if ($sqlErrNo!=0) {
          _yLoaderDie(false,"Error:\n\t'$sqlError'\nwhen run:\n\t( $sql; )");
        } else {
          $lastCommands.="$sqlCount) $sql;<BR>";
        }

      } else if (db_connectionTypeIs(_FIREBIRD_)) {
        $cfgIbaseUsePrepare=false;
        $cfgIbaseUseTransactions=false;
        $transactionOpened=false;
        if (($updateSQLStatement) and ($cfgIbaseUseTransactions)) {
          $lastCommands.="$sqlCount) (TRANSACTION OPENED)<BR>";
          $transactionOpened=true;
          $transaction=ibase_trans(IBASE_WRITE|IBASE_CONSISTENCY|IBASE_WAIT, $ydb_conn);
        }
        // se for um 'deadlock update conflicts with concurrent update' a gente tenta mais uma vez
        $maxRetryCount=4;
        $retryCount=$maxRetryCount;
        $waitTime=7812;
        $allWastedTime=0;
        // purga a lista de erros?
        $sqlErrNo = ibase_errcode();
        $sqlError = ibase_errmsg();

        // forcamos entrada no loop do IB
        $sqlErrNo=-913;
        while (($sqlErrNo==-913) && ($retryCount>0)) {
          $retryCount--;
          if ($cfgIbaseUsePrepare) {
            if ($transactionOpened) {
              $sth=ibase_prepare($ydb_conn, $transaction, $sql);
            } else {
              $sth=ibase_prepare($ydb_conn, $sql);
            }
            $lastCommands.="$sqlCount) (TRANSACTION PREPARED)<BR>";
            $rs=ibase_execute($sth);
            $lastCommands.="$sqlCount) (TRANSACTION EXECUTED)<BR>";
          } else
            $rs = ibase_query($sql) or _dump("SQL ERROR DOING: $sql");

          $sqlErrNo = intval(ibase_errcode());
          $sqlError = strtolower(ibase_errmsg());
          if (($sqlErrNo==-913) || (trim($sqlError)=='deadlock update conflicts with concurrent update'))  {
            _dumpY(4,0,"deadlock.. waiting $retryCount");
            usleep($waitTime);
            $allWastedTime+=$waitTime;
          }
          $waitTime*=2;
        }
        /*
        if(strpos($sql,"into")>0)
          die(str_replace("<BR>", "\n", intval($updateSQLStatement).".".intval($cfgIbaseUseTransactions).".".intval($cfgIbaseUsePrepare)."\n$sql\n$lastCommands\n"));
        */

        if (($sqlErrNo!=0) && (strpos('LASTACCESS',strtoupper($sql))===FALSE) && (strpos('IS_SQLCACHE',strtoupper($sql))===FALSE)) {
          _dumpY(4,0,"*** ERRO AO EXECUTAR\n$sql");
          _dumpY(4,0,"$sqlErrNo: $sqlError");
          if ($transactionOpened) {
            ibase_rollback($transaction);
            $lastCommands.="$sqlCount) (TRANSACTION ROLLEDBACK)<BR>";
          }

          if ($retryCount==0) {
            _yLoaderDie(false,"Erro '$sqlError'  ao executar comando [$sql] ap&oacute;s $maxRetryCount tentativas em $allWastedTime microsegundos",$SQLDieOnError);
          } else{
            _yLoaderDie(false,"Error:\n\t'$sqlError'\nwhen run:\n\t( $sql; )",$SQLDieOnError);
          }
        } else
          $lastCommands.="$sqlCount) $sql;<BR>";

        if ($transactionOpened) {
          ibase_commit($transaction);
          $lastCommands.="$sqlCount) (TRANSACTION COMMITED)<BR>";
          if ($cfgIbaseUsePrepare)
            ibase_free_query($sth);
        }

      } else if (db_connectionTypeIs(_PGSQL_)) {
        $rs = pg_query($ydb_conn, $sql);
        $sqlErrNo = pg_result_status($rs, PGSQL_STATUS_LONG);
        $sqlError = pg_last_error($ydb_conn);
        _dumpY(4,1,"ret: $rs ErrNo: $sqlErrNo ErrMsg: $sqlError ydb_conn: $ydb_conn");
        if (trim($sqlError)>'') {
          _yLoaderDie(false,"Error:\n\t'$sqlError'\nwhen run:\n\t( $sql; )",$SQLDieOnError);
        } else {
          $lastCommands.="$sqlCount) $sql;<BR>";
        }

      } else
        _die("Identifique a sua Conex&atilde;o com o banco de dados. '".db_getConnectionTypeName()."' N&atildeo &eacute; reconhecido como v&aacute;lido");

      if (strtoupper($sql)=='COMMIT') {
        // alfa - 20120731 - ao criar tabelas, o sistema n&atilde;o reconhece quando est&aacute; na mesma Conex&atilde;o
        db_close();
        db_reconnect();
      }

    } else {
      $dbErrMsg=nl2br(explainDBError());
      $sql="    ".trim($sql);
      while (strpos($sql, "\n ")>0)
        $sql=str_replace("\n ", "\n", $sql);
      $sql=str_replace("\n", "\n    ", $sql);
      $msgErr="Error when doing [<pre>$sql</pre>].<BR />\n$dbErrMsg<BR />\n:".getCurrentIp();
      _dumpY(4,0,$msgErr);
      $SQLdebugLevel=4;
      _yLoaderDie(false,$msgErr,true);
    }

    $auxSQL=explode('<BR>',$lastCommands);
    $auxSQL=array_slice($auxSQL, -50);
    $lastCommands=join('<BR>',$auxSQL);
    return $rs;
  }

  function db_fetch($suffix)
  {
    if (db_connectionTypeIs(_MYSQLI_))
      $r="mysqli_fetch_$suffix";

    else if (db_connectionTypeIs(_MYSQL_))

      $r="mysql_fetch_$suffix";

    else if (db_connectionTypeIs(_FIREBIRD_)) {

      if (strtoupper($suffix)=='ARRAY')
        $suffix='assoc';
      $r="ibase_fetch_$suffix";

    } else if (db_connectionTypeIs(_PGSQL_)) {

      if (strtoupper($suffix)=='ARRAY')
        $suffix='assoc';
      $r="pg_fetch_$suffix";

    } else
      _die ("indique tipo de Conex&atilde;o ao banco de dados");

    return ($r);
  }

  function db_queryAndFillArray($sql, $keyField='', $withCountField=true)
  {
    $ret=array();
    _dumpY(4,5,"Running [$sql]");
    $qq=db_query($sql);
    if ($qq===false)
      _dumpY(4,5,"Returning FALSE");
    else
      _dumpY(4,5,"Returning a resource.  Ok!");
    $keys=explode(';', $keyField);
    $usingRecNdx=(trim($keyField)=='');
    $recNdx=-1;

    $asUTF8=outIsJSON();

    while ($dd=db_fetch_array($qq, false)) {
      $recNdx++;
      _dumpY(4,5,"Record# $recNdx");
      if ($usingRecNdx)
        $id=$recNdx;
      else  {
        $id='';
        foreach($keys as $k) {
          if ($id>'') $id.='.';
          $id.=$dd[$k];
        }
      }

      if (!isset($ret[$id]))
        $ret[$id]=array();
      foreach($dd as $k=>$v) {
        if (!is_numeric($k)) {
          if ($asUTF8) {
            $auxCharset=detect_encoding($v);
            $v=iconv($auxCharset, 'UTF-8', $v);
            /* 2016-11-01 AG
             * $v=preg_replace('/[[:^print:]]/', '', $v);
             */
          }
          if (!in_array($k, $keys))
            $ret[$id][$k]=$v;
        }
      }
      if ($withCountField) {
        if (!isset($ret[$id]['__COUNT__']))
          $ret[$id]['__COUNT__']=0;
        $ret[$id]['__COUNT__']++;
      }
    }
    db_free($qq);
    return $ret;
  }

  function db_sql($sql, $asRow=true, $fixIBFields=true)
  {
    global $ydb_conn, $toDebug;

    // $lastCommands.="$sql<BR>";

    try {
      $ret=null;

      $_rs=db_query($sql);

      if ($_rs) {
        if ((is_resource($_rs)) || (is_object($_rs))) {
          if ($asRow)
            $_values = db_fetch_row($_rs, $fixIBFields);
          else
            $_values = db_fetch_array($_rs, $fixIBFields);

          if ( $_values ) {

            if ($asRow) {
              for ($i=0; $i<count($_values); $i++) {
                $_v = $_values[$i];
                if (false)
                  $_v=RFC_3986($_v);
                _dumpY(4,2,"_values[$i] = $_v");
                $_values[$i]=trim($_v);
              }
            } else {

              foreach($_values as $_k => $_v) {
                if (false)
                  $_v=RFC_3986($_v);
                _dumpY(4,2,"$_v");
                $_values[$_k]=trim($_v);
              }
            }

            db_free($_rs);
            _dumpY(4,2,count($_values));
            if (count($_values)==1)
              $ret=$_values[0];
            else
              $ret=$_values;

            _dumpY(4,2,var_export($ret,true));

          } else
            $ret = (substr(strtoupper($sql),0,6)=='UPDATE');
        } else {
          $ret=$_rs;
        }
      } else {
        _dumpY(4,0,"\t\tEsta retornando zero");
        $ret = null;
      }
    } catch (yException $e) {

    }

    return $ret;
  }

  function db_getList($sql, $sep=',')
  {
    $list='';
    $qq=db_query($sql);
    while ($dd=db_fetch_row($qq)) {
      if ($list>'')
        $list.=',';
      $list.=$dd[0];
    }
    return $list;
  }

  function _IB_CorrectDate($v)
  {
    $universalDate='([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})\ ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})';
    $usaDate='([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})\ ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})';

    $universalDateB='([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})';
    $usaDateB='([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})';

    $universalDateC='([0-9]{4})([0-9]{1,2})([0-9]{1,2})([0-9]{1,2})([0-9]{1,2})([0-9]{1,2})';
    $usaDateC='([0-9]{1,2})([0-9]{1,2})([0-9]{4})([0-9]{1,2})([0-9]{1,2})([0-9]{1,2})';
    $k=false;
    $vn=$v;
    if (strlen($v)==19) {
      // $k=preg_match($universalDate,$v,$regs);
      $k=preg_match("/$universalDate/",$v,$regs);
      if ($k) {
        $vn=$regs[2].$regs[3].$regs[1].$regs[4].$regs[5].$regs[6];
        $ret[$k]=$vn;
      } else {
        //$k=preg_match($usaDate,$v,$regs);
        $k=preg_match("/$usaDate/",$v,$regs);
        if ($k) {
          $vn=$regs[1].$regs[2].$regs[3].$regs[4].$regs[5].$regs[6];
          $ret[$k]=$vn;
        }
      }
    } else if (strlen($v)==10) {
      // $k=preg_match($universalDateB,$v,$regs);
      $k=preg_match("/$universalDateB/",$v,$regs);
      if ($k) {
        $vn=$regs[2].$regs[3].$regs[1].'000000';
        $ret[$k]=$vn;
      } else {
        // $k=preg_match($usaDateB,$v,$regs);
        $k=preg_match("/$usaDateB/",$v,$regs);
        if ($k) {
          $vn=$regs[1].$regs[2].$regs[3].'000000';
          $ret[$k]=$vn;
        }
      }
    } else if ((strlen($v)==14) || (strlen($v)==12)) {
      if (strlen($v)==12)
        $v=$v.'00';
      // $k=preg_match($universalDateC,$v,$regs);
      $k=preg_match("/$universalDateC/",$v,$regs);
      if ($k) {
        $vn=$regs[2].$regs[3].$regs[1].$regs[4].$regs[5].$regs[6];
        $ret[$k]=$vn;
      } else {
        //$k=preg_match($usaDateC,$v,$regs);
        $k=preg_match("/$usaDateC/",$v,$regs);
        if ($k) {
          $vn=$regs[1].$regs[2].$regs[3].$regs[4].$regs[5].$regs[6];
          $ret[$k]=$vn;
        }
      }
    }
    return ($vn);
  }

  function _IB_FixFields(&$ret)
  {
    $regs=array();
    foreach($ret as $key=>$v) {
      $v=trim($v);
      $ret[$key]=$v;
      $vn=_IB_CorrectDate($v);
      if ($vn>'')
        $ret[$key]=$vn;
      /*
      if ($k) {
        echo "$key = $v -&gt; $vn = ".dataFormatada($vn)."<BR>";
      }
      */

    }
  }

  function _db_cureFieldsFromDB(&$ret)
  {
    global $appCharset, $dbCharset;

    foreach($ret as $k=>$v) {
      $value=trim($ret[$k]);
      if ($value>'') {
        $value=html_entity_decode($value);
        if ($appCharset>'') {
          $strCharset = mb_detect_encoding($value, "$dbCharset,ISO-8859-1,UTF-8", true);
          if ($strCharset=="") {
            $strCharset = $dbCharset;
          }
          _dumpY(4,5,"$k from $strCharset to $appCharset ($value)");
          $value=mb_convert_encoding($value,$appCharset,$strCharset);
        }
      }
      $ret[$k]=$value;
      $ret[$k]=xq_calculatedField($ret, $k, $value);
    }
  }

  function db_fetch_array($res, $fixIBFields=true, $mirrorFieldNames=true)
  {
    global $cfgDBCureFields;

    if ($res) {
      $fetch_func=db_fetch('array');

      $ret=$fetch_func($res);
      /*
      try {
        $ret=$fetch_func($res);
      } catch (Exception $_E_) {
        _yLoaderDie(false,$_E_->getMessage(), true);
      }
      */

      if ($ret) {
        _db_cureFieldsFromDB($ret);

        if (db_connectionTypeIs(_FIREBIRD_)) {
          if ($fixIBFields)
            _IB_FixFields($ret);
          // corrige os nomes para minusculas
          // isso para simplificar a integracao mysql/interbase
          if ($mirrorFieldNames) {
            foreach($ret as $k => $v) {
              if (!(is_numeric($k))) {
                $k=strtolower($k);
                $ret[$k]=$v;
              }
            }
          }
        }
      }

      return $ret;
    }
  }

  function db_fetch_row($res, $fixIBFields=true)
  {
    $ret=null;
    if ($res) {
      $fetch_func=db_fetch('row');

      if ($ret=$fetch_func($res)) {
        _db_cureFieldsFromDB($ret);

        if ((db_connectionTypeIs(_FIREBIRD_)) && ($ret))
          if ($fixIBFields)
            _IB_FixFields($ret);
      }
    }
    return $ret;
  }

  function db_exportLine($dataArray, &$header, $requiredColumns='')
  {
    $canDo=true;
    if ($requiredColumns>'') {
      if (strpos(" $requiredColumns",';')>0)
        $requiredColumns=explode(";",$requiredColumns);
      else
        $requiredColumns=explode(",",$requiredColumns);
      foreach($requiredColumns as $reqCol)
        $canDo=$canDo and (trim($dataArray[$reqCol])>'');
    }

    $dataLine='';
    if ($canDo) {
      $order   = array("\r\n", "\n", "\r");
      $firstLine=($header=='');
      $colCount=0;
      foreach($dataArray as $k=>$v) {
        $v=trim($v);
        if (!is_numeric($k)) {
          if ($firstLine) {
            if ($header>'')
              $header.=';';
            $header.=$k;
          }
          if ($colCount++>0)
            $dataLine.=';';
          if (strpos($v,';')!==FALSE) {
            $v=addslashes($v);
            $v='"'.$v.'"';
          } else if (str_is_float($v)) {
            $v=str_replace(',','', $v);
            $v=str_replace('.', ',', $v);
          }
          $v = str_replace($order,'\\n',$v);
          $dataLine.=$v;
        }
      }
    }
    return $dataLine;
  }

  function db_free($rs)
  {

    if ($rs) {
      if (db_connectionTypeIs(_MYSQLI_))
        $r='mysqli_free_result';
      else if (db_connectionTypeIs(_MYSQL_))
        $r='mysql_free_result';
      else if (db_connectionTypeIs(_FIREBIRD_))
        $r='ibase_free_result';
      else if (db_connectionTypeIs(_PGSQL_))
        $r='pg_free_result';
      return $r($rs);
    }
  }

  function db_lasterror()
  {
    global $ydb_conn;

    if (db_connectionTypeIs(_MYSQLI_))
      $res=mysqli_error($ydb_conn);

    else if (db_connectionTypeIs(_MYSQL_))
      $res=mysql_error();

    else if (db_connectionTypeIs(_PGSQL_))
      $res=pg_last_error();

    else if (db_connectionTypeIs(_FIREBIRD_))
      $res=ibase_errmsg();

    return $res;
  }

  function db_num_fields($rs)
  {

    if (db_connectionTypeIs(_MYSQLI_))
      $r='mysqli_num_fields';
    else if (db_connectionTypeIs(_MYSQL_))
      $r='mysql_num_fields';
    else if (db_connectionTypeIs(_PGSQL_))
      $r='pg_num_fields';
    else if (db_connectionTypeIs(_FIREBIRD_))
      $r='ibase_num_fields';
    return $r($rs);
  }

  function db_field_name($rs, $i)
  {

    if (db_connectionTypeIs(_MYSQLI_))
      $r='mysqli_field_name';
    else if (db_connectionTypeIs(_MYSQL_))
      $r='mysql_field_name';
    else if (db_connectionTypeIs(_PGSQL_))
      $r='pg_field_name';
    else if (db_connectionTypeIs(_FIREBIRD_))
      $r='ibase_field_info';

    $res=$r($rs,$i);
    if (db_connectionTypeIs(_FIREBIRD_)) {
      $r1=trim($res['name']);
      if ($r1=='')
        $r1=trim($res['alias']);
      $res=$r1;
    }
    return $res;
  }

  function db_affected_rows($res)
  {

    if (db_connectionTypeIs(_MYSQLI_))
      $r='mysqli_affected_rows';
    else if (db_connectionTypeIs(_MYSQL_))
      $r='mysql_affected_rows';
    else if (db_connectionTypeIs(_PGSQL_))
      $r='pg_affected_rows';
    else if (db_connectionTypeIs(_FIREBIRD_))
      $r='ibase_affected_rows';
    if ($res)
      return $r($res);
    else
      return $r();
  }

  function db_fieldList($tableName)
  {
    /*
     * Isso aqui � um pouco de tudo
     * Enquanto no mysql a estrutura vem com um �nico comando, no fb temos
     * de montar uma baita estrutura para poder recuperar alguma coisa parecida
     * Pior que isso � que a repesenta��o interna e externa n�o batem nem mesmo
     * quando vc tem os mesmos nomes de campos.  Beleza, YeAPF pretende simplificar
     * isso tudo pelo menos o suficiente para poder migrar dados de um banco a outro.
     *
     * Esta fun��o devolve um vetor bidimensional n�o associativo
     * As colunas de cada registro s�o as seguintes:
     * 1-nome da coluna
     * 2-tipo (unificado)
     * 3-largura
     * 4-decimais
     * 5-se pode conter NULL
     * 6-valor padr�o
     */
    $tableName=unquote($tableName);

    if (db_connectionTypeIs(_MYSQL_) || db_connectionTypeIs(_MYSQLI_))
      $sql="show columns from $tableName";
    else if (db_connectionTypeIs(_PGSQL_)) {
      $sql="SELECT a.attname, format_type(a.atttypid, a.atttypmod), null as fieldLen, a.attnotnull, a.atthasdef
            FROM pg_class c, pg_attribute a
            WHERE c.relname = '$tableName'
              AND a.attnum > 0 AND a.attrelid = c.oid
            ORDER BY a.attnum";
    } else if (db_connectionTypeIs(_FIREBIRD_)) {
      $tableName=strtoupper($tableName);
      $sql="SELECT       RRF.RDB\$FIELD_NAME AS CAMPO,
                          CASE
                            RTP.RDB\$TYPE_NAME
                              WHEN 'VARYING'  THEN  'VARCHAR'
                              WHEN 'LONG'     THEN  'INTEGER'
                              WHEN 'SHORT'    THEN  'SMALLINT'
                              WHEN 'DOUBLE'   THEN  'DOUBLE PRECISION'
                              WHEN 'FLOAT'    THEN  'DOUBLE PRECISION'
                              WHEN 'INT64'    THEN  'NUMERIC'
                              WHEN 'TEXT'     THEN  'CHAR'
                              ELSE RTP.RDB\$TYPE_NAME
                          END TIPO_CAMPO,
                          CASE
                            RTP.RDB\$TYPE_NAME
                              WHEN  'VARYING' THEN RFL.RDB\$FIELD_LENGTH
                              WHEN  'TEXT' THEN RFL.RDB\$FIELD_LENGTH
                              ELSE  RFL.RDB\$FIELD_PRECISION
                          END AS TAMANHO,
                          IIF(  EXISTS(   SELECT      FIRST 1 1
                                          FROM        RDB\$RELATION_CONSTRAINTS  RCN
                                          INNER JOIN  RDB\$CHECK_CONSTRAINTS     CCN     ON    RCN.RDB\$CONSTRAINT_NAME = CCN.RDB\$CONSTRAINT_NAME AND
                                                                                              CCN.RDB\$TRIGGER_NAME = RRF.RDB\$FIELD_NAME
                                           WHERE      RCN.RDB\$RELATION_NAME =  RRF.RDB\$RELATION_NAME ),
                                'X',
                                '-')  AS  NOT_NULL,
                          IIF(  EXISTS(   SELECT      FIRST 1 1
                                          FROM        RDB\$RELATION_CONSTRAINTS  RCN
                                          INNER JOIN  RDB\$INDEX_SEGMENTS        ISG     ON    RCN.RDB\$INDEX_NAME = ISG.RDB\$INDEX_NAME AND
                                                                                              ISG.RDB\$FIELD_NAME = RRF.RDB\$FIELD_NAME
                                          WHERE       RCN.RDB\$CONSTRAINT_TYPE = 'PRIMARY KEY' AND
                                                      RCN.RDB\$RELATION_NAME =  RRF.RDB\$RELATION_NAME),
                                'X',
                                '-')  AS  PRIMARY_KEY,
                          IIF(  RRC.RDB\$RELATION_NAME IS NOT NULL,
                                'X',
                                '-')  AS  FOREIGN_KEY,
                          (RFL.RDB\$FIELD_SCALE * -1) AS ESCALA,
                          RFC.RDB\$CONST_NAME_UQ AS  INDICE_CHAVE,
                          RRC.RDB\$RELATION_NAME AS  TABELA_CHAVE,
                          RIS2.RDB\$FIELD_NAME   AS  CAMPO_CHAVE,
                          RFC.RDB\$UPDATE_RULE   AS  REGRA_UPDATE,
                          RFC.RDB\$DELETE_RULE   AS  REGRA_DELETE
              FROM        RDB\$RELATION_FIELDS   RRF
              INNER JOIN  RDB\$FIELDS            RFL     ON    RFL.RDB\$FIELD_NAME = RRF.RDB\$FIELD_SOURCE
              INNER JOIN  RDB\$TYPES             RTP     ON    RTP.RDB\$TYPE = RFL.RDB\$FIELD_TYPE AND
                                                              RTP.RDB\$FIELD_NAME = 'RDB\$FIELD_TYPE'
              LEFT JOIN   RDB\$INDEX_SEGMENTS    RIS     ON    RIS.RDB\$FIELD_NAME = RRF.RDB\$FIELD_NAME AND
                                                              EXISTS (  SELECT      FIRST 1 1
                                                                        FROM        RDB\$INDICES   IND
                                                                        INNER JOIN  RDB\$REF_CONSTRAINTS   RFC   ON    RFC.RDB\$CONSTRAINT_NAME = IND.RDB\$INDEX_NAME
                                                                        WHERE       IND.RDB\$INDEX_NAME = RIS.RDB\$INDEX_NAME AND
                                                                                    IND.RDB\$RELATION_NAME = RRF.RDB\$RELATION_NAME)
              LEFT JOIN   RDB\$REF_CONSTRAINTS   RFC     ON    RFC.RDB\$CONSTRAINT_NAME = RIS.RDB\$INDEX_NAME
              LEFT JOIN   RDB\$INDEX_SEGMENTS    RIS2    ON    RIS2.RDB\$INDEX_NAME = RFC.RDB\$CONST_NAME_UQ AND
                                                              RIS2.RDB\$FIELD_POSITION = RIS.RDB\$FIELD_POSITION
              LEFT  JOIN  RDB\$RELATION_CONSTRAINTS RRC  ON    RFC.RDB\$CONST_NAME_UQ = RRC.RDB\$CONSTRAINT_NAME AND
                                                              RRC.RDB\$CONSTRAINT_TYPE = 'PRIMARY KEY'
              WHERE       /* RRF.RDB\$RELATION_NAME NOT STARTING WITH 'RDB\$' and */ RRF.RDB\$RELATION_NAME ='$tableName'
              ORDER BY    RRF.RDB\$RELATION_NAME";

    }

    $ret=Array();
    $qq=db_query($sql);
    while ($dd=db_fetch_row($qq))
    {
      $i = count($ret);
      $aux = array();
      for ($k=0; $k<count($dd); $k++)
        $aux[$k]=trim($dd[$k]);
      $ret[$i] = $aux;
    }

    if (db_connectionTypeIs(_MYSQL_) || db_connectionTypeIs(_MYSQLI_)) {
      for($y=0; $y<count($ret); $y++) {
        for($x=4; $x>1; $x--)
          $ret[$y][$x+2] = $ret[$y][$x];
        $aux = $ret[$y][1];
        $p=strpos($aux."()",'(');
        $type = substr($aux,0,$p);
        $aux=substr($aux,$p+1);
        if ($aux>'') {
          $p=strpos($aux.")",')');
          $size = substr($aux,0,$p);
        } else
          $size = 'null';
        $dec='';
        switch($type)
        {
          case 'int':
            $type='INTEGER';
            break;
          case 'varchar':
          case 'char':
            $type=strtoupper($type);
            break;
          case 'tinyint':
            $type='SMALLINT';
            break;
          case 'double':
            $type='DECIMAL';
            $dec=4;
            break;
        }
        $ret[$y][1]=$type;
        $ret[$y][2]=$size;
        $ret[$y][3]=$dec;
      }
    } else if (db_connectionTypeIs(_PGSQL_)) {
      for($y=0; $y<count($ret); $y++) {
        $auxType=$ret[$y][1];
        $p=new xParser($auxType);
        $type='';
        $tokenType=0;
        while (($tokenType!=4) && (!$p->eof())) {
          if ($p->get($token,$tokenType))
            if ($tokenType!=4)
              $type=trim("$type $token");
        }
        $p->getExpectingType($len,1);
        $p->getExpectingType($token,$tokenType);
        if ($token==',') {
          $p->get($dec,$tokenType);
        } else
          $dec=0;
        unset($p);
        // die("[$auxType][$type][$len][$dec]\n");
        switch($type)
        {
          case 23:
            $type='INTEGER';
            break;
          case 21:
            $type='SMALLINT';
            break;
          case 1042:
            $type='CHAR';
            break;
          case 1043:
            $type='VARCHAR';
            break;
        }
        $ret[$y][1]=$type;
        $ret[$y][2]=$len;
        $ret[$y][3]=$dec;
      }
    }

    for($y=0; $y<count($ret); $y++)
      $ret[$y][0]=trim($ret[$y][0]);

    return $ret;
  }

  function db_fieldType($tableName, $fieldName)
  {
    $aux=db_fieldList($tableName);
    $ret='';
    for ($y=0; ($ret=='') and ($y<count($aux)); $y++) {
      _dumpY(4,1,$fieldName.' == '.$aux[$y][0]);
      if (strtoupper($fieldName)==strtoupper($aux[$y][0])) {
        $ret=$aux[$y][1];
        // echo "\n<div>$tableName.$fieldName = $ret</div>";
      }
    }
    return $ret;
  }

  function db_fieldExists($tableName, $fieldName)
  {
    $tableName=trim($tableName);
    $fieldName=trim($fieldName);
    if (db_connectionTypeIs(_MYSQL_) || db_connectionTypeIs(_MYSQLI_)) {

      $sql="show columns from $tableName like '$fieldName'";

    } else if (db_connectionTypeIs(_PGSQL_)) {
      $tableName=strtoupper($tableName);
      $fieldName=strtoupper($fieldName);

      $sql="SELECT upper(attname)
            FROM pg_attribute
            WHERE attrelid = (SELECT oid FROM pg_class WHERE upper(relname) = '$tableName')
            AND upper(attname) = '$fieldName'";

    } else if (db_connectionTypeIs(_FIREBIRD_)) {

      $tableName=strtoupper($tableName);
      $fieldName=strtoupper($fieldName);
      $sql="SELECT RDB\$FIELD_NAME
            FROM RDB\$RELATION_FIELDS
            WHERE RDB\$RELATION_NAME='$tableName'
              AND RDB\$FIELD_NAME='$fieldName'";
    }
    $r=db_sql($sql);
    if (is_array($r))
      $r=$r[0];
    $r=intval(strtoupper(trim($r))==strtoupper(trim($fieldName)));

    _dumpY(4,1,"*** $tableName.$fieldName? = $r");
    return $r;
  }

  function db_triggerExists($triggerName)
  {
    $triggerName=trim($triggerName);
    $ret=false;
    if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_))) {
      $sql="SHOW TRIGGERS";
      $auxRet=db_queryAndFillArray($sql);
      foreach($auxRet as $trigger) {
        if ($trigger['Trigger']==$triggerName)
          $ret=true;
      }
    } else
      _die("ERROR, db_triggerExists() not implemented");

    return $ret;
  }
  // table index
  $requestedTables=array();

  function db_tableList()
  {
    if (db_connectionTypeIs(_MYSQL_) || db_connectionTypeIs(_MYSQLI_)) {
      $sql="show tables";
    } else if (db_connectionTypeIs(_PGSQL_)) {
      $sql="select tablename from pg_tables where schemaname='public'";
    } else if (db_connectionTypeIs(_FIREBIRD_)) {
      $sql="SELECT RDB\$RELATION_NAME FROM RDB\$RELATIONS WHERE RDB\$VIEW_BLR IS NULL  and not (RDB\$RELATION_NAME like 'RDB\$%')";
    } else
      _die($GLOBALS['ydb_type']." Not recognized as a valid connection type");
    $auxRet=db_queryAndFillArray($sql);
    $keyName='';
    $ret=array();
    foreach($auxRet as $v) {
      switch(db_getConnectionType())
      {
        case _MYSQLI_:
        case _MYSQL_:
          if ($keyName=='') {
            reset($v);
            $keyName=key($v);
          }
          $ret[]=$v[$keyName];
          break;
        case _PGSQL_:
          $ret[]=$v['tablename'];
          break;
        case _FIREBIRD_:
          if ($keyName=='') {
            reset($v);
            $keyName=key($v);
          }
          $ret[]=trim($v[$keyName]);
          break;
      }
    }
    return $ret;
  }

  function db_tableExists($tableName)
  {
    global $requestedTables;

    $r="N0T-EXIST$";
    if ($tableName>'') {
      if (isset($requestedTables[$tableName]))
        $r=$requestedTables[$tableName];
      else
        $r='';
      if ($r=='') {
        if (db_connectionTypeIs(_MYSQL_) || db_connectionTypeIs(_MYSQLI_)) {
          $sql="show tables like '$tableName'";
        } else if (db_connectionTypeIs(_PGSQL_)) {
          $tableName=strtoupper($tableName);
          $sql="select tablename from pg_tables where upper(tablename)='$tableName'";
        } else if (db_connectionTypeIs(_FIREBIRD_)) {
          $tableName=strtoupper($tableName);
          $sql="SELECT RDB\$RELATION_NAME FROM RDB\$RELATIONS WHERE RDB\$VIEW_BLR IS NULL AND RDB\$RELATION_NAME='$tableName'";
        } else
          _die($GLOBALS['ydb_type']." not known as 'dbType' value on db.csv");
        $r=db_sql($sql);
        $requestedTables[$tableName]="$r";
      }
    }

    return (trim(strtoupper($r))==trim(strtoupper($tableName)));
  }

  function db_grant_cached_query($sql, $sqlUID='', $maxRecordCount=-1)
  {
    global $_SQL_cleanCache, $_SQL_cleanAllCaches, $_SQL_doCacheOnTable, $_SQL_cacheTTL,$sysTimeStamp;

    $sqlID='';
    if ($_SQL_cleanCache) {
      if (lock("SQLCache.clean",true)) {
        if ($_SQL_cleanAllCaches)
          $res=db_query("select id from is_sqlcache");
        else
          $res=db_query("select id from is_sqlcache where lastAccess+ttl<$sysTimeStamp");
        $qc=0;
        while ($auxData=db_fetch_row($res)) {
          $auxID=$auxData[0];
          db_clean_cached_query($auxID);

          $qc++;
        }
        db_free($res);
        if ($qc>0)
          _statusBar("$qc cached queries cleaned-up");
        unlock("SQLCache.clean");
      }
    }

    if ($maxRecordCount>0) {
      if (db_connectionTypeIs(_FIREBIRD_)) {
        if (strpos(strtoupper($sql), ' FIRST ')==0)
          $sql=substr($sql,0,7)."FIRST $maxRecordCount ".substr($sql,7);
      } else {
        if (strpos(strtoupper($sql),' LIMIT ')==0)
          $sql="$sql limit 0,$maxRecordCount";
      }
    }

    $sqlStatement=md5($sql);
    if ($_SQL_doCacheOnTable) {
      $sqlID=valorSQL("select id from is_sqlcache where statement='$sqlStatement'");
      if ($sqlID>'') {
        if (lock("sqlCache.$sqlStatement", true)) {
          db_sql("delete from is_sqlcache where statement='$sqlStatement'");
          unlock("sqlCache.$sqlStatement");
        }
        $sqlID='';
      }
    }
    if ($sqlID=='') {
      $sqlID=y_uniqid();
      _dumpY(4,0,"granting $sqlID");
      $startTime=date("U");
      if ($_SQL_doCacheOnTable)
        db_sql("insert into is_sqlcache (id, buildDate, ttl, statement, lastAccess) values ('$sqlID',$sysTimeStamp,$_SQL_cacheTTL,'$sqlStatement', $sysTimeStamp)");
      else {
        if (!(file_exists("cachedQueries")))
          if (!mkdir("cachedQueries",0777))
            _dumpY(4,0,"ERROR: Was not possible to create 'cachedQueries' folder");

        $fCacheFileName="cachedQueries/".$sqlID.".xml";
        $fIndexFileName="cachedQueries/".$sqlID.".ndx";
        $fCountFileName="cachedQueries/".$sqlID.".count";
        $fSQLFileName="cachedQueries/".$sqlID.".sql";
        $fInfoFileName="cachedQueries/".$sqlID.".inf";

        _statusBar("$fCacheFileName");

        $fCache=fopen($fCacheFileName, 'wb');
        $fIndex=fopen($fIndexFileName, 'wb');
        $fCount=fopen($fCountFileName, 'w');
        $fSQL=fopen($fSQLFileName, 'w');
        $fInf=fopen($fInfoFileName, 'w');
      }

      $res=db_query($sql);
      $po=0;
      $IndexBase=0;
      while ($auxData=db_fetch_array($res)) {
        $rowData='';

        foreach($auxData as $k=>$v) {
          if (db_connectionTypeIs(_FIREBIRD_))
            $k=strtoupper($k);
          // $v=utf8_encode(str_replace("'",'`',$v));
          $v=str_replace("\n","\\"."n",$v);
          // $v=utf8_encode($v);
          $auxCharset=detect_encoding($v);
          $v = iconv($auxCharset, 'UTF-8', $v);

          if (!is_numeric($k)) {
            if (strpos($rowData,"<$k>")===false)
              $rowData.="<$k>$v</$k>";
          }
        }
        // echo "$rowData\n";
        $rowData.="\n";

        _dumpY(4,5,"----$rowData----");

        if ($_SQL_doCacheOnTable)
          db_sql("insert into is_sqlcache_content (id, o, content, comp) values ('$sqlID', $po, '$rowData', null)");
        else {
          $DataLen=strlen($rowData);
          fwrite($fCache, "$rowData") || _die("erro ao gravar $rowData em $f0");
          // fwrite($fIndex,sprintf('%06d%06d',intval($IndexBase),intval($DataLen)));
          fwrite($fIndex,pack('N',$IndexBase));
          fwrite($fIndex,pack('N',$DataLen));
          $IndexBase+=$DataLen;
        }
        $po++;
      }
      db_free($res);

      $secs=date("U")-$startTime;

      if (!$_SQL_doCacheOnTable) {
        fwrite($fInf,"sqlUID=$sqlUID");
        fwrite($fCount,"$po");
        fwrite($fSQL,"$sql");

        fclose($fCache);
        fclose($fIndex);
        fclose($fCount);
        fclose($fSQL);
        fclose($fInf);
      }

      _statusBar("Query Cached.  $po lines in $secs seconds");
    } else {
      if (lock("SQLCache.$sqlID",true)) {
        db_sql("update is_sqlcache set lastAccess='$sysTimeStamp' where id='$sqlID'");
        unlock("SQLCache.$sqlID");
      }
    }

    return ($sqlID);
  }

  function db_clean_cached_query($sqlID)
  {
    global $_SQL_doCacheOnTable;

    if ($_SQL_doCacheOnTable) {
      $auxID=db_sql("select id from is_sqlcache where statement='$sqlID'");
      if ($auxID>'')
        $sqlID=$auxID;
    }

    _dumpY(4,0,"cleaning $sqlID");

    if (!$_SQL_doCacheOnTable) {

      if (file_exists("cachedQueries/$sqlID.xml"))
        unlink("cachedQueries/$sqlID.xml");

      if (file_exists("cachedQueries/$sqlID.inf"))
        unlink("cachedQueries/$sqlID.inf");

      if (file_exists("cachedQueries/$sqlID.ndx"))
        unlink("cachedQueries/$sqlID.ndx");

      if (file_exists("cachedQueries/$sqlID.sql"))
        unlink("cachedQueries/$sqlID.sql");

      if (file_exists("cachedQueries/$sqlID.count"))
        unlink("cachedQueries/$sqlID.count");
    } else {
      if (lock("sqlCache.$sqlStatement", true)) {
        db_sql("delete from is_sqlcache where statement='$sqlStatement'");

        db_sql("delete from is_sqlcache_content where id='$sqlID'");
        db_sql("delete from is_sqlcache where id='$sqlID'");

        unlock("sqlCache.$sqlStatement");
      }
    }
  }

  function db_remove_cached_query($sqlUID)
  {
    _dumpY(4,0,"Removing $sqlUID");
    $d=dir("cachedQueries");
    $fileName='';
    while ($entry=$d->read()) {
      $aux=pathinfo("cachedQueries/$entry");
      if ($aux['extension']=='inf') {
        _dumpY(4,1,"$entry  -> ".$aux['extension']);
        $f=fopen("cachedQueries/$entry",'r');
        while ($info=fgets($f)) {
          _dumpY(4,2,"    $info");
          if (substr($info,0,6)=='sqlUID') {
            getNextValue($info,'=');
            if ($info==$sqlUID)
              $fileName=$aux['filename'];
          }
        }
        fclose($f);
      }
    }

    if ($fileName>'') {
      db_clean_cached_query($fileName);
    }
  }

  function db_remove_all_cached_queries()
  {
    if (is_dir("cachedQueries")) {
      $d=dir("cachedQueries");
      while ($entry=$d->read()) {
        $aux=pathinfo("cachedQueries/$entry");
        if ($aux['extension']=='sql')
          db_clean_cached_query($aux['filename']);
      }
    }
  }

  global $cfgMainFolder;

  /*
   * sqlite3 extensions
   */
  if (class_exists("SQLite3")) {
    class ySQlite3 extends SQLite3
    {
      function tableExists($db, $tableName) {
        $res=$db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$tableName'");
        $ret = $res->fetchArray();
        return $ret['name']==$tableName;
      }
    }
  }

  if (file_exists("$cfgMainFolder/flags/flag.dbgloader")) error_log(basename(__FILE__)." 0.8.62 ".date("i:s").": yeapf.db.php ready\n",3,"$cfgCurrentFolder/logs/yeapf.loader.log");

  _recordWastedTime("Iniciando yeapf.db.php ...");
  getConfigFileName();
  _recordWastedTime("... getConfigFileName()");
  db_startup();
  _recordWastedTime("... db_startup()");

  if (function_exists('db_checkConfig')) {
    db_checkConfig();
    _recordWastedTime("... db_checkConfig()");
  }

  _recordWastedTime("yeapf.db.php Carregado");
?>
