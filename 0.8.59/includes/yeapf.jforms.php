<?php
/*
    includes/yeapf.jforms.php
    YeAPF 0.8.59-128 built on 2017-12-22 07:10 (-2 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2017-12-14 18:31:30 (-2 DST)
*/


function jf_getFieldsNames($jsonFilename) {

  $ret = array();

  function nodeSearch(&$ret, $node) {
  	$reservedWords = array('type','width', 'decimal','nullable','hidden','class','label','order', 'fields', 'domType', 'readOnly', 'value');
    $reservedStructures = array('query', 'events', 'options', 'resultSpec', 'array');
    foreach($node as $k=>$v) {
      if (!is_numeric($k)) {
        if (!in_array($k, $reservedWords)) {
          if (!in_array($k, $reservedStructures))
            $ret[$k]=array();
        }
      }
      if (is_array($v)) {
        if (!in_array($k, $reservedStructures))
          nodeSearch($ret, $v);
      }
    }
  }
  $jsonFile = file_get_contents($jsonFilename);
  $mainNode = json_decode($jsonFile, true);
  nodeSearch($ret, $mainNode);

  return $ret; 
}

?>
