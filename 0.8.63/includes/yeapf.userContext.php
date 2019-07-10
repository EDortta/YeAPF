<?php
/*
    includes/yeapf.userContext.php
    YeAPF 0.8.63-104 built on 2019-07-10 19:52 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
    2019-05-24 09:34:55 (-3 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  /*
   * This class enforces the user context
   * After login, create a new instance of this class
   * and call createUserContext
   * At use, create an instance and call isValidUser()
   *
   * In order to achive better performance, place lock files
   * on a filesystem that handles lots of small files efficiently
   *
   * It works well on Linux and Windows servers
   */

  global $xUserContextList;
  $xUserContextList = array();

  class xUserContext
  {
    private $userContext, $u, $validUser,$logonRights;
    private $contextPath='lock';
    private $isInsecureEntry;

    function __construct($u=0, $pseudoValidUser=null)
    {
      global $yeapfConfig, $cfgMainFolder, $xUserContextList;
      $this->userContext=array();
      $this->u=$u;
      $this->validUser=$pseudoValidUser;
      /* 20190509 OBSOLETE
      if (($yeapfConfig) && ($yeapfConfig['cfgCurrentFolder']) && ($yeapfConfig['cfgCurrentFolder']>''))
        $yeapBase=$yeapfConfig['cfgCurrentFolder'].'/';
      else
        $yeapBase=getcwd();
      $this->contextPath=$yeapBase.$this->contextPath;
      */

      $this->contextPath = $cfgMainFolder.'/.messages';
      if (!is_dir($this->contextPath)) {
        $created=@mkdir($this->contextPath, 0764, true);
        if (!$created) 
          _die("Was impossible to create '".$this->contextPath."' folder");
      }

      $this->setTimeTraking();
      _dumpY(8,0,"uc:  yeapf.context ($u) = ".$this->contextPath);

      $xUserContextList[$u]=$this;
    }

    function destroy() {
      global $u;
      if (isset($u)) {
        if (isset($xUserContextList[$u]))
          unset($xUserContextList[$u]);
      }
    }

    function __destruct()
    {
      $this->destroy();
      // $this->_commit();
    }

    function fileName()
    {
      return $this->contextPath.'/user.'.$this->u;
    }

    function prepareContextToSave($userContext)
    {
      $res = array();
      foreach($userContext as $key => $val) {
        if(is_array($val)) {
          $res[] = "[$key]";
          foreach($val as $skey => $sval)
            $res[] = "$skey=".(is_numeric($sval) ? $sval : '"'.$sval.'"');
        } else
          $res[] = "$key=".(is_numeric($val) ? $val : '"'.$val.'"');
      }

      foreach ($res as $aux)
        _dumpY(8,0,$aux);
      return $res;
    }

    function _commit()
    {
      global $u;

      if (($u!='') && (!$this->isInsecureEntry)) {
        _dumpY(8,0,"uc:  _commit() user session '$u'");
        if ($this->validUser) {
          $fileName=$this->fileName();

          $res = $this->prepareContextToSave($this->userContext);

          $lockName="user.$this->u.lock";
          _dumpY(8,0,"uc:  *** LOCK $lockName as $fileName? ");
          if (lock($lockName)) {
            $f=fopen($fileName,'w');
            fwrite($f,implode("\n", $res)."\n\n");
            fclose($f);
            unlock($lockName);
          } else
            _dumpY(8,0,"ERROR uc:  *** CANNOT LOCK ($lockName)");

        } else
          _dumpY(8,0,"ERROR uc:  !validUser()");

      } else
        _dumpY(8,0,"'u' is not defined and this is not an open event");
    }

    function _deleteContextFile($fileName)
    {
      if (file_exists($fileName)) {
        _dumpY(8,0,"deleting $fileName");
        unlink($fileName);
      }
    }

    function _destroyUserContext($fileName='')
    {
      if ($fileName>'')
        $fBase=$fileName;
      else
        $fBase="$this->contextPath/user.".$this->u;

      $fStack="$fBase.msgStack";
      $fCounter="$fBase.msgCounter";
      $lFlag="$fBase.formListFlag";
      $fFlag="$fBase.msgFlag";

      $fList="$fBase.formList";

      _dumpY(8,0,"CLEANING USER CONTEXT ($fBase.*)");
      if(isset($formList)) {
        foreach($formList as $kFormList => $aForm) {
          $aFormID=getNextValue($aForm);
          $this->_deleteContextFile("$fStack.$aFormID");
          $this->_deleteContextFile("$fCounter.$aFormID");
        }
      }
      $this->_deleteContextFile("$lFlag");
      $this->_deleteContextFile("$fFlag");
      $this->_deleteContextFile("$fBase");
      $this->_deleteContextFile("$fList");
    }

    function _cleanupUserContext($userGID)
      /*
       * find the context this user used the last time it
       * was connected and free it
       */
    {

      if ($userGID>'') {
        _dumpY(8,0,"cleaning userGID: $userGID");
        if ($d=dir($this->contextPath)) {
          while ($entry=$d->read()) {
            if (substr($entry,0,5)=='user.') {
              $context=parse_ini_file("$this->contextPath/$entry",true);
              if ($context['user']['gid']==$userGID) {
                $entry=$this->contextPath.'/'.$entry;
                _dumpY(8,0,"unlink $entry :".$context['user']['gid']);
                $this->_destroyUserContext($entry);
              }
            }
          }
          $d->close();
        }
      }
    }

    function BroadcastMessage($aVarName, $aVarValue, $aMessage, $aWParam, $aLParam)
        /*
         * Post Messages on all the user's queue that has var=value
         * in it context
         */
    {

      _dumpY(8,0,"@ broadcasting $aMessage");
      $ret=0;
      if ($d=dir($this->contextPath)) {
        $userList=array();
        while ($entry=$d->read()) {
          if (substr($entry,0,5)=='user.') {
            _dumpY(8,2,"@ broadcasting $entry");
            $context=parse_ini_file("$this->contextPath/$entry",true);
            $uTarget=substr($entry,5);
            $uTarget=substr($uTarget,0,strpos($uTarget.'.','.'));
            $auxValue=trim(unquote($context['vars'][$aVarName]));
            _dumpY(8,2,"@ broadcasting $uTarget $aVarName = '$auxValue' = '$aVarValue'?");
            if (($auxValue==$aVarValue) || ($aVarValue=='*')) {
              array_push($userList, $uTarget);
              _dumpY(8,0,"[$aMessage] to $uTarget");
            }
          }
        }
        $d->close();

        $userList = array_unique($userList);

        foreach($userList as $uTarget) {
          _dumpY(8,0,"@ broadcasting to $uTarget");
          $ret+=$this->PostMessage($uTarget, $aMessage, $aWParam, $aLParam);
        }

      }
      return $ret;
    }


    function __saveFormList($fBase, $formList)
    {
      $fList="$fBase.formList";

      if (is_array($formList))
        $formList=join("\n",$formList);

      $formList=str_replace("\n\n", "\n", $formList);
      _dumpY(8,2,"formList \n$formList");
      $f=fopen($fList,'w');
      fwrite($f,$formList);
      fclose($f);

    }

    function PostMessage($aTargetUser, $aMessage, $aWParam, $aLParam)
      /*
       * Push a message into the user's stack
       *
       * for each user form stack do
       *   1) Remove dead forms and commit form list
       *   2) Check if there is enough room in message stack
       *   3) Lock the message stack
       *   4) Increment message counter
       *   5) Push the message
       *   6) Unlock the message stack
       */
    {

      $ret=0;

      _dumpY(8,0,"Message for $aTargetUser");

      $fBase="$this->contextPath/user.$aTargetUser";

      $fStack="$fBase.msgStack";
      $fCounter="$fBase.msgCounter";
      $lFlag="user.$aTargetUser.formListFlag";
      $fFlag="user.$aTargetUser.msgFlag";

      /* clean the form list removing dead forms
       * A form is dead when it hasnt been seen alive
       * for more than five (5) minutes
       */
      $fList="$fBase.formList";
      $formList = file($fList);
      $formTimeLimit = date('U') - 5 * 60;
      if (lock($lFlag,true)) {
        $formsDead=array();
        foreach($formList as $kFormList => $aForm) {
          $aFormID=getNextValue($aForm);
          $aMessagePeekerInterval=intval(getNextValue($aForm));
          $aLastTimeAlive=getNextValue($aForm);
          $aDeadTime = $formTimeLimit - $aLastTimeAlive;
          if ($aDeadTime>0) {
            array_push($formsDead, $kFormList);
            if (file_exists("$fStack.$aFormID"))
              unlink("$fStack.$aFormID");
            if (file_exists("$fCounter.$aFormID"))
              unlink("$fCounter.$aFormID");
          }

          _dumpY(8,3,"$kFormList -> $aFormID , $aMessagePeekerInterval , $aLastTimeAlive ($aDeadTime)");
        }

        foreach($formsDead as $kForm) {
          _dumpY(8,1,"Deleting $kForm from formList as it is dead");
          unset($formList[$kForm]);
        }
        $this->__saveFormList($fBase, $formList);
        unlock($lFlag);
      } else
        _dumpY(8,0,"formList BUSY!");

      $formList = file($fList);
      foreach($formList as $kFormList => $aForm) {
        $aFormID=getNextValue($aForm);

        $cc=intval(implode(file("$fCounter.$aFormID")));

        _dumpY(8,0,"stack: $fStack.$aFormID  counter: $fCounter.$aFormID  flag: $fFlag.$aFormID");
        _dumpY(8,0,"message counter: $cc");

        if ($cc<150) {
          if (lock($fFlag)) {
            _dumpY(8,1,"succefully locked");
            $cc++;
            $f=fopen("$fCounter.$aFormID",'w');
            if (flock($f,LOCK_EX)) {
              $cc++;
              fwrite($f,$cc);
              flock($f,LOCK_UN);
              fclose($f);

              $aMessage=addslashes($aMessage);

              _dumpY(8,2,"preparing to send '$aMessage'");

              $msg="$this->u;'$aMessage';$aWParam;$aLParam";

              $f=fopen("$fStack.$aFormID","a+");
              if ($f) {
                fwrite($f,"$msg\n");
                _dumpY(8,2,"sending '$msg' into '$fStack.$aFormID'");
                fclose($f);
              } else
                _dumpY(8,2,"error writing on '$fStack.$aFormID'");

              $ret=1;

            } else
              _dumpY(8,0,"PostMessage error.  Cannot lock counter file");
            unlock($fFlag);
            _dumpY(8,1,"lock released");
          } else
            _dumpY(8,0,"Impossible to lock");
        }
      }
      return $ret;
    }

    function PeekMessages()
      /*
       * PeekMessages
       * It gets the complete list of messages ready to be processed by the client
       * There is not a "PeekMessage()" (in singular) function
       */
    {

      $ret=array();

      // It only reads the message stack if and only if exists an 'formID'
      // This avoid the script to send messages to itself and not be readed later by another form
      if ($this->formID>'') {

        $fBase="$this->contextPath/user.".$this->u;

        $fStack="$fBase.msgStack.$this->formID";
        $fCounter="$fBase.msgCounter.$this->formID";
        $fFlag="user.$this->u.msgFlag";
        $lFlag="user.$aTargetUser.formListFlag";

        _dumpY(8,1,"\n\tformID: $this->formID\n\tstack: $fStack\n\tcounter: $fCounter\n\t$fFlag");

        $fList="$fBase.formList";
        if (lock($lFlag)) {
          $formList = file($fList);
          $needSaveFormList = false;
          foreach($formList as $kFormList => $aForm) {
            $aFormID=getNextValue($aForm);
            $aMessagePeekerInterval=getNextValue($aForm);
            $aLastTimeAlive=getNextValue($aForm);
            if ($aFormID==$formID) {
              $auxNow = date('U');
              $aux = "$aFormID,$aMessagePeekerInterval,$auxNow";
              _dumpY(8,3,"$aux");
              $formList[$kFormList] = $aux;
              $needSaveFormList=true;
            }
          }

          if ($needSaveFormList)
            $this->__saveFormList($fBase, $formList);
          unlock($lFlag);
        }


        $cc=intval(implode(file("$fCounter")));
        if ($cc>0) {
          if (lock($fFlag,true)) {
            $ret=file($fStack);
            _dumpY(8,0,"@ $ret");

            unlink($fCounter);
            unlink($fStack);

            unlock($fFlag);
          }
        }
      }

      return $ret;

    }


    function RegisterFormID($messagePeekerInterval)
    {

      $fBase="$this->contextPath/user.".$this->u;
      $fList="$fBase.formList";

      $lFlag="user.$this->u.formListFlag";
      if (lock($lFlag)) {
        _dumpY(8,0,"Registering form '".$this->formID."'");

        $formList = trim(join("\n",file($fList)));
        _dumpY(8,2,"formList '$formList'");
        if ($formList>'')
          $formList.="\n";
        $formList.=$this->formID.','.$messagePeekerInterval.','.date('U');

        $this->__saveFormList($fBase, $formList);

        unlock($lFlag);
      } else {
        $this->formID='';
      }
    }

    function createUserContext($userGID,
                               $logonRights=65535,
                               $tableName='',
                               $IDFieldName='')
    {
      global $sysTimeStamp,
             $usrNicknameField, $usrTableName, $usrUniqueIDField,
             $usrUniqueIDFieldType, $usrSessionIDField;

      _dumpY(8,0,"uc:  createUserContext($userGID, $logonRights, $tableName, $IDFieldName, $usrNicknameField) - $sysTimeStamp");
      $this->_cleanupUserContext($userGID);
      $this->check_usrFieldTypes();

      $lrA=intval($logonRights);
      $lrB=trim($logonRights);

      if ("$lrA"!="$lrB") {

        $this->validUser=false;
        $this->logonRights=$logonRights;

        _dump("INVALID VALUE FOR 'logonRights' PARAMETER");
        showDebugBackTrace("Invalid value for 'logonRights' parameter",true);

      } else {

        if (strpos(strtoupper(" $usrUniqueIDFieldType"),'CHAR')>0)
          $userGID="'$userGID'";

        $sql="select $usrNicknameField from $usrTableName where $usrUniqueIDField=$userGID";
        _dumpY(8,0,$sql);

        $this->userContext=array();
        $this->userContext['user']['gid']=$userGID;
        $this->userContext['user']['tableName']=$tableName>''?$tableName:$usrTableName;
        $this->userContext['user']['IDFieldName']=$IDFieldName>''?$IDFieldName:$usrSessionIDField;
        $this->userContext['user']['logon']=$sysTimeStamp;
        $this->userContext['user']['logonRights']=$logonRights;
        $this->userContext['user']['remote_addr']=getRemoteIp();
        $this->userContext['user']['user_name']=db_sql($sql);

        $this->validUser=true;
        $this->logonRights=$logonRights;

      }

      $this->_commit();
    }

    function check_usrFieldTypes()
    {
      global $usrTableName,
             $usrSessionIDFieldType, $usrSessionIDField,
             $usrUniqueIDFieldType, $usrUniqueIDField,
             $usrPasswordAlgorithm;

      if ("$usrSessionIDFieldType"=='') {
        if (isset($this->userContext['user']))
          $usrSessionIDFieldType=$this->userContext['user']['usrSessionIDFieldType'];
        if ($usrSessionIDFieldType=='') {
          $usrSessionIDFieldType=db_fieldType($usrTableName,$usrSessionIDField);
          $this->userContext['user']['usrSessionIDFieldType']=$usrSessionIDFieldType;
        }
      }
      if ("$usrUniqueIDFieldType"=='') {
        $usrUniqueIDFieldType=isset($this->userContext['user']['usrUniqueIDFieldType'])?$this->userContext['user']['usrUniqueIDFieldType']:'';
        if ($usrUniqueIDFieldType=='') {
          $usrUniqueIDFieldType=db_fieldType($usrTableName,$usrUniqueIDField);
          $this->userContext['user']['usrUniqueIDFieldType']=$usrUniqueIDFieldType;
        }
      }

      if ($usrPasswordAlgorithm=='')
        $usrPasswordAlgorithm='md5';

      _dumpY(8,0,"usrSessionIDFieldType = '$usrSessionIDField' '$usrSessionIDFieldType'");
      _dumpY(8,0,"usrUniqueIDFieldType = '$usrUniqueIDField' '$usrUniqueIDFieldType'");
    }

    /*
     * createNewUser()
     * Creates a new user into the security database
     * The security database is defined by usr* global variables
     * The default logon rights are '2'
     *
     * Return -1 if the nickName already exists,
     *         0 if it was not possible to add a new entry and
     *         1 if the entry was succefully added
     */
    function createNewUser($nickName, $eMail, $password, $rights=2)
    {
      global $usrTableName,
             $usrSessionIDFieldType, $usrSessionIDField,
             $usrUniqueIDFieldType, $usrUniqueIDField,
             $usrEMail, $usrRightsField, $usrNicknameField, $usrPassword,
             $usrPasswordAlgorithm;


      $this->check_usrFieldTypes();

      $stage=0;
      try {
        $cc=db_sql("select count(*) from $usrTableName where $usrNicknameField='$nickName'");
        $stage++;
        if ($cc==0) {

          switch (strtoupper($usrUniqueIDFieldType))
          {
            case 'INTEGER':
              $guid=intval(db_sql("select max($usrSessionIDField) from $usrTableName"));
              $guid++;
              break;
            case 'CHAR':
            case 'VARCHAR':
            case 'CHARACTER VARYING':
              if (function_exists($usrPasswordAlgorithm))
                $guid=$usrPasswordAlgorithm(md5('USER_SECURITY'.y_uniqid()));
              else {
                _recordWastedTime("Throwing an exception "+__FILE__+":"+__LINE__);
                throw new Exception("userPasswordAlgorithm '$usrPasswordAlgorithm' is not a recognized function");
              }
              break;
            default: {
              _recordWastedTime("Throwing an exception "+__FILE__+":"+__LINE__);
              throw new Exception("$usrTableName.$usrSessionIDField is of type '$usrSessionIDFieldType' which is not usable by userContext");
            }
          }
          $stage++;

          $userData=array();
          $userData[$usrUniqueIDField]=$guid;
          $userData[$usrPassword]=$usrPasswordAlgorithm($password);
          $userData[$usrEMail]=$eMail;
          $userData[$usrRightsField]=$rights;
          $userData[$usrNicknameField]=$nickName;

          // die("plain password: '$password'<br>\n".var_dump($userData));

          $fieldList='';
          $fieldValue='';
          foreach($userData as $k=>$v) {
            if ($k>'') {
              if (db_fieldExists($usrTableName, $k)) {
                $fieldType=strtoupper(db_fieldType($usrTableName,$k));
                if (strpos("*$fieldType",'CHAR')>0)
                  $v="'$v'";
                if ($fieldList>'') {
                  $fieldList.=', ';
                  $fieldValue.=', ';
                }
                $fieldList.=$k;
                $fieldValue.=$v;
              } else
                throw new Exception("$k does not exists in table $usrTableName");
            }
          }

          $stage++;

          $sql="insert into $usrTableName ($fieldList) values ($fieldValue)";
          // die($password."<br>".$sql);
          db_sql($sql);
          $stage++;
        }
      }
      catch(Exception $e)
      {
        $aux="ERROR AT USER CREATION (stage: $stage) ".$e->getMessage();
        _dumpY(8,0,$aux);
        die($aux);
      }
    }

    function userLogon($nickName, $password, &$rights)
    {
      global $usrTableName, $usrSessionIDField, $usrUniqueIDField, $usrEMail,
             $usrRightsField, $usrNicknameField, $usrPassword, $usrPasswordAlgorithm,
             $usrLastAccess, $u, $usrSessionIDFieldType, $usrUniqueIDFieldType;

      $GID='';

      $this->check_usrFieldTypes();
      $reqFields=array("usrUniqueIDField", "usrPassword", "usrRightsField",
                       "usrTableName", "usrNicknameField");
      foreach($reqFields as $rf) {
        $auxRF=isset($$rf)?$$rf:'';
        _dumpY(8,4,"Required Field $rf = '$auxRF'");
        if ((!isset($$rf)) || ($$rf=='')) {
          $err="Required fiels:<br>";
          foreach($reqFields as $rf1)
            $err.="&nbsp;&nbsp;$rf1<br>";
          _die("$err<hr>'$rf' not defined in '".$GLOBALS['dbCSVFilename']."'");
        }
      }

      if ($usrPassword=='') {
        _dumpY(8,0,"INSECURE LOGIN.  Global usrPassword is not setted");
        $usrPassword="$usrNicknameField";
      }

      if ($usrRightsField=='') {
        _dumpY(8,0,"INSECURE LOGIN. Global usrRightField is not defined");
        $usrRightsField='null';
      }

      $uInfo=db_sql("select $usrUniqueIDField, $usrPassword, $usrRightsField
                     from $usrTableName
                     where $usrNicknameField='$nickName' and $usrPassword>''");

      $savedPassword=$uInfo[1];

      if ($savedPassword>'') {
        _dumpY(8,4,"There is a password.  Algorithm: '$usrPasswordAlgorithm'. Try: '$password'");
        $cPassword=$usrPasswordAlgorithm($password);

        _dumpY(8,4,"$cPassword == $savedPassword ?");

        if ($cPassword==$savedPassword) {
          $GID=$uInfo[0];
          $userRights=$uInfo[2];

          if (strpos(strtoupper(" $usrUniqueIDFieldType"),'CHAR')>0)
            $GID="'$GID'";

          // create 'u' session identifier
          switch (strtoupper($usrSessionIDFieldType))
          {
            case 'SMALLINT':
            case 'INTEGER':
              $u=intval(db_sql("select max($usrSessionIDField) from $usrTableName"));
              $u++;
              break;
            case 'CHAR':
            case 'VARCHAR':
            case 'CHARACTER VARYING':
              do {
                $u=md5('userContextID'.y_uniqid());
                $cc=db_sql("select count(*) from $usrTableName where $usrSessionIDField='$u'");
              } while ($cc>=1);
              $u="'$u'";
              break;
          }

          $lastAccess=date("YmdHi");

          if (lock('usrLastAccess')) {

            db_sql("update $usrTableName
                    set $usrSessionIDField=$u, $usrLastAccess='$lastAccess'
                    where $usrUniqueIDField=$GID");
            unlock('usrLastAccess');
          }

          $rights = $rights & $userRights;

          $GID=unquote($GID);
          $u=unquote($u);


          $this->u=$u;
          $this->createUserContext($GID, $rights, $usrTableName, $usrSessionIDField);
          /*
          if (!$this->isValidUser($rights)) {
            $this->logoff();
            $u=0;
            $GID='';
          }
          */
        } else
          _recordError("Password or User name wrong");
      } else {
        _recordError("User name or password wrong");
        _dumpY(8,4,"NO PASSWORD SAVED");
      }
      return $GID;
    }

    function enoughRights($requiredRights=0)
    {
      global $usrRightsField, $usrTableName, $usrSessionIDField;

      $ret=false;
      $sql="select $usrRightsField from $usrTableName where $usrSessionIDField='".$this->u."'";
      $usrRights=db_sql($sql);
      $ret=($usrRights & $requiredRights)>0;
      return $ret;
    }

    function userGID()
    {
      return $this->userContext['user']['gid'];
    }

    function setTimeTraking($aTracking=true)
    {
      $this->timeTracking=$aTracking;
    }

    function isValidUser($appFolderRights=-1, $myAppInsecureEvents='')
      /*
       * Check if the user is a valid one
       * This is the first function called after script initialization
       * and before load the user context variables
       */
    {
      global $sysTimeStamp, $s, $a, $u, $sua,
             $usrTableName, $usrSessionIDField, $usrLastAccess, $usrSessionIDFieldType,
             $developmentStage, $appFolderInsecureEvents, $cfgMainFolder;

      if (!isset($this->validUser)) {
        $ret=false;
        $fileName=$this->fileName();
        $this->isInsecureEntry=false;

        $auxInsecureEvents = $appFolderInsecureEvents.','.$myAppInsecureEvents;

        if (($auxInsecureEvents>'') || ($sua>'') || (file_exists("$cfgMainFolder/flags/flag.develop"))) {
          /*
           * ARQUIVO DE CONFIG: appFolderName.def
           * A terceira linha contém uma lista de "sujeito.acao" separados por ','
           * que passam por cima da verificação de segurança.
           * Além de esses eventos terem que estar ali declarados,
           * deve existir uma variável global 'sua' (super user authentication)
           * que é o md5(s.a)... a liberação só é possível se o evento for
           * declarado como inseguro, existir 'sua' compatível com o evento
           * e NÃO existir 'u'
           * A exceção é ter a constante '3db6003ce6c1725a9edb9d0e99a9ac3d'
           * definida na listal já que '3db6003ce6c1725a9edb9d0e99a9ac3d' é
           * md5('*.') e permite a entrada de todos os eventos sem segurança.
           * 2012-09-25 - Aos efeitos de simplificar o desenvolvimento, os
           * eventos indicados no appFolderDef podem estar sem codificação,
           * mas nesse caso a bandeira de desenvolvimento (development.debug)
           * deve estar ligada mesmo que com valor '0'
           * 2013-02-26 - Quando um evento está marcado como inseguro, ele
           * deve ter garantido o acesso aos eventos do yeapf.  Isto é, se
           * um evento permitido é provocado para o index ou o body, deve
           * existir uma forma de liberar os eventos 'yeapf' como se de um
           * logon válido se tratasse.  Isso é assim para poder usar o
           * 'dataset' por exemplo e outros que de outra forma acabam sendo
           * bloqueados.
           * FALTA IMPLEMENTAR UMA SOLUÇÃO.  Simplesmente liberar 'yeapf'
           * não é bom pois abre as pernas para todo o sistema.
           * 2013-12-28 - Os eventos 'yeapf:develop.'
           * ('bd7b5ca48f3bfda678c90d7945910ecf') devem ser liberados
           * quando a bandeira '$cfgMainFolder/flags/flag.develop' está ligada.  Caso
           * queira liberar sempre, tem que estar anotado no arquivo
           * appFolderName.def
          */
          $sa="$s.$a";
          $mySua=md5("$s.$a");
          $mySuaJoker=md5("$s.");
          _dumpY(8,0,"$s.$a = $mySua ($sua)");
          if ($sua==$mySua)
            $ret="$u"=='';
          if (file_exists("$cfgMainFolder/flags/flag.develop"))
            $ret=(($ret) || ($mySuaJoker=='bd7b5ca48f3bfda678c90d7945910ecf'));
          if (!$ret) {
            $aux = explode(',', $auxInsecureEvents);
            foreach($aux as $fie) {
              _dumpY(8,1,"insecure event declaration: '$fie' ".isset($developmentStage));
              if (($fie=='3db6003ce6c1725a9edb9d0e99a9ac3d') ||
                  (isset($developmentStage) && ((md5($fie)==$mySuaJoker) || (md5($fie)==$mySua))) ||
                  ($fie==$mySua) ||
                  ($fie==$mySuaJoker)) {
                $ret=true;
              }
            }
          }

          $this->isInsecureEntry=$ret;
        }

        _dumpY(8,0,"user file: '$fileName' insecureEvent: $ret");
        if (file_exists($fileName)) {
          $this->userContext=parse_ini_file($fileName,true);
          // die(var_dump($this->userContext));
          foreach($this->userContext['user'] as $k => $v)
            _dumpY(8,2,"userContext $k = $v");

          if (isset($this->userContext['vars']))
            foreach($this->userContext['vars'] as $k => $v)
              _dumpY(8,2,"userContext $k = $v");

        }

        if (!$ret) {
          $ret=true;

          // time difference in seconds between current time and last click
          if ((isset($this->userContext['user'])) && (intval($this->userContext['user']['lastAccess'])>0))
            $difTime = $sysTimeStamp - $this->userContext['user']['lastAccess'];
          else
            $difTime=0;

          // usually you don't want to register time acces when doing queries or websocket
          if (($difTime<30) || (!$this->timeTracking))
            $ret=true;
          else {
            $ret=false;

            $this->check_usrFieldTypes();

            switch (strtoupper($usrSessionIDFieldType))
            {
              case 'SMALLINT':
              case 'INTEGER':
                $qu=intval($u);
                break;
              case 'CHAR':
              case 'VARCHAR':
              case 'CHARACTER VARYING':
                $qu="'$u'";
                break;
            }

            $cc=db_sql("select count(*) from $usrTableName where $usrSessionIDField=$qu");
            if ($cc>0) {
              if ($difTime>6000000) {
                _recordError("Usuário desconectado por inatividade ($difTime segundos)");
              }
              else {
                $ret=true;
                if (lock('usrLastAccess',true)) {
                  db_sql("update $usrTableName
                          set $usrLastAccess='$sysTimeStamp'
                          where $usrSessionIDField=$qu");
                  unlock('usrLastAccess');
                }
              }
            } else
               _recordError("Usuário conectado em outra sessão");
          }

          _dumpY(8,0,"difTime=$difTime");

          if ($ret) {
            if ($this->timeTracking)
              $this->userContext['user']['lastAccess']=$sysTimeStamp;

            $binAnd = $this->userContext['user']['logonRights'] & $appFolderRights;

            _dumpY(8,0,"@ ".$this->userContext['user']['logonRights']. ' (logonRights) & '.$appFolderRights.' (appFolderRights) = '.$binAnd);

            $ret=($binAnd>0);

            if ($appFolderRights==0)
              _recordError("Pasta bloqueada (appFolderName.def)");

            if (!$ret)
              _recordError("Insufficient rights ( ".$this->userContext['user']['logonRights']." ) to access this application ( $appFolderRights ) with '$s.$a' event ( $mySuaJoker )");
          }

        }
        // die("$fileName [$appFolderInsecureEvents] $ret");

        _dumpY(8,0,"@uc:  isValidUser? $fileName = ".intval($ret));

        $this->validUser=$ret;

        if (($ret) && (!$this->isInsecureEntry))
          $this->_commit();

        return $ret;
      } else
        return $this->validUser;
    }

    function logoff()
    {
      _dumpY(8,0,"LOGOFF");
      $this->validUser=false;
      $this->_destroyUserContext();
    }

    function loadUserVars($varNames='*', $asGlobals=true)
    {
      $ret=array();
      _recordWastedTime("YeAPF loading user context");
      if ($this->validUser) {
        _dumpY(8,0,"loading $varNames");

        if (!is_array($varNames)) {
          if (strpos($varNames,',')>0)
            $varNames=explode(',',$varNames);
          else
            $varNames=explode(';',$varNames);
        }

        $cc=isset($this->userContext['vars'])?count($this->userContext['vars']):0;
        _dumpY(8,1,"$cc entries");

        if (isset($this->userContext['vars'])) {
          foreach($this->userContext['vars'] as $key => $value) {
            _dumpY(8,2,"\t$key = $value");
            if (($varNames[0]=='*') || (in_array($key, $varNames))) {
              if (!is_numeric($key)) {
                _dumpY(8,0,"uc:  loading user var '$key' as '$value'");
                $ret["$key"]=$value;
                if ($asGlobals)
                  $GLOBALS["$key"]=$value;
              }
            } else {
              _dumpY(8,0,"uc: not using var '$key' as '$value'");
            }
          }
        }
      } else {
        _dumpY(8,0,"INVALID USER");
      }
      _dumpY(8,0,"userVars ready");
      return $ret;
    }

    function addUserValue($varName, $value)
    {

      _dumpY(8,0,"uc:  saving user var '$varName' as '$value' on $this->u");
      $this->userContext['vars'][$varName]=$value;

      $this->_commit();
    }

    function addUserVars($varNames)
    {
      if (!is_array($varNames)) {
        if (strpos($varNames,',')>0)
          $varNames=explode(',',$varNames);
        else
          $varNames=explode(';',$varNames);
      }
      foreach($varNames as $key) {
        _dumpY(8,0,"uc:  saving user var '$key' as '$GLOBALS[$key]' on $this->u");
        $this->userContext['vars'][$key]=$GLOBALS[$key];
      }
      $this->_commit();
    }

    function delUserVars($varNames)
    {
      if (!is_array($varNames))
        $varNames=explode(';',$varNames);
      foreach($varNames as $key)
        if (isset($this->userContext['vars'][$key]))
          unset($this->userContext['vars'][$key]);
    }

    function setGlobalsVar($varName, $varValue)
    {
      /*
       * set a variable to all the users
       */
      if (lock('globalVarSet')) {
        _dumpY(8,0,"uc:  *** setGlobalsVar($varName, $varValue)");
        if ($d=dir($this->contextPath)) {
          while ($entry=$d->read()) {
            if (substr($entry,0,5)=='user.') {
              $fileName="$this->contextPath/$entry";
              $context=parse_ini_file($fileName,true);
              $context['vars'][$varName]=$varValue;
              $res=$this->prepareContextToSave($context);

              $auxU=$entry;
              getNextValue($auxU,'.');
              $auxU=getNextValue($auxU,'.');
              $lockName="user.$auxU.lock";
              _dumpY(8,0,"uc:  *** LOCK $lockName for $fileName");
              if (lock($lockName,true)) {
                $f=fopen($fileName,'w');
                fwrite($f,implode("\n", $res));
                fclose($f);
                unlock($lockName);
              }

            }
          }
          $d->close();
        }
        unlock('globalVarSet');
      }
    }

  }

  /*
  as there are some functions that relies on $userContext
  and we're introducing multi-user backend with webSocket server,
  this function helps switching the userContext to the indicated
  */
  function switchUserContext($u) {
    global $xUserContextList, $userContext;

    $userContext=$xUserContextList[$u];
  }
?>
