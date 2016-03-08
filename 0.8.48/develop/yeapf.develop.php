<?php
/*
    develop/yeapf.develop.php
    YeAPF 0.8.48-1 built on 2016-03-07 13:46 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2014-08-12 16:55:06 (-3 DST)
*/

  function binarySlice($fieldBasename)
  {
    $aValue=$GLOBALS[$fieldBasename];
    for($b=0; $b<8; $b++)
      $GLOBALS[$fieldBasename.'_'.$b]=(pow(2,$b) & $aValue)>0?1:0;
  }

  function binaryJoin($fieldBasename)
  {
    $aValue=0;
    for($b=0; $b<8; $b++) {
      $aValue+=intval($GLOBALS[$fieldBasename.'_'.$b]) * pow(2,$b);
    }
    $GLOBALS[$fieldBasename]=$aValue;
  }

  class xDevelopMSG {

    private $devSession;
    private $devSessionFilename;
    private $devSessionFile;
    private $onDisk;
    private $ready;
    private $devSession_shmID;

    function __construct(&$aNewDevSession, $onDisk=false) {

      _dumpY(256,0,"Open develop session '$aNewDevSession'");

      if ((!isset($this->devSession_shmID)) || (!$this->devSession_shmID)) {
        $this->ready=false;

        $uniqueSession=md5(uniqid());

        if (lock($uniqueSession)) {
          try {
            if (!function_exists("shmop_open"))
              $onDisk=true;

            if ($aNewDevSession=='') {
              $aNewDevSession=md5(uniqid());
              $this->devSession=$aNewDevSession;
            }


            $this->devSessionFilename=".developSession/$aNewDevSession";
            _dumpY(256,0,"new instance of $this->devSessionFilename");
            if (!file_exists($this->devSessionFilename)) {
              touch($this->devSessionFilename);
              chmod($this->devSessionFilename, 0777);
              $creating=true;
              $flags='c';
            } else {
              $creating=false;
              $flags='w';
            }

            if (!$onDisk) {
              $devSessionFTOK=ftok($this->devSessionFilename, 'R');
              if (is_int($devSessionFTOK)) {
                $this->devSession_shmID=shmop_open($devSessionFTOK, $flags, 0666, 512);
                if (!($this->devSession_shmID))                
                  $onDisk=true;
              } else
                $onDisk=true;
            }

            if ($onDisk)
              $this->devSession_shmID=false;

            if (!$this->devSession_shmID) {
              if (isset($devSessionFTOK))
                _dumpY(256,3,"ftok() = $devSessionFTOK | flags=$flags");
              else
                _dumpY(256,3,"flags=$flags");
              _dumpY(256,3,"Develop Session shared memory cannot be opened ( '$this->devSessionFilename' )");
              _dumpY(256,0,"Using onDisk implementation");
              $this->onDisk=true;
              if ($this->devSessionFile=fopen($this->devSessionFilename,'r+'))
                $this->ready=true;
              else
                $this->ready=false;
            } else {
              $this->ready=true;
              $this->onDisk=false;
            }
            if ($creating) {
              _dumpY(256,0,"Initializing");
              $this->initialize();
            }

            unlock($uniqueSession);
          } catch(Exception $e) {
            _dumpY(256,0,"Exception: ".$e->getMessage());
            unlock($uniqueSession);
          }
        }
      }
    }

    function initialize() {
      $binVirginMap=pack('N',4);
      if ($this->onDisk) {
        fseek($this->devSessionFile, 0);
        fwrite($this->devSessionFile, $binVirginMap, 4);
      } else
        shmop_write($this->devSession_shmID, $binVirginMap, 0);
    }

    function getFreePosition() {
      if ($this->onDisk) {
        fseek($this->devSessionFile, 0);
        $binMap=fread($this->devSessionFile, 4);
      } else
        $binMap = shmop_read($this->devSession_shmID, 0 , 4 );

      $freePosition=unpack('N',$binMap);
      $freePosition=join($freePosition);
      return $freePosition+0;
    }

    function setNextFreePosition($newFreePos) {
      $binMap=pack('N',$newFreePos);
      _dumpY(256,5,"New free position: $newFreePos");
      if ($this->onDisk) {
        fseek($this->devSessionFile, 0);
        fwrite($this->devSessionFile, $binMap, 4);
      } else
        shmop_write($this->devSession_shmID, $binMap, 0);
    }

    function sendMessage($aTxtMessage) {
      if ($this->ready) {
        if (lock($this->devSession)) {
          try {
            $pos=$this->getFreePosition();
            $aTxtMessage=trim($aTxtMessage)."\0";
            $msgLen=intval(strlen($aTxtMessage));
            $binMsgLen=pack('N',$msgLen);
            // write message length
            _dumpY(256,5,"writing msgLen at $pos");
            if ($this->onDisk) {
              fseek($this->devSessionFile, $pos);
              fwrite($this->devSessionFile, $binMsgLen, 4);
            } else
              shmop_write($this->devSession_shmID, $binMsgLen, $pos);
            $pos+=4;
            _dumpY(256,3,"writing $msgLen bytes at $pos [ ".substr($aTxtMessage,0,intval($msgLen)-1)." ]");
            // write message text
            if ($this->onDisk)
              fwrite($this->devSessionFile, $aTxtMessage, $msgLen);
            else
              shmop_write($this->devSession_shmID, $aTxtMessage, $pos);
            $this->setNextFreePosition($pos+$msgLen);

            unlock($this->devSession);
          } catch(Exception $e) {
            unlock($this->devSession);
          }
        }
      } else {
        _dumpY(256,0,"Develop Session not opened");
        return false;
      }
    }

    function sendStagedMessage($message='', $messageDetail='') {
      global $s, $a, $currentYeapfStage;

      $msg=json_encode(array('s'=>$s, 'a'=>$a,
                             'stage'=>$currentYeapfStage,
                             'message'=>$message,
                             'messageDetail'=>$messageDetail));
      $this->sendMessage($msg);
    }

    function getMessages() {
      $ret=array();
      if ($this->ready) {
        if (lock($this->devSession)) {
          try {
            $freePos=$this->getFreePosition()+0;
            _dumpY(256,5,"Next free position: $freePos");

            $msgOff=4;
            if ($this->onDisk)
              fseek($this->devSessionFile, $msgOff);

            while ($msgOff+4<$freePos) {
              _dumpY(256,5,"Recovering msgLen from $msgOff");
              if ($this->onDisk)
                $binMsgLen=fread($this->devSessionFile, 4);
              else
                $binMsgLen=shmop_read($this->devSession_shmID, $msgOff, 4);
              $msgLen=unpack('N', $binMsgLen);
              $msgLen=join($msgLen);
              _dumpY(256,5,"Len: $msgLen");
              $msgOff+=4;
              _dumpY(256,5,"Recovering msg from pos $msgOff");
              if ($this->onDisk)
                $aTxtMessage=fread($this->devSessionFile, $msgLen);
              else
                $aTxtMessage=shmop_read($this->devSession_shmID, $msgOff, $msgLen);
              $aTxtMessage=substr($aTxtMessage,0,$msgLen-1);
              _dumpY(256,3,"Msg: $aTxtMessage at pos $msgOff");
              $ret[]=$aTxtMessage;
              $msgOff+=$msgLen;
            }
            _dumpY(256,5,"GetMessage OK");
            $this->initialize();
            unlock($this->devSession);
          } catch(Exception $e) {
            unlock($this->devSession);
          }
        }
      }
      return $ret;

    }
  }

  // read yDebugEvents explanation forward
  function qyd_dbMenu($a) {
    global $userContext, $sysDate, $u, $dbConnect,
           $fieldValue, $fieldName,
           $userMsg,
           $sgugIni;

    $useColNames = true;
    $countLimit=20;
    $ret='';

    $dbConnect='yes';
    db_startup();

    $context = xq_extractValuesFromQuery();
    extract($context);
    if ((!isset($ancestor)) || ($ancestor==''))
      $ancestor=$GLOBALS['yMenuRoot'];


    switch($a)
    {
      case 'getMenuRoot':
        $ret=array('menuRoot' => $ancestor);
        break;

      case 'getList':
        $aux=db_queryAndFillArray("select id, enabled, s, a, label, explanation
                                   from is_menu
                                   where ancestor=$ancestor
                                   order by o");
        $ret=array();
        foreach($aux as $k=>$v) {
          $id=$v['id'];
          $myS=$v['s'];
          $myA=$v['a'];
          $label=$v['label'];
          $enabled=strtoupper($v['enabled'])!='N';
          if (!$enabled)
            $label="<em>$label</em>";

          $implemented=implementation($myS, $myA, 'f', true);
          if (!$implemented)
            $wrench="<a href='javascript:ydbMenuCreateEvent(\"$myS\",\"$myA\")'><i class='step fi-wrench' style='color:#FFA500'></i></a>";
          else
            $wrench="<a href='javascript:ydbMenuLaunch(\"$myS\",\"$myA\")'><i class='step fi-play' style='color:#4A8BC2'></i></a>";

          $label="<span><a href='javascript:ydbMenuEditMenuEntry({id: \"$id\"})'><i class='step fi-page-edit' style='color:#D9D9D9'></i></a>&nbsp;$label&nbsp;$wrench</span>";
          $ret[]=array('ID'          => $v['id'],
                       'label'       => maskHTML($label),
                       'description' => maskHTML($v['explanation']),
                       'href'        => "javascript:ydbMenuViewEntry({ s:'$myS', ancestor: '$id'})",
                       'isDefault'   => 0 );
        }
        break;

      case 'getForm':
        $sql="select * from is_menu where id='$id'";
        publishSQL($sql, false, 'X');
        binarySlice('Xrights');
        binarySlice('Xapp');
        /* this continues as in newItem event */
      case 'newItem':

        /*
         * we cannot use .form file as it will be translated
         * and we are working with self done sql */
        $formName=dirname(__FILE__).'/dbMenu/f_dbMenuEntry.html';
        if (file_exists($formName))
          $formContent=_file($formName);
        else
          $formContent="File '<b>$formName</b>' not found";
        $ret['html']=maskHTML($formContent);
        break;

      case 'deleteItem':
        if ($deleteChildrens=='Y') {
          function eliminarItemMenu($id) {
            $cc=db_sql("select count(*) from is_menu where ancestor=$id");
            if ($cc>0) {
              $q=db_query("select id from is_menu where ancestor=$id");
              while ($d =  db_fetch_row($q)) {
                $aID = $d[0];
                eliminarItemMenu($aID);
              }
            }
            db_sql("delete from is_menu where id='$id'");
          }
          eliminarItemMenu($XID);
        } else {
          $thisAncestor = db_sql("select ancestor from is_menu where id='$XID'");
          db_sql("delete from is_menu where id='$XID'");
          db_sql("update is_menu set ancestor=$thisAncestor where ancestor='$XID'");
        }
        break;

      case 'saveForm':
        foreach($context as $k => $v) {
          $GLOBALS[$k] = $v;
        }
        binaryJoin('Xrights');
        binaryJoin('Xapp');
        $Xrights=$GLOBALS['Xrights'];
        $Xapp=$GLOBALS['Xapp'];
        // http://localhost/nuvemFiscalEletronica/query.php?s=yeapf:develop&a=dbMenu:saveForm&u=&fieldName=(Xlabel,Xs,Xa,Xexplanation,XID,Xenabled,Xancestor,Xattr_0,Xattr_1,Xattr_2,Xattr_3,Xattr_4,Xattr_5,Xattr_6,Xattr_7,Xrights_0,Xrights_1,Xrights_2,Xrights_3,Xrights_4,Xrights_5,Xrights_6,Xrights_7,Ximplementation,Xapp,,undefined)&fieldValue=(Menu%20Superior,menuSuperior,,,2101,0,2100,,,,,,,,,,,on,,,,on,,,65535,Salvar,undefined)&ts=1395262205930

        $cc=db_sql("select count(*) from is_menu where id='$XID'");
        if ($cc==1)
          $sql="update is_menu set label='$Xlabel', s='$Xs', a='$Xa', explanation='$Xexplanation', ID='$XID', enabled='$Xenabled', ancestor='$Xancestor', implementation='$Ximplementation', app='$Xapp', rights='$Xrights' where ID=$XID";
        else
          $sql="insert into is_menu (label, s, a, explanation, ID, enabled, ancestor, implementation, app, rights) values ('$Xlabel', '$Xs', '$Xa', '$Xexplanation', '$XID', '$Xenabled', '$Xancestor', '$Ximplementation', '$Xapp', '$Xrights')";

        $ret['sql']=$sql;
        db_sql($sql);
        break;
    }
    xq_produceReturnLines($ret, $useColNames, $countLimit);
  }

  // read yDebugEvents explanation forward
  function qyd_dbTables($a) {
    global $userContext, $sysDate, $u, $dbConnect,
           $fieldValue, $fieldName,
           $userMsg,
           $sgugIni;

    $useColNames = true;
    $countLimit=20;
    $ret='';

    $dbConnect='yes';
    db_startup();

    extract(xq_extractValuesFromQuery());

    switch($a)
    {
      case 'getList':
        $ret=array();
        $aux=db_tableList();
        foreach($aux as $k=>$tableName) {
          $ret[]=array('ID'          => $k,
                       'label'       => $tableName,
                       'description' => '',
                       'href'        => "javascript:ydbTablesViewEntry('$tableName')",
                       'isDefault'   => 0 );
        }
        break;
      case 'new':
        break;
      case 'edit':
        break;
      case 'delete':
        break;
    }

    xq_produceReturnLines($ret, $useColNames, $countLimit);

  }

  function getBetterValue($key, $defaultValue)
  {
    $ret=$defaultValue;
    if ((isset($GLOBALS[$key])) && ($GLOBALS[$key]>''))
      $ret=$GLOBALS[$key];
    return $ret;
  }

  // read yDebugEvents explanation forward
  function qyd_dbConn($a) {
    global $userContext, $sysDate, $u,
           $fieldValue, $fieldName,
           $userMsg,
           $sgugIni,
           $dbTEXT_NO_ERROR, $dbTEXT_EOF, $dbTEXT_BOF;;

    $useColNames = true;
    $countLimit=20;
    $ret='';

    extract(xq_extractValuesFromQuery());

    switch($a)
    {
      case 'getList':
        $ret=array();
        if ($setupIni=createDBText($sgugIni)) {
          $setupIni->goTop();
          while (!($setupIni->eof())) {
            $aux=array();
            $setupIni->getValues($aux);
            $ret[]=array('ID'          => $aux['appRegistry'],
                         'label'       => $aux['appTitle'],
                         'description' => $aux['dbName'].';'.
                                          $aux['dbServer'],
                         'href'        => 'javascript:ydbConnViewEntry({ id:"'.$aux['appRegistry'].'"})',
                         'isDefault'   => $aux['active'] );
            $setupIni->skip();
          }
        } else
          _recordError("Was not possible to read $sgugIni");
        break;

      case 'getForm':
        if ($setupIni=createDBText($sgugIni)) {
          $setupIni->goTop();
          if ($setupIni->locate('appRegistry', $id)==$dbTEXT_NO_ERROR) {
            // default values for non-common parameters
            $GLOBALS['XdbConnect']='yes';
            $GLOBALS['XdbOnline']='07:00-20:40, 22:30-23:59';

            $GLOBALS['XdbCharset']='ISO-8859-1';
            $GLOBALS['XappCharset']='UTF-8';

            $GLOBALS['XyMenuRoot']='-1';
            $GLOBALS['XyMenuAttr']='1';
            $GLOBALS['XaDebugIP']='127.0.0.1';

            $GLOBALS['XmessagePeekerInterval']='750';

            $GLOBALS['XdontUpdate']='1';

            // default values owned by yeapf low-level

            $GLOBALS['XusrTableName']         = getBetterValue('usrTableName',         'is_usuarios');
            $GLOBALS['XusrEMail']             = getBetterValue('usrEMail',             'email');
            $GLOBALS['XusrRightsField']       = getBetterValue('usrRightsField',       'userRights');
            $GLOBALS['XusrSessionIDField']    = getBetterValue('usrSessionIDField',    'userID');
            $GLOBALS['XusrSuperField']        = getBetterValue('usrSuperField',        'super');
            $GLOBALS['XusrNicknameField']     = getBetterValue('usrNicknameField',     'apelido');
            $GLOBALS['XusrPassword']          = getBetterValue('usrPassword',          'senha');
            $GLOBALS['XusrRightsField']       = getBetterValue('usrRightsField',       'userRights');
            $GLOBALS['XusrPasswordAlgorithm'] = getBetterValue('usrPasswordAlgorithm', 'md5');
            $GLOBALS['XusrLastAccess']        = getBetterValue('usrLastAccess',        'lastAccess');
            $GLOBALS['XusrUniqueIDField']     = getBetterValue('usrUniqueIDField',     'id');
            $GLOBALS['XusrIPField']           = getBetterValue('usrIPField',           'lastIP');
            $GLOBALS['XyMenuAttr']            = getBetterValue('yMenuAttr',            '2');

            foreach($setupIni->fields() as $k) {
              $nv="X$k";
              $v=$setupIni->getValue($k);
              $GLOBALS[$nv]=$v;
            }

          }

          $formName=dirname(__FILE__).'/dbConn/f_dbConn.html';
          if (file_exists($formName))
            $formContent=_file($formName);
          else
            $formContent="File '<b>$formName</b>' not found";
          $ret['html']=maskHTML($formContent);
        }
        break;

      case 'saveForm':
      /*
http://localhost/testes/testar-develop/query.php?s=yeapf:develop
&a=dbConn:saveForm
&u=
&fieldName=(XappRegistry,Xactive,XautoDocumentation,XdontWorkUntil,XfreeIP,XpauseCause,XphpErrorDebug,XSQLdebugLevel,XshowSQLcommands,XdebugNotifierSevice,XappName,XappTitle,XappSlogan,XlogonForm,XinitialVerb,Xunique_id_seed,XyMenuRoot,XyMenuAttr,XappCharset,XdbCharset,XbannerHeight,XdbConnect,XphoneticSearch,XdbOnline,XdbServer,XdbName,XdbType,XdbUser,XdbPassword,XusrTableName,XusrUniqueIDField,XusrSessionIDField,XusrSuperField,XusrNicknameField,XusrPassword,XusrPasswordAlgorithm,XusrIPField,XusrRightsField,XusrLastAccess,XbinDistributionFolder,XupdateSource,XsystemUpdateSource,oldAppRegistry,,undefined)
&fieldValue=(asg3q8e30gqegr59529c592aeac95f53,1,,,127.0.0.1,,0,0,0,,Sa%C3%BAde%20Ocupacional,soLocal,,,,,-1,2,ISO-8859-1,ISO-8859-1,,yes,,07%3A30-18%3A30,%2022%3A00-22%3A30,localhost,saudeOcupacional_desenvolvimento,mysql,root,czyhnp,is_usuarios,ID,userID,super,apelido,senha,md5,lastIP,userRights,lastAccess,,,,asg3q8e30gqegr59529c592aeac95f53,Salvar,undefined)
&ts=1392245447767
       */
        if ($setupIni=createDBText($sgugIni)) {
          $Xactive=intval($Xactive);
          $setupIni->goTop();
          if ($Xactive!=0) {
            if ($setupIni->locate('active', "1")==$dbTEXT_NO_ERROR) {
              $setupIni->setValue("active", "0");
              $setupIni->commit();
            }
          }
          $canDo=false;
          if ($oldAppRegistry=='') {
            if (trim($XappRegistry)=='')
              $XappRegistry=md5(uniqid('sgugini',true));
            $setupIni->addRecord();
            $setupIni->setValue('appRegistry', $XappRegistry);
            $canDo=true;
          } else {
            if ($setupIni->locate('appRegistry', $XappRegistry)==$dbTEXT_NO_ERROR) {
              $canDo=true;
            } else
              _recordError("Registro não localizado");
          }

          _dump("====[ $canDo ]==");

          if ($canDo) {
            $setupIni->addField('active');
            $setupIni->addField('aDebugIP');
            $setupIni->addField('appCharset');
            $setupIni->addField('appName');
            $setupIni->addField('appRegistry');
            $setupIni->addField('appSlogan');
            $setupIni->addField('appTitle');
            $setupIni->addField('autoDocumentation');
            $setupIni->addField('bannerHeight');
            $setupIni->addField('binDistributionFolder');
            $setupIni->addField('dbCharset');
            $setupIni->addField('dbConnect');
            $setupIni->addField('dbName');
            $setupIni->addField('dbOnline');
            $setupIni->addField('dbPassword');
            $setupIni->addField('dbServer');
            $setupIni->addField('dbType');
            $setupIni->addField('dbUser');
            $setupIni->addField('debugNotifierSevice');
            $setupIni->addField('dontUpdate');
            $setupIni->addField('dontWorkUntil');
            $setupIni->addField('freeIP');
            $setupIni->addField('initialVerb');
            $setupIni->addField('logonForm');
            $setupIni->addField('messagePeekerInterval');
            $setupIni->addField('pauseCause');
            $setupIni->addField('phoneticSearch');
            $setupIni->addField('phpErrorDebug');
            $setupIni->addField('showSQLcommands');
            $setupIni->addField('SQLdebugLevel');
            $setupIni->addField('systemUpdateSource');
            $setupIni->addField('unique_id_seed');
            $setupIni->addField('updateSource');
            $setupIni->addField('usrEMail');
            $setupIni->addField('usrIPField');
            $setupIni->addField('usrLastAccess');
            $setupIni->addField('usrNicknameField');
            $setupIni->addField('usrPassword');
            $setupIni->addField('usrPasswordAlgorithm');
            $setupIni->addField('usrRightsField');
            $setupIni->addField('usrSessionIDField');
            $setupIni->addField('usrSuperField');
            $setupIni->addField('usrTableName');
            $setupIni->addField('usrUniqueIDField');
            $setupIni->addField('yMenuAttr');
            $setupIni->addField('yMenuRoot');

            foreach($setupIni->fields() as $fld) {
              $Xfld="X$fld";
              // echo "$Xfld<br>";
              _dump("$fld = ".$$Xfld);
              $setupIni->setValue($fld, $$Xfld);
            }

            if ($setupIni->commit())
              _recordAction("OK");
            else
              _recordError("Can't write");
          }
        }
        break;

      case 'new':
        break;

      case 'edit':
        break;

      case 'delete':
        break;
    }

    xq_produceReturnLines($ret, $useColNames, $countLimit);

  }

  // read yDebugEvents explanation forward
  function qyd_todo($a) {
    global $userContext, $sysDate, $u,
           $fieldValue, $fieldName,
           $userMsg,
           $sgugIni,
           $dbTEXT_NO_ERROR, $dbTEXT_EOF, $dbTEXT_BOF;;

    $useColNames = true;
    $countLimit=20;
    $ret=array();

    $dbName='.yproject/develop.sqlite3';

    if (!is_dir('.yproject'))
      mkdir('.yproject', 0766);

    if (class_exists('SQLite3')) {

      $needRebuild = !file_exists($dbName);

      if ($db = new ySQlite3($dbName)) {

        if (($needRebuild) || (!$db->tableExists($db,'todo'))) {
          $db->exec('CREATE TABLE todo (id char(32), todoItem STRING, creationDate timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL, lastModification timestamp)');
        }

        extract(xq_extractValuesFromQuery());

        switch ($a) {
          case 'open':
            $formName=dirname(__FILE__).'/todo/l_todo.html';
            if (file_exists($formName))
              $formContent=_file($formName);
            else
              $formContent="File '<b>$formName</b>' not found";
            $ret['html']=maskHTML($formContent);
            break;

          case 'getFormValues':
            $res = $db->query("select * from todo where id='$id'");
            $ret = $res->fetchArray();
           break;

          case 'saveForm':
            $now=time();
            if ($id=='') {
              $id=md5(uniqid('todo'));
              $db->exec("insert into todo(id, todoItem, creationDate) values ('$id', '$todoItem', $now)");
            } else {
              $db->exec("update todo set todoItem='$todoItem', lastModification=$now where id='$id'");
            }
            break;

          case 'deleteItem':
            $db->exec("delete from todo where id='$id'");
            break;

          case 'getList':
            $res = $db->query("select * from todo order by creationDate");
            while ($aux = $res->fetchArray()) {
              $ret[]=array( 'ID'          => $aux['id'],
                            'label'       => $aux['todoItem'],
                            'description' => maskHTML('<small>C: '.date("Y-m-d H:i:s", $aux['creationDate']).'<br>M: '.date("Y-m-d H:i:s", $aux['lastModification'])."</small>"),
                            'href'        => 'javascript:ydbTodoViewEntry({ id:"'.$aux['id'].'"})',
                            'isDefault'   => 0 );
            }
            break;
        }
      } else
        $ret['error']="Was not possible to open '.yproject/develop.sqlite' database";
    } else
      $ret['error']="sqlite not installed";

    xq_produceReturnLines($ret, $useColNames, $countLimit);
  }

  /*
   * The develop events are multiplexed events as 's=yeapf:develop' and
   * a='dbConn:getList' for example.
   * So, you only need to free yeapf:develop at appFolderName.def or
   * has flags/flag.develop turned on
   * These events are translated into a query.php manner where you need
   * to return just a JSON/XML with data.
   * For example, dbConn:getList is translated to become
   * qyd_dbConn(getList).  where qyd = [q]uery+[y]eapf+[d]evelop
   *
   * getList event:
   * all debug list must have next fields:
   *     ID (char32)
   *     label (text)
   *     description (csv ';' text)
   *     href (javascript link i.e.)
   *     isDefault (integer 0,1)
   */

  function yDebugEvents($s, $a) {
    _dump("=== $s -> $a { ");
    if ($s=='yeapf:develop') {
      $aAux=split(':', $a);
      $funcName="qyd_".$aAux[0];
      if (function_exists($funcName)) {
        _dump("calling $funcName");
        $funcName($aAux[1]);
      } else
        _dump("$funcName() not found");

    }
    _dump("=== } $s -> $a ");
  }

  if (!is_dir(".developSession")) {
    if (!mkdir(".developSession", 0777))
      die("<div style='border-color: #CB0000; background: #FFC0CB; width: 520px; margin: 8px; padding: 32px; border-style: solid; border-width: 2px; padding: 16px; border-radius:4px; font-family: arial; font-size: 12px'>Folder '.developSession' cannot be created<br>Check user rights on this directory</div>");
  }

  addEventHandler('yDebugEvents');

  _dump("OK!!!!");
?>
