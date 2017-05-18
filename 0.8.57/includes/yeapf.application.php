<?php
/*
    includes/yeapf.application.php
    YeAPF 0.8.57-14 built on 2017-05-18 16:40 (-3 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2017-05-18 10:39:24 (-3 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  $yHtml_Header=false;
  $yHtml_CSS=false;
  $yHtml_Body=false;
  $yHtml_Footer=false;

  function closeCurrentWindow()
  {
    echo "<script>window.close();</script>";
  }

  function redirectOpener($s, $a, $url='')
  {
    global $u;

    if ($url>'') {
      if ((substr($url,0,1)=='?') || (substr($url,0,1)=='&'))
        $url=substr($url,1);
      $urlItems=explode('&',$url);
      $url='';
      foreach($urlItems as $ui) {
        $ui=explode('=', $ui);
        $k=$ui[0];
        $v=$ui[1];
        if (!((strtolower($k)=='s') || (strtolower($k)=='a') || (strtolower($k)=='u'))) {
          if ($url>'')
            $url.='&';
          $url.="$k=$v";
        }
      }
      _dumpY(16,0,"Redirecting opener to '$url'");
    }
    echo "<script>window.opener.document.location = '?u=$u&s=$s&a=$a&$url';</script>";
  }

  function _createHTMLHeader_($cssHeaderText)
  {
    global $yHtml_Header, $appCharset, $cfgAppLang, $appTitle;

    if (!$yHtml_Header) {
      $yHtml_Header=true;

      echo "<!DOCTYPE html>\n";
      if ($cfgAppLang>'')
        echo "<html lang='$cfgAppLang'>\n";
      echo "<head>\n";
      echo "<meta http-equiv='X-UA-Compatible' content='IE-edge,chrome=1' />\n";
      if ($appCharset>'')
        echo "<meta charset='$appCharset'>\n";
      else {
        $serverCharset = setlocale(LC_CTYPE, 0);
        if (strpos($serverCharset, '.')>0)
          $serverCharset = substr($serverCharset, strpos($serverCharset, '.')+1);
        echo "<meta charset='$serverCharset'>\n";
      }
      echo "<title>$appTitle</title>\n";
      echo "<meta http-equiv='X-UA-Compatible' content='IE=EmulateIE8' />\n";
      echo $cssHeaderText;
      /*
      echo "\n<!--[if lt IE 9]>\n\t<script src='http://html5shiv.googlecode.com/svn/trunk/html5.js'></script>\n<![endif]-->\n";
      //echo "<!-- $appName.$s.$a.$aBody (WoH:$withoutHeader WoB:$withoutBody)-->\n";
      echo "<script src=".bestName("yloader.js",1)."></script>\n";
      */
      echo "</head>\n";
    }
  }

  /*
   * Load CSS if it is not an CLI application
   * Create HTML header
   */
  function initOutput()
  {
    global $appName, $a, $s, $aBody, $withoutBody, $withoutHeader, $isTablet;

    $cssHeaderText='';

    // echo "<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>\n";

    if ($isTablet)
      $device='mobile';
    else
      $device='desktop';
    _dumpY(16,1,"device: $device");

    // if there is a pre-configure css file, we try to load it
    // cfgUniversalCSS is loaded first and ever
    // cfgDesktopCSS ou cfgMobileCSS are loaded as the context indicates it
    $cssList=array();

    if (isset($GLOBALS["cfgUniversalCSS"]))
      if (file_exists($GLOBALS['cfgUniversalCSS']))
        array_push($cssList, $GLOBALS['cfgUniversalCSS']);

    $cfgVar="cfg".ucfirst($device)."CSS";
    if (isset($GLOBALS[$cfgVar]))
      if (file_exists($GLOBALS[$cfgVar]))
        array_push($cssList, $GLOBALS[$cfgVar]);

    $cssList=join(',', $cssList);

    if ($cssList=='') {
      $cwdName = basename(getcwd());
      $appBaseName=substr($cwdName,0,strpos($cwdName.'-','-'));
      /*
       * sequencia original de busca do CSS
      $cssNames = array("default", "default-$device",
                        "../$appName", "../$appName-$device",
                        "$appName/$appName", "$appName/$appName-$device",
                        "includes/default", "includes/default-$device",
                        "includes/$appName", "includes/$appName-$device",
                        "$appName", "$appName-$device",
                        "$appBaseName", "$appBaseName-$device",
                        "$cwdName", "$cwdName-$device");
      */
      $cssBaseNames = array ("default", "$appBaseName", "$appName", "$cwdName");
      _dumpY(16,0,$_SERVER['HTTP_USER_AGENT']);
      foreach($cssBaseNames as $cssBaseName) {
        $cssNames = array("$cssBaseName", "$cssBaseName-$device",
                          "css/$cssBaseName", "css/$cssBaseName-$device",
                          "$appName/$cssBaseName", "$appName/$cssBaseName-$device",
                          "includes/$cssBaseName", "includes/$cssBaseName-$device",
                          "../$cssBaseName", "../$cssBaseName-$device");
        foreach($cssNames as $cssName) {
          if (file_exists("$cssName.min.css"))
            $cssName.='.min.css';
          else
            $cssName.='.css';
          _dumpY(16,4,"is an $cssName ?");
          if ( (file_exists($cssName)) &&
               (strpos($cssList,$cssName)===FALSE) )  {
            if (file_exists('version.inf'))
              $version='v='.join('',file('version.inf'));
            else
              $version=md5(date('U'));
            $cssHeaderText.="\n<link href='$cssName?$version' rel='stylesheet' type='text/css'>";
            if ($cssList>'')
              $cssList.=', ';
            $cssList.=$cssName;
          }
        }
      }
    }
    _dumpY(16,1,"CSS: ($cssList) (Tablet?: $isTablet)");
    if ($cssList=='')
      _dumpY(16,0,"Without CSS");

    _createHTMLHeader_($cssHeaderText);
    echo "\n<!-- START OF OUTPUT -->\n";
  }

  function finishOutput()
  {
    global $s, $a, $u;
    echo "\n<!-- END OF OUTPUT FOR '$s.$a'-->\n";
    _dumpY(16,1,"End Output");
  }

  $__messagesHandler = array();


  // this functions transform comma separated parameters and value
  // in an associative array
  function getQueryParameters($asGlobals=false)
  {
    _die("OBSOLETO, utilize 'xq_extractValuesFromQuery()' em lugar de 'getQueryParameters()'");
    /*
    $fieldName=unparentesis($GLOBALS['fieldName']);
    $fieldValue=unparentesis($GLOBALS['fieldValue']);
    $ret=array();
    while ($fieldName>'') {
      $aFieldName=getNextValue($fieldName,',');
      $aFieldValue=getNextValue($fieldValue,',');
      $ret[$aFieldName]=$aFieldValue;
      if ($asGlobals)
        $GLOBALS[$aFieldName]=$aFieldValue;
    }
    return $ret;
    */
  }

  function xq_addToFieldList(&$fieldList, $domainTable, $fieldToBeAdd)
  {
    $fieldToBeAdd = explode(';',$fieldToBeAdd);
    foreach($fieldToBeAdd as $fieldName) {
      if (!in_array($fieldName, $fieldList))
        if (db_fieldExists($domainTable, $fieldName))
          $fieldList[]=$fieldName;
    }
  }

  function xq_getFieldList($domainTable='', $exceptionList='', $prefix='', $posfix='')
  {
    $exceptionList = explode(';',$exceptionList);
    $fieldName = unparentesis($GLOBALS['fieldName']);
    $fieldList = explode(",",$fieldName);

    $ret=array();
    foreach($fieldList as $fieldName) {
      $originalFieldName = $fieldName;

      $fieldNameCanBeUsed = ($domainTable=='');

      if (($prefix>'') && (substr($fieldName,0,strlen($prefix))==$prefix)) {
        $fieldName  = substr($fieldName, strlen($prefix));
        if (!$fieldNameCanBeUsed) {
          $fieldNameCanBeUsed = db_fieldExists($domainTable, $fieldName);
          if (!$fieldNameCanBeUsed)
            $fieldName = lcfirst($fieldName);
        }
      }

      if (($posfix>'') && (substr($fieldName,strlen($fieldName)-strlen($posfix))==$posfix)) {
        $fieldName = substr($fieldName, 0, strlen($fieldName)-strlen($posfix));
      }

      if (!$fieldNameCanBeUsed)
        $fieldNameCanBeUsed = db_fieldExists($domainTable, $fieldName);

      if ( $fieldNameCanBeUsed ) {
        if (!in_array($fieldName, $exceptionList))
          $ret[$fieldName] = $GLOBALS[$originalFieldName];
      }
    }

    return $ret;
  }

  function xq_extractValue(&$ret, $aFieldName, $aFieldValue, $asGlobals, $xq_prefix, $xq_postfix, $xq_only_composed_names=false)
  {
    // $aFieldValue=urldecode($aFieldValue);
    $reserverWords = array('u', 's', 'a', 'fieldName', 'fieldValue');
    if (!in_array(strtolower($aFieldName), $reserverWords)) {
      _dumpY(16,0,"A: $aFieldName");
      if ((strtolower($aFieldValue)=='null') || (strtolower($aFieldValue)=='undefined'))
        $aFieldValue=null;
      $aFieldValue = str_replace("\!\!", '&', $aFieldValue);
      $canUse=!$xq_only_composed_names;

      /* discard prefix */
      if ($xq_prefix>'') {
        if (substr($aFieldName,0,strlen($xq_prefix))==$xq_prefix) {
          $canUse=true;
          $aFieldName=substr($aFieldName,strlen($xq_prefix));
        }
      }
      /* discard postfix */
      if ($xq_postfix>'') {
        if (substr($aFieldName, -strlen($xq_postfix))==$xq_postfix) {
          $canUse=true;
          $aFieldName=substr($aFieldName, 0, strlen($aFieldName)-strlen($xq_postfix));
        }
      }
      if (($canUse) && ($aFieldName>'')) {
        if ($aFieldName!='fieldValue')
          $aFieldValue=rawurldecode($aFieldValue);

        _dumpY(16,0,"B: $aFieldName");
        $ret[$aFieldName]=$aFieldValue;
        if ($asGlobals)
          $GLOBALS[$aFieldName]=$aFieldValue;
      }
    }
  }

  function xq_varValue($arrayOfValues, $varName)
  {
    $ret=null;
    if (isset($arrayOfValues[$varName]))
      $ret=$arrayOfValues[$varName];
    else if (isset($GLOBALS[$varName]))
      $ret=$GLOBALS[$varName];
    return $ret;
  }

  global $_REQUEST2;
  $_REQUEST2 = array();

  function xq_injectValueIntoQuery($k, $v) 
  {
    global $_REQUEST2;
    $_REQUEST2[$k] = $v;
  }

  function xq_extractValuesFromQuery($asGlobals=false, $xq_prefix='', $xq_postfix='', $xq_only_composed_names=false)
  {
    global $_REQUEST2;
    $ret=array();


    if (isset($_REQUEST)) {
      foreach($_REQUEST as $k=>$v)
        xq_extractValue($ret, $k, $v, $asGlobals, $xq_prefix, $xq_postfix, $xq_only_composed_names);
    }

    foreach($_REQUEST2 as $k=>$v)
      xq_extractValue($ret, $k, $v, $asGlobals, $xq_prefix, $xq_postfix, $xq_only_composed_names);

    $fieldName  = unparentesis(xq_varValue($ret, 'fieldName'));
    $fieldValue = unparentesis(xq_varValue($ret, 'fieldValue'));

    while ($fieldName>'') {
      $aFieldName=unquote(getNextValue($fieldName,','));
      $aFieldValue=unquote(getNextValue($fieldValue,','));
      xq_extractValue($ret, $aFieldName, $aFieldValue, $asGlobals, $xq_prefix, $xq_postfix, $xq_only_composed_names);
    }
    // die('\n\n');
    return $ret;
  }

  function xq_printXML(&$output, $keyName, $keyValue)
  {
    if (is_array($keyValue)) {
      $output.="<$keyName>";
      foreach($keyValue as $k => $v)
        xq_printXML($output, $k, $v);
      $output.="</$keyName>";
    } else {

      if (strpos($keyName,'/') > 0 ) {
        $keyList=explode('/',$keyName);
        $k0=$keyList[0];
        $openKey='';
        $closeKey='';
        for($i=0;$i<count($keyList);$i++) {
          $k=$keyList[$i];
          if (is_numeric($k)) {
            $k=$k0.$k."_";
          }
          $openKey.="<$k>";
          $closeKey="</$k>".$closeKey;
        }
        $output.="$openKey$keyValue$closeKey";
      } else {
        if (is_numeric($keyName))
          $keyName="n$keyName";
        $output.="<$keyName>$keyValue</$keyName>\n";
      }
    }
    return $output;
  }

  global $_xq_context_;
  if (!isset($_xq_context_))
    $_xq_context_=array();

  function xq_context($aIndex, $aValue, $replaceIfExists=true)
  {
    global $_xq_context_;
    if (is_string($aValue)) {
      $auxValue=iconv("UTF-8", "iso-8859-1", $aValue);
      if (strlen($auxValue)>0) {
        if (strlen($auxValue<$aValue))
          $aValue=$auxValue;
      }
    }
    // echo "$aValue ".mb_detect_encoding($aValue)." - "."\n";

    // mb_encode_numericentity($aValue, array(0x80, 0xff, 0, 0xff), "iso-8859-1")."\n";

    if (trim($aIndex)>'') {
      if (strpos($aIndex,'/')>0) {
        $keyList = explode('/', $aIndex);
        $k0=$keyList[0];
        if (!isset($_xq_context_[$k0]))
          $_xq_context_[$k0] = array();
        $aux=&$_xq_context_[$k0];
        for($i=1; $i<count($keyList)-1; $i++) {
          $k=$keyList[$i];
          if (!isset($aux[$k]));
            $aux[$k]=array();
          $aux=$aux[$k];
        }
        $itemKey=$keyList[count($keyList)-1];
        if (is_numeric($itemKey))
          $itemKey="$k0$itemKey";
        $aux[$itemKey]=$aValue;
      } else {
        if ((!is_array($aValue)) && (trim($aValue)=='')) {
          if (isset($_xq_context_[$aIndex]))
            unset($_xq_context_[$aIndex]);
        } else {
          if (($replaceIfExists) || (!isset($_xq_context_[$aIndex])))
            $_xq_context_[$aIndex]=$aValue;
        }
      }
    }

    return $_xq_context_;
  }

  function xq_produceContext($callBackFunction, $xmlRowsData, $cRegs, $userMsg=null, $firstRow=null, $requestedRows=null, $sqlID='',$progressBarID='', $navigatorID='',$formFile='')
  {
    global $formID, $targetTableID, $_xq_context_, $lastAction, $lastError, $_requiredFields, $xq_start, $xq_requestedRows;

    if ($firstRow == null)
      $firstRow = intval($xq_start);
    if ($requestedRows == null)
      $requestedRows = intval($xq_requestedRows);

    xq_context('navScript', $formFile, false);
    xq_context('formID', $formID, false);
    xq_context('targetTableID', $targetTableID, false);
    xq_context('navigatorID', $navigatorID, false);
    xq_context('progressBarID', $progressBarID, false);
    xq_context('sqlID', $sqlID, false);
    xq_context('requestedRows', $requestedRows, false);
    xq_context('firstRow', $firstRow, false);
    if ($userMsg!=null)
      xq_context('userMsg', $userMsg, false);
    xq_context('lastAction', $lastAction, false);
    xq_context('lastError', explode("\n",$lastError), false);
    xq_context('rowCount', intval($cRegs), false);
    xq_context('requiredFields', $_requiredFields, false);

    while (strpos($userMsg,"\n ")>0)
      $userMsg=str_replace("\n ", "\n", $userMsg);

    $xmlData ="  <callBackFunction>$callBackFunction</callBackFunction>\n";
    $xmlData.="  <dataContext>\n";
    foreach($_xq_context_ as $k => $v) {
      xq_printXML($xmlData, $k, $v);
    }
    $xmlData.="  </dataContext>\n";
    $xmlData.="  <data>\n$xmlRowsData</data>\n";
    return $xmlData;
  }

  /*
   * xq_produceReturnLinesFromArray
   * xq_produceReturnLinesFromSQL
   *
   * as duas geram linhas parciais de xml simples para usar com QUERY.PHP
   *
   * 26/ago/10 - caso precise calcular uma coluna a partir de outra,
   *             indique calc_nomeFuncao como nome do campo
   *             e implemente CALC_NOMEFUNCAO() nas suas rotinas
   * 29/out/10 - foi acrescentada xq_produceReturnLinesFromArray para gerar resultados
   *             a partir de um vetor associativo.  � usada por xq_produceReturnLinesFromSQL
   * 05/jul/11 - foi acrescentada xq_calculatedField para atender os campos CALC_NOMEFUNCAO()
   *             desde outros comandos db_*.  Veja por exemplo db_fetch_array
   */
  $uncoveredFunctions=Array();

  function xq_calculatedField(&$d, &$k, &$v)
  {
    global $uncoveredFunctions;

    $knum=intval(is_numeric($k));
    if (!$knum) {
      $funcName=strtoupper($k);
      if (substr($funcName,0,5)=='CALC_') {
        if (function_exists($k)) {
          $v=maskHTML($k($d));
        } else {
          if (! in_array($funcName, $uncoveredFunctions)) {
            array_push($uncoveredFunctions, $funcName);
            _dumpY(16,0,"ERROR: function '$funcName()' does not exists in context");
          }
        }
      }
    }
    return $v;
  }

  function xq_produceReturnLinesFromInnerArray($d, $colNames=false, $nonEmptyField='', $innerKeySeed='', $xq_prefix='', $xq_postfix='')
  {
    $ret='';
    foreach($d as $k => $v) {
      if ("$k"!='__COUNT__') {
        if (is_numeric($k)) {
          $keyName = $innerKeyNdx.'_'.$k;
        } else
          $keyName = trim($k);
        $keyName="$xq_prefix$keyName$xq_postfix";
        if (is_array($v))
          $v=xq_produceReturnLinesFromInnerArray($v, $colNames, $nonEmptyField, $keyName, $xq_prefix, $xq_postfix);
        $ret.="<$keyName>$v</$keyName>";
      }
    }
    return $ret;
  }

  function xq_produceReturnLinesFromArray($d, &$cRegs, $colNames=false, $nonEmptyField='', $xq_prefix='', $xq_postfix='')
  {
    global $xq_return_array, $_ydb_ready;

    $auxRow='';
    $col=0;

    $mandatoryFieldFilled=($nonEmptyField=='');

    $allAreNumricKeys=true;
    foreach($d as $k=>$v)
      if (!is_numeric($k))
        $allAreNumricKeys=false;

    if ($allAreNumricKeys) {
      $cRegs=intval($cRegs);

      foreach($d as $k=>$v) {
        if (is_array($v)) {
          $v=xq_produceReturnLinesFromInnerArray($v, $colNames, $nonEmptyField, '_'.$k, $xq_prefix, $xq_postfix);
        } else
          $v=maskHTML(trim($v));

        $xmlRow.="  <row rowid='$cRegs'>\n";
        $xmlRow.="    <rowid>$cRegs</rowid>\n";
        $xmlRow.="    <data>$v</data>";
        $xmlRow.="  </row>\n";

        $cRegs++;

      }

    } else {

      $colNames=intval($colNames);
      $CIKeys=array();

      foreach($d as $k=>$v) {
        if ("$k"!="__COUNT__") {
          $CIK=strtolower($k);
          if ((  ($_ydb_ready & _DB_CONNECTED_)==0) ||
                 (db_connectionTypeIs(_MYSQL_)) ||
              (!in_array($CIK, $CIKeys))) {
            $CIKeys[]=$CIK; 

            $knum = is_numeric($k);
            if (is_array($v)) {
              $v=xq_produceReturnLinesFromInnerArray($v, $colNames, $nonEmptyField, '_'.$k, $xq_prefix, $xq_postfix);
            } else
              $v=maskHTML(trim($v));
            $canAdd=false;

            $v=mb_convert_encoding($v, "iso-8859-1", mb_detect_encoding($v));

            // $v=xq_calculatedField($d, $k, $v);

            _dumpY(16,3,"$k => $v");

            $fieldAttrib='';
            if ($colNames)
              $canAdd=!$knum;
            else {
              $canAdd=true;
              if ($canAdd) {
                $k="data";
                $col++;
                $fieldAttrib=" col='$col'";
              }
            }

            $k="$xq_prefix$k$xq_postfix";


            if ($canAdd) {
              $auxRow.="    <$k$fieldAttrib>$v</$k>\n";
              if ($k==$nonEmptyField) {
                $mandatoryFieldFilled=(trim($v)>'');
              }
            }
          }
        }
      }

      if ($mandatoryFieldFilled) {

        $cRegs=intval($cRegs);

        $xmlRow ="  <row rowid='$cRegs'>\n";
        $xmlRow.="    <rowid>$cRegs</rowid>\n";
        $xmlRow.="$auxRow";
        $xmlRow.="  </row>\n";

        $cRegs++;
      } else {
        $xmlRow='';
      }
    }

    return $xmlRow;
  }

  /* xq_produceReturnLinesFromSQL
   *    recebe um comando SQL
   *    devolve um xml parcial contendo as colunas indicadas no XML
   */

  function xq_produceReturnLinesFromSQL($sql, &$cRegs, $colNames=false, $maxRecordCount=-1, $nonEmptyField='', $xq_prefix='', $xq_postfix='')
  {
    global $uncoveredFunctions, $userMsg;

    $xmlRows='';
    $sql=html_entity_decode(unquote($sql));

    $q=db_query($sql);
    while ($d=db_fetch_array($q,false)) {
      if (!$colNames)
        $d=array_unique($d);
      $xmlRows.=xq_produceReturnLinesFromArray($d, $cRegs, $colNames, $nonEmptyField, $xq_prefix, $xq_postfix);
      if (($maxRecordCount>0) && ($cRegs>=$maxRecordCount)) {
        $userMsg="Limite de busca atingido.  Seja mais especifico";
        _dump($userMsg);
        break;
      }
    }


    return $xmlRows;
  }

  function xq_produceReturnLines($returnSet, $xq_usingColNames, $xq_countLimit, $xq_prefix='', $xq_postfix='')
  {
    global $xq_return, $xq_regCount, $xq_requestedRows;
    $xq_requestedRows = $xq_countLimit;

    if (is_array($returnSet)) {
      $isArrayOfArray=false;
      foreach($returnSet as $k=>$v)
        if (is_array($v))
          $isArrayOfArray=true;
      if ($isArrayOfArray) {
        $xq_return='';
        foreach($returnSet as $k=>$v) {
          $xq_return.= xq_produceReturnLinesFromArray($v, $xq_regCount, $xq_usingColNames, '', $xq_prefix, $xq_postfix);
        }
      } else
        $xq_return = xq_produceReturnLinesFromArray($returnSet, $xq_regCount, $xq_usingColNames, '', $xq_prefix, $xq_postfix);
    } else {
      if ($returnSet>'')
        $xq_return=xq_produceReturnLinesFromSQL($returnSet, $xq_regCount, $xq_usingColNames, $xq_countLimit, '', $xq_prefix, $xq_postfix);
    }
    return $xq_return;
  }

  function jr_produceReturnLines($returnSet) {
    if (is_string($returnSet)) {
      $auxSet=$returnSet;
      $auxSet=strtolower(getNextValue($auxSet,' '));
      if (($auxSet=="select") || ($auxSet=="insert") || ($auxSet=="delete") || ($auxSet=="update") || ($auxSet=="replace")) {

        $auxSet=db_queryAndFillArray($returnSet);
        $returnSet=array();

        foreach($auxSet as $d) {
          $auxLine=array();
          foreach($d as $k=>$v) {
            $canUse=true;
            if (db_connectionTypeIs(_FIREBIRD_))
              $canUse=(strtoupper($k)==$k);
            if ($canUse)
              if ($k!="__COUNT__")
                $auxLine[$k]=$v;
          }
          $returnSet[count($returnSet)] = $auxLine;
        }
      }
    }
    return json_encode($returnSet);
  }


  /* functions to verify form content */
  function strFilled($str)
  {
    return strlen(trim($str))>0;
  }

  function validEmail($email)
  {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
  }

  global $_xq_formErrorSequence_;
  $_xq_formErrorSequence_=0;

  function setFieldError($msg, $varName='')
  {
    global $_xq_formErrorSequence_;
    _requiredField($varName);
    $_xq_formErrorSequence_++;
    _recordError("$_xq_formErrorSequence_) $msg");
    xq_context("formError/$varName", $msg);
  }

  function verifyFormValue($varName, $func, $msg, $setAsFormError=true)
  {
    $ret=true;
    $functions=explode(';',$func);
    foreach($functions as $func) {
      if ($ret) {
        if (function_exists($func)) {
          if ($func($GLOBALS[$varName])==false) {
            if ($setAsFormError)
              setFieldError($msg, $varName);
            else {
              _requiredField($varName);
              _recordError($msg,0);
            }
            $ret=false;
          }
        } else
          _die("'$func' was not found as global function");
      }
    }
    return $ret;
  }


  /*
   * Functions to be used with Javascript Inter User Messages in
   * the context of YeAPF applications
   *
   */

  function qy_msgProc($aSourceUserId, $aMessage, $aWParam, $aLParam)
  {
    global $sysTimeStamp, $__messagesHandler;

    $ret=array();

    if ($aMessage=='') {
      $ret['sourceUserId']=$aSourceUserId;
      $ret['message']='systemTick';
      $ret['wParam']=$sysTimeStamp;
      $ret['lParam']=0;
    } else {
      $ret['sourceUserId']=$aSourceUserId;
      $ret['message']=$aMessage;
      $ret['wParam']=$aWParam;
      $ret['lParam']=$aLParam;

      foreach($__messagesHandler as $mh)
        if (function_exists($mh))
          $ret=$mh($aSourceUserId, $aMessage, $aWParam, $aLParam);
    }

    return $ret;
  }

  function qy_msg($a)
  {
    global $sysTimeStamp,
           $userContext,
           $u, $formID, $messagePeekerInterval,
           $xq_return, $xq_regCount,
           $targetUser, $message, $wParam, $lParam, $broadcastCondition;

    if (!is_object($userContext)) {
      $aux=debug_backtrace();
      foreach($aux as $k=>$v) {
        foreach($v as $k1 => $v1) {
          echo "$k $k1 ";
          if ($k1=='args') {
            echo "(";
            foreach($v1 as $k2 => $v2) {
              if ($k2>0)
              echo ',';
              echo "'$v2'";
            }
            echo ")\n";
          } else
            echo " $v1";
          echo "\n";
        }
      }
      die("userContext not initialized");
    }

    $xq_regCount=0;
    $xq_return='';

    /*
     * $formID vazio indica primeira solicita��o de lista
     * de mensagens sendo requirida pelo cliente yeapf.js
     */
    if ($formID=='') {
      $formID=md5('ym'.y_uniqid());
      $userContext->RegisterFormID($messagePeekerInterval);
    }

    //$messages=xq_produceReturnLinesFromArray($xq_return_array,$xq_regCount,true);

    // messages vindos do pr�prio usu�rio tem prioridade sobre os enviados pelo resto
    // ent�o eles n�o entram no processamento natural da pilha

    if ( ($a=='peekMessage') || ($targetUser==$u) )  {
        $messageList = $userContext->PeekMessages();
        $aSourceUserID=$u;
        $aMessage=$message;
        $aWParam=$wParam;
        $aLParam=$lParam;
        do {

          _dumpY(16,0,"@ sending $aSourceUserID, $aMessage, $aWParam, $aLParam");

          $xq_return_array = qy_msgProc($aSourceUserID, $aMessage, $aWParam, $aLParam);

          if (count($xq_return_array)>0)
            $xq_return.=xq_produceReturnLinesFromArray($xq_return_array, $xq_regCount, true, '', $xq_prefix, $xq_postfix);

          $moreFeed=false;

          $msg=array_shift($messageList);
          if ($msg>'') {
            $aSourceUserID=getNextValue($msg,';');
            $aMessage=getNextValue($msg,';');
            $aWParam=getNextValue($msg,';');
            $aLParam=getNextValue($msg,';');
            $moreFeed=($aMessage>'');
          }

        } while ($moreFeed);

    } else if ($a=='postMessage') {
      if ($targetUser=='*') {
        $aux=unquote($broadcastCondition);
        $varName=getNextValue($aux,'=');
        $varValue=getNextValue($aux,'=');

        $userContext->BroadcastMessage($varName, $varValue, $message, $wParam, $lParam);
      } else
        $userContext->PostMessage($targetUser, $message, $wParam, $lParam);
    }

  }

  function addMessageHandler($mh)
  {
    global $__messagesHandler;

    if ($mh!='qy_msgProc')
      if (!in_array($mh,$__messagesHandler)) {
        _dumpY(16,0,"registering '$mh' as message handler");
        array_push($__messagesHandler,$mh);
      }
  }

  function produceRestOutput($jsonData)
  {
    global $callback, $callbackId, $scriptSequence, $userMsg, $_xq_context_, $ts;

    if ((!isset($callbackId)) || ($callbackId==''))
      $callbackId = 'null';
    if ((!isset($scriptSequence)) || ($scriptSequence==''))
      $scriptSequence = '0';
    if ((!isset($callback)) || ($callback==''))
      $callback='ycomm.dummy';

    $_xq_context_['callback']=$callbackId;
    $_xq_context_['scriptSequence']=$scriptSequence;

    $context=json_encode($_xq_context_);

    $returnAsScript=false;
    /* 0.9.0 */
    if (isset($GLOBALS['_rap_'.$ts])) {
      if ($GLOBALS['_rap_'.$ts]==1)
        $returnAsScript=true;
    }

    if (isset($GLOBALS['fieldName']) && isset($GLOBALS['fieldValue']) && isset($GLOBALS['ts']))
      $returnAsScript=true;

    if ($returnAsScript) {
      $script=
        "if (typeof $callback == 'function') {
           $callback(200, 0, $jsonData, '$userMsg', $context);
         } else
           console.warn(\"'$callback' callback function was not found\");
        ";
    } else {
      $script = $jsonData;
    }

    return $script;
  }

  function db_isWorkingAsAppController() {
    $cc1=db_sql("select count(*) form is_node_control");
    $cc1=db_sql("select count(*) form is_server_control");
    return (!db_isWorkingAsAppNode() && ($cc1==0) && ($cc2==0));
  }

  function db_isWorkingAsAppNode() {
    global $cfgNodePrefix, $_ydb_ready;
    $ret=false;
    if (($_ydb_ready & _DB_CONNECTED_)==_DB_CONNECTED_) {
      if (db_tableExists("is_node_control")) {
        $cc=db_sql("select count(*) from is_node_control");
        $ret = (($db_checkNodeConfig!="UNK") || ($cc>0));
      }
    }
    return $ret;
  }

  function db_generateKey($maxLen=7)
  {
    $seq="QWERTYUIOPASDFGHJKLZXCVBNM0123456789";
    $key="";

    /* falta verificar que a key nao exista */
    while (strlen($key)<$maxLen) {
      $n=y_rand(0,strlen($seq));
      $key.=substr($seq,$n,1);
    }

    return $key;
  }

  $GLOBALS['cfgMaxSegmentReservationChunkCount']=10;

  function db_reserveSegments($serverKey, $nodePrefix, $count=10) {
    if (db_isWorkingAsAppController()) {
      $ret=false;
      $cc=db_sql("select count(*) 
                  from is_segment_control 
                  where serverKey='$serverKey' 
                    and nodePrefix='$nodePrefix' 
                    and regulation is null");
      /* no es posible pedir más que cincuenta ni mantener reservados más que cinquenta */
      $count=min($count,$GLOBALS['cfgMaxSegmentReservationChunkCount']-$cc);
      if (lock("reserve-segments")) {
        $ret=0;
        while ($count>0) {
          $count--;
          do {
            $key=db_generateKey(4);
            $cc=db_sql("select count(*) from is_segment_control where segment='$key'");
          } while ($cc>0);
          $now=date("YmdHis");
          db_sql("insert into is_segment_control(serverKey, nodePrefix, identity, segment, creation, regulation) values ('$serverKey', '$nodePrefix', null, '$key', '$now', null) ");
          $ret++;
        }  
        unlock("reserve-segments");      
      }
    }
    return $ret;
  }

  function db_associateSegment($serverKey, $nodePrefix, $segment, $identity) {
    $ret=false;
    if (db_isWorkingAsAppController()) {
      if (lock("associate-segment")) {

        $ret=0;
        $cc=db_sql("select count(*) 
                    from is_segment_control 
                    where serverKey='$serverKey'
                      and nodePrefix='$nodePrefix'
                      and segment='$segment'
                      and regulation is null");
        if ($cc==1) {
          $now=date("YmdHis");
          db_sql("update is_segment_control 
                  set regulation='$now', identity='$identity' 
                  where serverKey='$serverKey'
                    and nodePrefix='$nodePrefix'
                    and segment='$segment'
                    and regulation is null");
          $ret=1;
        } else {
          /* verifico se a identdade ya existe y si si, devuelvo la cuenta */
          $ret=db_sql("select count(*) 
                        from is_segment_control 
                        where serverKey='$serverKey'
                          and nodePrefix='$nodePrefix'
                          and segment='$segment'
                          and identity='$identity'
                          and regulation is not null");
        }
        unlock("associate-segment");
      }
    }
    return $ret;
  }

  function db_requestSegmentReservation($count) {
    global $cfgIdServerURL;

    if (!db_isWorkingAsAppController()) {
      if (db_isWorkingAsAppNode()) {
        $validServerURL = (isset($cfgIdServerURL)) && (!filter_var($cfgIdServerURL, FILTER_VALIDATE_URL) === false);
        if ($validServerURL) {
          $urlBase="$cfgIdServerURL/rest.php";
          $serverKey=$GLOBALS['cfgDBNode']['server_key'];
          $nodeName=$GLOBALS['cfgDBNode']['node_name'];

          $count=min($count,$GLOBALS['cfgMaxSegmentReservationChunkCount']);

          $url="$urlBase?s=ctrl&a=requestSegmentReservation&r=$r&serverKey=$serverKey&nodeName=$nodeName&count=$count";

          $ch=curl_init();
          curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,2); 
          curl_setopt($ch, CURLOPT_TIMEOUT, 5);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $canContinue=true;
          if (($ret=curl_exec($ch))===false) {
            $errorMsg="Error: #".curl_errno($ch).", ".curl_error($ch);
            _dump($errorMsg);
            $canContinue=false;
          }
          curl_close($ch);

        }
      }
    }
  }

  function db_checkNodeConfig() 
  {
    global $cfgNodePrefix, $_ydb_ready;

    $ret=true;

    $secondsPerDay = 24 * 60 * 60;

    _recordWastedTime("db_checkNodeConfig()");


    if (db_isWorkingAsAppNode()) {

      $ret=false;

      $now=date('U');
      $dbNodeInfo = db_sql("select n.serverKey, n.enabled as nodeEnabled, 
                                   n.last_verification, n.external_ip,
                                   n.nodePrefix,
                                   s.enabled as serverEnabled, 
                                   s.serverKey as sp2
                            from      is_node_control n 
                            left join is_server_control s on s.serverKey=n.serverKey
                            where nodePrefix='$cfgNodePrefix'",false);
      extract($dbNodeInfo);

      if ($serverKey>'') {
        $currentIP = getCurrentIp();
        if ($last_verification=='')
          $last_verification=$now;

        $dif = intval(intval($now) - intval($last_verification));

        if ($currentIP!=$external_ip) {
          _recordError("Error: node_control says '$external_ip' while your current ip is '$currentIP'");
          db_close();
          $_ydb_ready |= _DB_LOCKED | _DB_LOCK_EXTERNAL_IP_MISTAKE;

        } else if ($dif > $secondsPerDay) {
          $difHours = floor($dif / 60 / 60);
          _recordError("Error: node_control has been checked $difHours hours ago. It need to be checked each 24 hours");
          db_close();
          $_ydb_ready |= _DB_LOCKED | _DB_LOCK_TIME_MISTAKE;

        } else if ($serverEnabled!='Y') {
          _recordError("Error: server_control has been disabled");
          db_close();
          $_ydb_ready |= _DB_LOCKED | _DB_LOCK_DISABLED;

        } else if ($nodeEnabled=='N') {
          _recordError("Error: node_control has been disabled");
          db_close();
          $_ydb_ready |= _DB_LOCKED | _DB_LOCK_DISABLED;

        } else if ($nodePrefix!=$cfgNodePrefix) {
          _recordError("Error: node_control server prefix '$nodePrefix' differs from '$cfgNodePrefix' declared in .config/cloudAppNode.ini");
          db_close();
          $_ydb_ready |= _DB_LOCKED | _DB_LOCK_SERVER_PREFIX_MISTAKE;
          
        }
      } else if ($cfgNodePrefix!='UNK') {
        _recordError("Error: there is a server prefix '$nodePrefix' defined in .config/cloudAppNode.ini but none in is_node_control");
        db_close();
        $_ydb_ready |= _DB_LOCKED | _DB_LOCK_WRONG_SERVER_PREFIX;
      } else if (verifyNodeSequence()===false) {
        _recordError("Error: this node is out of sequence with id controller");
        db_close();
        $_ydb_ready |= _DB_LOCKED | _DB_LOCK_WRONG_SEQUENCE;

      }

      $ret=(db_status(_DB_LOCKED) == 0);
    }

    _recordWastedTime("is_node_control checked");

    return $ret;
  }

  function thisNodeExists($onlyEnabled=true)
  {
    global $cfgNodePrefix;
    if ($onlyEnabled)
      $w="n.enabled='Y' and s.enabled='Y' and ";
    else
      $w="";

    $sql="select count(*) 
                from is_node_control n, is_server_control s 
                where $w nodePrefix='$cfgNodePrefix' 
                  and s.serverKey=n.serverKey";

    $cc=db_sql($sql);  
    return ($cc==1);  
  }

  function disableThisNode()
  {
    global $cfgNodePrefix;
    db_sql("update is_node_control set enabled='N' where nodePrefix='$cfgNodePrefix'");    
  }

  function enableThisNode()
  {
    global $cfgNodePrefix;
    db_sql("update is_node_control set enabled='Y' where nodePrefix='$cfgNodePrefix'");
  }

  function verifyNodeSequence() 
  {
    global $cfgIdServerURL, $dbCSVPath, $cfgNodePrefix;

    $ret=-1;
    $ok=false;

    $toDebug=true;

    if (db_isWorkingAsAppNode()) {
      if (lock('verify-node-sequence',true)) {

        $tempTimeMark=sys_get_temp_dir()."/ctrl-tm-seq";
        $toTest=false;

        if ($toDebug) _dump("$tempTimeMark");

        if (file_exists($tempTimeMark)) {
          $tm=filemtime($tempTimeMark);
          $desired=intval(file_get_contents($tempTimeMark));
          $maxT = $tm + (4 * 60 * 60);
          $dtm=min(max($now,$maxT), $desired);
          $now=date('U');
          $toTest=intval(($now>=$dtm));

          if ($toDebug) {
            $xmaxT=date("Y-m-d H:i:s", $maxT);
            $xdesired=date("Y-m-d H:i:s", $desired);
            $xdtm=date("Y-m-d H:i:s", $dtm);
            $xnow=date("Y-m-d H:i:s", $now);
            _dump("maxT=$xmaxT |desired=$xdesired | dtm=$xdtm | now=$xnow | toTest=$toTest");
          }
        } else {
          $toTest=true;
        }

        if ($toTest) {
          $validServerURL = (isset($cfgIdServerURL)) && (!filter_var($cfgIdServerURL, FILTER_VALIDATE_URL) === false);
          if ($validServerURL) {
            $urlBase="$cfgIdServerURL/rest.php";

            $nodeSeq=@file_get_contents("$dbCSVPath/.config/cloudAppNode.seq");
            $nodeSeq=explode(":", $nodeSeq);
            $a=$nodeSeq[0];
            $b=$nodeSeq[1];
            $r=$a+$b;

            $serverKey=$GLOBALS['cfgDBNode']['server_key'];
            $nodeName=$GLOBALS['cfgDBNode']['node_name'];

            $url="$urlBase?s=ctrl&a=checkSeq&r=$r&serverKey=$serverKey&nodeName=$nodeName";

            $ch=curl_init();
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,2); 
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $canContinue=true;
            if (($ret=curl_exec($ch))===false) {
              $errorMsg="Error: #".curl_errno($ch).", ".curl_error($ch);
              _dump($errorMsg);
              $canContinue=false;
            }
            curl_close($ch);

            if ($canContinue) {
              $ret=json_decode($ret, true);

              $check=md5($ret['a'].':'.$ret['b'].':1');


              if ($check==$ret['c']) {
                @file_put_contents("$dbCSVPath/.config/cloudAppNode.seq", $ret['a'].':'.$ret['b']);
                $dt=$now+y_rand(15,60*60);
                @file_put_contents($tempTimeMark, $dt);
                $ok=true;
              } else {
                _dump("Is this a clone node?");
              }            
            } else {
              $toTest=false;
            }
          }      
        }

        if (($toTest) && (!$ok)) {
          disableThisNode();
          _dump("Node out of sequence.\nNode disabled. IdServerURL:'$cfgIdServerURL'");
        }

        $ret=$toTest?intval($ok):-1;

        unlock('verify-node-sequence');
      }
    }

    return $ret;
  }

  function ryeapf($a)
  {
    global $callback, $dbCSVPath, $cfgNodePrefix, $cfgClientConfig, $_ydb_ready, $server_IP;
    extract(xq_extractValuesFromQuery());

    $ret=array();

    if ($a=='ping') {
      $ret['serverTime'] = date('U');
      $ret['timezone'] = date('Z');
      $ret['ip'] = getCurrentIp();
    } else if ($a=='serverTime') {
      $ret['serverTime'] = date('Y-m-d H:i:s');
    } else if ($a=='nodeKeepAlive') {
      if (db_status(_DB_LOCKED)==0) {
        if (db_tableExists('is_node_control')) {
          if (thisNodeExists()) {
            $dbNodeInfo = db_sql("select enabled, last_verification, external_ip, internal_ip 
                                  from is_node_control 
                                  where nodePrefix='$cfgNodePrefix'", false);
            extract($dbNodeInfo);

            $currentIP = getCurrentIp();
            if ($currentIP==$external_ip) {

              if ($internal_ip == $server_IP) {
                $t=date('U');
                $reverse_ip = gethostbyaddr($currentIP);
                db_sql("update is_node_control 
                        set last_verification=$t, 
                            reverse_ip='$reverse_ip' 
                        where nodePrefix='$cfgNodePrefix'");
                $ret['reverse_ip'] = $reverse_ip;
                $ret['last_verification'] = $t;                
              } else {
                $ret['error'] = "Current internal ip '$server_IP' differs from '$internal_ip'";
              }
            } else {
              $ret['error'] = "Current ip '$currentIP' differs from '$ip4'";
            }
          } else {
            $ret['error'] = "No configuration enabled at 'is_node_control'";
          }
        } else {
          $ret['error'] = "Table 'is_node_control' not found in database";
        }
      } else {
        $ret['flags']=$_ydb_ready;
        $ret['error'] = explainDBError();
        $ret['lastError']=$GLOBALS['lastError'];

      }
      /*
      $ret['folder'] = $dbCSVPath;
      $ret['cfgNodePrefix'] = $cfgNodePrefix;
      $ret['cfgClientConfig'] = $cfgClientConfig;
      */
    } else if($a=='nodeCheckSeq') {
      $r=verifyNodeSequence();
      if ($r==-1) {
        $ret['result']='NotTested';
      } else {
        $ret['result']=$r?'true':'false';
      }
    }

    $jsonRet = json_encode($ret);

    echo produceRestOutput($jsonRet);
  }

  function yeapfAppEvents(&$s, $a)
  {
    global $userContext, $withoutHeader, $aBody,
           $withoutHeader, $currentSubject ;

    if ($s=='yeapf') {
      switch($a) {
        case 'getAppHeader':
          $withoutHeader=true;
          $aBody='e_app_header.html';
          break;
        case 'getMainBody':
        case 'buildMainBody':
            $withoutHeader=true;
            $aBody='e_main_body.html';
          break;
        case 'getAppFooter':
            $withoutHeader=true;
            $aBody='e_footer_body.html';
          break;
        case 'logoff':
        case 'exit':
            $userContext->logoff();
            $withoutHeader=true;
            $aBody='f_logoff.html';
          break;
        default:
          if (isset($userContext)) {
            if ($s=='') {
              $userContext->loadUserVars('currentSubject');
              $s=$currentSubject;
            } else {
              if ($s!='yeapf') {
                $currentSubject=$s;
                $userContext->addUserVars('currentSubject');
              }
            }
          }
          break;
      }
    }
  }

  addEventHandler('yeapfAppEvents');

  if (function_exists('db_checkNodeConfig')) {
    db_checkNodeConfig();
    _recordWastedTime("db_checkNodeConfig()");
  }

?>
