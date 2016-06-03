<?php
  /*
    includes/yeapf.migrateTools.php
    YeAPF 0.8.49-10 built on 2016-06-03 13:09 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-01-23 22:00:40 (-3 DST)
   */

  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  /*
   * This function returns an array with an intermediate interpretation
   * for the 'relations' parameters.
   * Each member of 'relations' is an string and has an explicit statment
   * that instruct how to create a field content from another field
   * as in the next examples:
   *
   *   address: customerAddress
   *             --> target field 'address' will pick it contents from
   *                 source field called 'customerAddress' without
   *                 type or content transformation
   *
   *   birthDate: (birth: fbdate)
   *             --> 'birthDate' will get it value from foreign field
   *                 called 'birth' that is formatted as firebird
   *                 natural date (mm-dd-yy hh:mm:ss). 'birthDate' will
   *                 use ISO8601 format (yyyymmddhhmmss)
   *   firstLetter: ( phrase: substr(0,1) )
   *             --> 'firstLetter' will assume the first character from
   *                 'phrase' field
   *   sex: (gender: query(1: 'Male', 3: 'Female'))
   *             --> in case 'gender' has '1', sex will be 'Male'
   *   sex: (gender: query("select genderCode, genderName from genders"))
   *             --> same than above but using 'genders' table with
   *                 next content:
   *                    genderCode |  genderName
   *                        1      |  Male
   *                        3      |  Female
   * Brazilian examples or how to use user-defined functions:
   *   sobrenome: (nome_fulano: split('SepNomes', 2))
   *   nome: (nome_fulano: split('SepNomes', 1))
   *             --> using function 'SepNomes' divide 'nome_fulano' field
   *             and extract name to 'nome' field and surname (index 2)
   *             to 'sobrenome' field.
   *   ddd: (fone: split('SepTelefone', 1))
   *   fone: (fone: split('SepTelefone', 2))
   *             --> using user-defined function 'SepTelefone' extract
   *             area code to 'ddd' field and telephone number to 'fone'
   *             field from a source field named 'fone' too.
   */

  function getGlueFieldsDescription($callBack, $relations, $srcDBTable='') {
    $dstFieldList='';
    $srcFieldList='';

    $srcInfo=array();

    $relLineNum=0;
    foreach($relations as $rel) {
      $relLineNum++;
      $rel=str_replace("\n",' ',$rel);
      $dstField = getNextValue($rel,':');
      if ($srcFieldList>'') {
        $srcFieldList.=', ';
        $dstFieldList.=', ';
      }
      $dstFieldList.=$dstField;
      $srcFieldName = getNextValue($rel);

      $srcNdx=count($srcInfo);
      $srcInfo[$srcNdx]=array();
      $srcInfo[$srcNdx]['name']=$dstField;
      $srcInfo[$srcNdx]['type']='';

      if (strpos($srcFieldName,'(')!==FALSE) {
        $srcFieldName=unparentesis($srcFieldName);
        $p=new xParser($srcFieldName);
        $p->get($srcFieldName, $tokenType);
        if (!$p->eof()) {
          $p->get($token, $tokenType);
          if ($token==':') {
            $p->get($token, $tokenType);
            $token=strtolower($token);

            $srcInfo[$srcNdx]['type']=$token;
            $srcInfo[$srcNdx]['query']=array();

            if ($token=='query') {
              if ($p->getExpectingType($token, 4)) {
                if ($token=='(') {
                  $querySeq=0;
                  while ((!$p->eof()) && ($token!=')')) {
                    $p->get($returnValue, $tokenType);
                    if (($p->getExpectingType($token, 4)) && ($token==':')) {
                      $p->get($keyValue, $tokenType);
                      $srcInfo[$srcNdx]['query'][unquote($returnValue)]=$keyValue;
                      // ',' ou ')'
                      $p->get($token, $tokenType);
                    } else if (($querySeq==0) && ($tokenType==5)) {
                      $sql=unquote($returnValue);
                      $queryArray=db_queryAndFillArray($sql);
                      foreach($queryArray as $k=>$v) {
                        $ak=array_keys($v);
                        $srcInfo[$srcNdx]['query'][$v[$ak[0]]]=$v[$ak[1]];
                      }
                    } else
                      $callBack(2, "ERROR: Was expeted a ':' after '$returnValue' value on query definition in .rel file at line $relLineNum");
                    $querySeq++;
                  }
                } else
                  $callBack(2, "ERROR: Was expected a '(' after ':' on query definition in .rel file line $relLineNum");
              } else
                $callBack(2, "ERROR: Was expected a '(' after 'query' in .rel file line $relLineNum");
            } else if ($token=='split') {
              if ($p->getExpectingType($token, 4)) {
                if ($token=='(') {
                  $p->get($funcName, $tokenType);
                  $funcName=unquote($funcName);
                  if (($p->getExpectingType($token, 4)) && ($token==',')) {
                    $p->get($splitNdx, $tokenType);
                  } else
                    $splitNdx=0;
                  $srcInfo[$srcNdx]['splitter']['funcName']=$funcName;
                  $srcInfo[$srcNdx]['splitter']['splitNdx']=$splitNdx;
                  $srcInfo[$srcNdx]['type']='char';
                } else
                  $callBack(2, "ERROR: Was expected a '(' after ':' on query definition in .rel file line $relLineNum");
              }
            } else if ($token=='substr') {
              if ($p->getExpectingType($token, 4)) {
                if ($token=='(') {
                  $p->get($strStart, $tokenType);
                  $p->get($token, $tokenType);
                  if ($token==',') {
                    $p->get($strLength, $tokenType);
                  } else
                    $strLength=0;
                  $srcInfo[$srcNdx]['limits']['strLength']=$strLength;
                  $srcInfo[$srcNdx]['limits']['strStart']=$strStart;
                  $srcInfo[$srcNdx]['type']='char';
                } else
                  $callBack(2, "ERROR: Was expected a '(' after ':' on query definition in .rel file line $relLineNum");
              }
            } else if ($token=='constant') {
              $srcFieldName="\"$srcFieldName\" as K$srcNdx";
            } else {
              $srcInfo[$srcNdx]['type']='query';
              $srcInfo[$srcNdx]['query']['function']=$token;
            }
          } else
            $callBack(2, "ERROR: Was expected a ':' after field name in .rel file line $relLineNum");
        }

      }


      if ($srcDBTable>'') {
        if (strpos($srcFieldName,' as ')===FALSE)
          if (!db_fieldExists($srcDBTable, $srcFieldName))
            $srcFieldName='null';
      }

      $srcFieldList.=$srcFieldName;
      $srcInfo[$srcNdx]['source']=$srcFieldName;
    }
    $ret=array('srcInfo' => $srcInfo,
               'srcFieldList' => $srcFieldList,
               'dstFieldList' => $dstFieldList);
    return $ret;
  }

  /*
   * applyGlueFieldDescription($glueFieldDescription, $fields)
   * Return an associative array with corrected values from the
   * application of the 'glueFieldDescription' on 'fields' assoc.array
   * 'glueFieldDescription' need to be generated with getGlueFieldsDescription()
   */
  function applyGlueFieldDescription($callBack, $glueFieldDescription, $fields, &$fieldValues) {
    $fieldValues='';
    $output=array();
    foreach($glueFieldDescription['srcInfo'] as $fNdx => $fInfo) {
      if (isset($fields[ $fInfo['source'] ])) {
        $value = trim($fields[ $fInfo['source'] ]);

        if ($fInfo['type']=='') {
          $fieldType=typeIndex($value,true);
          if ($fieldType==0)
            $value='NULL';
        } else
          $fieldType=$fInfo['type'];

        switch($fieldType)
        {
          case '4':
          case '1':
          case 'char':
            if (strtoupper(unquote($value))=="NULL")
              $value='NULL';
            else {
              if (isset($fInfo['limits']))
                $value=substr($value, $fInfo['limits']['strStart'], $fInfo['limits']['strLength']);
              if (isset($fInfo['splitter'])) {
                $funcName=$fInfo['splitter']['funcName'];
                $splitNdx=$fInfo['splitter']['splitNdx'];
                if (function_exists($funcName)) {
                  $value=$funcName($value,$splitNdx);
                } else {
                  $callBack(2, "ERROR: function '$funcName()' not defined");
                  die();
                }
              }
              $value="'$value'";
            }
            break;
          case 'fbdate':
            if (strtoupper(unquote($value))=="NULL")
              $value='NULL';
            else {
              $data=extractDateValues(soNumeros($value),'mmddyyyyHHMMSS');
              if (!isset($data['year']) || ($data['year']<'1899')) {
                $value='null';
              } else {
                $value=dateTransform(soNumeros($value),'mmddyyyyHHMMSS','yyyy-mm-dd HH:MM:SS');
                $value="'$value'";
              }
            }
            break;
          case 'query':
            if (isset($fInfo['query']['function']))
              $value=$fInfo['query']['function']($fInfo['source']);
            else
              $value='null';
            break;
          case 'integer':
            $value=intval($value);
            break;
        }

        $output[ $fInfo['name'] ]=$value;

        if ($fieldValues>'')
          $fieldValues.=', ';
        $fieldValues.=$value;
      }

    }
    return $output;
  }
?>
