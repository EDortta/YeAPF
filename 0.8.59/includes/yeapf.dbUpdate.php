<?php
/*
    includes/yeapf.dbUpdate.php
    YeAPF 0.8.59-41 built on 2017-08-28 20:40 (-3 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2017-08-28 19:37:13 (-3 DST)
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
    global $setupIni, $dbCSVFilename;
    if (!isset($setupIni)) $setupIni=createDBText($dbCSVFilename);
  }

  function _db_upd_createAuditingTrackTable()
  {
    if (!db_tableExists('is_auditing_track')) {
      if (db_connectionTypeIs(_FIREBIRD_)) {
        $sql='SELECT count(*) FROM rdb$relations WHERE (rdb$relation_name = \'IS_AUDITING_TRACK\') AND (rdb$view_blr IS NOT NULL)';
        $cc=db_sql($sql);
      } else {
        $cc=0;
      }
      if ($cc==0) {
        $sql = "CREATE TABLE  is_auditing_track (
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
        if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
          $sql.="prevRecord text,
                 newRecord text ";
        else
          $sql.="prevRecord varchar(32000),
                 newRecord  varchar(32000)";
        $sql.=")";

        db_sql($sql);
      }
    }
  }

  function _db_upd_newVersion($aVersion)
  {
    global $setupIni, $dbTEXT_NO_ERROR, $currentDBVersion, $flagDBStructureReviewed, $cfgCurrentAppRegistry;

    $currentDBVersion=$aVersion;
    _db_grantSetupIni();

    $ok=false;

    if ($cfgCurrentAppRegistry>'') {
      $ok=($setupIni->locate("appRegistry", $cfgCurrentAppRegistry)==$dbTEXT_NO_ERROR);
    } else {
      $ok=($setupIni->locate("active",1)==$dbTEXT_NO_ERROR);
    }

    if ($ok) {
      $setupIni->addField('currentDBVersion');
      $setupIni->setValue('currentDBVersion',$currentDBVersion);
      $setupIni->commit();
    }
    $flagDBStructureReviewed=true;
  }

  global $setupIni;

  if (db_status(_DB_CONNECTED_)==_DB_CONNECTED_) {
    if (db_status(_DB_UPDATABLE)==_DB_UPDATABLE) {
      $currentDBVersion=(intval("$currentDBVersion"));
      if (_db_upd_canReviewVersion(1)) {
        _recordWastedTime("checking v1");
        try {
          if (!db_tableExists('is_context')) {
            $sql = "CREATE TABLE is_context (";
            $sql.= "  userID int NOT NULL,";
            $sql.= "  varName varchar(120) DEFAULT NULL,";
            $sql.= "  varValue varchar(254) DEFAULT NULL,";
            $sql.= "  PRIMARY KEY (userID)";
            $sql.= ")";
            db_sql($sql);
          }

          if (!db_tableExists('is_menu')) {
            $sql ="CREATE TABLE is_menu (";
            if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
              $sql.="  ID int  NOT NULL AUTO_INCREMENT,";
            else
              $sql.="  ID int  NOT NULL ,";
            $sql.="  enabled char(1) DEFAULT 'Y',";
            $sql.="  attr int  DEFAULT '0',";
            if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
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
            if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
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
            if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_))) {
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
            if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_))) {
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
            if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
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
              if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
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
              db_sql("INSERT INTO is_menu (ID, enabled, attr, o, ancestor, label, d, rights, a, s, implementation, explanation, ativo, app, permiteAtivacao, permiteCriarItems, lnkNewWindow, lnkNewWindowWidth, lnkNewWindowHeight, lnkAllWide, lnkWOHeader, clickCounter, lnkNewWindowLeft, lnkNewWindowTop, lnkCacheable, hasImplementation) VALUES ($newID, 'Y', '2', '0', '-1', 'Navega��o', '', '2', NULL, 'menus', NULL, NULL, 'N', '65535', '1', '0', '0', '600', '480', '1', '1', '0', '20', '40', '0', '0')");
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
              db_sql("INSERT INTO is_menu (ID, enabled, attr, o, ancestor, label, d, rights, a, s, implementation, explanation, ativo, app, permiteAtivacao, permiteCriarItems, lnkNewWindow, lnkNewWindowWidth, lnkNewWindowHeight, lnkAllWide, lnkWOHeader, clickCounter, lnkNewWindowLeft, lnkNewWindowTop, lnkCacheable, hasImplementation) VALUES ($newID, 'Y', '2', '0', '-1', 'Sess�es', '', '2', NULL, 'sessoes', NULL, NULL, 'S', '128', '1', '1', '0', '600', '480', '1', '1', '0', '20', '40', '0', '0')");
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
            if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
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
            if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
              $sql.="  buffer text,";
            else
              $sql.="  buffer varchar(2048),";
            $sql.="  creationDate integer ,";
            $sql.="  lastAccess integer DEFAULT NULL ,";
            $sql.="  status integer ,";
            $sql.="  erro varchar(240) ,";
            $sql.="  valor float DEFAULT NULL ,";
            $sql.="  PRIMARY KEY (id)";
            if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
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
            if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
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
            if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
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
            if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
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
          _db_upd_createAuditingTrackTable();
          _db_upd_newVersion(10);
        } catch (Exception $e) {
          _db_upd_error($e->getMessage());
        }

      }

      if (_db_upd_canReviewVersion(11)) {
        _recordWastedTime("checking v11");

        try {
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
          _db_upd_newVersion(10);
        } catch(Exception $e) {
          _db_upd_error($e->getMessage());
        }

      }

      if(_db_upd_canReviewVersion(12)) {
        _recordWastedTime("checking v12");
        try {
          if (!db_tableExists('is_tasks')) {
            $sql="CREATE TABLE  is_tasks (
                                id INT NOT NULL ,
                                creation_ts INT,
                                finalization_ts INT ,
                                iteraction_ts INT ,
                                iteraction_ttl INT DEFAULT 480,
                                stage INT ,
                                priority INT ,
                                mru INT DEFAULT 0,
                                s VARCHAR(48) ,
                                a VARCHAR(48) ,
                                xq_start INT ,
                                xq_target INT ,
                                j_params VARCHAR(512) ,
                                PRIMARY KEY (id))";
            db_sql($sql);
            db_commit();
          }
          _db_upd_newVersion(12);
        } catch(Exception $e) {
          _db_upd_error($e->getMessage());
        }
      }

      if(_db_upd_canReviewVersion(13)) {
        _recordWastedTime("checking v13");
        try {
          if (db_tableExists("is_auditingTrack")) {
            if (db_tableExists("is_auditing_track")) {
              _die("'is_auditing_track' already exists.\nYeAPF db v.13 is trying to rename 'is_auditingTrack' to 'is_auditing_track'.\nYou need to solve this by yourself as it can have serious impact in your application\n");
            } else {
              if (db_connectionTypeIs(_FIREBIRD_)) {
                /*
                _db_upd_createAuditingTrackTable();
                db_close();
                sleep(5);
                db_reconnect();
                $sql="insert into is_auditing_track select * from is_auditingTrack";
                db_sql($sql);
                */
                $sql='SELECT count(*) FROM rdb$relations WHERE (rdb$relation_name = \'IS_AUDITING_TRACK\') AND (rdb$view_blr IS NOT NULL)';
                $cc=intval(db_sql($sql));
                if ($cc==0) {
                  $sql="create view is_auditing_track as select * from is_auditingTrack";
                  db_sql($sql);
                }
              } else if ((db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_))) {
                $sql="RENAME TABLE `is_auditingTrack` TO `is_auditing_track`";
                db_sql($sql);
              } else if (db_connectionTypeIs(_PGSQL_))  {
                $sql="alter table is_auditingTrack rename to is_auditing_track";
                db_sql($sql);
              }
            }
          }
          _db_upd_newVersion(13);
        } catch(Exception $e) {
          _db_upd_error($e->getMessage());
        }
      }

      if (_db_upd_canReviewVersion(14)) {
        _recordWastedTime("checking v14");
        try {
          if (db_tableExists("wbp_buffer")) {
            if (!db_fieldExists("wbp_buffer", "o")) {
              $sql="ALTER TABLE wbp_buffer ADD o Integer";
              db_sql($sql);
            }
            if (!db_fieldExists("wbp_buffer", "queue")) {
              $sql="ALTER TABLE wbp_buffer ADD queue char(32)";
              db_sql($sql);
            }

            if (!db_tableExists("wbp_queue")) {
              $sql="CREATE TABLE wbp_queue (
                      id char(32) NOT NULL,
                      device char(32) NOT NULL,
                      printer char(32) NOT NULL,
                      creationDate integer NOT NULL,
                      queueSize smallint NOT NULL,
                      status integer NOT NULL
                    )";
              db_sql($sql);
            }
          } else {
            _die("'wbp_printers' table not found");
          }
          _db_upd_newVersion(14);
        } catch(Exception $e) {
          _db_upd_error($e->getMessage());
        }

      }

      if (_db_upd_canReviewVersion(15)) {
        _recordWastedTime("checking v15");
        try {
          if (!db_tableExists("is_server_control")) {
            $sql="create table is_server_control(
              serverKey char(16) not null,
              enabled char(1) default 'N',
              last_verification integer
            )";
            db_sql("$sql");
          }

          if (!db_tableExists("is_node_control")) {
            $sql="create table is_node_control(
              serverKey char(16) not null,
              nodePrefix char(3) not null,
              enabled char(1) default 'N',
              internal_ip char(15),
              external_ip char(15),
              reverse_ip varchar(255),
              last_verification integer
            )";
            db_sql("$sql");
            $sql="create index idx_node_control on is_node_control(serverKey, nodePrefix)";
            db_sql($sql);
          }

          if (!db_tableExists("is_sequence")) {
            $sql="CREATE TABLE is_sequence (
                    nodePrefix char(3) NOT NULL,
                    segment char(4) NOT NULL,
                    seq_value bigint NOT NULL
                  )";
            db_sql($sql);
            $sql="create index idx_sequence on is_sequence(nodePrefix, segment)";
            db_sql($sql);
          }

          if (!db_tableExists("is_segment_control")) {
            /* nodeController side */
            $sql="CREATE TABLE is_segment_control (
                    serverKey char(16) NOT NULL,
                    nodePrefix char(3) NOT NULL,
                    identity char(32) NULL,
                    segment char(4) not null,
                    creation char(14) not null,
                    regulation char(14)
                  )";
            db_sql($sql);
            db_sql("ALTER TABLE is_segment_control ADD PRIMARY KEY ( serverKey , nodePrefix, segment )");
            if (db_connectionTypeIs(_FIREBIRD_) || db_connectionTypeIs(_PGSQL_)) {
              db_sql("create unique index ndxIdentity on is_segment_control (identity)");
            } else {
              db_sql("ALTER TABLE is_segment_control ADD UNIQUE ndxIdentity ( identity )");
            }
          }

          if (!db_tableExists("is_segment_reservation")) {
            /* nodeApp side */
            $sql="CREATE TABLE is_segment_reservation (
                    serverKey char(16) NOT NULL,
                    nodePrefix char(3) NOT NULL,
                    identity char(32) NULL,
                    segment char(4) not null,
                    request char(14) not null,
                    regulation char(14) null,
                    regulation_message char(48) null
                  )";
            db_sql($sql);
            db_sql("ALTER TABLE is_segment_reservation ADD PRIMARY KEY ( serverKey , nodePrefix, segment )");
            if (db_connectionTypeIs(_FIREBIRD_) || db_connectionTypeIs(_PGSQL_)) {
              db_sql("create unique index ndxIdentity on is_segment_reservation (identity)");
            } else {
              db_sql("ALTER TABLE is_segment_reservation ADD UNIQUE ndxIdentity ( identity )");
            }
          }

          _db_upd_newVersion(15);
        } catch(Exception $e) {
          _db_upd_error($e->getMessage());
        }
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
