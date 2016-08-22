<?php
  /*
    includes/yeapf.txtTools.php
    YeAPF 0.8.50-1 built on 2016-08-22 17:09 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-05-30 09:45:48 (-3 DST)
   */

  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  function txt_tryAdjustTextLine(&$tempColDef, $textLine, $columnBorderImprecision, $deslocateAllColumns, &$modif, &$out, &$filledFieldsPercentil) {

    $i=0;

    foreach($tempColDef as $colName=>$auxFieldLimits) {
      $fieldLimits=$tempColDef[$colName];
      if ($fieldLimits[1]==-1) {
        $fieldLimits[1]=strlen($textLine);
        $tempColDef[$colName][1]=$fieldLimits[1];
      }

      if ($i>0) {
        if (substr($textLine,$fieldLimits[0],$fieldLimits[1]-$fieldLimits[0])>'')

          $wordInLimits=((substr($textLine, $fieldLimits[0]-1,1)!=' ') && (substr($textLine, $fieldLimits[0],1)!=' '))?true:false;

          $charInDifuseArea=(strlen(trim(substr($textLine, $fieldLimits[0]-$columnBorderImprecision,$columnBorderImprecision+1)))<=$columnBorderImprecision-2);

          if ( $wordInLimits || $charInDifuseArea ) {
            $out.=substr($textLine, $fieldLimits[0]-1,1).' '.$fieldLimits[0].'..'.$fieldLimits[1]." ( $wordInLimits ) ( $charInDifuseArea )\n";
            $n=$fieldLimits[0]-$columnBorderImprecision;
            $priorStart=$tempColDef[$priorCol][0];
            while (($n>$priorStart) && (substr($textLine, $n, 1)!=' '))
              $n--;
            if ($n>$priorStart) {
              if (substr($textLine, $n, 1)==' ')
                $n++;
              $modif++;
              $offset=$tempColDef[$colName][0]-$n;
              $out.="Deslocating $offset bytes from $colName\n";
              $tempColDef[$priorCol][1]=$n-1;

              if (!$deslocateAllColumns) {
                while ((substr($textLine, $n, 1)==' ') && ($n<$tempColDef[$colName][1]))
                  $n++;
                $tempColDef[$colName][0]=$n;
              } else {
                $canChange=false;
                foreach($tempColDef as $aColName => $colLimits) {
                  if ($aColName==$colName)
                    $canChange=true;
                  if ($canChange) {
                    $tempColDef[$aColName][0]-=$offset;
                    if ($tempColDef[$aColName][1]>0)
                      $tempColDef[$aColName][1]-=$offset;
                  }
                }
              }
            }
          }
      }
      $i++;
      $priorCol=$colName;
    }

    $filledFieldsPercentil=0;
    $fieldCount=0;
    foreach($tempColDef as $colName=>$colLimits) {
      $fieldCount++;
      $value=trim(substr($textLine, $colLimits[0], $colLimits[1]-$colLimits[0]));
      if ($value>'')
        $filledFieldsPercentil++;
    }
    $filledFieldsPercentil=$filledFieldsPercentil * 100 / $fieldCount;
    $out.="Method (".intval($deslocateAllColumns).") $filledFieldsPercentil%\n";
  }

  /*
   * $deslocateAllColumns: -1 -> Yes, Deslocate all columns as one deslocates
   *                        0 -> Try by better percentual of field fillment
   *                       +1 -> Only deslocate one cell each time
   */


  function txt2csv_processGroupOfLines($fOut, &$recNo, $data, $header, $requiredColumns)
  {

    $outLine='';
    $recordLine=db_exportLine($data, $header, $requiredColumns);
    if ($recordLine>'') {
      if ($recNo==0)
        fwrite($fOut,"$header\n");
      fwrite($fOut,"$recordLine\n");

      $recNo++;
    }

  }

  function txt2csv($callBack, $txtFileName, $csvFileName,
                   $columnBorderImprecision=0,
                   $headerRecognizer='----', $headerLinesCount=1,
                   $footerRecognizer='Page', $footerLinesCount=1,
                   $keyColumns='*',
                   $requiredColumns='',
                   $exportableColumns='*',
                   $deslocateAllColumns = 0) {

    $debugLine= 557;
    $debugRecNo=108;

    _dumpY(128,0,"txtFilename = $txtFileName");

    if (file_exists($txtFileName)) {
      $f=fopen($txtFileName,'r');
      if ($f) {
        _dumpY(128,0,"csvFileName = $csvFileName");
        $fOut=fopen($csvFileName,'w');
        if ($fOut) {
          set_time_limit(0);

          // reconhecer a definição das colunas
          $columnDefinition=array();
          $colDescription=fgets($f);
          $lastCol='';
          $firstCol='';
          for($i=0; $i<strlen($colDescription); $i++) {
            $c=ord(substr($colDescription, $i, 1));
            if ((($c>=65) && ($c<=90)) || (($c>=97) && ($c<=122))) {
              $start=$i;
              $colName=substr($colDescription, $i, 1);
              do {
                $c=-1;
                $i++;
                if ($i<strlen($colDescription)) {
                  $c=ord(substr($colDescription, $i, 1));
                  if ((($c>=65) && ($c<=90)) || (($c>=97) && ($c<=122)) || (($c>=48) && ($c<=57)) || ($c==95)) {
                    $colName.=substr($colDescription, $i, 1);
                    $end=$i;
                  }
                  else
                    $c=-1;
                }
              } while ($c>-1);
              if ($lastCol>'')
                $columnDefinition[$lastCol][1]=$start;
              $canExport=(($exportableColumns=='*') || (strpos(strtoupper(" ;$exportableColumns;"),strtoupper(";$colName;"))>0))?true:false;
              $columnDefinition[$colName]=array($start, $end, $canExport);
              $lastCol=$colName;
              if ($firstCol=='')
                $firstCol=$colName;
            }
          }
          $columnDefinition[$lastCol][1]=-1;

          // percorrer o arquivo
          // reconhecer um registro (a presença da primeira coluna é o start do registro)
          $textLine='';
          $data=array();
          $firstGroupLine=true;
          $recNo=0;
          $header='';
          $sourceLine=1;
          while (!feof($f)) {
            if (trim($textLine)=='') {
              $textLine=fgets($f);
              $sourceLine++;
              if (!(strpos($textLine, $headerRecognizer)===FALSE)) {
                $n=0;
                while ($n<$headerLinesCount) {
                  $textLine=fgets($f);
                  $sourceLine++;
                  $n++;
                }
              }
              if (!(strpos($textLine, $footerRecognizer)===FALSE)) {
                $n=0;
                while ($n<$footerLinesCount) {
                  $textLine=fgets($f);
                  $sourceLine++;
                  $n++;
                }
              }
              $textLine=str_replace("\n","",$textLine);
              $textLine=str_replace("\r","",$textLine);
              if (trim($textLine)>'') {
                $tempData=array();
                if ($columnBorderImprecision>0) {
                  $priorCol='';

                  $modif=0;

                  $out="\n=======[ $sourceLine ]============================\n$textLine\n";
                  $auxLastColumn=0;
                  foreach($columnDefinition as $colName=>$auxFieldLimits) {
                    $auxLen=max(0,$auxFieldLimits[1]-$auxLastColumn-1);
                    $out.=substr($colName.str_repeat(' ',$auxLen),0,$auxLen).'^';
                    $auxLastColumn=$auxFieldLimits[1];
                  }
                  $out.="\n";

                  $tempColDef1=$columnDefinition;
                  $tempColDef2=$columnDefinition;
                  $filledFieldsPercentil1=0;
                  $filledFieldsPercentil2=0;
                  $modif1=0;
                  $modif2=0;
                  $out1='';
                  $out2='';

                  if ($deslocateAllColumns<=0)
                    txt_tryAdjustTextLine($tempColDef1, $textLine, $columnBorderImprecision, true, $modif1, $out1, $filledFieldsPercentil1);
                  if ($deslocateAllColumns>=0)
                    txt_tryAdjustTextLine($tempColDef2, $textLine, $columnBorderImprecision, false, $modif2, $out2, $filledFieldsPercentil2);

                  if ($filledFieldsPercentil2>$filledFieldsPercentil1) {
                    $tempColDef=$tempColDef2;
                    $out.=$out2;
                  } else {
                    $tempColDef=$tempColDef1;
                    $out.=$out1;
                  }


                  if ($modif1+$modif2>0)  {
                    foreach($tempColDef as $colName=>$fieldLimits)
                      if (($columnDefinition[$colName][0]!=$tempColDef[$colName][0]) || ($columnDefinition[$colName][1]!=$tempColDef[$colName][1])) {
                        $out.="\n$colName: ".$columnDefinition[$colName][0].'..'.$columnDefinition[$colName][1].' --> '.$tempColDef[$colName][0].'..'.$tempColDef[$colName][1];
                        $out.=" [".str_replace(' ','^',substr($textLine,$columnDefinition[$colName][0],$columnDefinition[$colName][1]-$columnDefinition[$colName][0]))."]  --> ";
                        $out.=" [".str_replace(' ','^',substr($textLine,$tempColDef[$colName][0],$tempColDef[$colName][1]-$tempColDef[$colName][0]))."]";
                      }
                      if (($sourceLine==$debugLine) || ($recNo==$debugRecNo)) {
                        $callBack(-1,$out);
                        // die();
                      }
                  }

                } else
                  $tempColDef=$columnDefinition;

                foreach($tempColDef as $colName=>$fieldLimits) {
                  if ($fieldLimits[1]==-1)
                    $fieldLimits[1]=strlen($textLine);
                  $tempData[$colName]=substr($textLine,$fieldLimits[0],$fieldLimits[1]-$fieldLimits[0]);
                }

                if ($sourceLine==$debugLine) {
                  foreach($tempData as $colName=>$colValue)
                    $callBack(-1,"\n$colName = [ $colValue ]");
                }
              }
            }

            if (trim($textLine)>'') {
              if ($keyColumns=='*') {
                $data=array();
                foreach($columnDefinition as $colName=>$fieldLimits)
                  if ($fieldLimits[2]==true)
                    $data[$colName]='';
                $dataLen=0;
                $colCount=0;
                foreach($tempData as $colName=>$value)
                  if (isset($data[$colName])) {
                    $data[$colName]=trim($value);
                    $dataLen+=strlen(trim($value));
                    $colCount++;
                  }

                if ($dataLen>0) {
                  $outLine='';
                  $recordLine=db_exportLine($data, $header, $requiredColumns);
                  if ($recordLine>'') {
                    if ($recNo==0)
                      fwrite($fOut,"$header\n");
                    fwrite($fOut,"$recordLine\n");

                    $recNo++;
                  }

                  $dataAvg=$dataLen / $colCount;
                  if ($dataAvg>8)
                    $callBack(-1,"[ $sourceLine -> $recNo ] $recordLine\n");
                }

                $textLine='';

              } else {
                if ($firstGroupLine) {
                  $data=array();
                  foreach($columnDefinition as $colName=>$fieldLimits)
                    if ($fieldLimits[2]==true)
                      $data[$colName]='';

                  foreach($tempData as $colName=>$value)
                    $data[$colName]=trim($value);

                  $firstGroupLine=false;
                  $textLine='';
                } else {
                  if (trim($tempData[$firstCol])=='') {
                    foreach($tempData as $colName=>$value)
                      if (isset($data[$colName]))
                        $data[$colName]=trim($data[$colName].' '.trim($value));
                    $textLine='';
                  } else {
                    txt2csv_processGroupOfLines($fOut, $recNo, $data, $header, $requiredColumns);
                    $firstGroupLine=true;
                  }
                }
              }
            }
          }
          /* process last group of lines */
          if (!$firstGroupLine) {
            txt2csv_processGroupOfLines($fOut, $recNo, $data, $header, $requiredColumns);
          }
          fclose($fOut);
          fclose($f);
        } else
          $callBack(2,"File '$csvFileName' cannot be oppened in 'write-mode'");
      } else
        $callBack(2,"File '$txtFileName' cannot be oppened in 'read-mode'");
    } else
      $callBack(2,"File '$txtFileName' not found");
  }
?>
