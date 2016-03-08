<?php
/*
    includes/yeapf.sql.php
    YeAPF 0.8.48-1 built on 2016-03-07 13:46 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-01-23 22:00:40 (-3 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  function dateSQL2timestamp($dd, $forceInternalFormat=false)
  {

    $dd=ereg_replace("[^0-9]", "", $dd);
    // echo ".:$dd:.<br>";
    while (strlen($dd)<14)
      $dd.='0';
    if ((db_connectionTypeIs(_MYSQL_)) || ($forceInternalFormat)) {
      $d=intval(substr($dd,6,2));
      $m=intval(substr($dd,4,2));
      $a=intval(substr($dd,0,4));
    } else {
      $d=intval(substr($dd,2,2));
      $m=intval(substr($dd,0,2));
      $a=intval(substr($dd,4,4));
    }
    $hh=substr($dd,8,2);
    $mm=substr($dd,10,2);
    $ss=substr($dd,12,2);
    $dataAux = mktime($hh,$mm,$ss,$m,$d,$a);
    _dumpY(4,5,"$dd dia: $d mes: $m ano: $a hora: $hh:$mm:$ss = $dataAux");
    return $dataAux;
  }

  function valorDecimal($valor)
  {
    $valor = str_replace('.','', $valor);
    $valor = str_replace(',','.', $valor);
    if ($valor=='')
      $valor='0';
    return $valor;
  }

  function valorGringo($valor)
  {
    $valor = str_replace(',','', $valor);
    $valor = str_replace('.',',', $valor);
    if ($valor=='')
      $valor='0';
    return $valor;
  }

  function publicarPOST($debugging=false, $publish=true,$varPrefix='')
  {
    global $_POST, $_REQUEST;
    $ret='';

    _dump("***********************************************************************************");
    _dump("OBSOLETE - Your app is using an obsolete funcion.  Use 'publishFormRequest' instead");
    _dump("***********************************************************************************");

    foreach($_POST as $n => $v) {
      $v=RFC_3986($v);
      _dumpY(1,2,"$n=$v");
      if (substr($n,0,strlen($varPrefix))==$varPrefix) {
        if ($ret>'')
          $ret.='&';
        $ret.="$n=$v";
      }
      if ($publish)
        $GLOBALS[$n]=$v;
    }

    if (isset($_REQUEST)) {
      foreach($_REQUEST as $n=>$v) {
        $v=RFC_3986($v);
        _dumpY(1,2,"$n=$v");
        if (!in_array($n,$GLOBALS)) {
          if ($debugging)
            echo "$n=$v<br>";
          if (substr($n,0,strlen($varPrefix))==$varPrefix) {
            if ($ret>'')
              $ret.='&';
            $ret.="$n=$v";
          }
          if ($publish)
            $GLOBALS[$n]=$v;
        }
      }
    }

    return ($ret);
  }

  function publishFormRequest(&$outputArray, $overrideValues=true, $varPrefix='')
  {
    global $_POST, $_REQUEST;

    if (isset($_REQUEST))
      $aux = array_merge($_POST, $_REQUEST);
    else
      $aux = $_POST;

    foreach($aux as $n => $v) {
      $v=RFC_3986($v);
      _dumpY(1,2,"$n=$v");
      if (substr($n,0,strlen($varPrefix))==$varPrefix) {
        $varExists=(!in_array($n,$outputArray));
        if ((!$varExists) || ($overrideValues))
          $outputArray[$n] = $v;
      }
    }

    return ($outputArray);
  }

  function publishArray($arr)
  {
    foreach($arr as $k=>$v)
      if (!is_numeric($k))
        $GLOBALS[$k]=$v;
  }

  function publicarResultadoSQL($sql)
  {
    publishSQL($sql);
  }

  function publishSQL($sql, $publishAsLowerCaseKeyToo=true,
                      $fieldPrefix='', $fieldPostfix='')
  {
    global $lastCommands, $sqlCount;

    $fieldPrefix=trim($fieldPrefix);
    $fieldPostfix=trim($fieldPostfix);

    $ret=array();


    $rs=db_query($sql);
    if (!$rs)
      _dump("ERRO sql: $sql<br>");
    //$sqlCount++;
    //$lastCommands.="$sqlCount) $sql;<BR>";
    if ((strtoupper(substr($sql,0,6))=='SELECT') and ($rs)) {
      $i = 0;
      $dados=db_fetch_array($rs);
      if ($dados)
        foreach($dados as $nomeCampo=>$valorCampo) {
          if (!is_numeric($nomeCampo)) {
            if (!((strtoupper($nomeCampo)=='U') || (strtoupper($nomeCampo)=='A') || (strtoupper($nomeCampo)=='S'))) {
              $nomeCampo=$fieldPrefix.$nomeCampo.$fieldPostfix;
              /*
              echo "$nomeCampo = ";
              echo $dados[$nomeCampo];
              echo "<br>";
              */
              // $valorCampo=RFC_3986($valorCampo);
              $GLOBALS[$nomeCampo]=$valorCampo;
              if ($publishAsLowerCaseKeyToo)
                $GLOBALS[strtolower($nomeCampo)]=$valorCampo;

              $ret[$nomeCampo]=$valorCampo;
              if ($publishAsLowerCaseKeyToo)
                $ret[strtolower($nomeCampo)]=$valorCampo;

              $i++;
            }
          }
        }
      db_free($rs);
    }
    return $ret;
  }

  function doSystemLog($asql='')
  {
    global $ydb_conn, $u, $s, $a,  $sqlCount, $SQLLog,
            $usrTableName, $usrSessionIDField;;

    if ($SQLLog) {
      if ((strpos($asql,'is_')===false) and (strpos($asql,'IBS_')===false) and (strpos($asql,'IBS$')===false)) {
        // echo "$sql<br>";

        $u=intval($u);

        $unique_id=valorSQL("select id from $usrTableName where $usrSessionIDField='$u'", $ydb_conn);
        $context='';

        $sql="select varName,varValue from is_context where userID='$u'";

        // echo "$sqlCount) $sql<br>";

        if (db_connectionTypeIs(_MYSQL_))
          $q = mysql_query($sql,$ydb_conn);
        else
          $q = ibase_query($ydb_conn, $sql);

        if ($q) {
          $fetch_func=db_fetch('row');
          while ($r=$fetch_func($q)) {
            if ($context>'')
              $context.=', ';
            $context.="$r[0]='$r[1]'";
          }
          /*
          try {
            while ($r=$fetch_func($q)) {
              if ($context>'')
                $context.=', ';
              $context.="$r[0]='$r[1]'";
            }
          } catch (Exception $_E_) {
              showDebugBackTrace($_E_->getMessage(), true);
          }
          */

          if ($toDebug)
            echo "{ $context }<br>";
        }


        $context=str_replace("'","\'",$context);
        $asql=str_replace("'",'"',$asql);
        $asql=addslashes($asql);
        $id=y_uniqid();
        $dt=date("YmdHis");
        if (db_connectionTypeIs(_FIREBIRD_))
          $__SQL__="insert into is_system_log (id, dt, id_user, s, a, context, isql) values ('$id', '$dt', '$unique_id', '$s', '$a', '$context', '$asql')";
        else
          $__SQL__="insert into is_system_log (id, dt, user, s, a, context, isql) values ('$id', '$dt', '$unique_id', '$s', '$a', '$context', '$asql')";
        // echo "$__SQL__<br>";

        if (db_connectionTypeIs(_MYSQL_))
          mysql_query($__SQL__,$ydb_conn);
        else
          ibase_query($ydb_conn, $__SQL__);
      }
    }
  }

  function sqlProducer($sql, $quoting=false, $splitting='', $minLen=3,
                       $defaultValue='', $aRegExp='', $forcedValue='')
  {
    $toDebug=false;
    if ($toDebug)
      echo "sql=$sql<br>quoting=$quoting<br>splitting=$splitting<br>minLen=$minLen<br>defaultValue=$defaultVale";

    $rs=db_query($sql);
    $r=$forcedValue;
    if ($rs) {
      $aOut=array();
      while ($dados=db_fetch_row($rs)) {
        if ($splitting>'') {
          if ($toDebug)
            echo "<br>Separando '$dados[0]' por $splitting<br>";
          // $aux=ereg_replace("[^A-Z],\$splitting", " ",$dados[0]);
          // $aux=preg_replace('/[\W]/',' ',$dados[0]);
          $aux=preg_replace("/[^a-zA-Z«√’¡…Õ”⁄¿»Ã“ŸÁ„ı·ÈÌÛ˙‡ËÏÚ˘\-0-9]/",' ',$dados[0]);
          $values=explode($splitting,$aux);
        } else
          $values=array($dados[0]);
        foreach($values as $value) {
          $value=trim(toUpper($value));
          $valueLines=explode("\n",$value);
          foreach($valueLines as $valueItem) {
            // $valueItem=ereg_replace("[^A-Za-z0-9\-]", "",$valueItem);
            // $valueItem=preg_replace('/[\W0-9]/','',$valueItem);
            $valueItem=preg_replace('/$aRegExp/','',$valueItem);
            if ($toDebug)
              echo '&nbsp;&nbsp;'.$valueItem.'<BR>';
            if (strlen($valueItem)>=$minLen)
              if (trim($aRegExp)>'') {
                if (preg_match("/$aRegExp/", $valueItem)>0)
                  array_push($aOut,$valueItem);
              } else
                array_push($aOut,$valueItem);
          }
        }
      }
      db_free($rs);

      if ($splitting>'') {
        if ($toDebug)
          echo "<BR>Agrupando";
        asort($aOut);
        $aOut=array_unique($aOut);
      }
      foreach($aOut as $value) {
        if($toDebug)
          echo "<br>$value";
        if ($r>'')
          $r.=', ';
        if ((!is_numeric($value)) && ($quoting))
          $value="'$value'";
        $r.=$value;
      }

    }
    if (($r=='') && ($defaultValue>''))
      $r=$defaultValue;

    if ($quoting)
      if ($r=='')
        $r="''";

    $r="($r)";
    if ($toDebug)
      echo "result= $r<br>";
    return $r;
  }

  function fazerSQL($sql, $conn=FALSE)
  {
    return do_sql($sql, $conn);
  }

  function do_sql($sql, $conn=FALSE)
  {
    global $ydb_conn;

    if ($sql>'') {
      if ($conn==FALSE)
        $conn=$ydb_conn;

      $rs=db_query($sql);
      if (!$rs) {
        _recordError("Erro ao fazer <i>$sql</i> ");
        _recordError(db_lasterror());

        _echo("Erro ao fazer $sql");
      }

      return $rs;
    } else
      return null;
  }

  function doCacheSQL($sql)
  {
    global $_sqlCache;

    $found=0;
    _dumpY(4,1,"Search $sql started");
    foreach($_sqlCache as $query) {
      _dumpY(4,2,"% $query[0]");
      if ($query[0]==$sql) {
        $res=$query[1];
        _dumpY(4,2,"cached!");
        $found=1;
        break;
      }
    }

    _dumpY(4,1,"Search $sql stopped");

    if ($found==0) {
      $res=valorSQL($sql);
      array_push($_sqlCache,array($sql, $res));
      _dumpY(4,1,"$sql pushed");
    }
    return $res;
  }

  function valorSQL($sql, $asRow=true)
  {
    return db_sql($sql, $asRow);
  }

  define('_FIELDS_AND', 1);
  define('_WORDS_AND',  4);
  define('_USE_WILDCARDS', 16);

  function buildSQLfilter($words, $fields, $andJunction=true, $andWords=false, $useWilcards=true, $considerFirstAsNaturalKey=false)
  {
    global $prepositions;
    /*
    if (db_connectionTypeIs(_MYSQL_))
      $words = rawChars($words);
    */

    $words=' '.trim($words).' ';
    $words=str_replace(',',' ',$words);
    foreach($prepositions as $prep)
      $words=str_replace(" $prep ", ' ', $words);
    if (db_connectionTypeIs(_FIREBIRD_))
      $words=toUpper($words);

    if (strpos(" $fields",";")>0)
      $c=explode(';',$fields);
    else
      $c=explode(',',$fields);
    $sql='';

    if ($words>'') {
      $W=explode(' ',$words);
      if (count($W)<=1)
        $andJunction=false;
      foreach($c as $vc) {
        $sql1='';
        if (db_connectionTypeIs(_FIREBIRD_))
          $vc="UPPER($vc)";
        $firstWord=true;
        foreach($W as $vf) {
          if ((is_numeric($vf)) || (strlen($vf)>2)) {
            if ($sql1>'') {
              if (!$andWords)
                $sql1.=' or ';
              else
                $sql1.=' and ';
            }
            if (is_numeric($vf))
              $sql1.="($vc='$vf')";
            else {
              if ($useWilcards) {
                if ($firstWord && $considerFirstAsNaturalKey)
                  $sql1.="($vc like '$vf%')";
                else
                  $sql1.="($vc like '%$vf%')";
              } else {
                $sql1.="(($vc ='$vf') ";
                $sql1.="or ($vc like '% $vf') ";
                $sql1.="or ($vc like '$vf %') ";
                $sql1.="or ($vc like '% $vf %'))";
              }
            }
          }
          $firstWord=false;
        }

        if ($sql1>'') {
          $sql1="($sql1)";
          if ($sql>'') {
            if ($andJunction)
              $sql.=' and ';
            else
              $sql.=' or ';
          }
          $sql.=$sql1;
        }
      }
    } else {
      foreach($c as $vc) {
        if ($sql>'')
          $sql.=' and ';
        $sql.="($vc=$vc)";
      }
      $sql="($sql)";
    }
    // echo "<hr>$sql<hr>";
    return $sql;
  }

  function montarComandoSQL($verbo, $tabela, $campos)
  {
    $verbo=strtolower($verbo);
    $r=$verbo;
    if ($verbo=='insert') {
      $r.=" into $tabela (";
      $p=0;
      foreach($campos as $c) {
        if ($p++>0)
          $r.=', ';
        $r.=$c;
      }
      $r.=') values (';

      $p=0;
      foreach($campos as $c) {
        if ($p++>0)
          $r.=', ';
        $r.="'$$c'";
      }
      $r.=')';
    } else if ($verbo=='update') {
      $r.=" $tabela set ";
      $p=0;
      foreach($campos as $c) {
        if ($p++>0)
          $r.=', ';
        $r.="$c='$$c'";
      }
    } else
      die("$verbo n„o È um verbo reconhecido no SQL");

    return $r;
  }

?>
