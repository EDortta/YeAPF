<?php
/*
    includes/yeapf.dbText.php
    YeAPF 0.8.51-39 built on 2016-10-13 10:38 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-05-30 09:45:48 (-3 DST)
*/
  if (function_exists('_recordWastedTime'))
    _recordWastedTime("Gotcha! ".$dbgErrorCount++);
  /*
    ==================================================================
    dbText - acesso a tabelas em formato texto
                a primeira linha define o nome dos campos
          cada campo é separado por ';' e pode estar contido entre aspas (duplas ou simples)

    ==================================================================
  */

  define("LEFT","0");
  define("VALUE","1");
  define("RIGHT","2");
  define("UPPER","3");
  define("POSITION","4");

  $dbTEXT_NO_ERROR=0;
  $dbTEXT_EOF=2;
  $dbTEXT_BOF=4;

  global $dbRepository;
  $dbRepository=array();

  function createDBText($fileName, $createNewFile=false)
  {
    global $dbRepository, $db_creationEnabled_;

    $found=0;
    for ($i=0; $i<count($dbRepository) && ($found==0); $i++) {
      if ($dbRepository[$i]['fileName']==$fileName) {
        $aux=$dbRepository[$i]['dbText'];
        $found=1;
      }
    }

    if (!$found) {
      $db_creationEnabled_=true;
      $aux=new dbText($fileName, $createNewFile);
      $db_creationEnabled_=false;

      $item=array('fileName'=>$fileName,'dbText'=>$aux);
      array_push($dbRepository, $item);
    }

    return $aux;
  }

  class dbTEXT {
      var $fileName;
      var $fieldsName;
      var $indexes;
      var $data;
      var $curPos;
      var $recordCount;

      function unquote($v)
      {
        if (strlen($v)>1) {
          if ((substr($v,0,1)=='"') or (substr($v,0,1)=="'") or (substr($v,0,1)=="`"))
            $v=substr($v,1,strlen($v)-2);
          else if (strtolower(substr($v,0,6))=='&quot;')
            $v=substr($v,6,strlen($v)-12);
          else if ((strtolower(substr($v,0,2))=='\\'.'"') || (strtolower(substr($v,0,2))=='\\'."'"))
            $v=substr($v,6,strlen($v)-4);
        }
        return($v);
      }

      function getNextField(&$line, $sep=',')
      {
        $p=strpos($line,$sep);
        if ($p===false)
          $res=$line;
        else
          $res=substr($line,0,$p);
        $line=substr($line,strlen($res)+1,strlen($line));
        return trim($res);
      }

      function dbTEXT($fileName, $createNewFile=false)
      {
        global $db_creationEnabled_;
        $this->recordCount=0;
        $this->curPos=0;
        $this->fileName=$fileName;

        if (!$db_creationEnabled_)
          die("EM LUGAR DE CHAMAR DIRETAMENTE ESTA FUNÇÃO, UTILIZE createDBText('$fileName', createNewFile=false)");

        if (!file_exists($fileName))
          if ($createNewFile)
            touch($fileName);
        $this->loadData();
      }

      function loadData()
      {
        $localAppCharset='ISO-88'.'59-1';  // esta cortada para evitar que o parser do ycharsetconvert a reconheça

        $this->fieldsName=array();
        $this->data=array();
        $this->indexes=array();
        $this->recordCount=0;

        if (file_exists($this->fileName)) {
          $f=file($this->fileName);
          if ($f) {
            $seq=0;

            // carrega a tabela
            foreach ($f as $s) {
              $s=preg_replace('/[^(\x20-\x7F)]*/', '', $s);
              if ($s>'') {
                if ($seq==0) {
                  $fldSeq=0;
                  while ($s>'') {
                    $v=$this->getNextField($s,';');
                    $v=$this->unquote($v);
                    $this->fieldsName[$fldSeq++]=$v;
                  }

                  $seq++;
                } else {
                  $fldSeq=0;
                  $rec=$this->recCount();
                  while ($s>'') {
                    $v=$this->getNextField($s,';');
                    $v=$this->unquote($v);
                    $fld=$this->fieldsName[$fldSeq++];
                    $this->data[$rec][$fld]=$v;
                  }
                  $localAppCharset=strtolower(isset($this->data[$rec]['appCharset'])?$this->data[$this->recCount()]['appCharset']:'ISO-88'.'59-1');
                  for ($i=0; $i<$fldSeq; $i++) {
                    $fld=$this->fieldsName[$i];
                    $v=$this->data[$rec][$fld];
                    if (function_exists("detect_encoding")) {
                      $strCharset=detect_encoding($v);
                      if (strtolower($strCharset)!=$localAppCharset) {
                        $v=iconv($strCharset, $localAppCharset, $v);
                        $this->data[$rec][$fld]=$v;
                      }
                    }
                  }
                  $this->recordCount++;
                }
              }
            }
          }
        }

        $this->goTop();
      }

      function verifyRecBounds()
      {
        global $dbTEXT_NO_ERROR, $dbTEXT_EOF, $dbTEXT_BOF;
        $res=$dbTEXT_NO_ERROR;
        if ($this->curPos<0) {
          $this->curPos=0;
          $res=$dbTEXT_BOF;
        }

        if ($this->curPos>$this->recCount()) {
          $this->curPos=$this->recCount();
          $res=$dbTEXT_EOF;
        }
        return $res;
      }

      function importXML($xml)
      {
        // $xml=utf8_decode($xml);
        $xml_parser = xml_parser_create('ISO-8859-1');
        xml_parse_into_struct($xml_parser, $xml, $vals, $index);
        xml_parser_free($xml_parser);

        foreach($vals as $v) {
          if (($v['tag']=='ITEM') && ($v['type']=='open'))
//          if ($v['type']=='open')
            $item=array();
          if ($v['type']=='complete') {
            $item[$v['tag']]=$v['value'];
          }
          if (($v['type']=='close') && isset($item) && (is_array($item))) {
            $this->addRecord();
            foreach($item as $k1 => $v1) {
              $this->addField($k1);
              $this->setValue($k1,$v1);
            }
            unset($item);
          }
        }
      }

      function fields()
      {
        return $this->fieldsName;
      }

      function goTop()
      {
        $this->curPos=0;
        return $this->verifyRecBounds();
      }

      function skip($step=1)
      {
        $this->curPos+=$step;
        return $this->verifyRecBounds();
      }

      function gotoRecord($recNo)
      {
        $this->curPos=$recNo;
        return $this->verifyRecBounds();
      }

      function eof()
      {
        return ($this->curPos>=$this->recCount());
      }

      function eraseRecord()
      {
      }

      function recNo()
      {
        return $this->curPos;
      }

      function recCount()
      {
        return $this->recordCount;
      }

      function truncate()
      {
        array_splice($this->data, $this->recNo());
      }

      function addRecord()
      {
        $this->curPos=$this->recCount();
        $this->recordCount++;
      }

      function fieldExists($fieldName) {
        $found=0;
        foreach($this->fieldsName as $fName)
          if ($fName==$fieldName)
            $found=1;
        return $found;
      }

      function addField($fieldName)
      {
        if (!$this->fieldExists($fieldName))
          array_push($this->fieldsName,$fieldName);
      }

      function dropField($fieldName)
      {
        for($n=0; $n<count($this->fieldsName) && ($this->fieldsName[$n]!=$fieldName); $n++);

        if ($n<count($this->fieldsName))
          array_splice($this->fieldsName, $n, 1);
      }

      function eraseField($fieldName)
      {
        if (in_array($fieldName,$this->fieldsNames))
          unset($this->fieldsName[$fieldName]);
      }

      function setValue($fieldName, $value)
      {
        $this->data[$this->curPos][$fieldName]=$value;
      }

      function getValue($fieldName)
      {
        $ret=null;
        if (isset($this->data[$this->curPos])) {
          if (isset($this->data[$this->curPos][$fieldName]))
            $ret=$this->data[$this->curPos][$fieldName];
        }
        return $ret;
      }

      function getFieldNameLike($fieldName)
      {
        $ret=$fieldName;
        for ($n=0; $n<count($this->fieldsName); $n++)
          if (strtolower($this->fieldsName[$n])==strtolower($fieldName))
            $ret=$this->fieldsName[$n];
        return $ret;
      }

      function populateValues($toDebug=false, $exceptionList='', $override=false)
      {
        $exceptionList=explode(',', $exceptionList);
        foreach($this->fieldsName as $fld) {
          if ((!in_array($fld, $exceptionList)) && (($override || !isset($GLOBALS[$fld])))) {
            $value=$this->data[$this->curPos][$fld];
            _dumpY(1,1,"Publishing '$fld' = '$value'");
            $GLOBALS[$fld]=$value;
            if ($toDebug) {
              if (strpos(strtolower(" $fld"),'password')>0)
                $v='***************';
              else
                $v=$GLOBALS[$fld];
                echo "$fld=$v<br>";
            }
          } else
            _dumpY(1,1,"Discarding* '$fld'");
        }
      }

      function getValues(&$array)
      {
        foreach($this->fieldsName as $fld)
          $array[$fld]=$this->data[$this->curPos][$fld];
      }

      function _addIndexItem(&$myIndex, $data, $position)
      {
        $p=0;
        $r=0;
        while (($p>=0) && (isset($myIndex[$p]))) {
          $r=$p;
          if ($data>$myIndex[$p][VALUE])
            $p=$myIndex[$p][RIGHT];
          else
            $p=$myIndex[$p][LEFT];
        }

        if ($r==$p) {
          $myIndex[$p][UPPER]=-1;
        } else {
          $p=count($myIndex);
          if ($data>$myIndex[$r][VALUE])
            $myIndex[$r][RIGHT]=$p;
          else
            $myIndex[$r][LEFT]=$p;
          $myIndex[$p][UPPER]=$r;
        }
        $myIndex[$p][VALUE]=$data;
        $myIndex[$p][LEFT]=-1;
        $myIndex[$p][RIGHT]=-1;
        $myIndex[$p][POSITION]=$position;
      }

      function _dumpIndex($myIndex, $p)
      {
        if ($myIndex[$p][LEFT]>=0)
          $this->_dumpIndex($myIndex, $myIndex[$p][LEFT]);
        echo $myIndex[$p][VALUE].' - '.$myIndex[$p][POSITION].'<br>';
        if ($myIndex[$p][RIGHT]>=0)
          $this->_dumpIndex($myIndex, $myIndex[$p][RIGHT]);
      }

      function _buildIndex($fieldName)
      {
        if (!in_array($fieldName,$this->indexes)) {
          // echo "criando indize para $fieldName<br>";
          $myIndex=array();
          for ($n=0; $n<$this->recCount(); $n++)
            $this->_addIndexItem($myIndex, $this->data[$n][$fieldName],$n);
          array_push($this->indexes, $fieldName);
          $this->indexes[$fieldName]=$myIndex;
        }
      }

      function _searchIndex($myIndex, $value, $p)
      {
        $ret=-1;
        if ($value>$myIndex[$p][VALUE]) {
          if ($myIndex[$p][RIGHT]>=0)
            $ret=$this->_searchIndex($myIndex, $value, $myIndex[$p][RIGHT]);
        } else if ($value<$myIndex[$p][VALUE]) {
          if ($myIndex[$p][LEFT]>=0)
            $ret=$this->_searchIndex($myIndex, $value, $myIndex[$p][LEFT]);
        } else if ($value==$myIndex[$p][VALUE]) {
          $ret=$myIndex[$p][POSITION];
        }
        return ($ret);
      }

      function locate($fieldName, $value)
      {
        global $dbTEXT_NO_ERROR, $dbTEXT_EOF, $dbTEXT_BOF;
        $pos=-1;

        $value=$this->unquote(strtoupper($value));

        $start=time();
        if ($this->recCount()>50) {
          $method='indexed';
          $this->_buildIndex($fieldName);
          $pos=$this->_searchIndex($this->indexes[$fieldName], $value,0);
        } else {
          $method='sequential';
          for($n=0; $n<$this->recCount(); $n++) {
            $auxValue=unquote(strtoupper($this->data[$n][$fieldName]));
            // echo "$n ($auxValue==$value?) <ul>".var_export($this->data[$n],true)."</ul>";
            if ($auxValue==$value) {
              $pos=$n;
              break;
            }
          }
        }
        $end=time();
        $wastedTime=$start-$end;
        if ($pos>-1) {
          $this->curPos=$pos;
          $ret=$dbTEXT_NO_ERROR;
        } else
          $ret=$dbTEXT_EOF;

        // echo "$method: $start ... $end = ".$wastedTime." ($pos [$fieldName=$value]) ret=$ret\n<br>";
        return $ret;
      }

      function countValues($fieldName, $value)
      {
        $ret=0;
        for($n=0; $n<$this->recCount(); $n++)
          if ($this->data[$n][$fieldName]==$value)
            $ret++;
        return $ret;
      }

      function commit($fake=false)
      {
        $ret = false;
        if (lock(basename($this->fileName))) {
          if (is_writable($this->fileName)) {
            if (!$fake)
              $f=fopen($this->fileName,"w");

            if (($fake) || ($f)) {
              $s='';

              foreach($this->fieldsName as $fld) {
                if ($s>'')
                  $s.=';';
                $s.=$fld;
              }

              if (!$fake)
                fwrite($f,"$s\n");

              foreach($this->data as $k) {
                $s='';
                $dataLength=0;
                foreach($this->fieldsName as $fld) {
                  if ($s>'')
                    $s.=';';
                  $auxValue=isset($k[$fld])?$k[$fld]:'';
                  $s.="'".$auxValue."'";
                  $dataLength+=strlen(trim($auxValue));
                }
                if ($dataLength>0) {
                  if (!$fake)
                    fwrite($f,"$s\n");
                  else
                    echo "$s<br>";
                }
              }
              if (!$fake) {
                fclose($f);
                $ret = true;
              }

              if (!$fake)
                $this->loadData();
            } else
              echo "<font color=#cc0000><ul>Failed to open $this->fileName with WRITE rights</ul></font>";
          } else {
            _dump("Can't write on '".$this->fileName."'");
          }
          unlock(basename($this->fileName));
        } else {
          _dump("Can't lock '".basename($this->fileName)."'");
        }
        return $ret;
      }

  }
?>
