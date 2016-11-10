<?php
/*
    includes/yeapf.auditTrack.php
    YeAPF 0.8.52-1 built on 2016-11-10 13:41 (-2 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-01-23 22:00:40 (-2 DST)
*/

  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  function at_getTableContent($tableName, $idFields, $idValues)
  {

    $idFields = explode(';',$idFields);
    $idValues = explode(';',$idValues);

    $w = '';
    for($i=0; $i<count($idFields); $i++) {
      if ($w>'')
        $w.=' AND ';
      $w.=$idFields[$i].'='."'".$idValues[$i]."'";
    }
    $qq = db_query("select * from $tableName where $w");
    $values = db_fetch_array($qq);
    $ret = '';
    foreach ($values as $k => $v)
      if (!is_numeric($k))
        if ($v>'') {
          if (!is_numeric($v)) {
            if (db_connectionTypeIs(_FIREBIRD_))
              $v='"'.str_replace("'","''",$v).'"';
            else
              $v="\'".addslashes($v)."\'";
          }
          if ($ret>'')
            $ret.=",\n";
          $ret.="  $k: $v";
        }
    $ret = "{\n$ret\n}";
    return $ret;
  }

  function at_createEntry($tableName, $idFields, $idValues)
  {
    global $userContext, $auditingTrackEnabled;

    if ($auditingTrackEnabled) {
      if ($userContext)
        $userID = unquote($userContext->userGID());
      else
        $userID = 'UNKNOWED';

      $id=md5('audit'.y_uniqid());
      $eventDate = date("YmdHis");
      $prevRecord = at_getTableContent($tableName, $idFields, $idValues);

      $sql="insert into is_auditingTrack(id, state, userID, eventDate, tableName, tableID, tableIDField, prevRecord)
                   values ('$id', 'O', '$userID', '$eventDate', '$tableName', '$idValues', '$idFields', '$prevRecord')";
      db_sql($sql);

      return $id;
    } else
      return null;
  }

  function at_closeEntry($id, $sqlVerb='', $eventDescription='')
  {
    global $auditingTrackEnabled;

    if ($auditingTrackEnabled) {
      $dd=db_sql("select tableName, tableID, tableIDField from is_auditingTrack where id='$id'");
      $tableName=$dd[0];
      $idValues=$dd[1];
      $idFields=$dd[2];
      $newRecord = at_getTableContent($tableName, $idFields, $idValues);

      $sql="update is_auditingTrack set newRecord='$newRecord', state='C', eventDescription='$eventDescription', sqlVerb='$sqlVerb' where id='$id' and state='O'";
      db_sql($sql);
    }
  }

?>
