<?php
/*
    skel/webApp/index.php
    YeAPF 0.8.63-113 built on 2019-07-12 15:23 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    2019-07-12 15:23:24 (-3 DST)

    skel/webApp / index.php
    This file cannot be modified within skel/webApp
    folder, but it can be copied and changed outside it.
*/

  function myErrorHandler($errno, $errstr, $errfile, $errline)
  {
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }

    switch ($errno) {
    case E_USER_ERROR:
        echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
        echo "Aborting...<br />\n";
        exit(1);
        break;

    case E_USER_WARNING:
        echo "<b>My WARNING</b> [$errno] $errstr<br />\n&nbsp;&nbsp;$errfile:$errline<br />\n";
        break;

    case E_USER_NOTICE:
        echo "<b>My NOTICE</b> [$errno] $errstr<br />\n&nbsp;&nbsp;$errfile:$errline<br />\n";
        break;

    default:
        echo "Unknown error type: [$errno] $errstr<br />\n&nbsp;&nbsp;$errfile:$errline<br />\n";
        break;
    }

    /* Don't execute PHP internal error handler */
    return true;
  }

  if ((file_exists("flags/flag.dbgloader")) || (file_exists("flags/flag.dbgphp")) || (file_exists("flag.dbgphp")))
    if (!file_exists("logs/yeapf.loader.log"))
      touch("logs/yeapf.loader.log");


  if ((file_exists("flags/flag.dbgphp")) || (file_exists("flag.dbgphp"))) {
    error_log(date('i:s').": All debug flags on\n",3,'logs/yeapf.loader.log');
    ini_set('display_errors','1');
    error_reporting (E_ALL);
    $old_error_handler = set_error_handler("myErrorHandler");
  }

  if (!file_exists('yeapf.php')) {
    if (file_exists('flags/flag.dbgloader')) error_log(date('i:s').": yeapf.php not found\n",3,'logs/yeapf.loader.log');
    $devSession = isset($devSession)?$devSession:'';
    die("<div  style='border-color: #CB0000; background: #FFC0CB; width: 520px; margin: 8px; padding: 32px; border-style: solid; border-width: 2px; padding: 16px; border-radius:4px; font-family: arial; font-size: 12px'>
             <b>yeapf.php cannot be found<br>
             <a href='configure.php?devSession=$devSession'>Click here to configure</a></b><br><br>
             YeAPF 0.8.63-113 built on 2019-07-12 15:23 (-3 DST)<br>
             Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
        </div>");
  }

  if (file_exists('flags/flag.dbgloader')) error_log(date('i:s').": loading yeapf.php\n",3,'logs/yeapf.loader.log');
  (@include_once "yeapf.php") or die ("Error loading 'yeapf.php'");
  if (file_exists('flags/flag.dbgloader')) error_log(date('i:s').": yeapf.php ready\n",3,'logs/yeapf.loader.log');

  /* @OBSOLETE 20170111
  $developBase=$yeapfConfig['yeapfPath']."/../develop";
  (@include_once "$developBase/yeapf.develop.php") or die ("Error loading '$developBase/yeapf.develop.php'");
  if (file_exists('flags/flag.dbgloader')) error_log(date('i:s').": yeapf.develop.php ready\n",3,'logs/yeapf.loader.log');
  $devMsgQueue=new xDevelopMSG($devSession, file_exists('flags/flag.nosharedmem'));
  $devMsgQueue->sendStagedMessage('busy');
  */

  // logOutput = -1: Arquivo, 0: silencio, 1: tela, 2: xml
  $logOutput=-1;  /* HTML -> Local TextFile */
  $u=isset($u)?$u:null;

  _dumpY(1,1,"Start of process * $s.$a ($u)");
  try {
    switch ($s) {
      case 'logon':
        // assume user rights equals with folder rights
        $logonRights = $appFolderRights;

        if (function_exists('tryLogon'))
          // let's try logon using user defined function
          $GID=tryLogon($q2, $q1, $logonRights);
        else {
          // is user function doesn't exists, we try to logon using
          // internal implementation
          $fakeUserContext=new xUserContext($u, true);
          $GID=$fakeUserContext->userLogon($q2, $q1, $logonRights);
          unset($fakeUserContext);
        }

        if (("$GID"=='') || (is_numeric($u) && ($u<=0))) {
          $s='';
          $aBody=bestName("f_logon");
        } else {
          $aBody="e_index";

          $userContext=new xUserContext($u);
          $userContext->createUserContext($GID, $logonRights, $usrTableName);
          $userContext->addUserVars('devSession');

          $scriptName=getAppScript();
          if ($scriptName>'')
            (@include_once $scriptName) or die("Error loading $scriptName");

          yeapfStage("afterLogon");
        }
        break;

      case 'createUser':
        $aBody=bestName('f_create_user');
        if ($q3==$q1) {
          if (strlen(trim($q3))>5) {
            if (trim($q1)>'') {
              $userContext=new xUserContext('',true);
              $userContext->createNewUser($q2,$q4,$q3);
              $aBody=bestName($logonForm);
            } else
              _recordError("Please indicate a user name");
          } else
            _recordError("Fill password with more than 5 characters");
        } else
          _recordError("Both passwords need to the same");

      case '':
        if (_getErrorCount()==0) {
          if ((!isset($yUserCount)) || ($yUserCount==0)) {
            if (db_tableExists($usrTableName)) {
              $yUserCount=db_sql("select count(*) from $usrTableName");
              if ($yUserCount>0) {
                $setupIni=createDBText($sgugIni);
                if ($setupIni->locate("active",1)==$dbTEXT_NO_ERROR) {
                  $setupIni->setValue('yUserCount',$yUserCount);
                  $setupIni->commit();
                }
              }
            }
          }

          if ($yUserCount==0)
            $logonForm='f_create_user';
          else {
            if ((isset($cfgJumpToBody)) && (intval($cfgJumpToBody)==1))
              $logonForm='e_index';
            else {
              if ($logonForm=='')
                $logonForm='f_logon';
            }
          }

          _dump("yUserCount=$yUserCount : logonForm='$logonForm'");

          $aBody=bestName($logonForm);
          if ($aBody=='')
            die("'$logonForm' not found");
        } else {
          die("$lastError");
        }
        break;

      default:
        $aBody="e_yeapf_app";
        break;
    }

    initOutput();
    /* @OBSOLETE 20170111
    $devMsgQueue->sendStagedMessage('aBody',$aBody);
    */
    echo "\n<!-- aBody: $aBody start -->\n";
    processFile("$aBody");
    echo "\n<!-- aBody: $aBody finish -->\n";

    registerAPIUsageFinish();
    db_close();
  } catch(Exception $e) {
    /* @OBSOLETE 20170111
    $devMsgQueue->sendStagedMessage('exception',$e->getMessage());
    */
  }
  /* @OBSOLETE 20170111
  $devMsgQueue->sendStagedMessage('idle');
  */
  _dumpY(1,1,"End of process * $s.$a ($u) -> aBody = $aBody");
  _recordWastedTime("Good bye");
?>
