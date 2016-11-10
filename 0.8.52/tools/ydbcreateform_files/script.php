<?php
  function q#(dbTable)($a)
  {
    global $userContext, $sysDate, $u,
           $fieldValue, $fieldName,
           $userMsg, $xq_start;

    // will the data be sended using column names or column integer index?
    $useColNames = true;
    // numer of rows to limit queries result
    $countLimit=20;
    // return set.  Could be an array or an SQL statement
    $ret='';

    // publish query variables as local variables
    extract(xq_extractValuesFromQuery());
    $xq_start=isset($xq_start)?intval($xq_start):0;

    switch($a)
    {
      case 'getTableRows':
        $ret="select count(*) as cc from #(dbTable)";
        break;

      case 'getTable':
        $ret="select #(fieldList) 
              from #(dbTable) 
              order by #(primaryKey)
              limit $xq_start, $countLimit";
        break;

      case 'getItem':
        $ret="select #(fieldList) from #(dbTable) where #(primaryKey)='$#(primaryKey)'";
        break;

      case 'deleteItem':
        $ret="delete from #(dbTable) where #(primaryKey)='$#(primaryKey)'";
        break;

      case 'saveItem':
        $dados=xq_extractValuesFromQuery(true, "frm_#(dbTable)_");
        $ret=saveFormContent("#(dbTable).form", false);
        break;
    }

    xq_produceReturnLines($ret, $useColNames, $countLimit);

  }

?>