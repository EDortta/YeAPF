<?php
/*
    includes/yeapf.jforms.php
    YeAPF 0.8.59-156 built on 2018-03-12 07:01 (-3 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2018-03-12 07:00:48 (-3 DST)
*/


function jf_getFieldsNames($jsonFilename) {

  $ret = array();

  if (!function_exists("nodeSearch")) {
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
  }
  $jsonFile = file_get_contents($jsonFilename);
  $mainNode = json_decode($jsonFile, true);
  nodeSearch($ret, $mainNode);

  return $ret; 
}

?>
