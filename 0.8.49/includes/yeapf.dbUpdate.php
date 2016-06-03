<?php
/*
    includes/yeapf.dbUpdate.php
    YeAPF 0.8.49-10 built on 2016-06-03 13:09 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-06-02 11:06:57 (-3 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  $flagCanReviewDBUpdate=true;
  $flagDBStructureReviewed=false;

  function _db_upd_canReviewVersion($aVersion)
  {
    global $flagCanReviewDBUpdate, $currentDBVersion;

    $ret=($flagCanReviewDBUpdate) && ($currentDBVersion<$aVersion);
    return $ret;
  }

  function _db_upd_error($errorMsg)
  {
    global $flagCanReviewDBUpdate, $currentDBVersion;
    $flagCanReviewDBUpdate=false;
  }

  function _db_grantSetupIni()
  {
    global $setupIni, $sgugIni;
    if (!isset($setupIni)) $setupIni=createDBText($sgugIni);
  }

  function _db_upd_newVersion($aVersion)
  {
    global $setupIni, $dbTEXT_NO_ERROR, $currentDBVersion, $flagDBStructureReviewed;

    $currentDBVersion=$aVersion;
    _db_grantSetupIni();
    if (($setupIni->locate("active",1))==$dbTEXT_NO_ERROR) {
      $setupIni->addField('currentDBVersion');
      $setupIni->setValue('currentDBVersion',$currentDBVersion);
      $setupIni->commit();
    }
    $flagDBStructureReviewed=true;
  }

  if ((isset($_ydb_ready)) && ($_ydb_ready & _DB_CONNECTED_)) {
    if ((isset($_ydb_ready)) && ($_ydb_ready & _DB_UPDATABLE)) {
      $currentDBVersion=(intval("$currentDBVersion"));
      if (_db_upd_canReviewVersion(1)) {
        _recordWastedTime("checking v1");
        try {
          if (!db_tableExists('is_context')) {
            $sql = "CREATE TABLE is_context (";
            $sql.= "  userID int  DEFAULT NULL,";
            $sql.= "  varName varchar(120) DEFAULT NULL,";
            $sql.= "  varValue varchar(254) DEFAULT NULL,";
            $sql.= "  PRIMARY KEY (userID)";
            $sql.= ")";
            db_sql($sql);
          }

          if (!db_tableExists('is_menu')) {
            $sql ="CREATE TABLE is_menu (";
            if (db_connectionTypeIs(_MYSQL_))
              $sql.="  ID int  NOT NULL AUTO_INCREMENT,";
            else
              $sql.="  ID int  NOT NULL ,";
            $sql.="  enabled char(1) DEFAULT 'Y',";
            $sql.="  attr int  DEFAULT '0',";
            if (db_connectionTypeIs(_MYSQL_))
              $sql.="  o tinyint  DEFAULT '0',";
            else
              $sql.="  o smallint  DEFAULT '0',";
            $sql.="  ancestor int DEFAULT '0',";
            $sql.="  label varchar(64) DEFAULT NULL,";
            $sql.="  d char(1)  DEFAULT '',";
            $sql.="  rights int  DEFAULT '2',";
            $sql.="  a varchar(60) DEFAULT NULL,";
            $sql.="  s varchar(30) DEFAULT NULL,";
            $sql.="  implementation varchar(80) DEFAULT NULL,";
            $sql.="  lnkNewWindow int DEFAULT '0',";
            $sql.="  lnkNewWindowWidth int DEFAULT '600',";
            $sql.="  lnkNewWindowHeight int DEFAULT '480',";
            $sql.="  lnkAllWide int DEFAULT '1',";
            $sql.="  lnkWOHeader int DEFAULT '1',";
            $sql.="  clickCounter int DEFAULT '0',";
            $sql.="  lnkNewWindowLeft int DEFAULT '20',";
            $sql.="  lnkNewWindowTop int DEFAULT '40',";
            $sql.="  lnkCacheable int DEFAULT '0',";
            $sql.="  hasImplementation int DEFAULT '0',";
            $sql.="  app int DEFAULT '0',";
            $sql.="  permiteAtivacao int DEFAULT '0',";
            $sql.="  permiteCriarItems int DEFAULT '0',";
            if (db_connectionTypeIs(_MYSQL_))
              $sql.="  explanation text,";
            else
              $sql.="  explanation varchar(4096),";
            $sql.="  ativo char(1) DEFAULT 'S',";
            $sql.="  lnkAutoPrint int DEFAULT '0',";
            $sql.="  PRIMARY KEY (ID) ";
            $sql.=")";
            db_sql($sql);
          }
          if (!db_tableExists('is_usuarios')) {
            $sql="CREATE TABLE is_usuarios (";
            $sql.="  id varchar(40) DEFAULT '',";
            $sql.="  userID int DEFAULT '0',";
            $sql.="  pessoa varchar(48) DEFAULT NULL,";
            $sql.="  senha varchar(32) DEFAULT NULL,";
            $sql.="  nome varchar(40) DEFAULT NULL,";
            $sql.="  apelido varchar(20) DEFAULT NULL,";
            $sql.="  tipo char(3) DEFAULT NULL,";
            $sql.="  lastAccess varchar(14) DEFAULT NULL,";
            $sql.="  super char(1) DEFAULT 'N',";
            $sql.="  userRights int DEFAULT '0',";
            $sql.="  PRIMARY KEY (id)";
            $sql.=")";
            db_sql($sql);

            $idRoot=md5('root');
            db_sql("INSERT INTO is_usuarios (id, apelido, senha, super, UserRights) VALUES ('$idRoot','root','$cfgRootFirstPassword','Y',65535)");
          }
          if (db_tableExists('is_menu')) {
            $u1=db_fieldExists('is_usuarios','userRights');
            if (!($u1)) {
              db_sql("alter table is_usuarios add userRights integer default 0");
              $u2=db_fieldExists('is_usuarios','ut');
              if ($u2) {
                db_sql("update is_usuarios set userRights=ut");
                db_sql("alter table is_usuarios drop ut");
              }
            }
            $u1=db_fieldExists('is_menu','implementation');
            if (!($u1)) {
              db_sql("alter table is_menu add implementation varchar(80)");
              $u2=db_fieldExists('is_menu','page');
              if ($u2) {
                db_sql("update is_menu set implementation=page");
                db_sql("alter table is_menu drop page");
              }
            }
          }
          _db_upd_newVersion(1);
        } catch (Exception $e) {
          _db_upd_error($e->getMessage());
        }
      }

      if (_db_upd_canReviewVersion(2)) {
        _recordWastedTime("checking v2");
        try {
          if (!db_tableExists('is_sqlcache')) {
            $sql="create table is_sqlcache  (";
            $sql.="  id CHAR(32) NOT NULL,";
            $sql.="  buildDate INTEGER,";
            $sql.="  ttl INTEGER,";
            $sql.="  statement char(32),";
            $sql.="  lastAccess INTEGER);";
            db_sql($sql);
          }
          if (!db_tableExists('is_sqlcache_content')) {
            $sql="create table is_sqlcache_content  (";
            $sql.="  id CHAR(32) NOT NULL,";
            $sql.="  o integer,";
            if (db_connectionTypeIs(_MYSQL_)) {
              $sql.="  content text,";
              $sql.="  comp text);";
            } else {
              $sql.="  content VARCHAR(16368),";
              $sql.="  comp VARCHAR(32765));";
            }
            db_sql($sql);
          }
          _db_upd_newVersion(2);
        } catch (Exception $e) {
          _db_upd_error($e->getMessage());
        }

      }

      if (_db_upd_canReviewVersion(3)) {
        _recordWastedTime("checking v3");
        try {
          if (!db_tableExists('is_doc_tokens')) {
            $sql ="create table is_doc_tokens (";
            $sql.="  token varchar(128) not null, ";
            $sql.="  tokenAlias varchar(128), ";
            $sql.="  appLevel integer, ";
            $sql.="  g smallint default 0, ";
            $sql.="  o smallint default 0, ";
            if (db_connectionTypeIs(_MYSQL_)) {
              $sql.="  description text, ";
              $sql.="  examples text)";
            } else {
              $sql.="  description varchar(2048), ";
              $sql.="  examples VARCHAR(32765))";
            }
            db_sql($sql);
          }

          if (!db_tableExists('is_doc_parameters')) {
            $sql ="create table is_doc_parameters (";
            $sql.="  token varchar(128) not null, ";
            $sql.="  paramNdx smallint, ";
            $sql.="  paramName varchar(60), ";
            $sql.="  isOptional char(1) default 'N', ";
            if (db_connectionTypeIs(_MYSQL_))
              $sql.="  paramDescription text)";
            else
              $sql.="  paramDescription varchar(2048))";
            db_sql($sql);
          }
          _db_upd_newVersion(3);
        } catch (Exception $e) {
          _db_upd_error($e->getMessage());
        }

      }

      if (_db_upd_canReviewVersion(4)) {
        _recordWastedTime("checking v4");
        try {
          if (db_tableExists('is_menu')) {
            if (!db_fieldExists('is_menu','lnkNewWindow'))
            db_sql("alter table is_menu add lnkNewWindow integer default 0");

            if (!db_fieldExists('is_menu','lnkNewWindowWidth'))
            db_sql("alter table is_menu add lnkNewWindowWidth integer default 600");

            if (!db_fieldExists('is_menu','lnkNewWindowHeight'))
            db_sql("alter table is_menu add lnkNewWindowHeight integer default 480");

            if (!db_fieldExists('is_menu','lnkAllWide'))
            db_sql("alter table is_menu add lnkAllWide integer default 1");

            if (!db_fieldExists('is_menu','lnkWOHeader'))
            db_sql("alter table is_menu add lnkWOHeader integer default 1");

            if (!db_fieldExists('is_menu','clickCounter'))
            db_sql("alter table is_menu add clickCounter integer default 0");
          }
          _db_upd_newVersion(4);
        } catch (Exception $e) {
          _db_upd_error($e->getMessage());
        }

      }

      if (_db_upd_canReviewVersion(5)) {
        _recordWastedTime("checking v5");
        try {
          if (db_tableExists('is_menu')) {
            if (!db_fieldExists('is_menu','lnkNewWindowLeft'))
              db_sql("alter table is_menu add lnkNewWindowLeft integer default 20");

            if (!db_fieldExists('is_menu','lnkNewWindowTop'))
              db_sql("alter table is_menu add lnkNewWindowTop integer default 40");

            if (!db_fieldExists('is_menu','lnkCacheable'))
              db_sql("alter table is_menu add lnkCacheable integer default 0");

            if (!db_fieldExists('is_menu','lnkAutoPrint'))
              db_sql("alter table is_menu add lnkAutoPrint integer default 0");

            if (!db_fieldExists('is_menu','hasImplementation'))
              db_sql("alter table is_menu add hasImplementation integer default 0");

          }
          _db_upd_newVersion(5);
        } catch (Exception $e) {
          _db_upd_error($e->getMessage());
        }
      }

      if (_db_upd_canReviewVersion(6)) {
        _recordWastedTime("checking v6");
        try {
          if (db_tableExists('is_menu')) {
            if (!db_fieldExists('is_menu','app'))
              db_sql("alter table is_menu add app integer default 0");
            if (!db_fieldExists('is_menu','permiteAtivacao'))
              db_sql("alter table is_menu add permiteAtivacao integer default 0");
            if (!db_fieldExists('is_menu','permiteCriarItems'))
              db_sql("alter table is_menu add permiteCriarItems  integer default 0");
            if (!db_fieldExists('is_menu','explanation')) {
              if (db_connectionTypeIs(_MYSQL_))
                db_sql("alter table is_menu add explanation text");
              else
                db_sql("alter table is_menu add explanation varchar(2048)");
            }
            if (!db_fieldExists('is_menu','ativo'))
              db_sql("alter table is_menu add ativo char(1) default 'S'");

            if (valorSQL("select count(*) from is_menu where s='menus'")==0) {
              $newID=intval(db_sql("select max(ID) from is_menu"))+1;
              if (($newID)<2100)
                $newID=2100;
              db_sql("INSERT INTO is_menu (ID, enabled, attr, o, ancestor, label, d, rights, a, s, implementation, explanation, ativo, app, permiteAtivacao, permiteCriarItems, lnkNewWindow, lnkNewWindowWidth, lnkNewWindowHeight, lnkAllWide, lnkWOHeader, clickCounter, lnkNewWindowLeft, lnkNewWindowTop, lnkCacheable, hasImplementation) VALUES ($newID, 'Y', '2', '0', '-1', 'Navegação', '', '2', NULL, 'menus', NULL, NULL, 'N', '65535', '1', '0', '0', '600', '480', '1', '1', '0', '20', '40', '0', '0')");
            }
            if (valorSQL("select count(*) from is_menu where s='menuSuperior'")==0) {
              $newID=intval(db_sql("select max(ID) from is_menu"))+1;
              if (($newID)<2101)
                $newID=2101;
              db_sql("INSERT INTO is_menu (ID, enabled, attr, o, ancestor, label, d, rights, a, s, implementation, explanation, ativo, app, permiteAtivacao, permiteCriarItems, lnkNewWindow, lnkNewWindowWidth, lnkNewWindowHeight, lnkAllWide, lnkWOHeader, clickCounter, lnkNewWindowLeft, lnkNewWindowTop, lnkCacheable, hasImplementation) VALUES ($newID, 'Y', '0', '0', '2100', 'Menu Superior', '', '4', NULL, 'menuSuperior', NULL, NULL, NULL, '65535', '1', '1', '0', '600', '480', '1', '1', '0', '20', '40', '0', '0')");
            }
            if (valorSQL("select count(*) from is_menu where s='menuLateral'")==0) {
              $newID=intval(db_sql("select max(ID) from is_menu"))+1;
              if (($newID)<2102)
                $newID=2102;
              db_sql("INSERT INTO is_menu (ID, enabled, attr, o, ancestor, label, d, rights, a, s, implementation, explanation, ativo, app, permiteAtivacao, permiteCriarItems, lnkNewWindow, lnkNewWindowWidth, lnkNewWindowHeight, lnkAllWide, lnkWOHeader, clickCounter, lnkNewWindowLeft, lnkNewWindowTop, lnkCacheable, hasImplementation) VALUES ('$newID', 'Y', '0', '0', '2100', 'Menu Lateral', '', '4', NULL, 'menuLateral', NULL, NULL, NULL, '65535', '1', '1', '0', '600', '480', '1', '1', '0', '20', '40', '0', '0')");
            }
            if (valorSQL("select count(*) from is_menu where s='menuInferior'")==0) {
              $newID=intval(db_sql("select max(ID) from is_menu"))+1;
              if (($newID)<2103)
                $newID=2103;
              db_sql("INSERT INTO is_menu (ID, enabled, attr, o, ancestor, label, d, rights, a, s, implementation, explanation, ativo, app, permiteAtivacao, permiteCriarItems, lnkNewWindow, lnkNewWindowWidth, lnkNewWindowHeight, lnkAllWide, lnkWOHeader, clickCounter, lnkNewWindowLeft, lnkNewWindowTop, lnkCacheable, hasImplementation) VALUES (2103, 'Y', '0', '0', '2100', 'Menu Inferior', '', '4', NULL, 'menuInferior', NULL, NULL, NULL, '65535', '1', '1', '0', '600', '480', '1', '1', '0', '20', '40', '0', '0')");
            }
            if (valorSQL("select count(*) from is_menu where s='menuPopup'")==0) {
              $newID=intval(db_sql("select max(ID) from is_menu"))+1;
              if (($newID)<2104)
                $newID=2104;
              db_sql("INSERT INTO is_menu (ID, enabled, attr, o, ancestor, label, d, rights, a, s, implementation, explanation, ativo, app, permiteAtivacao, permiteCriarItems, lnkNewWindow, lnkNewWindowWidth, lnkNewWindowHeight, lnkAllWide, lnkWOHeader, clickCounter, lnkNewWindowLeft, lnkNewWindowTop, lnkCacheable, hasImplementation) VALUES ($newID, 'Y', '0', '0', '2100', 'Pop-up', '', '4', NULL, 'menuPopup', NULL, NULL, NULL, '65535', '1', '1', '0', '600', '480', '1', '1', '0', '20', '40', '0', '0')");
            }
            if (valorSQL("select count(*) from is_menu where s='menuChamada'")==0) {
              $newID=intval(db_sql("select max(ID) from is_menu"))+1;
              if (($newID)<2105)
                $newID=2105;
              db_sql("INSERT INTO is_menu (ID, enabled, attr, o, ancestor, label, d, rights, a, s, implementation, explanation, ativo, app, permiteAtivacao, permiteCriarItems, lnkNewWindow, lnkNewWindowWidth, lnkNewWindowHeight, lnkAllWide, lnkWOHeader, clickCounter, lnkNewWindowLeft, lnkNewWindowTop, lnkCacheable, hasImplementation) VALUES ($newID, 'Y', '0', '0', '2100', 'Chamada', '', '4', NULL, 'menuChamada', NULL, NULL, NULL, '65535', '1', '1', '0', '600', '480', '1', '1', '0', '20', '40', '0', '0')");
            }
            if (valorSQL("select count(*) from is_menu where s='artigos'")==0) {
              $newID=intval(db_sql("select max(ID) from is_menu"))+1;
              if (($newID)<4000)
                $newID=4000;
              db_sql("INSERT INTO is_menu (ID, enabled, attr, o, ancestor, label, d, rights, a, s, implementation, explanation, ativo, app, permiteAtivacao, permiteCriarItems, lnkNewWindow, lnkNewWindowWidth, lnkNewWindowHeight, lnkAllWide, lnkWOHeader, clickCounter, lnkNewWindowLeft, lnkNewWindowTop, lnkCacheable, hasImplementation) VALUES ($newID, 'Y', '2', '0', '-1', 'Artigos', '', '2', NULL, 'artigos', NULL, NULL, 'S', '128', '1', '1', '0', '600', '480', '1', '1', '0', '20', '40', '0', '0')");
            }

            if (valorSQL("select count(*) from is_menu where s='sessoes'")==0) {
              $newID=intval(db_sql("select max(ID) from is_menu"))+1;
              if (($newID)<4001)
                $newID=4001;
              db_sql("INSERT INTO is_menu (ID, enabled, attr, o, ancestor, label, d, rights, a, s, implementation, explanation, ativo, app, permiteAtivacao, permiteCriarItems, lnkNewWindow, lnkNewWindowWidth, lnkNewWindowHeight, lnkAllWide, lnkWOHeader, clickCounter, lnkNewWindowLeft, lnkNewWindowTop, lnkCacheable, hasImplementation) VALUES ($newID, 'Y', '2', '0', '-1', 'Sessões', '', '2', NULL, 'sessoes', NULL, NULL, 'S', '128', '1', '1', '0', '600', '480', '1', '1', '0', '20', '40', '0', '0')");
            }
          }
          _db_upd_newVersion(6);
        } catch (Exception $e) {
          _db_upd_error($e->getMessage());
        }
      }

      if (_db_upd_canReviewVersion(7)) {
        _recordWastedTime("checking v7");
        try {
          if (!db_tableExists('is_jails')) {

            $sql ="create table is_jails (";
            $sql.="  userID integer, ";
            if (db_connectionTypeIs(_MYSQL_))
            $sql.="  jails text ";
            else
            $sql.="  jails varchar(512) ";
            $sql.=")";
            db_sql($sql);

            $sql="create unique index idx_jails on is_jails(userID)";
            db_sql($sql);

          }
          _db_upd_newVersion(7);
        } catch (Exception $e) {
          _db_upd_error($e->getMessage());
        }
      }

      if (_db_upd_canReviewVersion(8)) {
        _recordWastedTime("checking v8");
        try {
          if (!db_tableExists('wbp_buffer')) {
            $sql ="CREATE TABLE wbp_buffer (";
            $sql.="  id varchar(48) NOT NULL,";
            $sql.="  device varchar(22),";
            $sql.="  printer varchar(48) ,";
            $sql.="  description varchar(120) ,";
            $sql.="  orientation char(1) DEFAULT NULL,";
            if (db_connectionTypeIs(_MYSQL_))
              $sql.="  buffer text,";
            else
              $sql.="  buffer varchar(2048),";
            $sql.="  creationDate integer ,";
            $sql.="  lastAccess integer NOT NULL ,";
            $sql.="  status integer ,";
            $sql.="  erro varchar(240) ,";
            $sql.="  valor float NOT NULL ,";
            $sql.="  PRIMARY KEY (id)";
            if (db_connectionTypeIs(_MYSQL_))
              $sql.="  ,KEY printer (printer)";
            $sql.=")";
            db_sql($sql);
          }
          // 082Msvs+B+v1F4SYokfZmLI1+RJH8vdv17JGzdUa9y0rC7sINtNll+CqN36y05w6tFxl5JsMUBHl99xQNrvzGmb0
          if (!db_tableExists('wbp_devices')) {
            $sql="CREATE TABLE wbp_devices (";
            $sql.="  id varchar(22) NOT NULL, ";
            $sql.="  verification varchar(48),";
            $sql.="  place varchar(120) ,";
            $sql.="  lastAccess integer ,";
            $sql.="  keepAlive integer DEFAULT '10',";
            $sql.="  PRIMARY KEY (id)";
            $sql.=")";
            db_sql($sql);
          }

          if (!db_tableExists('wbp_files')) {
            $sql ="CREATE TABLE wbp_files (";
            $sql.="  id varchar(48) NOT NULL,";
            $sql.="  fileName varchar(120) ,";
            $sql.="  sequence integer,";
            if (db_connectionTypeIs(_MYSQL_))
              $sql.="  data text,";
            else
              $sql.="  data varchar(2048),";
            $sql.="  PRIMARY KEY (id)";
            $sql.=")";
            db_sql($sql);
          }

          if (!db_tableExists('wbp_printers')) {
            $sql ="CREATE TABLE wbp_printers (";
            $sql.="  id varchar(48) NOT NULL,";
            $sql.="  sid integer ,";
            $sql.="  device varchar(22) ,";
            $sql.="  virtualType char(3),";
            $sql.="  virtualName varchar(120),";
            $sql.="  windowsName varchar(120),";
            $sql.="  lastAccess integer,";
            $sql.="  PRIMARY KEY (id)";
            $sql.=")";
            db_sql($sql);
          }

          if (!db_fieldExists('wbp_printers','sid')) {
            if (db_connectionTypeIs(_MYSQL_))
              db_sql("ALTER TABLE wbp_printers ADD sid INT  DEFAULT NULL NULL after ID");
            else
              db_sql("ALTER TABLE wbp_printers ADD sid INTEGER DEFAULT NULL");
          }

          _db_upd_newVersion(8);
        } catch (Exception $e) {
          _db_upd_error($e->getMessage());
        }
      }


      if (_db_upd_canReviewVersion(9)) {
        _recordWastedTime("checking v9");
        try {
          if (!db_tableExists('is_updates')) {
            $sql ="create table is_updates (";
            $sql.="id varchar(40), ";
            $sql.="realization varchar(14) DEFAULT NULL, ";
            $sql.="s varchar(50) DEFAULT NULL, ";
            $sql.="layer varchar(78) DEFAULT NULL, ";
            if (db_connectionTypeIs(_MYSQL_))
              $sql.="description text, ";
            else
              $sql.="description varchar(8192), ";
            $sql.="userID varchar(40) DEFAULT NULL, ";
            $sql.="pageID varchar(78) DEFAULT NULL) ";
            db_sql($sql);

            $sql="create unique index idx_updates on is_updates(id)";
            db_sql($sql);
            $sql="create index idx_updates2 on is_updates(s)";
            db_sql($sql);

          }
          _db_upd_newVersion(9);
        } catch (Exception $e) {
          _db_upd_error($e->getMessage());
        }
      }


      if (_db_upd_canReviewVersion(10)) {
        _recordWastedTime("checking v10");
        try {
          if (!db_tableExists('is_auditingTrack')) {
            $sql = "CREATE TABLE  is_auditingTrack (
                      id char(32),
                      state char(1),
                      userID char(32),
                      eventDate char(14),
                      tableName varchar(80) DEFAULT NULL,
                      tableID char(250) DEFAULT NULL,
                      tableIDField char(250) DEFAULT NULL,
                      sqlVerb char(6),
                      yeapfContext varchar(120),
                      eventDescription varchar(250) DEFAULT NULL, ";
            if (db_connectionTypeIs(_MYSQL_))
              $sql.="prevRecord text,
                     newRecord text ";
            else
              $sql.="prevRecord varchar(32000),
                     newRecord  varchar(32000)";
            $sql.=")";

            db_sql($sql);

          }
          _db_upd_newVersion(10);
        } catch (Exception $e) {
          _db_upd_error($e->getMessage());
        }

      }

      if (_db_upd_canReviewVersion(11)) {
        _recordWastedTime("checking v11");

        if (!db_tableExists('is_perfil_usuarios')) {
          $sql="CREATE TABLE is_perfil_usuarios (
                  bit integer DEFAULT NULL,
                  etiqueta varchar(40) DEFAULT NULL,
                  explanacao varchar(512)
                )";
          db_sql($sql);
          db_commit();

          db_sql("INSERT INTO is_perfil_usuarios (bit, etiqueta, explanacao) VALUES(0, 'YeAPF-reserved', NULL)");
          db_sql("INSERT INTO is_perfil_usuarios (bit, etiqueta, explanacao) VALUES(1, 'YeAPF-cms', NULL)");
          db_sql("INSERT INTO is_perfil_usuarios (bit, etiqueta, explanacao) VALUES(2, 'YeAPF-reserved', NULL)");

        }

        $currentDBVersion=11;
        _db_grantSetupIni();
        $setupIni->setValue('currentDBVersion',$currentDBVersion);
        $setupIni->commit();

      }

      if(_db_upd_canReviewVersion(12)) {
        _recordWastedTime("checking v12");
        if (!db_tableExists('is_tasks')) {
          $sql="CREATE TABLE  `is_tasks` (
                              `id` INT NOT NULL AUTO_INCREMENT,
                              `creation_ts` INT,
                              `finalization_ts` INT NULL,
                              `iteraction_ts` INT NULL,
                              `iteraction_ttl` INT DEFAULT 480,
                              `stage` INT NULL,
                              `priority` INT NULL,
                              `mru` INT DEFAULT 0,
                              `s` VARCHAR(48) NULL,
                              `a` VARCHAR(48) NULL,
                              `xq_start` INT NULL,
                              `xq_target` INT NULL,
                              `j_params` VARCHAR(512) NULL,
                              PRIMARY KEY (`id`))";
          db_sql($sql);
          db_commit();
        }
        $currentDBVersion=12;
        _db_grantSetupIni();
        $setupIni->setValue('currentDBVersion',$currentDBVersion);
        $setupIni->commit();        
      }
    }

    if ($flagDBStructureReviewed) {
      _recordWastedTime("checking is_context");
      if (((date('w')==1) && ($lastDBCleanUp<date('Ymd0000'))) || ($lastDBCleanUp=='')) {
        $lastDBCleanUp=date('YmdHi');
        if (db_tableExists('is_context'))
          db_sql("delete from is_context");
        _db_grantSetupIni();
        $setupIni->addField('lastDBCleanUp');
        $setupIni->setValue('lastDBCleanUp',$lastDBCleanUp);
        $setupIni->commit();
      }
    }

  }
?>
