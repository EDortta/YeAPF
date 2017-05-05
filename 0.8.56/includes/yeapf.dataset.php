<?php
/*
    includes/yeapf.dataset.php
    YeAPF 0.8.56-100 built on 2017-05-05 10:47 (-3 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2016-01-23 22:00:40 (-3 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  function reorderTable($tableName, $orderFieldName, $sqlWhere,
                             $direction='', $curPosition=-1)
  {
    $q=db_query("select ID, $orderFieldName from $tableName where $sqlWhere order by $orderFieldName");
    $o=array();
    $ndx=-1;
    $aux=0;
    while ($r=db_fetch_row($q)) {
      $id=trim($r[0]);
      $ordem=$r[1];
      if ($ordem==$curPosition)
        $ndx=$aux;
      $aux++;
      array_push($o, array($id, $ordem,0));
    }

    $direction=strtoupper($direction);

    if ($direction>'') {
      if ($direction=='UP') {
        if ($ndx>0) {
          $j=$o[$ndx];
          $o[$ndx]=$o[$ndx-1];
          $o[$ndx-1]=$j;
        }
      } else if ($direction=='DOWN') {
        if ($ndx<count($o)-1) {
          $j=$o[$ndx];
          $o[$ndx]=$o[$ndx+1];
          $o[$ndx+1]=$j;
        }
      }
    }

    $no=1;
    foreach($o as $oo) {
      $id=$oo[0];
      fazerSQL("update $tableName set $orderFieldName=$no where id='$id'");
      $no++;
    }
  }

  function maskHTML($text)
  {
    $text = str_replace('[','&#91;', $text);
    $text = str_replace(']','&#93;', $text);
    $text = str_replace('<','[',$text);
    $text = str_replace('>',']',$text);
    $text = str_replace('&','!!',$text);

    return $text;
  }

  function umaskHTML($text)
  {
    $text = str_replace('!!','&',$text);
    $text = str_replace(']', '>',$text);
    $text = str_replace('[', '<',$text);

    $text = str_replace('&#93;', ']', $text);
    $text = str_replace('&#91;', '[',$text);

    return $text;
  }

  function dsTranslateHTML(&$jArray)
  {
    foreach($jArray as $k => $v)
      if (is_array($v))
        dsTranslateHTML($jArray[$k]);
      else
        if (($k=='html')||($k=='header')||($k=='body')||($k=='footer')||($k=='condLabel')||($k=='label')) {
          $jArray[$k] = maskHTML($v);
        }
  }

  function _getDSName_($dsName)
  {
    $n1="q_$dsName.dataset";  $n2="$dsName.dataset";
    if (file_exists($n1))
      $fileName=$n1;
    else if (file_exists($n2))
      $fileName=$n2;
    else {
      $fileName=bestName('q_'.$dsName);
      if ($fileName=='')
        $fileName=bestName($dsName);
    }

    return $fileName;
  }

  function _openDataset_($fileName, &$dsID, &$aux)
  {
    if (file_exists($fileName)) {

      set_time_limit(0);

      $ds=_file($fileName);

      $dsID = getNextValue($ds,':');
      $ds = str_replace("\n","",$ds);
      $aux = mjsonDecode($ds, false);
      /*
      echo "\n$ds\n==========================\n";
      var_dump($aux);
      die();
      */
      dsTranslateHTML($aux);

      return true;

    } else

      return false;
  }

  function _GetLastModification($fileName)
  {
    if (file_exists($fileName)) {
      $ret=stat($fileName);
      $ret = $ret['mtime'];
    } else
      $ret=-1;
    return $ret;
  }

  function openDataset($dsName, $purgeCache=false, &$sqlID, $isQuest=false)
  {
    $fileName=_getDSName_($dsName);
    $cachedQuery = "cachedQueries/$sqlID.sql";
    if ((!$purgeCache) && (!$isQuest)){
      $sDataset = _GetLastModification($fileName);
      $sCache = _GetLastModification($cachedQuery);
      $purgeCache = intval($sDataset>$sCache);
      _dumpY(32,0,"Calc of Purge:[$purgeCache] lastModif $fileName: [$sDataset] $cachedQuery: [$sCache]");
    }


    if (_openDataset_($fileName, $dsID, $aux)) {

      $fileInfo = pathinfo($fileName);
      $scriptName=bestName($fileInfo['filename'].'.php');
      /*
      if (isset($fileInfo['filename']))
        $scriptName = $fileInfo['dirname'].'/'.$fileInfo['filename'].'.php';
      else {
        $auxName = $fileInfo['basename'];
        $auxName = substr($auxName, 0, strrpos($auxName,'.'));
        $scriptName = $fileInfo['dirname'].'/'.$auxName.'.php';
      }
      */
      if (file_exists($scriptName)) {
        _dumpY(32,0,"Loading $scriptName");
        @include_once "$scriptName";
      } else {
        _dumpY(32,0,$fileInfo['filename'].".php not found ($fileName)");
      }

      $mainQuery = unquote($aux['mainQuery']);
      $maxRecordCount = unquote($aux['maxRecordCount']);
      $dsContext = unquote($aux['context']);

      if (db_connectionTypeIs(_FIREBIRD_)) {
        $columns = $aux['columns'];
        $ibColumns = array();
        foreach($columns as $cName => $cValue) {
          $ibcName = strtoupper($cName);
          $ibColumns[$ibcName] = $columns[$cName];
        }

        $aux['columns'] = $ibColumns;
      }


      if ($purgeCache) {
        _dumpY(32,1,"PURGE SQL ".str_replace("\n",' ',$mainQuery));
        db_clean_cached_query($sqlID);
        _dumpY(32,1,"GRANT CACHED QUERY ".str_replace("\n",' ',$mainQuery));
        $sqlID = db_grant_cached_query($mainQuery, "$dsID.$dsContext", $maxRecordCount);
        _dumpY(32,1,"READY");
      } else {
        // as the sql is preprocessed, we recover it from
        // the .sql file
        $fSQLFileName="cachedQueries/".$sqlID.".sql";
        $mainQuery=file($fSQLFileName);
      }
    } else
      _dumpY(32,0,"SQL ERROR: dataset '$dsName' ($fileName)  not found");

    return $aux;
  }

  function genDataset_sqlUID($dsName)
  {
    $ret='';
    $fileName=_getDSName_($dsName);

    if (_openDataset_($fileName, $dsID, $aux)) {
      $dsContext = unquote($aux['context']);
      $ret = "$dsID.$dsContext";
    }
    return $ret;
  }


  /*
   * There must to exists only one dataset per webApp/session
   * To achive that, you can modify your app frameset as this:

      <frameset rows="30,*,0" frameborder=no border=0>
        <frame src="body.php?u=#(u)&s=yeapf&a=getAppHeader" name="headerBody" scrolling=no>
        <frame src="body.php?u=#(u)&s=yeapf&a=buildMainBody" name="mainBody" scrolling=auto>
        <frame src="body.php?u=#(u)&s=yeapf&a=getAppDataset" name="dataset" scrolling=no>
      </frameset>

   */
  function datasetEvents(&$s, $a)
  {
    global $userContext, $withoutHeader,
           $withoutBody, $isDataset, $isMessageProcessor, $currentYeapfStage,
           $sqlID, $dsName, $purgeCache,
           $recordBlockStart, $recordBlockSize,
           $queryValue, $queryField, $tableName, $columnList,
           $whereClause, $orderBy, $groupBy, $tableJoin,
           $dsContext,
           $xq_return, $xq_regCount, $userMsg;

    // these events are triggered when the application is being loaded
    if ($s=='yeapf') {
      switch($a) {
        case 'getAppDataset':
          $withoutHeader=true;
          $withoutBody=true;
          $isDataset=true;
          $isMessageProcessor=true;
          $GLOBALS['aBody'] = _file("/YeAPF/xYApp.js");
          break;
      }
    // these events happens when the application is running
    } else if ($s=='dataset') {
      if ($dsContext>'')
        $description = openDataset($dsContext, $purgeCache>0, $sqlID, true);
      else
        $description = openDataset($dsName, $purgeCache>0, $sqlID, false);

      _dumpY(32,0,"SQL dsName: $dsName dsContext: $dsContext purgeCache:$purgeCache whereClause: '$whereClause'");

      $fCountFileName="cachedQueries/".$sqlID.".count";
      $fCacheFileName="cachedQueries/".$sqlID.".xml";
      $fIndexFileName="cachedQueries/".$sqlID.".ndx";

      switch ($a) {
        case 'getDataGeometry':
          $xq_return = "<geometry>";
          $xq_return.= "<sqlID>$sqlID</sqlID>";
          $xq_return.= "<columns>".mjsonRencode($description['columns'])."</columns>";
          $xq_return.= "<navigator>".mjsonRencode($description['navigator'])."</navigator>";
          $xq_return.= "</geometry>";

          $cc=join('',file($fCountFileName));
          $xq_regCount=$cc;

          break;

        case 'getRecordCount':
          $cc=join('',file($fCountFileName));
          $xq_regCount=$cc;
          break;

        case 'getDataBlock':
          $fCache=fopen($fCacheFileName, 'r');
          $fIndex=fopen($fIndexFileName,'rb');
          fseek($fIndex,$recordBlockStart * 8);
          $indexEntry=unpack('N',fread($fIndex,4));  $indexEntry=$indexEntry[1];
          $dataLen=unpack('N',fread($fIndex,4));  $dataLen=$dataLen[1];
          fclose($fIndex);

          fseek($fCache,$indexEntry);
          $rowCount = 0;
          $rowid = $recordBlockStart;

          do {
            $auxData=utf8_decode(fgets($fCache));
            $dataReady=(($rowCount < $recordBlockStart + $recordBlockSize ) || ($recordBlockSize <= 0)) && (strlen($auxData)>0);
            if ($dataReady) {
              $rowCount++;
              $xq_return.="<row rowid='$rowid'>\n\t<rowid>$rowid</rowid>\n\t$auxData</row>\n";
              $rowid++;
            }
          } while ($dataReady);
          $xq_regCount=$rowCount;


          break;

        case 'getQuestValue':
          if ($whereClause=='')
            $whereClause = "$queryField='$queryValue'";
          else
            $whereClause = "$queryField='$queryValue' and ($whereClause)";

          // in case there is a join command
          // check the name of each field
          if ($tableJoin>'') {
            $cList = explode(',', $columnList);
            foreach($cList as $k => $v) {
              if (strpos($v,'.')==0) {
                if (!((substr($v,0,1)=='"') || (substr($v,0,1)=="'") || (is_numeric(substr($v,0,1))))) {
                  $v="$tableName.$v";
                  $cList[$k]=$v;
                }
              }
            }
            $columnList=join(',', $cList);
          }

          $sql="select $columnList from $tableName $tableJoin where $whereClause";

          if ($groupBy>'')
            $sql = "$sql group by $groupBy";

          if ($orderBy>'')
            $sql = "$sql order by $orderBy";

          _dumpY(32,0,"SQL $sql");
          $xq_return = xq_produceReturnLinesFromSQL($sql, $xq_regCount, true);
          _dumpY(32,0,"SQL recordCount: $xq_regCount ($whereClause)");
          break;
      }
      if ($xq_return>'')
        _dumpY(32,2,"($s.$a) = $xq_return");
    } else if ($s=='yeapfDB') {
      if ($a=='doSQL') {
        $params=xq_extractValuesFromQuery();
        $sql=$params['sql'];
        //die(var_dump($params));
        // echo "\n$sql\n";
        $xq_regCount=0;
        $xq_return = xq_produceReturnLinesFromSQL($sql, $xq_regCount, true, '', $xq_prefix, $xq_postfix);
      } else if ($a=='getFormSelectOptions') {
        //die("OK");
        $params=xq_extractValuesFromQuery();
        $formName=$params['formName'];
        $formField=$params['formField'];

        $formProcessor=new xForm(bestName($formName));
        $values=unparentesis(unquote($formProcessor->xfQueries[$formField]));
        $values=explode(',',$values);
        $vList = array();
        foreach($values as $v) {
          $vId=getNextValue($v,':');
          $vTag=getNextValue($v);
          $vList[$vId]=$vTag;
        }

        $xq_regCount=0;
        $xq_return = xq_produceReturnLinesFromArray($vList, $xq_regCount, true, '', $xq_prefix, $xq_postfix);
      }
    }

  }

  function notifyDatasetClients($dsName, $aVerb, $aMessage='')
  {
    global $userContext;
    $userContext->BroadcastMessage('','*',$aVerb, $dsName, $aMessage);
  }

  addEventHandler('datasetEvents');

?>
