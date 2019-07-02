<?php
/*
    includes/yeapf.jforms.php
    YeAPF 0.8.63-54 built on 2019-07-02 19:38 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
    2018-05-30 11:21:05 (-3 DST)
*/

function jf_getFieldsNames($jsonFilename) {

  $ret = array();

  if (!function_exists("nodeSearch")) {
    function nodeSearch(&$ret, $node) {
      $reservedWords = array('type','width', 'decimal','nullable','hidden','class','label','order',
                             'fields', 'domType', 'readOnly', 'value',
                             'mainRow', 'mainColumn', 'footerRow', 'footerColumn',
                             'rows');
      $reservedStructures = array('query', 'events', 'options', 'resultSpec', 'array');
      foreach($node as $k=>$v) {
        if (!is_numeric($k)) {
          if (!in_array($k, $reservedWords)) {
            if (!in_array($k, $reservedStructures)) {
              $ret[$k]=$v;
            }
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

function jf_checkFieldValues($formFields, $values) {
  $ret = array();
  foreach($values as $k=>$v) {
    if (array_key_exists($k, $formFields)) {
      if (isset($formFields[$k]['type'])) {
        $fieldType = $formFields[$k]['type'];
        $fieldSubtype = substr($fieldType, strpos($fieldType .'(', '('));
        $fieldType = trim(substr($fieldType,0,strlen($fieldType) - strlen($fieldSubtype)));
        $fieldSubtype = str_replace("(", "", str_replace(")", "", $fieldSubtype));
        $fieldSubtype = explode(",", $fieldSubtype);

        switch($fieldType) {
          case 'decimal':
            $v = decimalSQL($v);
            $v = number_format($v,$fieldSubtype[1]);
            break;
        }

        if (isset($formFields[$k]['options'])) {
          $valueAccepted = false;
          foreach($formFields[$k]['options'] as $opk=>$opv) {
            if ($v==$opv['value'])
              $valueAccepted=true;
          }
          if (!$valueAccepted)
            $v=null;
        }
        if (($v) && (trim($v)>''))
          $ret[$k]=trim("$v");
      }
    }
  }
  return $ret;
}

function jf_getBriefingFields($formFields) {
  $ret = array();
  foreach($formFields as $k=>$v) {
    if (is_array($v)) {
      if (isset($v['briefingField'])) {
        if (mb_strtolower(trim($v['briefingField']))=='yes') {

          $fieldType = isset($v['type'])?$v['type']:'char';
          $fieldSubtype = substr($fieldType, strpos($fieldType .'(', '('));
          $fieldType = trim(substr($fieldType,0,strlen($fieldType) - strlen($fieldSubtype)));
          $fieldSubtype = str_replace("(", "", str_replace(")", "", $fieldSubtype));

          $ret[]=array( 'field'=>$k,
                        'briefingOrder' => $v['briefingOrder'],
                        'type'          => $fieldType,
                        'label'         => isset($v['briefingLabel'])?$v['briefingLabel']:$v['label'],
                        'subType'       => $fieldSubtype,
                        'rightAddon'    => isset($v['rightAddon'])?$v['rightAddon']:'',
                        'leftAddon'     => isset($v['leftAddon'])?$v['leftAddon']:'' );
        }
      }
    }
  }
  usort($ret, function($a, $b) { return ($a['briefingOrder']-$b['briefingOrder']);});

  return $ret;
}

?>
