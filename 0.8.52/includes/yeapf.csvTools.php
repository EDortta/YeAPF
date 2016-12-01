<?php
  /*
    includes/yeapf.csvTools.php
    YeAPF 0.8.52-107 built on 2016-12-01 07:30 (-2 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-05-30 09:45:48 (-2 DST)
   */

  _recordWastedTime("Gotcha! ".$dbgErrorCount++);


  /*
   * csvImport
   *         callBack            - function - params: (errorLevel: 0-info, 1-warning, 2-fatalerror; errorMessage: string)
   *         dbTable             - string - database table to be created/changed/updated
   *         doImportData        - boolean - true: import data
   *         doDeleteEmptyFields - boolean - true: check for empty fields to delete them after import action
   *         doModifyStructure   - boolean - true: modify the existent table
   */

  function db_sql_echo($callBack, $sql) {
    $callBack(-1, "$sql;\n");
    db_sql($sql);
  }

  function csvImport($callBack, $dbTable, $keys, $csvName,
                     $firstRow=0, $rowCount=-1,
                     $doImportData=true, $doForceCreateTable=true, $doDeleteEmptyFields=false, $doModifyStructure=true,
                     $targetCharset="UTF-8", $commitCount=1000,
                     $enumerateRows=false) {
    global $targetCharset;

    if (!file_exists($csvName))
      $csvName="$csvName.csv";

    _dump("Importing $csvName");

    if (file_exists($csvName)) {

      $f=fopen($csvName,"r");
      $camposCriados=array();
      $campos=array();
      $importar=array();
      $tamanho=array();
      $tipos=array();
      $tamMax=array();
      $mediaTamanho=array();
      $l=0;
      $importedLines=0;

      function validFieldName($fieldName)
      {
        $fieldName=rawChars($fieldName);
        $fieldName=str_replace('/','',$fieldName);
        $fieldName=str_replace('-','_',$fieldName);
        $fieldName=str_replace('(','_',$fieldName);
        $fieldName=str_replace(')','_',$fieldName);
        $fieldName=suggestVarName($fieldName);
        return $fieldName;
      }

      function z($v, $l=2)
      {
        while (strlen($v)<$l)
          $v="0$v";
        return $v;
      }

      $dbTable=validFieldName($dbTable);
      if ($dbTable>'') {

        $newTableCreated=true;

        if ($doImportData)
          $aVerb='imported';
        else
          $aVerb='analised';

        $canCheckTable=true;

        while ((!feof($f)) && (($importedLines<$rowCount) || ($rowCount==-1))) {
          $line=chop(fgets($f,8192));
          if ($line>'') {
            // CHECK THE DATA CONSISTENCY
            // The line must contain the same number of columns as in the header
            // as sometimes the line could be splitted into more than one, we try
            // loading the next line and gluing with the current one
            if ($l>0) {
              // if we need to enumerate file rows, we need to add 'rowno' column value
              if ($enumerateRows)
                $line=$importedLines.';'.$line;

              $fieldInLine=0;
              while ($fieldInLine<$fieldCount) {
                $fieldInLine=0;
                $aux=$line;
                while ($aux>'') {
                  $teste=getNextValue($aux,';',false);
                  $fieldInLine++;
                  // echo "$fieldInLine/$fieldCount) $teste\n";
                }

                if ($fieldInLine<$fieldCount)
                  $line.='\n'.chop(fgets($f,8192));
              }
            }
            if ($targetCharset)
              $line=mb_convert_encoding($line,$targetCharset,mb_detect_encoding($line,
              array('utf-8', 'iso-8859-1', 'iso-8859-15', 'windows-1251')));
            $line=str_replace('\\'.'n',"\n",$line);
            $line=str_replace('\\'.'t',"\t",$line);
            $line=str_replace('\\'.'r',"\r",$line);
            if ($l==0) {
              $myTableExists=db_tableExists("$dbTable");
              if ($doForceCreateTable) {
                if (db_tableExists($dbTable)) {
                  $callBack(0,"-- Dropping table $dbTable\n");
                  db_sql_echo($callBack,"drop table $dbTable");
                  db_sql_echo($callBack,"commit");
                  $myTableExists=false;
                }
              }


              if (!$myTableExists) {
                if ($doModifyStructure) {
                  $callBack(0,"-- Creating $dbTable\n");
                  db_sql_echo($callBack,"create table $dbTable (id char(32))");
                  db_sql_echo($callBack,"commit");
                  if (!db_tableExists("$dbTable")) {
                    $canCheckTable=false;
                    $callBack(2,"ERRO: Table '$dbTable' could not be created");
                    break;
                  }
                  $newTableCreated=true;

                } else
                  $callBack(2,"ERROR: table '$dbTable' don't exists");
              }
              if ($doImportData)
                $callBack(0,"-- Importing data\n");
              else
                $callBack(0,"-- Analysing data\n");

              if ($enumerateRows)
                $line='rowno;'.$line;
              // die ("[ $line ]\n");

              $n=0;
              do {
                $nomeCampo=getNextValue($line,';');
                $nomeCampo=unquote($nomeCampo);
                $nomeCampo=validFieldName($nomeCampo);
                if ($nomeCampo>'') {
                  // headers with null or empty field names needs to be renamed
                  if (strtoupper($nomeCampo)=='NULL')
                    $nomeCampo='null_'.$n;

                  if (strtoupper($nomeCampo)!=$nomeCampo)
                    $nomeCampo=strtolower(substr($nomeCampo,0,1)).substr($nomeCampo,1);

                  if (!in_array($nomeCampo,$campos)) {
                    array_push($campos,$nomeCampo);
                    array_push($tamanho,120);
                    array_push($tipos,-1);     // o melhor tipo será definido na volta completa
                    array_push($tamMax,0);
                    array_push($mediaTamanho,0);
                    array_push($importar,true);
                  }

                  if (!db_fieldExists("$dbTable", $nomeCampo)) {
                    $callBack(0,"-- Creating $dbTable.$nomeCampo\n");
                    array_push($camposCriados, $nomeCampo);
                    if ($doModifyStructure) {
                      db_sql_echo($callBack,"alter table $dbTable add $nomeCampo varchar(120)");
                      db_sql_echo($callBack,"commit");
                    } else {
                      $callBack(0,"-- Warning: Field '$nomeCampo' dont exists on '$dbTable' and will not be created\n");
                      $importar[count($importar)-1]=false;
                    }
                  }

                  $n++;
                }
              } while ($line>'');

              $fieldCount=count($campos);

              if ($newTableCreated) {
                $auxN=-1;
                for($k1=0; $k1<count($campos); $k1++)
                  if (strtoupper($campos[$k1])=='ID')
                    $auxN=$k1;
                if ($auxN==-1) {
                  array_push($campos,'id');
                  array_push($tamanho,32);
                  array_push($tipos,4);
                  array_push($tamMax,0);
                  array_push($mediaTamanho,0);
                  array_push($importar,true);
                  // array_push($camposCriados, 'id');
                } else {
                  $tamanho[$auxN]=32;
                  $camposCriados[$auxN]=true;
                }
              }


            } else {
              if (($l>=$firstRow) && (($importedLines<=$rowCount) || ($rowCount==-1))) {
                if ($importedLines % 100==0)
                  $callBack(0,"-- $importedLines lines $aVerb\n");
                $importedLines++;

                $currentLine=$line;

                $valores=array();
                $n=0;
                $whereClause='';
                $updateSQLStatement='';

                // echo "------------------------------------------\n$line\n";

                do {
                  $valorCampo=getNextValue($line,';',false);
                  $valorCampo=unquote($valorCampo);

                  if ($n<count($campos)) {

                    if ($importar[$n]) {

                      if (strtolower($campos[$n])=='id')
                        if ($valorCampo=='')
                          $valorCampo=y_uniqid();

                      $tIndex=typeIndex($valorCampo, true);
                      if ((!isset($tipos[$n])) || ($tIndex>$tipos[$n])) {
                        // echo "\n\n\t ".$campos[$n]." ($n '$valorCampo') -> $tIndex\n\n\n";
                        $tipos[$n]=$tIndex;
                      }

                      if (preg_match("/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{2,4})/", $valorCampo, $matches)) {
                        $horario=trim(substr($valorCampo,strlen($matches[0])));
                        $yy=z($matches[3],4); if (intval($yy)<1900) $yy=2000+$yy;
                        $mm=z($matches[2],2);
                        $dd=z($matches[1],2);

                        $hora=getNextValue($horario,':');
                        $min=getNextValue($horario,':');
                        $seg=getNextValue($horario,':');
                        $valorCampo="$yy$mm$dd$hora$min$seg";

                        $tipos[$n]=4;
                      } else
                        if ($tipos[$n]==2)
                          $valorCampo=intval($valorCampo);

                      $valores[$n]=trim($valorCampo);

                      if ((!isset($tamMax[$n])) ||  (strlen($valorCampo)>$tamMax[$n]))
                        $tamMax[$n]=strlen($valorCampo);

                      if (!isset($mediaTamanho[$n]))
                        $mediaTamanho[$n]=0;
                      $mediaTamanho[$n]+=strlen($valorCampo);


                      /*
                      echo "\n".$campos[$n].' t:'.$tipos[$n];
                      echo " - ".$tamanho[$n];
                      echo " - ".strlen($valorCampo)."\n";
                      */

                      if ((!isset($tamanho[$n])) || (strlen($valorCampo)>$tamanho[$n])) {
                        $tamanho[$n]=floor(strlen($valorCampo)*1.1);
                        if ($doModifyStructure) {
                          if (in_array($campos[$n], $camposCriados)) {
                            $callBack(0,"-- Refactoring $campos[$n] to hold $tamanho[$n] characters\n");
                            if (db_connectionTypeIs(_FIREBIRD_)) {
                              db_sql_echo($callBack,"alter table $dbTable alter $campos[$n] type varchar($tamanho[$n])");
                            } else if (db_connectionTypeIs(_MYSQL_)) {
                              db_sql_echo($callBack,"alter table $dbTable modify $campos[$n] varchar($tamanho[$n])");
                            } else {
                              db_sql_echo($callBack,"alter table $dbTable alter column $campos[$n] type character varying($tamanho[$n])");
                            }
                            db_sql_echo($callBack,"commit");
                          }
                        }
                      }

                    }
                    $n++;
                  } else
                    if (trim($valorCampo)>'') {
                      $errAux ="There are only ".count($campos)." fields\n";
                      $errAux.="But there are more info to be read at line #$l \n";
                      $errAux.="Current not processed value: [ $valorCampo ]\nLine remaining: [ $line ]";
                      foreach($campos as $k=>$v) {
                        $errAux.="\n\t$v ($k) = ".$valores[$k];
                      }
                      $errAux.="\n[ $currentLine ]";
                      $callBack(2,"*** Syncronism error\n".$errAux);
                    }
                } while ($line>'');


                $sqlF='';
                $sqlV='';
                for($n=0; $n<count($campos); $n++) {
                  if ($importar[$n]) {
                    if ($n>0) {
                      $sqlF.=', ';
                      $sqlV.=', ';
                    }
                    $sqlF.=$campos[$n];
                    if (isset($valores[$n])) {
                      $v=$valores[$n];
                      if ($v=='')
                        $v='NULL';
                    } else if ($campos[$n]=='id')
                      $v=md5('ydbmigrate'.y_uniqid());
                    else
                      $v='NULL';

                    $v=str_replace("'",chr(44),$v);
                    $v=str_replace(chr(92),chr(92).chr(92),$v);
                    if (($tipos[$n]==4) and (strtoupper($v)!='NULL'))
                      $v="'".$v."'";

                    $sqlV.=$v;


                    if (in_array( strtolower($campos[$n]), $keys)) {
                      if ($whereClause>'')
                        $whereClause.=' AND ';
                      $whereClause.=$campos[$n].' = '.$v;
                    } else {
                      if ($updateSQLStatement>'')
                        $updateSQLStatement.=', ';
                      $updateSQLStatement.=$campos[$n];
                      $updateSQLStatement.='='.$v;
                    }
                  }
                }

                $sql="INSERT INTO $dbTable ($sqlF) values ($sqlV)";

                if ($whereClause>'') {
                  $cc=db_sql("select count(*) from $dbTable where $whereClause");
                  if ($cc>0) {
                    $sql="UPDATE $dbTable set $updateSQLStatement where $whereClause";
                  }
                }

                if ($doImportData)
                  db_sql_echo($callBack,$sql);

                if ($importedLines % $commitCount==0) {
                  db_sql_echo($callBack,"commit");
                }
              }
            }
            $l++;
          }
        }

        if ($canCheckTable) {

          $callBack(0,"-- $importedLines lines $aVerb at all\n");

          fclose($f);

          db_sql_echo($callBack,'commit');

          if ($doModifyStructure) {

            $callBack(0,"-- Altering table structure\n");

            for ($n=0; $n<count($campos); $n++) {
              if (in_array($campos[$n], $camposCriados)) {
                $sql='';
                $tamMax[$n]=intval($tamMax[$n]);
                if (($tipos[$n]==2) || ($tipos[$n]==3)) {
                  if ($tamMax[$n]>5)
                    $tipos[$n]=4;
                }
                if ($tamMax[$n]>255)
                  $tipos[$n]=4;
                $callBack(0,'-- '.$campos[$n]." is defined as ".$tipos[$n].".  maxLength: ".$tamMax[$n]."\n");

                switch ($tipos[$n])
                {
                  case 0:
                  case 4:
                    $novoTipo="varchar($tamMax[$n])";
                    $mediaTamanho[$n]=$mediaTamanho[$n] / $l;
                    if (db_connectionTypeIs(_MYSQL_))
                      if ((($tamMax[$n]<48) || (($mediaTamanho[$n] * 100 / $tamMax[$n])<=10)) && ($tamMax[$n]<255))
                        $novoTipo="char($tamMax[$n])";
                    break;
                  case 1:
                    if (db_connectionTypeIs(_MYSQL_))
                      $novoTipo="char($tamMax[$n])";
                    else
                      $novoTipo="varchar($tamMax[$n])";
                    break;
                  case 2: $novoTipo="integer"; break;
                  case 3:
                    if (db_connectionTypeIs(_PGSQL_))
                      $novoTipo="decimal";
                    else if (db_connectionTypeIs(_MYSQL_))
                      $novoTipo="double";
                    break;
                }

                if ($tamMax[$n]==0) {
                  if ($doDeleteEmptyFields) {
                    $callBack(0,'-- '."Droping $campos[$n]\n");
                    $sql="alter table $dbTable drop $campos[$n]";
                  }
                } else {
                  $novoNome=$campos[$n];
                  if ($novoNome==strtoupper($novoNome))
                    $novoNome=strtolower($novoNome);

                  if (db_connectionTypeIs(_MYSQL_)) {
                    $callBack(0,'-- '."Refactoring $campos[$n] to $novoNome as $novoTipo\n");
                    $sql="alter table $dbTable change $campos[$n] $novoNome $novoTipo";

                  } else if ((db_connectionTypeIs(_PGSQL_)) || (db_connectionTypeIs(_FIREBIRD_)))  {
                    $campoAuxiliar="xx_$novoNome";

                    $callBack(0,'-- '."Creating auxiliar field $campoAuxiliar as $novoTipo\n");
                    db_sql_echo($callBack,"alter table $dbTable add $campoAuxiliar $novoTipo");
                    db_sql_echo($callBack,"commit");
                    $auxSQL="update $dbTable set $campoAuxiliar=cast($campos[$n] as $novoTipo)";
                    if (($tipos[$n]==2) || ($tipos[$n]==3))
                      $auxSQL.=" where $campos[$n]>''";

                    $callBack(0,'-- '."Copying from $campos[$n] to $campoAuxiliar\n");
                    db_sql_echo($callBack,"$auxSQL");
                    db_sql_echo($callBack,"commit");

                    $callBack(0,'-- '."Droping $campos[$n]\n");
                    db_sql_echo($callBack,"alter table $dbTable drop $campos[$n]");

                    if (db_connectionTypeIs(_FIREBIRD_))
                      $sql="alter table $dbTable alter column $campoAuxiliar to $novoNome";
                    else
                      $sql="alter table $dbTable rename $campoAuxiliar to $novoNome";
                    $callBack(0,'-- '."Renaming $campoAuxiliar to $novoNome\n");

                  }
                }
                if ($sql>'') {
                  db_sql_echo($callBack,$sql);
                }
              }
            }
            db_sql_echo($callBack,"commit");
          }
        }
      } else
        $callBack(2,"ERROR: You need to supply a table name");
    } else
      $callBack(2,"ERROR: File '$csvName' don't exists");
  }
?>
